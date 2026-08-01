<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BenchmarkReturn extends Model
{
    protected $fillable = [
        'benchmark_id',
        'return_date',
        'period_return',
        'period_type',
        'source',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'return_date' => 'date',
            'period_return' => 'decimal:8',
            'metadata' => 'array',
        ];
    }

    public function benchmark(): BelongsTo
    {
        return $this->belongsTo(Benchmark::class);
    }
}
