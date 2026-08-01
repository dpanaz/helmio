<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HelmScoreSnapshot extends Model
{
    protected $fillable = [
        'user_id',
        'overall_score',
        'cost_score',
        'diversification_score',
        'performance_score',
        'risk_score',
        'trading_score',
        'tax_score',
        'data_completeness',
        'score_details',
        'formula_version',
        'calculated_for_date',
    ];

    protected function casts(): array
    {
        return [
            'data_completeness' => 'decimal:4',
            'score_details' => 'array',
            'calculated_for_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
