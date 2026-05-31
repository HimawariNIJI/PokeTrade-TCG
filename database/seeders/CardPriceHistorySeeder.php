<?php

namespace Database\Seeders;

use App\Models\Card;
use App\Models\CardPriceHistory;
use Illuminate\Database\Seeder;

/**
 * Backfills ~30 days of plausible price history so the tracker
 * charts are useful from day one. These points are synthetic demo
 * data — real snapshots accrue daily via `cards:refresh-prices`.
 *
 * Idempotent: reruns overwrite the same (card, date) rows.
 */
class CardPriceHistorySeeder extends Seeder
{
    private const DAYS = 30;

    public function run(): void
    {
        $cards = Card::query()
            ->where('market_price', '>', 0)
            ->get(['id', 'market_price']);

        if ($cards->isEmpty()) {
            $this->command?->warn('No priced cards to backfill — run CardSeeder first.');

            return;
        }

        foreach ($cards as $card) {
            foreach ($this->walk((float) $card->market_price) as $date => $price) {
                // Don't clobber rows already accrued by RefreshCardPrices —
                // those are real snapshots and must survive a reseed.
                $existing = CardPriceHistory::where('card_id', $card->id)
                    ->where('recorded_at', $date)
                    ->first();

                if ($existing && ! $existing->is_synthetic) {
                    continue;
                }

                CardPriceHistory::updateOrCreate(
                    ['card_id' => $card->id, 'recorded_at' => $date],
                    ['market_price' => $price, 'is_synthetic' => true],
                );
            }
        }

        $this->command?->info(
            'Backfilled ~' . self::DAYS . " days of price history for {$cards->count()} cards."
        );
    }

    /**
     * Random walk backwards from today's price, returning
     * [Y-m-d => price] oldest first. The final (today) point is
     * exactly the card's current market value.
     */
    private function walk(float $current): array
    {
        $rows = [];
        $price = $current;

        for ($daysAgo = 0; $daysAgo < self::DAYS; $daysAgo++) {
            $date = today()->subDays($daysAgo)->toDateString();
            $rows[$date] = max(1, round($price));

            // Step one day further into the past, ±4.5% per day.
            $drift = random_int(-450, 450) / 10000;
            $price = $price / (1 + $drift);
        }

        return array_reverse($rows, true);
    }
}
