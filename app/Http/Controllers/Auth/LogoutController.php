<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'You have successfully logged out',
        ]);
    }
}
