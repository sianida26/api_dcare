<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\AuthUserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    // use CreateToken;

    public function index(LoginRequest $request)/* : JsonResponse */
    {
        if (! $this->ensureUserIsExist($request)) {
            return response()
            ->json(['message' => 'Unauthorized'], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user = Auth::user();

        return new AuthUserResource($user);
    }

    private function ensureUserIsExist(LoginRequest $request): bool
    {
        return Auth::attempt($request->validated());
    }
}
