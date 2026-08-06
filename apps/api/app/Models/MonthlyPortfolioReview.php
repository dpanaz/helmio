<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonthlyPortfolioReview extends Model
{
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_BLOCKED = 'blocked';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'user_id',
        'period_start',
        'period_end',
        'status',
        'headline',
        'summary',
        'starting_portfolio_value',
        'ending_portfolio_value',
        'portfolio_value_change',
        'portfolio_value_change_rate',
        'starting_helm_score',
        'ending_helm_score',
        'helm_score_change',
        'starting_audit_grade',
        'ending_audit_grade',
        'starting_annual_cost',
        'ending_annual_cost',
        'annual_cost_change',
        'event_count',
        'positive_event_count',
        'attention_event_count',
        'key_changes',
        'positive_changes',
        'review_items',
        'limitations',
        'data_snapshot',
        'generated_at',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'starting_portfolio_value' => 'decimal:2',
            'ending_portfolio_value' => 'decimal:2',
            'portfolio_value_change' => 'decimal:2',
            'portfolio_value_change_rate' => 'decimal:8',
            'starting_helm_score' => 'integer',
            'ending_helm_score' => 'integer',
            'helm_score_change' => 'integer',
            'starting_annual_cost' => 'decimal:2',
            'ending_annual_cost' => 'decimal:2',
            'annual_cost_change' => 'decimal:2',
            'event_count' => 'integer',
            'positive_event_count' => 'integer',
            'attention_event_count' => 'integer',
            'key_changes' => 'array',
            'positive_changes' => 'array',
            'review_items' => 'array',
            'limitations' => 'array',
            'data_snapshot' => 'array',
            'generated_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}