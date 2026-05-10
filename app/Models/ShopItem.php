<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ShopItem extends Model
{
    use HasFactory;

    public const CATEGORIES = ['booster', 'bundle', 'accessory', 'plush', 'other'];

    protected $fillable = [
        'name', 'slug', 'description', 'category',
        'price', 'stock', 'image',
        'featured', 'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'featured' => 'boolean',
        'is_active' => 'boolean',
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
