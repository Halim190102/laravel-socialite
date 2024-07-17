<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\SocialLoginController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function () {
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/refresh', [AuthController::class, 'refresh'])->name('refresh');
    Route::get('/socialite/{driver}', [SocialLoginController::class, 'toProvider'])->where('driver', 'github|google|facebook');
    Route::get('/{driver}/login', [SocialLoginController::class, 'handleCallback'])->where('driver', 'github|google|facebook');

    Route::middleware('auth:api')->group(function () {
        Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/me', [AuthController::class, 'me'])->name('me');
    });
});
