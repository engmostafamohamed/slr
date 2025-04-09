<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyOtpController;
use App\Http\Controllers\Auth\SocialLoginController;
use App\Http\Controllers\Auth\OtpController;
use Illuminate\Support\Facades\Mail;

use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => response()->json(['status' => 'ok']));

Route::get('/send-test-email', function () {
    $email = 'eng.mostafa155@gmail.com';
    Mail::raw('This is a test email from Laravel.', function ($message) use ($email) {
        $message->to($email)->subject('Test Email');
    });
    return response()->json(['message' => 'Test email sent successfully!']);
});

Route::post('/register', [RegisteredUserController::class, 'store'])
    ->middleware(['guest','RegisterMiddleware'])
    ->name('register');

Route::post('/login', [LoginController::class, 'store'])
    ->middleware(['guest','LoginMiddleware'])
    ->name('login');

Route::post('/resend-otp', [OtpController::class, 'resendOtp'])
    ->middleware(['guest','SendOtpMiddleware', 'throttle:6,1']);

Route::post('/auth/{provider}/login', [SocialLoginController::class, 'login'])
    ->where('provider', 'google|apple')
    ->middleware('guest');

Route::post('/reset-password', [ResetPasswordController::class, 'resetPassword'])
    ->middleware(['guest','ResetPasswordMiddleware'])
    ->name('password.store');

Route::post('/verify-otp', [VerifyOtpController::class, 'verify'])
    ->middleware(['VerifyOtpMiddleware','throttle:6,1']) // Limit OTP attempts to 6 per minute
    ->name('verification.verify');

// Route::post('/resend-otp', [OtpController::class, 'resendOtp'])
//     ->middleware(['SendOtpMiddleware','auth:sanctum', 'signed', 'throttle:6,1']);
// Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
//     ->middleware(['auth:sanctum', 'throttle:6,1'])
//     ->name('verification.send');

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth:sanctum')
    ->name('logout');
