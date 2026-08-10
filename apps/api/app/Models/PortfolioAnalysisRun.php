<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortfolioAnalysisRun extends Model
{
    public const STATUS_PENDING =
        'pending';

    public const STATUS_SYNCING =
        'syncing';

    public const STATUS_BUILDING_HISTORY =
        'building_history';

    public const STATUS_ANALYZING =
        'analyzing';

    public const STATUS_READY =
        'ready';

    public const STATUS_FAILED =
        'failed';

    protected $fillable = [
        'user_id',
        'brokerage_connection_id',
        'trigger',
        'status',
        'current_step',
        'started_at',
        'completed_at',
        'failed_at',
        'error_message',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'started_at' =>
                'datetime',

            'completed_at' =>
                'datetime',

            'failed_at' =>
                'datetime',

            'metadata' =>
                'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
        );
    }

    public function brokerageConnection(): BelongsTo
    {
        return $this->belongsTo(
            BrokerageConnection::class,
        );
    }

    public function markStep(
        string $status,
        string $step,
        array $metadata = [],
    ): void {
        $existingMetadata =
            $this->metadata ?? [];

        $this->update([
            'status' =>
                $status,

            'current_step' =>
                $step,

            'metadata' =>
                array_merge(
                    $existingMetadata,
                    $metadata,
                ),
        ]);
    }

    public function markReady(
        array $metadata = [],
    ): void {
        $existingMetadata =
            $this->metadata ?? [];

        $this->update([
            'status' =>
                self::STATUS_READY,

            'current_step' =>
                'complete',

            'completed_at' =>
                now(),

            'failed_at' =>
                null,

            'error_message' =>
                null,

            'metadata' =>
                array_merge(
                    $existingMetadata,
                    $metadata,
                ),
        ]);
    }

    public function markFailed(
        string $message,
    ): void {
        $this->update([
            'status' =>
                self::STATUS_FAILED,

            'current_step' =>
                'failed',

            'failed_at' =>
                now(),

            'error_message' =>
                $message,
        ]);
    }
}