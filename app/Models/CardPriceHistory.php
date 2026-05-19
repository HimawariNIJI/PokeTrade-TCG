<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A single daily snapshot of a card's tracked market value.
 */
class CardPriceHistory extends Model
{
    protected $table = 'card_price_history';

    protected $fillable = [
        'card_id', 'market_price', 'recorded_at',
    ];

    protected $casts = [
        'market_price' => 'decimal:2',
        'recorded_at' => 'date',
    ];

    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class);
    }
}
