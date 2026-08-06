<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AskHelmioMessage extends Model
{
    public const ROLE_USER = 'user';
    public const ROLE_ASSISTANT = 'assistant';
    public const ROLE_SYSTEM = 'system';

    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_BLOCKED = 'blocked';

    protected $fillable = [
        'ask_helmio_conversation_id',
        'user_id',
        'role',
        'content',
        'provider',
        'model',
        'status',
        'confidence',
        'citations',
        'limitations',
        'context_snapshot',
        'response_payload',
        'input_tokens',
        'output_tokens',
        'generated_at',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'citations' => 'array',
            'limitations' => 'array',
            'context_snapshot' => 'array',
            'response_payload' => 'array',
            'input_tokens' => 'integer',
            'output_tokens' => 'integer',
            'generated_at' => 'datetime',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(
            AskHelmioConversation::class,
            'ask_helmio_conversation_id',
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}