<?php

namespace Database\Seeders;

use App\Models\Card;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Pulls all Prismatic Evolutions cards from pokemontcg.io v2.
 * Set ID: sv8pt5 — 180 cards total.
 *
 * Free, no auth required. If the API is unreachable, the seeder
 * exits gracefully so the build doesn't break.
 */
class CardSeeder extends Seeder
{
    private const API_URL = 'https://api.pokemontcg.io/v2/cards';
    private const SET_QUERY = 'set.id:sv8pt5';
    private const PAGE_SIZE = 60;

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
            $this->command?->info("Fetching Prismatic Evolutions page {$page}…");

            try {
                $response = Http::timeout(30)
                    ->retry(2, 1000)
                    ->get(self::API_URL, [
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
                $marketUsd = $this->extractMarketPrice($c['tcgplayer']['prices'] ?? []);
                $marketIdr = $marketUsd > 0
                    ? $this->roundToNearest($marketUsd * self::USD_TO_IDR, 500)
                    : 0;
                $priceIdr = $marketIdr > 0
                    ? $this->roundToNearest($marketIdr * self::HOUSE_MARKUP, 500)
                    : self::PRICE_FLOOR_IDR;

                Card::updateOrCreate(
                    ['api_id' => $c['id']],
                    [
                        'name'        => $c['name'] ?? 'Unknown',
                        'slug'        => Str::slug(($c['name'] ?? 'card') . '-' . ($c['number'] ?? $c['id'])),
                        'supertype'   => $c['supertype'] ?? 'Pokémon',
                        'subtypes'    => $c['subtypes'] ?? [],
                        'hp'          => $c['hp'] ?? null,
                        'types'       => $c['types'] ?? [],
                        'rarity'      => $c['rarity'] ?? null,
                        'regulation_mark' => $c['regulationMark'] ?? null,
                        'number'      => $c['number'] ?? null,
                        'set_id'      => $c['set']['id'] ?? 'sv8pt5',
                        'set_name'    => $c['set']['name'] ?? 'Prismatic Evolutions',
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

        $this->command?->info("Imported {$imported} Prismatic Evolutions cards.");
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
}
