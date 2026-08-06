<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BenchmarkPrice extends Model
{
    protected $fillable = [
        'benchmark_id',
        'price_date',
        'close_price',
        'adjusted_close_price',
        'metadata',
    ];

    protected function casts(): array
{
    return [
        'price_date' => 'date:Y-m-d',
        'close_price' => 'decimal:6',
        'adjusted_close_price' => 'decimal:6',
        'metadata' => 'array',
    ];
}

    public function benchmark(): BelongsTo
    {
        return $this->belongsTo(Benchmark::class);
    }

    public function getPerformancePriceAttribute(): float
    {
        return (float) (
            $this->adjusted_close_price
            ?? $this->close_price
        );
    }
}