<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Holding extends Model
{
    protected $fillable = [
        'investment_account_id',
        'security_id',
        'quantity',
        'price',
        'market_value',
        'cost_basis',
        'unrealized_gain_loss',
        'portfolio_weight',
        'as_of_date',
        'metadata',
        'provider_position_id',
        'provider_synced_at',
        'provider_metadata',
    ];

    protected function casts(): array
    {
        return [
            'quantity' =>
                'decimal:8',

            'price' =>
                'decimal:6',

            'market_value' =>
                'decimal:2',

            'cost_basis' =>
                'decimal:2',

            'unrealized_gain_loss' =>
                'decimal:2',

            'portfolio_weight' =>
                'decimal:6',

            'as_of_date' =>
                'date',

            'metadata' =>
                'array',

            'provider_synced_at' =>
                'datetime',

            'provider_metadata' =>
                'array',
        ];
    }

    public function investmentAccount(): BelongsTo
    {
        return $this->belongsTo(
            InvestmentAccount::class
        );
    }

    public function security(): BelongsTo
    {
        return $this->belongsTo(
            Security::class
        );
    }

    /**
     * Restrict a holdings query to the latest available holding snapshot
     * for each investment account.
     *
     * Historical Holding rows remain intact, but current portfolio
     * consumers can use this scope without accidentally summing multiple
     * daily snapshots together.
     */
    public function scopeCurrentSnapshot(
        Builder $query
    ): Builder {
        return $query->whereRaw(
            'holdings.as_of_date = (
                SELECT MAX(latest_holdings.as_of_date)
                FROM holdings AS latest_holdings
                WHERE latest_holdings.investment_account_id = holdings.investment_account_id
            )'
        );
    }
}