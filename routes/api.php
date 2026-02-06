<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:3,1'); // Rate limit OTP
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('/set-pin', [AuthController::class, 'setPin']);
    Route::post('/login-pin', [AuthController::class, 'loginPin']);
});
