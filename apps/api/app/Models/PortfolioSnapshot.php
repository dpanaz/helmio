<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortfolioSnapshot extends Model
{
    protected $fillable = [
        'investment_account_id',
        'snapshot_date',
        'ending_value',
        'cash_value',
        'external_cash_flow',
        'source',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'snapshot_date' => 'date',
            'ending_value' => 'decimal:2',
            'cash_value' => 'decimal:2',
            'external_cash_flow' => 'decimal:2',
            'metadata' => 'array',
        ];
    }

    public function investmentAccount(): BelongsTo
    {
        return $this->belongsTo(InvestmentAccount::class);
    }
}
