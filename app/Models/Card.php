<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Card extends Model
{
    use HasFactory;

    protected $fillable = [
        'api_id', 'name', 'slug',
        'supertype', 'subtypes', 'hp', 'types',
        'rarity', 'regulation_mark', 'number',
        'set_id', 'set_name', 'set_series',
        'national_pokedex_numbers',
        'image_small', 'image_large',
        'attacks', 'weaknesses', 'resistances', 'retreat_cost',
        'flavor_text', 'artist', 'language',
        'price', 'market_price', 'stock', 'featured',
    ];

    protected $casts = [
        'subtypes' => 'array',
        'types' => 'array',
        'national_pokedex_numbers' => 'array',
        'attacks' => 'array',
        'weaknesses' => 'array',
        'resistances' => 'array',
        'retreat_cost' => 'array',
        'price' => 'decimal:2',
        'market_price' => 'decimal:2',
        'featured' => 'boolean',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function wishlistedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'wishlists')->withTimestamps();
    }

    public function auctions(): HasMany
    {
        return $this->hasMany(Auction::class);
    }

    public function cartItems(): MorphMany
    {
        return $this->morphMany(CartItem::class, 'itemable');
    }

    public function orderItems(): MorphMany
    {
        return $this->morphMany(OrderItem::class, 'itemable');
    }

    /**
     * Best price to display: house price if set, else market.
     */
    public function getDisplayPriceAttribute(): float
    {
        return (float) ($this->price > 0 ? $this->price : ($this->market_price ?? 0));
    }
}
