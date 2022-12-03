<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [LogoutController::class, 'index'])->name('logout');
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});
