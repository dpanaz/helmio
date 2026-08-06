<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AskHelmioConversation extends Model
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'user_id',
        'title',
        'status',
        'last_message_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(
            AskHelmioMessage::class,
        );
    }
}