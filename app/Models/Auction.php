<?php

namespace App\Models;

use App\Notifications\AuctionRefundedNotification;
use App\Notifications\AuctionWonNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Auction extends Model
{
    use SoftDeletes;

    public const STATUSES = ['scheduled', 'live', 'ended', 'cancelled'];
    public const REFUND_STATUSES = ['none', 'requested', 'approved', 'rejected'];

    protected $fillable = [
        'card_id', 'seller_id', 'current_leader_id',
        'starting_bid', 'current_bid', 'bid_increment', 'buy_now_price',
        'starts_at', 'ends_at', 'status', 'is_highlighted',
        'winner_id', 'winning_amount', 'winner_paid_at',
        'refund_status', 'refund_resolved_at',
    ];

    protected $casts = [
        'starting_bid' => 'decimal:2',
        'current_bid' => 'decimal:2',
        'bid_increment' => 'decimal:2',
        'buy_now_price' => 'decimal:2',
        'winning_amount' => 'decimal:2',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'deleted_at' => 'datetime',
        'winner_paid_at' => 'datetime',
        'refund_resolved_at' => 'datetime',
    ];

    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function currentLeader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'current_leader_id');
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'winner_id');
    }

    public function bids(): HasMany
    {
        return $this->hasMany(Bid::class);
    }

    public function paidBids(): HasMany
    {
        return $this->hasMany(Bid::class)
            ->where('status', Bid::STATUS_PAID)
            ->latest('amount');
    }

    public function getIsLiveAttribute(): bool
    {
        return $this->status === 'live'
            && $this->starts_at <= now()
            && $this->ends_at > now();
    }

    public function getMinNextBidAttribute(): float
    {
        return (float) ($this->current_bid > 0
            ? $this->current_bid + $this->bid_increment
            : $this->starting_bid);
    }

    /**
     * Promote due scheduled auctions to 'live' and retire expired live
     * auctions to 'ended'. Shared by the auctions page and the home page
     * so both render the same notion of "what's live right now".
     */
    public static function settleDueStatuses(): void
    {
        static::where('status', 'scheduled')
            ->where('starts_at', '<=', now())
            ->update(['status' => 'live']);

        static::where('status', 'live')
            ->where('ends_at', '<=', now())
            ->get()
            ->each(function (self $auction) {
                $auction->snapshotWinner();
                $auction->update(['status' => 'ended']);
            });
    }

    /**
     * Stamp the current leader as the official winner, create their
     * Order (mirroring the merch checkout flow so the win shows up in
     * /orders), auto-refund every losing paid bid, and dispatch the
     * appropriate notifications. Idempotent — guarded by winner_id so
     * repeated calls don't double-charge or double-notify.
     */
    public function snapshotWinner(): void
    {
        if ($this->winner_id || ! $this->current_leader_id) {
            return;
        }

        $latestWinningBid = $this->paidBids()
            ->where('user_id', $this->current_leader_id)
            ->orderByDesc('created_at')
            ->first();

        $order = null;

        DB::transaction(function () use ($latestWinningBid, &$order) {
            $this->forceFill([
                'winner_id'      => $this->current_leader_id,
                'winning_amount' => $this->current_bid,
                'winner_paid_at' => $latestWinningBid?->created_at ?? now(),
            ])->save();

            $order = $this->createWinnerOrder($latestWinningBid);
            $this->refundLosingBids();
        });

        if ($winner = $this->winner) {
            $winner->notify(new AuctionWonNotification($this, $order));
        }
    }

    /**
     * Build an Order + OrderItem for the winner so the auction win
     * appears in /orders alongside merch purchases. Money was already
     * collected at bid time, so the order opens in a paid state.
     * Shipping fields default from the user's most recent order, with
     * a fallback to their profile — same approach as CheckoutController.
     */
    protected function createWinnerOrder(?Bid $winningBid): ?Order
    {
        $this->loadMissing('card');

        $winner = $this->winner ?? User::find($this->current_leader_id);
        if (! $winner) {
            return null;
        }

        $lastOrder = $winner->orders()->latest()->first();
        $amount    = (float) $this->current_bid;

        $order = Order::create([
            'code'                 => 'PT-' . date('YmdHis') . '-' . strtoupper(Str::random(6)),
            'user_id'              => $winner->id,
            'status'               => 'paid',
            'payment_status'       => 'paid',
            'payment_method'       => 'auction',
            'payment_reference'    => $winningBid?->order_id,
            'subtotal'             => $amount,
            'shipping_fee'         => 0,
            'tax'                  => 0,
            'total'                => $amount,
            'shipping_name'        => $lastOrder?->shipping_name        ?? $winner->name,
            'shipping_phone'       => $lastOrder?->shipping_phone       ?? $winner->phone,
            'shipping_address'     => $lastOrder?->shipping_address,
            'shipping_city'        => $lastOrder?->shipping_city,
            'shipping_postal_code' => $lastOrder?->shipping_postal_code,
            'notes'                => 'Auction win — auction #' . $this->id,
            'paid_at'              => now(),
        ]);

        OrderItem::create([
            'order_id'       => $order->id,
            'itemable_id'    => $this->card->id,
            'itemable_type'  => Card::class,
            'name_snapshot'  => $this->card->name,
            'image_snapshot' => $this->card->image_small ?? $this->card->image_large,
            'price_snapshot' => $amount,
            'quantity'       => 1,
            'subtotal'       => $amount,
        ]);

        return $order;
    }

    /**
     * Flip every paid bid that isn't the winner's to 'refunded' and
     * notify each losing bidder. The bid amount was charged via
     * Midtrans at bid time, so this represents the actual refund
     * being kicked off.
     *
     * TODO(team-backend): trigger the real Midtrans refund API call
     * here. The bid carries the original Midtrans order_id needed.
     */
    protected function refundLosingBids(): void
    {
        $losingBids = $this->bids()
            ->where('status', Bid::STATUS_PAID)
            ->where('user_id', '!=', $this->winner_id)
            ->with('user')
            ->get();

        foreach ($losingBids as $bid) {
            $bid->update([
                'status'      => Bid::STATUS_REFUNDED,
                'refunded_at' => now(),
            ]);

            if ($bid->user) {
                $bid->user->notify(new AuctionRefundedNotification($this, $bid));
            }
        }
    }

    public function isWinner(?int $userId): bool
    {
        return $userId !== null && $this->winner_id === $userId;
    }

    public function isPaid(): bool
    {
        return $this->winner_paid_at !== null;
    }

    /** Winners can request a refund within 7 days of paying. */
    public function isRefundWindowOpen(): bool
    {
        return $this->isPaid()
            && $this->refund_status === 'none'
            && $this->winner_paid_at->gt(now()->subDays(7));
    }

    /**
     * The Order this auction win produced, if any. Located via the
     * winning Bid's order_id, which the settlement flow stamps onto
     * Order.payment_reference.
     */
    public function winnerOrder(): ?Order
    {
        if (! $this->winner_id) {
            return null;
        }

        $winningBid = $this->bids()
            ->where('user_id', $this->winner_id)
            ->where('status', Bid::STATUS_PAID)
            ->orderByDesc('amount')
            ->first();

        if (! $winningBid?->order_id) {
            return null;
        }

        return Order::where('payment_reference', $winningBid->order_id)
            ->where('payment_method', 'auction')
            ->first();
    }
}
