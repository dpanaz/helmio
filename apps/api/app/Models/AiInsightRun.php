<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiInsightRun extends Model
{
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_BLOCKED = 'blocked';

    protected $fillable = [
        'user_id',
        'provider',
        'model',
        'status',
        'context_version',
        'prompt_version',
        'headline',
        'summary',
        'priorities',
        'positive_changes',
        'limitations',
        'context_snapshot',
        'response_payload',
        'input_tokens',
        'output_tokens',
        'generated_at',
        'error_message',
        'is_stale',
        'stale_at',
        'stale_reason',
        'portfolio_value_at_generation',
        'account_count_at_generation',
        'portfolio_last_updated_at',
    ];

    protected function casts(): array
    {
        return [
            'priorities' => 'array',
            'positive_changes' => 'array',
            'limitations' => 'array',
            'context_snapshot' => 'array',
            'response_payload' => 'array',
            'input_tokens' => 'integer',
            'output_tokens' => 'integer',
            'generated_at' => 'datetime',
            'is_stale' => 'boolean',
'stale_at' => 'datetime',
'portfolio_value_at_generation' => 'decimal:2',
'account_count_at_generation' => 'integer',
'portfolio_last_updated_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function markStale(
        string $reason
    ): void {
        if ($this->is_stale) {
            return;
        }

        $this->update([
            'is_stale' => true,
            'stale_at' => now(),
            'stale_reason' => $reason,
        ]);
    }
}