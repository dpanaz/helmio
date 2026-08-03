<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BrokerageProviderUser extends Model
{
    protected $fillable = [
        'user_id',
        'provider',
        'provider_user_id',
        'provider_user_secret',
        'registered_at',
        'last_verified_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            /*
             * Laravel encrypts and decrypts this automatically.
             * Never log or expose this attribute in API responses.
             */
            'provider_user_secret' => 'encrypted',
            'registered_at' => 'datetime',
            'last_verified_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    protected $hidden = [
        'provider_user_secret',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}