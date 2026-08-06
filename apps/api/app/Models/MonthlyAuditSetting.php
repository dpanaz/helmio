<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonthlyAuditSetting extends Model
{
    protected $fillable = [
        'user_id',
        'is_enabled',
        'run_day',
        'timezone',
        'benchmark_id',
        'notify_on_completion',
        'notify_on_new_critical',
        'notify_on_score_change',
        'score_change_threshold',
        'last_run_at',
        'next_run_at',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' =>
                'boolean',

            'run_day' =>
                'integer',

            'notify_on_completion' =>
                'boolean',

            'notify_on_new_critical' =>
                'boolean',

            'notify_on_score_change' =>
                'boolean',

            'score_change_threshold' =>
                'integer',

            'last_run_at' =>
                'datetime',

            'next_run_at' =>
                'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class
        );
    }

    public function benchmark(): BelongsTo
    {
        return $this->belongsTo(
            Benchmark::class
        );
    }
}