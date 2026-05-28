<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

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
        return $this->hasMany(Bid::class)->latest('amount');
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
     * Stamp the current leader as the official winner. Called when an
     * auction transitions to 'ended'. Idempotent — won't overwrite a
     * winner that has already been recorded.
     */
    public function snapshotWinner(): void
    {
        if ($this->winner_id || ! $this->current_leader_id) {
            return;
        }

        $this->forceFill([
            'winner_id'      => $this->current_leader_id,
            'winning_amount' => $this->current_bid,
        ])->save();
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
}
