<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class InvestmentAccount extends Model
{
    protected $fillable = [
          'user_id',
    'brokerage_connection_id',
    'institution_id',
    'name',
    'account_type',
    'account_number_mask',
    'currency',
    'current_value',
    'cash_value',
    'annual_advisory_fee_rate',
    'annual_account_fee',
    'advisory_fee_applies_to_cash',
    'status',
    'last_synced_at',
    'metadata',
    'benchmark_id',
    'brokerage_connection_id',
    'provider_account_id',
    'provider',
    'provider_synced_at',
    'provider_metadata',
    ];

    protected function casts(): array
    {
        return [
             'current_value' => 'decimal:2',
        'cash_value' => 'decimal:2',
        'annual_advisory_fee_rate' => 'decimal:6',
        'annual_account_fee' => 'decimal:2',
        'advisory_fee_applies_to_cash' => 'boolean',
        'last_synced_at' => 'datetime',
        'metadata' => 'array',
        'provider_synced_at' => 'datetime',
        'provider_metadata' => 'array',
        ];
    }

    public function profile(): HasOne
{
    return $this->hasOne(
        InvestmentAccountProfile::class
    );
}

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function brokerageConnection(): BelongsTo
    {
        return $this->belongsTo(BrokerageConnection::class);
    }

    public function holdings(): HasMany
    {
    return $this->hasMany(Holding::class);
    }
    public function transactions(): HasMany
{
    return $this->hasMany(InvestmentTransaction::class);
}
public function benchmark(): BelongsTo
{
    return $this->belongsTo(Benchmark::class);
}

public function portfolioSnapshots(): HasMany
{
    return $this->hasMany(PortfolioSnapshot::class);
}
}
