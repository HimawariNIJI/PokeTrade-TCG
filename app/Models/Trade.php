<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Trade extends Model
{
    public const STATUSES = ['pending', 'accepted', 'rejected', 'cancelled', 'completed'];

    protected $fillable = [
        'sender_id', 'receiver_id',
        'status', 'message', 'responded_at',
    ];

    protected $casts = [
        'responded_at' => 'datetime',
    ];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(TradeItem::class);
    }

    public function offerItems(): HasMany
    {
        return $this->hasMany(TradeItem::class)->where('side', 'offer');
    }

    public function requestItems(): HasMany
    {
        return $this->hasMany(TradeItem::class)->where('side', 'request');
    }
}
