<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CartItem extends Model
{
    protected $fillable = [
        'cart_id', 'itemable_id', 'itemable_type',
        'quantity', 'price_snapshot',
    ];

    protected $casts = [
        'price_snapshot' => 'decimal:2',
    ];

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function itemable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getSubtotalAttribute(): float
    {
        return (float) $this->price_snapshot * $this->quantity;
    }
}
