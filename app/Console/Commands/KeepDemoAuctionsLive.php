<?php

namespace App\Console\Commands;

use App\Models\Auction;
use App\Models\User;
use Illuminate\Console\Command;

/**
 * Keeps the demo auction floor populated so the home "Live auctions"
 * panel and the /auctions page never go empty between seeds.
 *
 * Seeded auctions use windows relative to seed time (e.g. "ends in 18h"),
 * so a day or two after seeding they all expire and the public pages drop
 * to the empty state. On a schedule this command settles the normal
 * auction lifecycle, then rolls recently-ended demo lots forward so a
 * target number stay live — keeping their bids, current price and leader
 * so the panels still show real highest-bid data with a live timer.
 *
 * Scope: only auctions sold by the demo seller (admin@poketrade.test).
 * Genuine auctions created under any other account are left untouched.
 */
class KeepDemoAuctionsLive extends Command
{
    protected $signature = 'auctions:keep-live {--target=4 : How many demo auctions to keep live}';

    protected $description = 'Roll recently-ended demo auctions forward so the live-auction panels never go empty';

    private const DEMO_SELLER_EMAIL = 'admin@poketrade.test';

    public function handle(): int
    {
        // Normal lifecycle first: promote due scheduled lots, retire expired live ones.
        Auction::settleDueStatuses();

        $seller = User::where('email', self::DEMO_SELLER_EMAIL)->first();
        if (! $seller) {
            $this->warn('Demo seller not found — nothing to refresh. Seed the database first.');

            return self::SUCCESS;
        }

        $target = max(1, (int) $this->option('target'));

        $liveCount = Auction::where('seller_id', $seller->id)->where('status', 'live')->count();
        if ($liveCount >= $target) {
            $this->info("Live demo auctions: {$liveCount} (target {$target}) — nothing to do.");

            return self::SUCCESS;
        }

        // Revive the most-recently-ended demo lots first so the floor cycles
        // through the same cards rather than always resurrecting the oldest.
        $revivable = Auction::where('seller_id', $seller->id)
            ->where('status', 'ended')
            ->orderByDesc('ends_at')
            ->limit($target - $liveCount)
            ->get();

        foreach ($revivable as $auction) {
            $this->revive($auction);
        }

        $nowLive = Auction::where('seller_id', $seller->id)->where('status', 'live')->count();
        $this->info("Revived {$revivable->count()} demo auction(s); {$nowLive} now live (target {$target}).");

        if ($nowLive < $target) {
            $this->warn('Not enough demo lots to reach target — re-seed with: php artisan db:seed --class=AuctionSeeder');
        }

        return self::SUCCESS;
    }

    /**
     * Reopen an ended demo auction with a fresh window. Bids, current_bid
     * and current_leader survive, so the panels keep showing real prices;
     * the winner snapshot is cleared because the lot is in play again.
     */
    private function revive(Auction $auction): void
    {
        $auction->forceFill([
            'starts_at'          => now()->subHours(random_int(1, 12)),
            'ends_at'            => now()->addHours(random_int(2, 48)),
            'status'             => 'live',
            'winner_id'          => null,
            'winning_amount'     => null,
            'winner_paid_at'     => null,
            'refund_status'      => 'none',
            'refund_resolved_at' => null,
        ])->save();

        // Re-stamp the surviving bids across the new window so the bid
        // history reads plausibly (cheapest oldest → current leader newest),
        // mirroring how AuctionSeeder spaces its bids.
        $bids = $auction->bids()->orderBy('amount')->get()->values();
        if ($bids->isEmpty()) {
            return;
        }

        $start = $auction->starts_at;
        $spanMinutes = max(1, $start->diffInMinutes(now()));
        $count = $bids->count();

        foreach ($bids as $i => $bid) {
            $placedAt = $start->copy()->addMinutes(
                (int) round($spanMinutes * ($i + 1) / ($count + 1))
            );
            $bid->created_at = $placedAt;
            $bid->updated_at = $placedAt;
            $bid->save();
        }
    }
}
