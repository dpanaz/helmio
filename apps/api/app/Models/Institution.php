<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Institution extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'logo_url',
        'website_url',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function connections(): HasMany
    {
        return $this->hasMany(BrokerageConnection::class);
    }

    public function accounts(): HasMany
    {
        return $this->hasMany(InvestmentAccount::class);
    }
}
