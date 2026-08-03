<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'email', 'password', 'monthly_audit_enabled',
'monthly_audit_email',
'monthly_audit_day',
'monthly_audit_time',
'timezone',
'last_monthly_audit_sent_at',])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'monthly_audit_enabled' => 'boolean',
            'monthly_audit_day' => 'integer',
            'last_monthly_audit_sent_at' => 'datetime',
        ];
    }
    public function brokerageConnections(): HasMany
    {
        return $this->hasMany(
            BrokerageConnection::class,
        );
    }

    public function brokerageProviderUsers(): HasMany
    {
        return $this->hasMany(
            BrokerageProviderUser::class,
        );
    }
}
