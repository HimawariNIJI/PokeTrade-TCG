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

    protected $fillable = [
        'card_id', 'seller_id', 'current_leader_id',
        'starting_bid', 'current_bid', 'bid_increment', 'buy_now_price',
        'starts_at', 'ends_at', 'status', 'is_highlighted',
    ];

    protected $casts = [
        'starting_bid' => 'decimal:2',
        'current_bid' => 'decimal:2',
        'bid_increment' => 'decimal:2',
        'buy_now_price' => 'decimal:2',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'deleted_at' => 'datetime',
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
}
