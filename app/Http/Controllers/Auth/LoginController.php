<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    use CreateToken;

    public function index(LoginRequest $request): JsonResponse
    {
        if (! $this->ensureUserIsExist($request)) {
            return response()
            ->json(['message' => 'Unauthorized'], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user = Auth::user();

        $token = $this->createToken($user);

        return response()
        ->json([
            'message' => 'Hi '.$user->name.', welcome to home',
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    private function ensureUserIsExist(LoginRequest $request): bool
    {
        return Auth::attempt($request->validated());
    }
}
