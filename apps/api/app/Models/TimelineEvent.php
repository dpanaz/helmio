<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimelineEvent extends Model
{
    protected $fillable = [
        'user_id',
        'fingerprint',
        'event_date',
        'detected_at',
        'type',
        'category',
        'severity',
        'headline',
        'summary',
        'before',
        'after',
        'metrics',
        'metadata',
        'source_type',
        'source_id',
        'route_name',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'detected_at' => 'datetime',
            'before' => 'array',
            'after' => 'array',
            'metrics' => 'array',
            'metadata' => 'array',
            'source_id' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}