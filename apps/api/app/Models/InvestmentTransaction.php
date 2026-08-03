<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestmentTransaction extends Model
{
    protected $fillable = [
        'investment_account_id',
        'security_id',
        'transaction_type',
        'transaction_date',
        'settlement_date',
        'quantity',
        'price',
        'gross_amount',
        'fees',
        'net_amount',
        'currency',
        'description',
        'provider_transaction_id',
        'metadata',
        'realized_gain_loss',
        'holding_period_days',
        'is_qualified_dividend',
        'is_tax_exempt',
        'tax_withheld',
        'provider_transaction_id',
        'provider_synced_at',
        'provider_metadata',
    ];

    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
            'settlement_date' => 'date',
            'quantity' => 'decimal:8',
            'price' => 'decimal:6',
            'gross_amount' => 'decimal:2',
            'fees' => 'decimal:2',
            'net_amount' => 'decimal:2',
            'metadata' => 'array',
            'realized_gain_loss' => 'decimal:2',
            'holding_period_days' => 'integer',
            'is_qualified_dividend' => 'boolean',
            'is_tax_exempt' => 'boolean',
            'tax_withheld' => 'decimal:2',
            'provider_synced_at' => 'datetime',
            'provider_metadata' => 'array',
        ];
    }

    public function investmentAccount(): BelongsTo
    {
        return $this->belongsTo(InvestmentAccount::class);
    }

    public function security(): BelongsTo
    {
        return $this->belongsTo(Security::class);
    }
}
