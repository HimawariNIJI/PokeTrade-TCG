<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Auth\OtpPasswordResetController;
use App\Http\Controllers\Auth\OtpEmailVerificationController;
use Illuminate\Support\Facades\Route;

// `no-back-cache` sets Cache-Control: no-store on every auth page so the
// browser cannot serve a stale form from BFCache after Auth::login()
// rotates the session's CSRF token — without it, hitting Back from the
// OTP page surfaces a cached /register form that 419s on submit.
Route::middleware(['guest', 'no-back-cache'])->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    // OTP-based password reset
    Route::get('forgot-password', [OtpPasswordResetController::class, 'forgotPassword'])
        ->name('otp.forgot-password');

    Route::post('forgot-password', [OtpPasswordResetController::class, 'sendOtp'])
        ->name('otp.send');

    Route::get('verify-otp', [OtpPasswordResetController::class, 'verifyOtpForm'])
        ->name('otp.verify-form');

    Route::post('verify-otp', [OtpPasswordResetController::class, 'verifyOtp'])
        ->name('otp.verify');

    Route::get('reset-password', [OtpPasswordResetController::class, 'resetForm'])
        ->name('otp.reset-form');

    Route::post('reset-password', [OtpPasswordResetController::class, 'resetPassword'])
        ->name('otp.reset');
});

Route::middleware('auth')->group(function () {
    // This app uses OTP-based verification, not the default signed-link flow.
    // The Laravel `verified` middleware redirects unverified users here, so
    // verification.notice must land on the OTP form (not the legacy prompt).
    Route::get('verify-email', [OtpEmailVerificationController::class, 'showForm'])
        ->middleware('no-back-cache')
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    // OTP-based email verification (post-registration). `no-back-cache` so a
    // user hitting Back after verifying doesn't replay the OTP form with a
    // stale token (and so the form isn't kept in BFCache after they leave).
    Route::get('verify-email/otp', [OtpEmailVerificationController::class, 'showForm'])
        ->middleware('no-back-cache')
        ->name('otp.email.verify-form');

    Route::post('verify-email/otp', [OtpEmailVerificationController::class, 'verify'])
        ->name('otp.email.verify');

    Route::post('verify-email/otp/resend', [OtpEmailVerificationController::class, 'resendOtp'])
        ->name('otp.email.resend');

    Route::post('verify-email/otp/delete-account', [OtpEmailVerificationController::class, 'deleteAccountAndRegister'])
        ->name('otp.email.delete-account');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
