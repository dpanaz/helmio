<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Security extends Model
{
    protected $fillable = [
        'symbol',
        'name',
        'security_type',
        'cusip',
        'isin',
        'currency',
        'asset_class',
        'sector',
        'category',
        'expense_ratio',
        'last_price',
        'price_as_of',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'expense_ratio' => 'decimal:6',
            'last_price' => 'decimal:6',
            'price_as_of' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function holdings(): HasMany
    {
        return $this->hasMany(Holding::class);
    }
}
