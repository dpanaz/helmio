<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AuditRun extends Model
{
    protected $fillable = [
        'user_id',
        'calculated_for_date',
        'formula_version',
        'audit_score',
        'audit_grade',
        'audit_label',
        'portfolio_value',
        'annual_cost',
        'potential_savings',
        'issue_count',
        'critical_count',
        'high_count',
        'medium_count',
        'positive_count',
        'category_scores',
        'audit_details',
    ];

    protected function casts(): array
    {
        return [
            'calculated_for_date' => 'date',
            'audit_score' => 'integer',
            'portfolio_value' => 'decimal:2',
            'annual_cost' => 'decimal:2',
            'potential_savings' => 'decimal:2',
            'issue_count' => 'integer',
            'critical_count' => 'integer',
            'high_count' => 'integer',
            'medium_count' => 'integer',
            'positive_count' => 'integer',
            'category_scores' => 'array',
            'audit_details' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function findings(): HasMany
    {
        return $this->hasMany(AuditRunFinding::class);
    }
}
