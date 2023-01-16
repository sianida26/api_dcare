<?php

namespace App\Models;

use ErrorException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    public const ROLES = [
        'admin',
        'user',
        'developer'
    ];

    public function scopeRole(Builder $query, int $order): Builder
    {
        if (! array_key_exists($order, self::ROLES)) {
            throw new ErrorException('Role is not found');
        }

        return $query->where('name', self::ROLES[$order]);
    }

    /**
     * Get all of the users for the Role
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
