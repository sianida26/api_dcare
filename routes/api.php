<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

$unauthenticated = ['index', 'show'];

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('register', [RegisterController::class, 'create'])->name('register');
Route::post('login', [LoginController::class, 'index'])->name('login');

Route::apiResource('articles', ArticleController::class)->only($unauthenticated);

Route::middleware('auth:sanctum')
->group(function () use ($unauthenticated) {
    Route::post('logout', [LogoutController::class, 'index'])->name('logout');
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::apiResource('articles', ArticleController::class)->except($unauthenticated)
    ->missing(fn (Request $request) => response()->json([
        'message' => 'Artikel tidak ditemukan',
    ], 404));
});
