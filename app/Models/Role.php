<?php

namespace App\Models;

use ErrorException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    public const ROLES = [
        'admin',
        'user',
    ];

    public function scopeRole(Builder $query, int $order): Builder
    {
        if (! array_key_exists($order, self::ROLES)) {
            throw new ErrorException('Role is not found');
        }

        return $query->where('name', self::ROLES[$order]);
    }
}
