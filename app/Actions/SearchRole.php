<?php

namespace App\Actions;

use App\Models\Role;

trait SearchRole
{
    private function role(int $default = 1): int
    {
        return Role::query()->role($default)->first()->id ?? $default + 1;
    }
}
