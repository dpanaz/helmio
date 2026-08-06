<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecurityPrice extends Model
{
    protected $fillable = [
        'security_id',
        'price_date',
        'open_price',
        'high_price',
        'low_price',
        'close_price',
        'adjusted_close_price',
        'volume',
        'currency',
        'source',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'price_date' => 'date:Y-m-d',
            'open_price' => 'decimal:6',
            'high_price' => 'decimal:6',
            'low_price' => 'decimal:6',
            'close_price' => 'decimal:6',
            'adjusted_close_price' => 'decimal:6',
            'volume' => 'integer',
            'metadata' => 'array',
        ];
    }

    public function security(): BelongsTo
    {
        return $this->belongsTo(Security::class);
    }

    public function getPerformancePriceAttribute(): float
    {
        return (float) (
            $this->adjusted_close_price
            ?? $this->close_price
        );
    }
}