<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BrokerageConnection extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_SYNCING = 'syncing';
    public const STATUS_DISABLED = 'disabled';
    public const STATUS_ERROR = 'error';
    public const STATUS_DISCONNECTED = 'disconnected';

    protected $fillable = [
        'user_id',
        'provider',
        'provider_connection_id',
        'brokerage_name',
        'brokerage_slug',
        'status',
        'read_only',
        'connected_at',
        'last_sync_started_at',
        'last_synced_at',
        'last_successful_sync_at',
        'disabled_at',
        'last_error',
        'capabilities',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'read_only' => 'boolean',
            'connected_at' => 'datetime',
            'last_sync_started_at' => 'datetime',
            'last_synced_at' => 'datetime',
            'last_successful_sync_at' => 'datetime',
            'disabled_at' => 'datetime',
            'capabilities' => 'array',
            'metadata' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function investmentAccounts(): HasMany
    {
        return $this->hasMany(InvestmentAccount::class);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function requiresReconnect(): bool
    {
        return in_array(
            $this->status,
            [
                self::STATUS_DISABLED,
                self::STATUS_ERROR,
            ],
            true,
        );
    }
}