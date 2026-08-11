<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;
use App\Models\PushSubscription;

#[Fillable([
    'name',
    'email',
    'password',
    'monthly_audit_enabled',
    'monthly_audit_email',
    'monthly_audit_day',
    'monthly_audit_time',
    'timezone',
    'last_monthly_audit_sent_at',
])]
#[Hidden([
    'password',
    'remember_token',
])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use Billable;
    use HasFactory;
    use Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' =>
                'datetime',

            'password' =>
                'hashed',

            'monthly_audit_enabled' =>
                'boolean',

            'monthly_audit_day' =>
                'integer',

            'last_monthly_audit_sent_at' =>
                'datetime',
        ];
    }

    public function investorProfile(): HasOne
    {
        return $this->hasOne(
            InvestorProfile::class,
        );
    }

    public function monthlyAuditSetting(): HasOne
    {
        return $this->hasOne(
            MonthlyAuditSetting::class,
        );
    }

    public function investmentAccounts(): HasMany
    {
        return $this->hasMany(
            InvestmentAccount::class,
        );
    }

    public function askHelmioConversations(): HasMany
    {
        return $this->hasMany(
            AskHelmioConversation::class,
        );
    }

    public function askHelmioMessages(): HasMany
    {
        return $this->hasMany(
            AskHelmioMessage::class,
        );
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
    public function pushSubscriptions(): HasMany
    {
        return $this->hasMany(
            PushSubscription::class,
        );
    }
}