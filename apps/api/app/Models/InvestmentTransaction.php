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
