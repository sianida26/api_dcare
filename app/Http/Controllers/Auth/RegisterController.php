<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\AuthUserResource;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function create(RegisterRequest $request)/* : JsonResponse */
    {
        $user = User::query()->create(
            ['password' => Hash::make($request->password)] +
            $request->validated()
        );

        $user->setRole(
            Role::query()->role(1)->first()
        );

        return new AuthUserResource($user);
    }
}
