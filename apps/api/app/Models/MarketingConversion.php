<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketingConversion extends Model
{
    protected $fillable = [
        'marketing_visit_id',
        'user_id',
        'conversion_id',
        'type',
        'value',
        'currency',
        'converted_at',
        'reddit_status',
        'reddit_attempts',
        'reddit_sent_at',
        'reddit_error',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'converted_at' => 'datetime',
            'reddit_sent_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(
            MarketingVisit::class,
            'marketing_visit_id',
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}