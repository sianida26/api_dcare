<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;

trait CreateToken
{
    public function createToken(User $user): string
    {
        return $user->createToken('auth_token')->plainTextToken;
    }
}
