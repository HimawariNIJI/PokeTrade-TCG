<?php

namespace Database\Seeders;

use App\Models\Auction;
use App\Models\Bid;
use App\Models\Card;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds a handful of demo auctions with realistic dummy bids so the public
 * /auctions pages and the admin console have something to show.
 *
 * Safe to re-run: it clears existing auctions/bids first.
 */
class AuctionSeeder extends Seeder
{
    public function run(): void
    {
        // Need at least one Card to attach auctions to — without this guard a
        // failed CardSeeder (e.g. pokemontcg.io unreachable from CI) cascades
        // into a null-id crash here and takes down `migrate --seed`.
        if (! Card::query()->exists()) {
            $this->command?->warn('No cards in database — skipping AuctionSeeder.');
            return;
        }

        // Start clean so the seeder is idempotent.
        Bid::query()->delete();
        Auction::query()->delete();

        $seller = User::firstOrCreate(
            ['email' => 'admin@poketrade.test'],
            [
                'name' => 'PokeTrade Admin',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ADMIN,
                'email_verified_at' => now(),
            ]
        );

        // A pool of dummy bidders.
        $bidders = collect([
            'misty.waterflower', 'brock.pewter', 'gary.oak', 'may.petalburg',
            'dawn.twinleaf', 'serena.kalos', 'iris.opelucid', 'cynthia.champ',
        ])->map(fn ($handle) => User::firstOrCreate(
            ['email' => $handle.'@poketrade.test'],
            [
                'name' => $handle,
                'password' => Hash::make('password'),
                'role' => User::ROLE_CUSTOMER,
                'email_verified_at' => now(),
            ]
        ));

        // Prefer cards with a large image so the detail page looks good.
        $cards = Card::query()->whereNotNull('image_large')->inRandomOrder()->limit(6)->get();
        if ($cards->count() < 6) {
            $cards = $cards->merge(Card::query()->inRandomOrder()->limit(6)->get())->unique('id')->take(6)->values();
        }

        // [status, start (hours from now), end (hours from now), number of bids]
        $plan = [
            ['live',       -6,  18, 7],
            ['live',       -3,   5, 5],
            ['live',       -1,   2, 9],
            ['live',      -12,  36, 3],
            ['scheduled',  24,  72, 0],
            ['ended',     -72, -10, 6],
        ];

        foreach ($plan as $i => [$status, $startH, $endH, $bidCount]) {
            $card = $cards[$i] ?? Card::query()->inRandomOrder()->first();

            $starting  = 100000 + ($i * 50000);
            $increment = 25000;

            $auction = Auction::create([
                'card_id'       => $card->id,
                'seller_id'     => $seller->id,
                'starting_bid'  => $starting,
                'current_bid'   => 0,
                'bid_increment' => $increment,
                'buy_now_price' => $starting * 20,
                'starts_at'     => now()->addHours($startH),
                'ends_at'       => now()->addHours($endH),
                'status'        => $status,
            ]);

            // Scheduled auctions have not opened — no bids, no current bid.
            if ($bidCount === 0) {
                continue;
            }

            // Spread bids evenly across the bidding window: from the start up
            // to the end (ended auctions) or up to now (live auctions).
            $windowStart = now()->addHours($startH);
            $windowEnd   = $status === 'ended' ? now()->addHours($endH) : now();
            $spanMinutes = max(1, $windowStart->diffInMinutes($windowEnd));

            $amount = $starting;
            $leader = null;

            for ($b = 0; $b < $bidCount; $b++) {
                $bidder = $bidders->random();
                $amount += $increment * rand(1, 4);
                $placedAt = $windowStart->copy()->addMinutes(
                    (int) round($spanMinutes * ($b + 1) / ($bidCount + 1))
                );

                $bid = Bid::create([
                    'auction_id' => $auction->id,
                    'user_id'    => $bidder->id,
                    'amount'     => $amount,
                    'status' => Bid::STATUS_PAID,
                    'paid_at' => now(),
                    'order_id' => fake()->uuid(),
                ]);
                $bid->created_at = $placedAt;
                $bid->updated_at = $placedAt;
                $bid->save();

                $leader = $bidder;
            }

            // The final (highest) bid is the current bid and its bidder leads.
            $auction->update([
                'current_bid'       => $amount,
                'current_leader_id' => $leader?->id,
                'winning_amount'    => $status === 'ended' ? $amount : null,
            ]);
        }
        $showcaseMonths = [
            // Jan 2026: ~Rp 20 Juta (approx 28%)
            ['month' => 1, 'amount' => 20000000],
            // Feb 2026: ~Rp 28 Juta (approx 39%)
            ['month' => 2, 'amount' => 28000000],
            // Mar 2026: ~Rp 24 Juta (approx 33%)
            ['month' => 3, 'amount' => 24000000],
            // Apr 2026: ~Rp 32 Juta (approx 45%)
            ['month' => 4, 'amount' => 32000000],
            // May 2026: ~Rp 26 Juta (approx 36%)
            ['month' => 5, 'amount' => 26000000],
        ];

        foreach ($showcaseMonths as $data) {
            $card = Card::query()->inRandomOrder()->first();
            if (!$card) continue;
            
            $bidder = $bidders->random();
            $month = $data['month'];
            
            // Set lelang agar selesai pada tanggal 20 di masing-masing bulan
            $auctionEnd = \Carbon\Carbon::create(2026, $month, 20, 15, 0, 0);
            $auctionStart = $auctionEnd->copy()->subDays(3);
            
            $auction = Auction::create([
                'card_id'       => $card->id,
                'seller_id'     => $seller->id,
                'starting_bid'  => $data['amount'] - 5000000,
                'current_bid'   => $data['amount'],
                'winning_amount'=> $data['amount'],
                'current_leader_id' => $bidder->id,
                'winner_id'     => $bidder->id,
                'winner_paid_at'=> $auctionEnd->copy()->addHours(2),
                'bid_increment' => 500000,
                'buy_now_price' => $data['amount'] * 2,
                'starts_at'     => $auctionStart,
                'ends_at'       => $auctionEnd,
                'status'        => 'ended',
                'created_at'    => $auctionStart,
                'updated_at'    => $auctionEnd->copy()->addHours(2),
            ]);

            // Tambahkan "winning bid" yang dibayarkan agar terekam sebagai revenue
            $bid = Bid::create([
                'auction_id' => $auction->id,
                'user_id'    => $bidder->id,
                'amount'     => $data['amount'],
                // Tetap menggunakan format bawaan seeder-mu
                'status'     => Bid::STATUS_PAID, 
                'paid_at'    => $auctionEnd->copy()->addHours(2),
                'order_id'   => fake()->uuid(),
                // Tambahan jika ada refactor kolom pembayaran Midtrans sebelumnya:
                'payment_status' => 'paid', 
            ]);
            
            $bid->created_at = $auctionEnd->copy()->subMinutes(10);
            $bid->updated_at = $auctionEnd->copy()->addHours(2);
            $bid->save();
        }

        $this->command?->info('Seeded '.count($plan).' demo auctions with dummy bids.');
    }
}
