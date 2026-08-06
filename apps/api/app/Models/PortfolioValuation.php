<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortfolioValuation extends Model
{
    protected $fillable = [
        'user_id',
        'investment_account_id',
        'valuation_date',
        'market_value',
        'cash_value',
        'net_cash_flow',
        'currency',
        'source',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'valuation_date' => 'date',
            'market_value' => 'decimal:2',
            'cash_value' => 'decimal:2',
            'net_cash_flow' => 'decimal:2',
            'metadata' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function investmentAccount(): BelongsTo
    {
        return $this->belongsTo(InvestmentAccount::class);
    }

    public function getTotalValueAttribute(): float
    {
        return (float) $this->market_value + (float) $this->cash_value;
    }
}