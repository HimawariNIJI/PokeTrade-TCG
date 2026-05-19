<?php

namespace App\Console\Commands;

use App\Models\Card;
use App\Support\CardPricing;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Refreshes each card's live market_price from pokemontcg.io
 * (which embeds TCGplayer market data).
 *
 * Scope: market_price only. The house `price` is left untouched so
 * admin overrides survive — see App\Http\Controllers\Admin\CardController.
 * New cards are NOT created here; that remains CardSeeder's job.
 */
class RefreshCardPrices extends Command
{
    protected $signature = 'cards:refresh-prices';

    protected $description = "Refresh each card's market_price from pokemontcg.io (TCGplayer data)";

    private const API_URL = 'https://api.pokemontcg.io/v2/cards';
    private const SET_QUERY = '(regulationMark:H OR regulationMark:I OR regulationMark:J) OR set.id:sve';
    private const PAGE_SIZE = 250;

    public function handle(): int
    {
        $page = 1;
        $seen = 0;
        $updated = 0;
        $missing = 0;

        do {
            $this->info("Fetching cards page {$page}…");

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
                $this->error("API unreachable: {$e->getMessage()}. Aborting.");

                return self::FAILURE;
            }

            if (! $response->successful()) {
                $this->error("API returned {$response->status()}. Aborting.");

                return self::FAILURE;
            }

            $payload = $response->json();
            $cards = $payload['data'] ?? [];

            if (empty($cards)) {
                break;
            }

            foreach ($cards as $c) {
                $seen++;

                $card = Card::where('api_id', $c['id'] ?? null)->first();
                if (! $card) {
                    $missing++;
                    continue;
                }

                $marketIdr = CardPricing::marketPriceIdr($c['tcgplayer']['prices'] ?? []);

                // Keep the stored value when the API has no usable price,
                // rather than wiping a known price down to zero.
                if ($marketIdr <= 0) {
                    continue;
                }

                if ((int) $card->market_price !== $marketIdr) {
                    $card->update(['market_price' => $marketIdr]);
                    $updated++;
                }

                // Append today's snapshot to the price-tracker history —
                // one row per card per day, so a rerun just overwrites it.
                $card->priceHistory()->updateOrCreate(
                    ['recorded_at' => today()],
                    ['market_price' => $marketIdr],
                );
            }

            $totalCount = $payload['totalCount'] ?? 0;
            $page++;
        } while ($seen < $totalCount && count($cards) === self::PAGE_SIZE);

        $summary = "{$seen} API cards seen, {$updated} prices updated, {$missing} not in local catalogue.";
        $this->info("Done. {$summary}");
        Log::info("cards:refresh-prices — {$summary}");

        return self::SUCCESS;
    }
}
