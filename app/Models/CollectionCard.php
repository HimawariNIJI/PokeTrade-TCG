<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One digital card in a trainer's collection, pulled from gacha.
 */
class CollectionCard extends Model
{
    protected $fillable = ['user_id', 'card_id', 'source', 'obtained_at'];

    protected $casts = [
        'obtained_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class);
    }
}
