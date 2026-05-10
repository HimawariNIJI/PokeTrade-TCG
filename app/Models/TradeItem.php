<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TradeItem extends Model
{
    public const SIDE_OFFER = 'offer';
    public const SIDE_REQUEST = 'request';

    protected $fillable = ['trade_id', 'card_id', 'side'];

    public function trade(): BelongsTo
    {
        return $this->belongsTo(Trade::class);
    }

    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class);
    }
}
