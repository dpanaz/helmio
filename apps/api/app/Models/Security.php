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
        'comparison_group',
'benchmark_name',
'is_index_fund',
'trailing_1y_return',
'trailing_3y_annualized_return',
'trailing_5y_annualized_return',
'provider_security_id',
    ];

    protected function casts(): array
    {
        return [
            'expense_ratio' => 'decimal:6',
            'last_price' => 'decimal:6',
            'price_as_of' => 'datetime',
            'metadata' => 'array',
            'is_index_fund' => 'boolean',
'trailing_1y_return' => 'decimal:6',
'trailing_3y_annualized_return' => 'decimal:6',
'trailing_5y_annualized_return' => 'decimal:6',
        ];
    }

    public function constituents(): HasMany
        {
            return $this->hasMany(
                FundConstituent::class,
                'fund_security_id'
            );
        }

        public function heldByFunds(): HasMany
        {
            return $this->hasMany(
                FundConstituent::class,
                'constituent_security_id'
            );
        }

        public function supportsLookThrough(): bool
        {
            return in_array(
                strtolower(
                    (string) $this->security_type
                ),
                [
                    'etf',
                    'mutual_fund',
                ],
                true,
            );
        }

    public function holdings(): HasMany
    {
        return $this->hasMany(Holding::class);
    }
    public function prices(): HasMany
{
    return $this->hasMany(SecurityPrice::class);
}
}
