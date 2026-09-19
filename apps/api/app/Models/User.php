<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;
use App\Models\PushSubscription;
use Laravel\Sanctum\HasApiTokens;

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
    use HasApiTokens;

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
            'is_admin' => 'boolean',
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

    public function staffRoles(): BelongsToMany
    {
        return $this->belongsToMany(
            StaffRole::class,
            'staff_role_user',
        )->withTimestamps();
    }

    public function hasStaffRole(string $role): bool
    {
        return $this->staffRoles()
            ->where('slug', $role)
            ->exists();
    }

    public function hasStaffPermission(string $permission): bool
    {
        if ($this->is_admin || $this->hasStaffRole('admin')) {
            return true;
        }

        return $this->staffRoles()
            ->whereHas(
                'permissions',
                fn ($query) =>
                    $query->where(
                        'staff_permissions.slug',
                        $permission,
                    ),
            )
            ->exists();
    }

    /**
     * @param array<int, string> $permissions
     */
    public function hasAnyStaffPermission(array $permissions): bool
    {
        if ($permissions === []) {
            return $this->isStaff();
        }

        return collect($permissions)->contains(
            fn (string $permission): bool =>
                $this->hasStaffPermission($permission),
        );
    }

    public function supportConversations(): HasMany
    {
        return $this->hasMany(
            SupportConversation::class,
        );
    }

    public function assignedSupportConversations(): HasMany
    {
        return $this->hasMany(
            SupportConversation::class,
            'assigned_to_user_id',
        );
    }

    public function supportMessages(): HasMany
    {
        return $this->hasMany(
            SupportMessage::class,
            'sender_user_id',
        );
    }

    public function isStaff(): bool
    {
        return $this->is_admin
            || $this->staffRoles()->exists();
    }
}