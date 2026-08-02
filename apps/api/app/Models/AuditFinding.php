<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditFinding extends Model
{
    public const STATUS_OPEN = 'open';
    public const STATUS_REVIEWED = 'reviewed';
    public const STATUS_DISMISSED = 'dismissed';
    public const STATUS_RESOLVED = 'resolved';

    protected $fillable = [
        'user_id',
        'fingerprint',
        'category',
        'title',
        'description',
        'recommendation',
        'severity',
        'status',
        'score',
        'route_name',
        'first_detected_at',
        'last_detected_at',
        'reviewed_at',
        'dismissed_at',
        'resolved_at',
        'review_notes',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'integer',
            'first_detected_at' => 'datetime',
            'last_detected_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'dismissed_at' => 'datetime',
            'resolved_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isActive(): bool
    {
        return in_array(
            $this->status,
            [
                self::STATUS_OPEN,
                self::STATUS_REVIEWED,
                self::STATUS_DISMISSED,
            ],
            true,
        );
    }
}
