<?php

namespace Database\Seeders;

use App\Models\Card;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Pulls every Standard-legal Pokémon TCG card from pokemontcg.io v2.
 *
 * Standard = cards with regulation mark H / I / J (2026 rotation),
 * plus basic Energy cards from the sve set (always legal).
 *
 * If the API is unreachable, the seeder exits gracefully so the
 * build doesn't break.
 */
class CardSeeder extends Seeder
{
    private const API_URL = 'https://api.pokemontcg.io/v2/cards';
    private const SET_QUERY = '(regulationMark:H OR regulationMark:I OR regulationMark:J) OR set.id:sve';
    private const PAGE_SIZE = 250;

    /** USD → IDR conversion rate. Adjust if rates shift dramatically. */
    private const USD_TO_IDR = 16000;

    /** Markup applied over market price for our house listings. */
    private const HOUSE_MARKUP = 1.10;

    /** Floor price for cards with no available market data (Rp 5.000). */
    private const PRICE_FLOOR_IDR = 5000;

    public function run(): void
    {
        $page = 1;
        $imported = 0;
        $featuredCount = 0;

        do {
            $this->command?->info("Fetching Standard cards page {$page}…");

            try {
                $request = Http::timeout(30)->retry(2, 1000);

                if ($apiKey = config('services.pokemontcg.key')) {
                    $request = $request->withHeaders(['X-Api-Key' => $apiKey]);
                }

                $response = $request->get(self::API_URL, [
                    'q' => self::SET_QUERY,
                    'page' => $page,
                    'pageSize' => self::PAGE_SIZE,
                ]);
            } catch (\Throwable $e) {
                $this->command?->warn("API unreachable: {$e->getMessage()}. Stopping seeder.");
                return;
            }

            if (! $response->successful()) {
                $this->command?->warn("API returned {$response->status()}. Stopping seeder.");
                return;
            }

            $payload = $response->json();
            $cards = $payload['data'] ?? [];

            if (empty($cards)) {
                break;
            }

            foreach ($cards as $c) {
                $c = $this->renameColorlessToNormal($c);
                $marketUsd = $this->extractMarketPrice($c['tcgplayer']['prices'] ?? []);
                $marketIdr = $marketUsd > 0
                    ? $this->roundToNearest($marketUsd * self::USD_TO_IDR, 500)
                    : 0;
                $priceIdr = $marketIdr > 0
                    ? $this->roundToNearest($marketIdr * self::HOUSE_MARKUP, 500)
                    : self::PRICE_FLOOR_IDR;

                $setId = $c['set']['id'] ?? 'unknown';

                Card::updateOrCreate(
                    ['api_id' => $c['id']],
                    [
                        'name'        => $c['name'] ?? 'Unknown',
                        'slug'        => Str::slug($setId . '-' . ($c['name'] ?? 'card') . '-' . ($c['number'] ?? $c['id'])),
                        'supertype'   => $c['supertype'] ?? 'Pokémon',
                        'subtypes'    => $c['subtypes'] ?? [],
                        'hp'          => $c['hp'] ?? null,
                        'types'       => $c['types'] ?? [],
                        'rarity'      => $c['rarity'] ?? null,
                        'regulation_mark' => $c['regulationMark'] ?? null,
                        'number'      => $c['number'] ?? null,
                        'set_id'      => $setId,
                        'set_name'    => $c['set']['name'] ?? 'Unknown Set',
                        'set_series'  => $c['set']['series'] ?? 'Scarlet & Violet',
                        'national_pokedex_numbers' => $c['nationalPokedexNumbers'] ?? [],
                        'image_small' => $c['images']['small'] ?? null,
                        'image_large' => $c['images']['large'] ?? null,
                        'attacks'     => $c['attacks'] ?? [],
                        'weaknesses'  => $c['weaknesses'] ?? [],
                        'resistances' => $c['resistances'] ?? [],
                        'retreat_cost' => $c['retreatCost'] ?? [],
                        'flavor_text' => $c['flavorText'] ?? null,
                        'artist'      => $c['artist'] ?? null,
                        'price'       => $priceIdr,
                        'market_price' => $marketIdr,
                        'stock'       => random_int(0, 25),
                        'featured'    => $featuredCount < 6 && $this->isHighlightRarity($c['rarity'] ?? '')
                            ? (++$featuredCount && true)
                            : false,
                    ]
                );
                $imported++;
            }

            $totalCount = $payload['totalCount'] ?? 0;
            $page++;
        } while ($imported < $totalCount && count($cards) === self::PAGE_SIZE);

        $this->command?->info("Imported {$imported} Standard-legal cards.");
    }

    private function extractMarketPrice(array $prices): float
    {
        // Prefer normal, then holofoil, then reverseHolofoil
        foreach (['normal', 'holofoil', 'reverseHolofoil', '1stEditionHolofoil'] as $variant) {
            $market = $prices[$variant]['market'] ?? null;
            if (is_numeric($market) && $market > 0) {
                return (float) $market;
            }
        }
        return 0.0;
    }

    private function isHighlightRarity(string $rarity): bool
    {
        return str_contains(strtolower($rarity), 'illustration')
            || str_contains(strtolower($rarity), 'special')
            || str_contains(strtolower($rarity), 'hyper')
            || str_contains(strtolower($rarity), 'ultra');
    }

    /** Round an IDR amount to the nearest multiple (e.g. 500 or 1000). */
    private function roundToNearest(float $amount, int $step): int
    {
        return (int) (round($amount / $step) * $step);
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
