<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortfolioStateSnapshotHolding extends Model
{
    protected $fillable = [
        'portfolio_state_snapshot_id',
        'investment_account_id',
        'security_id',
        'holding_key',
        'symbol',
        'name',
        'security_type',
        'asset_class',
        'sector',
        'quantity',
        'price',
        'market_value',
        'cost_basis',
        'portfolio_weight',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:8',
            'price' => 'decimal:6',
            'market_value' => 'decimal:2',
            'cost_basis' => 'decimal:2',
            'portfolio_weight' => 'decimal:8',
            'metadata' => 'array',
        ];
    }

    public function snapshot(): BelongsTo
    {
        return $this->belongsTo(
            PortfolioStateSnapshot::class,
            'portfolio_state_snapshot_id',
        );
    }

    public function investmentAccount(): BelongsTo
    {
        return $this->belongsTo(
            InvestmentAccount::class,
        );
    }

    public function security(): BelongsTo
    {
        return $this->belongsTo(Security::class);
    }
}