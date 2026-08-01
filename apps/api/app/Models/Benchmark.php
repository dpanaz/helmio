<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Benchmark extends Model
{
    protected $fillable = [
        'name',
        'symbol',
        'benchmark_type',
        'currency',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function returns(): HasMany
    {
        return $this->hasMany(BenchmarkReturn::class);
    }

    public function investmentAccounts(): HasMany
    {
        return $this->hasMany(InvestmentAccount::class);
    }
}
