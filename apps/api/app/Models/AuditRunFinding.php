<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditRunFinding extends Model
{
    protected $fillable = [
        'audit_run_id',
        'audit_finding_id',
        'fingerprint',
        'category',
        'title',
        'description',
        'recommendation',
        'severity',
        'status',
        'score',
        'route_name',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'integer',
            'metadata' => 'array',
        ];
    }

    public function auditRun(): BelongsTo
    {
        return $this->belongsTo(AuditRun::class);
    }

    public function auditFinding(): BelongsTo
    {
        return $this->belongsTo(AuditFinding::class);
    }
}
