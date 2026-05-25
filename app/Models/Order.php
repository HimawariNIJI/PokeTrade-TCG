<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    public const STATUSES = ['pending', 'paid', 'shipped', 'delivered', 'cancelled'];
    public const PAYMENT_STATUSES = ['unpaid', 'paid', 'refunded', 'failed'];

    protected $fillable = [
        'code', 'user_id',
        'status', 'payment_status', 'payment_method', 'payment_reference',
        'subtotal', 'shipping_fee', 'tax', 'total',
        'shipping_name', 'shipping_phone', 'shipping_address',
        'shipping_city', 'shipping_postal_code', 'notes',
        'paid_at', 'shipped_at', 'delivered_at', 
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'shipping_fee' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'code';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending'   => 'amber',
            'paid'      => 'sky',
            'shipped'   => 'violet',
            'delivered' => 'emerald',
            'cancelled' => 'rose',
            default     => 'slate',
        };
    }
}
