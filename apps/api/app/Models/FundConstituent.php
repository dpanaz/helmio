<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FundConstituent extends Model
{
    protected $fillable = [
        'fund_security_id',
        'constituent_security_id',
        'weight',
        'as_of_date',
        'source',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'weight' =>
                'float',

            'as_of_date' =>
                'date',

            'metadata' =>
                'array',
        ];
    }

    public function fund(): BelongsTo
    {
        return $this->belongsTo(
            Security::class,
            'fund_security_id'
        );
    }

    public function constituent(): BelongsTo
    {
        return $this->belongsTo(
            Security::class,
            'constituent_security_id'
        );
    }
}