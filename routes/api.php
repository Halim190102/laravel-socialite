<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\UserDataController;

use App\Http\Controllers\Auth\SocialLoginController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::group([
    'middleware' => 'api',
], function () {
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    // Route::post('/verify_email', [AuthController::class, 'verifyUserEmail'])->name('verifyUserEmail');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/refresh', [AuthController::class, 'refresh'])->name('refresh');

    Route::get('/success={email}&{token}', [UserDataController::class, 'verifiedSuccess']);
    Route::post('/resend_email', [UserDataController::class, 'resendEmailVerificationLink'])->name('resendEmailVerificationLink');
    Route::post('/check_verify_email', [UserDataController::class, 'checkVerify'])->name('checkVerify');
    Route::post('/send_reset_code', [UserDataController::class, 'sendCodeLink'])->name('sendCodeLink');
    Route::post('/check_code_verify', [UserDataController::class, 'checkCodeVerify'])->name('checkCodeVerify');
    Route::post('/reset_password', [UserDataController::class, 'resetPassword'])->name('resetPassword');

    Route::get('/auth/{driver}', [SocialLoginController::class, 'toProvider'])->where('driver', 'github|google|facebook');
    Route::get('/callback/{driver}/login', [SocialLoginController::class, 'handleCallback'])->where('driver', 'github|google|facebook');

    Route::middleware('auth:api')->group(function () {
        Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/me', [AuthController::class, 'me'])->name('me');

        Route::post('/change_password', [UserDataController::class, 'changeUserPassword'])->name('changeUserPassword');
        Route::post('/update_image', [UserDataController::class, 'changeUserImage'])->name('changeUserImage');
        Route::post('/update_username', [UserDataController::class, 'changeUsername'])->name('changeUsername');
        Route::post('/update_name', [UserDataController::class, 'changeName'])->name('changeName');
    });
});
