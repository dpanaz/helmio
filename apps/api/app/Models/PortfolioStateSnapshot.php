<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PortfolioStateSnapshot extends Model
{
    protected $fillable = [
        'user_id',
        'brokerage_connection_id',
        'brokerage_sync_run_id',
        'source',
        'captured_at',
        'portfolio_value',
        'cash_value',
        'invested_value',
        'account_count',
        'holding_count',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'captured_at' => 'datetime',
            'portfolio_value' => 'decimal:2',
            'cash_value' => 'decimal:2',
            'invested_value' => 'decimal:2',
            'account_count' => 'integer',
            'holding_count' => 'integer',
            'metadata' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function brokerageConnection(): BelongsTo
    {
        return $this->belongsTo(
            BrokerageConnection::class,
        );
    }

    public function brokerageSyncRun(): BelongsTo
    {
        return $this->belongsTo(
            BrokerageSyncRun::class,
        );
    }

    public function holdings(): HasMany
    {
        return $this->hasMany(
            PortfolioStateSnapshotHolding::class,
        );
    }
}