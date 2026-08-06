<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BrokerageSyncRun extends Model
{
    public const STATUS_RUNNING = 'running';
    public const STATUS_SUCCESS = 'success';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'brokerage_connection_id',
        'user_id',
        'provider',
        'status',
        'started_at',
        'finished_at',
        'accounts_imported',
        'positions_imported',
        'transactions_imported',
        'duration_ms',
        'error_message',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'accounts_imported' => 'integer',
            'positions_imported' => 'integer',
            'transactions_imported' => 'integer',
            'duration_ms' => 'integer',
            'metadata' => 'array',
        ];
    }

    public function brokerageConnection(): BelongsTo
    {
        return $this->belongsTo(
            BrokerageConnection::class,
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function durationSeconds(): ?float
    {
        if ($this->duration_ms === null) {
            return null;
        }

        return round(
            $this->duration_ms / 1000,
            2,
        );
    }

    public function portfolioStateSnapshot(): HasOne
        {
            return $this->hasOne(
                PortfolioStateSnapshot::class,
            );
        }
}