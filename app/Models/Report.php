<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Report extends Model
{
    public const STATUS_OPEN = 'open';
    public const STATUS_RESOLVED = 'resolved';
    public const STATUS_DISMISSED = 'dismissed';

    protected $fillable = [
        'reporter_id', 'reportable_type', 'reportable_id',
        'reason', 'details', 'status', 'handled_by', 'handled_at',
    ];

    protected $casts = [
        'handled_at' => 'datetime',
    ];

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public function reportable(): MorphTo
    {
        return $this->morphTo();
    }

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }
}
