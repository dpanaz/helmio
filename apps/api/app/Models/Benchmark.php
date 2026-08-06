<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Benchmark extends Model
{
    protected $fillable = [
        'name',
        'symbol',
        'description',
        'benchmark_type',
        'is_active',
        'is_default',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'metadata' => 'array',
        ];
    }

    public function prices(): HasMany
    {
        return $this->hasMany(BenchmarkPrice::class);
    }
}