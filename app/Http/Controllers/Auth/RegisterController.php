<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    use CreateToken;

    public function create(RegisterRequest $request): JsonResponse
    {
        $user = User::query()->create(
            ['password' => Hash::make($request->password)] +
            $request->validated()
        );

        $token = $this->createToken($user);

        return response()
        ->json([
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }
}
