<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MarketingVisit extends Model
{
    protected $fillable = [
        'user_id',
        'visitor_uuid',
        'session_id',
        'source',
        'medium',
        'campaign',
        'content',
        'term',
        'reddit_click_id',
        'landing_page',
        'referrer',
        'user_agent',
        'ip_address',
        'first_seen_at',
        'last_seen_at',
    ];

    protected function casts(): array
    {
        return [
            'first_seen_at' => 'datetime',
            'last_seen_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function conversions(): HasMany
    {
        return $this->hasMany(MarketingConversion::class);
    }
}