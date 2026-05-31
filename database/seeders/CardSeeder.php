<?php

namespace Database\Seeders;

use App\Models\Card;
use App\Support\CardPricing;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Pulls every Standard-legal Pokémon TCG card from pokemontcg.io v2.
 *
 * Standard = cards with regulation mark H / I / J (2026 rotation),
 * plus basic Energy cards from the sve set (always legal).
 *
 * Uses batched upserts (one query per API page) so this runs in
 * single-digit seconds rather than minutes — important because the
 * admin Refresh-from-API button calls this synchronously from a
 * web request, where nginx will 504 after ~60s.
 *
 * On existing rows we deliberately preserve stock + featured so admin
 * overrides survive a refresh; only API-sourced fields are overwritten.
 *
 * If the API is unreachable, the seeder exits gracefully so the
 * build doesn't break.
 */
class CardSeeder extends Seeder
{
    private const API_URL = 'https://api.pokemontcg.io/v2/cards';
    private const SET_QUERY = '(regulationMark:H OR regulationMark:I OR regulationMark:J) OR set.id:sve';
    private const PAGE_SIZE = 250;
    private const REQUEST_TIMEOUT = 45;
    private const FIXTURE_PATH = __DIR__ . '/fixtures/cards.json';

    /** Columns rewritten from the API on every refresh. Excludes stock/featured. */
    private const REFRESH_COLUMNS = [
        'name', 'slug', 'supertype', 'subtypes', 'hp', 'types',
        'rarity', 'regulation_mark', 'number',
        'set_id', 'set_name', 'set_series',
        'national_pokedex_numbers',
        'image_small', 'image_large',
        'attacks', 'abilities', 'weaknesses', 'resistances', 'retreat_cost',
        'flavor_text', 'evolves_from', 'evolves_to', 'artist',
        'price', 'market_price',
        'updated_at',
    ];

    public function run(): void
    {
        // Seed "featured" allocation from what already exists so reruns don't pile on extras.
        $featuredCount = Card::where('featured', true)->count();
        $now = now();
        // Y-m-d string so it matches what CardPriceHistorySeeder writes — the
        // column is a DATE, and storing a Carbon (datetime) makes updateOrCreate
        // miss the existing row and then collide on the unique index.
        $today = today()->toDateString();
        $apiKey = config('services.pokemontcg.key');

        // Page 1 sequentially so we can read totalCount and size the parallel batch.
        // If the live API is unreachable, fall back to the bundled fixture so
        // dependent seeders (auctions, community collections, the Playwright
        // e2e suite) still have a usable Card table to work against.
        $first = $this->fetchPage(1, $apiKey);
        if (! $first) {
            $allCards = $this->loadFixture();
            if (empty($allCards)) {
                return;
            }
            $totalCount = count($allCards);
            $totalPages = 1;
        } else {
            $firstPayload = $first->json();
            $allCards = $firstPayload['data'] ?? [];
            $totalCount = $firstPayload['totalCount'] ?? count($allCards);

            if (empty($allCards)) {
                $this->command?->info('No cards returned from API.');
                return;
            }

            // Remaining pages fetched concurrently — this is the big win vs. the
            // previous sequential loop, which was taking ~8 × API latency and
            // tripping nginx's 60s fastcgi_read_timeout from the admin refresh button.
            $totalPages = (int) ceil($totalCount / self::PAGE_SIZE);
            if ($totalPages > 1) {
                $this->command?->info("Fetching pages 2–{$totalPages} in parallel…");
                $responses = Http::pool(function (\Illuminate\Http\Client\Pool $pool) use ($totalPages, $apiKey) {
                    return array_map(function ($page) use ($pool, $apiKey) {
                        $req = $pool->as("page_{$page}")->timeout(self::REQUEST_TIMEOUT)->retry(2, 750);
                        if ($apiKey) {
                            $req = $req->withHeaders(['X-Api-Key' => $apiKey]);
                        }
                        return $req->get(self::API_URL, [
                            'q' => self::SET_QUERY,
                            'page' => $page,
                            'pageSize' => self::PAGE_SIZE,
                        ]);
                    }, range(2, $totalPages));
                });

                foreach ($responses as $key => $res) {
                    if (! $res instanceof \Illuminate\Http\Client\Response || ! $res->successful()) {
                        $this->command?->warn("Skipping {$key} (no usable response).");
                        continue;
                    }
                    $allCards = array_merge($allCards, $res->json('data') ?? []);
                }
            }
        }

        // Batched upserts in 250-card chunks keep the SQL payload manageable
        // (MySQL max_allowed_packet, SQLite expression-tree depth, etc.).
        $imported = 0;
        foreach (array_chunk($allCards, self::PAGE_SIZE) as $chunk) {
            $rows = $this->buildRows($chunk, $featuredCount, $now);
            Card::upsert($rows, ['api_id'], self::REFRESH_COLUMNS);
            $this->appendPriceHistory($rows, $today, $now);
            $imported += count($rows);
        }

        $this->command?->info("Imported {$imported} Standard-legal cards.");
    }

    private function fetchPage(int $page, ?string $apiKey): ?\Illuminate\Http\Client\Response
    {
        try {
            $req = Http::timeout(self::REQUEST_TIMEOUT)->retry(2, 750);
            if ($apiKey) {
                $req = $req->withHeaders(['X-Api-Key' => $apiKey]);
            }
            $response = $req->get(self::API_URL, [
                'q' => self::SET_QUERY,
                'page' => $page,
                'pageSize' => self::PAGE_SIZE,
            ]);
        } catch (\Throwable $e) {
            $this->command?->warn("API page {$page} unreachable: {$e->getMessage()}");
            return null;
        }

        if (! $response->successful()) {
            $this->command?->warn("API page {$page} returned {$response->status()}.");
            return null;
        }

        return $response;
    }

    /**
     * Bundled snapshot used when the live API is unreachable.
     * Why: CI runners frequently see slow / failing requests to pokemontcg.io,
     * and silent-bailing leaves the Card table empty, which crashes downstream
     * seeders (AuctionSeeder) and the Playwright suite.
     */
    private function loadFixture(): array
    {
        if (! is_file(self::FIXTURE_PATH)) {
            $this->command?->warn('Live API unavailable and no bundled fixture found.');
            return [];
        }

        $contents = file_get_contents(self::FIXTURE_PATH);
        $decoded = $contents === false ? null : json_decode($contents, true);

        if (! is_array($decoded) || empty($decoded)) {
            $this->command?->warn('Bundled card fixture is empty or malformed.');
            return [];
        }

        $this->command?->info('Live API unreachable — seeding from bundled fixture ('.count($decoded).' cards).');
        return $decoded;
    }

    private function buildRows(array $cards, int &$featuredCount, $now): array
    {
        $rows = [];
        foreach ($cards as $c) {
            $c = $this->renameColorlessToNormal($c);
            $marketIdr = CardPricing::marketPriceIdr($c['tcgplayer']['prices'] ?? []);
            $priceIdr = CardPricing::housePriceIdr($marketIdr);

            $setId = $c['set']['id'] ?? 'unknown';
            $isFeatured = $featuredCount < 6 && $this->isHighlightRarity($c['rarity'] ?? '');
            if ($isFeatured) {
                $featuredCount++;
            }

            // Bulk upsert bypasses Eloquent casts, so JSON columns are encoded by hand.
            $rows[] = [
                'api_id'      => $c['id'],
                'name'        => $c['name'] ?? 'Unknown',
                'slug'        => Str::slug($setId . '-' . ($c['name'] ?? 'card') . '-' . ($c['number'] ?? $c['id'])),
                'supertype'   => $c['supertype'] ?? 'Pokémon',
                'subtypes'    => json_encode($c['subtypes'] ?? []),
                'hp'          => $c['hp'] ?? null,
                'types'       => json_encode($c['types'] ?? []),
                'rarity'      => $c['rarity'] ?? null,
                'regulation_mark' => $c['regulationMark'] ?? null,
                'number'      => $c['number'] ?? null,
                'set_id'      => $setId,
                'set_name'    => $c['set']['name'] ?? 'Unknown Set',
                'set_series'  => $c['set']['series'] ?? 'Scarlet & Violet',
                'national_pokedex_numbers' => json_encode($c['nationalPokedexNumbers'] ?? []),
                'image_small' => $c['images']['small'] ?? null,
                'image_large' => $c['images']['large'] ?? null,
                'attacks'     => json_encode($c['attacks'] ?? []),
                'abilities'   => json_encode($c['abilities'] ?? []),
                'weaknesses'  => json_encode($c['weaknesses'] ?? []),
                'resistances' => json_encode($c['resistances'] ?? []),
                'retreat_cost' => json_encode($c['retreatCost'] ?? []),
                'flavor_text' => $c['flavorText'] ?? null,
                'evolves_from' => $c['evolvesFrom'] ?? null,
                'evolves_to'  => json_encode($c['evolvesTo'] ?? []),
                'artist'      => $c['artist'] ?? null,
                'price'       => $priceIdr,
                'market_price' => $marketIdr,
                'stock'       => random_int(0, 25), // only used when INSERTing
                'featured'    => $isFeatured,        // only used when INSERTing
                'created_at'  => $now,
                'updated_at'  => $now,
            ];
        }
        return $rows;
    }

    /**
     * Append today's market-price snapshot for the rows we just upserted.
     * One query per page instead of one per card.
     */
    private function appendPriceHistory(array $rows, $today, $now): void
    {
        $apiIds = array_column($rows, 'api_id');
        $idMap = Card::whereIn('api_id', $apiIds)->pluck('id', 'api_id');

        $history = [];
        foreach ($rows as $row) {
            $cardId = $idMap[$row['api_id']] ?? null;
            // Skip zero-price snapshots — keep the last known price intact rather
            // than letting a missing TCGplayer entry drag the tracker chart to 0.
            if (! $cardId || $row['market_price'] <= 0) {
                continue;
            }
            $history[] = [
                'card_id'      => $cardId,
                'market_price' => $row['market_price'],
                'recorded_at'  => $today,
                'is_synthetic' => false,
                'created_at'   => $now,
                'updated_at'   => $now,
            ];
        }

        if (empty($history)) {
            return;
        }

        DB::table('card_price_history')->upsert(
            $history,
            ['card_id', 'recorded_at'],
            ['market_price', 'is_synthetic', 'updated_at']
        );
    }

    private function isHighlightRarity(string $rarity): bool
    {
        return str_contains(strtolower($rarity), 'illustration')
            || str_contains(strtolower($rarity), 'special')
            || str_contains(strtolower($rarity), 'hyper')
            || str_contains(strtolower($rarity), 'ultra');
    }

    /** Remap the TCG "Colorless" type to "Normal" across all relevant fields. */
    private function renameColorlessToNormal(array $c): array
    {
        $swap = static function ($value) use (&$swap) {
            if (is_string($value)) {
                return $value === 'Colorless' ? 'Normal' : $value;
            }
            if (is_array($value)) {
                return array_map($swap, $value);
            }
            return $value;
        };

        foreach (['types', 'subtypes', 'retreatCost'] as $k) {
            if (isset($c[$k]) && is_array($c[$k])) {
                $c[$k] = $swap($c[$k]);
            }
        }
        foreach (['weaknesses', 'resistances', 'attacks'] as $k) {
            if (isset($c[$k]) && is_array($c[$k])) {
                $c[$k] = $swap($c[$k]);
            }
        }
        return $c;
    }
}
