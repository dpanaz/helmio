<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class StaffRole extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            StaffPermission::class,
            'staff_permission_staff_role',
        );
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'staff_role_user',
        )->withTimestamps();
    }
}
