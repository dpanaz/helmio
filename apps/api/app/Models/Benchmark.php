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
        'currency',
        'expense_ratio',
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
            'expense_ratio' => 'float',
        ];
    }

    public function prices(): HasMany
    {
        return $this->hasMany(
            BenchmarkPrice::class
        );
    }

    public function isComposite(): bool
    {
        return (bool) data_get(
            $this->metadata,
            'composite',
            false
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function compositeComponents(): array
    {
        if (! $this->isComposite()) {
            return [];
        }

        $components = data_get(
            $this->metadata,
            'components',
            []
        );

        return is_array($components)
            ? $components
            : [];
    }
}