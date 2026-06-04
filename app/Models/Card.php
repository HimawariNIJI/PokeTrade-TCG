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
        'attacks', 'abilities', 'weaknesses', 'resistances', 'retreat_cost',
        'flavor_text', 'evolves_from', 'evolves_to', 'artist',
        'price', 'market_price', 'stock', 'featured',
    ];

    protected $casts = [
        'subtypes' => 'array',
        'types' => 'array',
        'national_pokedex_numbers' => 'array',
        'attacks' => 'array',
        'abilities' => 'array',
        'weaknesses' => 'array',
        'resistances' => 'array',
        'retreat_cost' => 'array',
        'evolves_to' => 'array',
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

    /**
     * Daily tracked market-value snapshots, oldest first.
     */
    public function priceHistory(): HasMany
    {
        return $this->hasMany(CardPriceHistory::class)->orderBy('recorded_at');
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

    /**
     * Whether this card has a real market price from the API. Cards whose
     * tcgplayer payload lacked any usable price land here with 0; the UI
     * uses this to show "Price unavailable" instead of "Rp 0".
     */
    public function getHasMarketPriceAttribute(): bool
    {
        return (float) $this->market_price > 0;
    }
}
