<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ShopItem extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::addGlobalScope('not_deleted', function ($query) {
            $query->where('is_deleted', false);
        });
    }

    public const CATEGORIES = ['booster', 'bundle', 'accessory', 'plush', 'other'];

    protected $fillable = [
        'name', 'slug', 'description', 'category',
        'price', 'stock', 'image',
        'featured', 'is_active', 'is_deleted',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'featured' => 'boolean',
        'is_active' => 'boolean',
        'is_deleted' => 'boolean',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function cartItems(): MorphMany
    {
        return $this->morphMany(CartItem::class, 'itemable');
    }

    public function orderItems(): MorphMany
    {
        return $this->morphMany(OrderItem::class, 'itemable');
    }
}
