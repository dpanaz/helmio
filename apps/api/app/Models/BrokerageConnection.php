<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BrokerageConnection extends Model
{
    protected $fillable = [
        'user_id',
        'institution_id',
        'provider',
        'provider_connection_id',
        'status',
        'last_synced_at',
        'requires_attention_at',
        'status_message',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'last_synced_at' => 'datetime',
            'requires_attention_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function accounts(): HasMany
    {
        return $this->hasMany(InvestmentAccount::class);
    }
}
