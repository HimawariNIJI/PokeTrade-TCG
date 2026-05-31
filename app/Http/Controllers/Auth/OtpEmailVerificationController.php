<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\OtpToken;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class OtpEmailVerificationController extends Controller
{
    public function showForm(Request $request)
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        $hasPending = OtpToken::where('email', $user->email)
            ->where('type', 'email_verification')
            ->where('verified', false)
            ->exists();

        if (! $hasPending) {
            return redirect()->route('verification.notice')
                ->with('error', 'No pending OTP found. Please request a new verification email.');
        }

        return view('auth.otp.verify-email', ['email' => $user->email]);
    }

    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'otp' => ['required', 'digits:6'],
        ]);

        $user = $request->user();

        $otpToken = OtpToken::where('email', $user->email)
            ->where('type', 'email_verification')
            ->where('verified', false)
            ->latest()
            ->first();

        if (! $otpToken) {
            throw ValidationException::withMessages([
                'otp' => 'No pending OTP request found. Please request a new one.'
            ]);
        }

        if ($otpToken->isExpired()) {
            $otpToken->delete();
            throw ValidationException::withMessages([
                'otp' => 'OTP has expired. Please request a new one.'
            ]);
        }

        if ($otpToken->hasMaxAttempts()) {
            $otpToken->delete();
            throw ValidationException::withMessages([
                'otp' => 'Too many attempts. Please request a new OTP.'
            ]);
        }

        if ($otpToken->otp !== $request->otp) {
            $otpToken->incrementAttempts();
            throw ValidationException::withMessages([
                'otp' => 'Invalid OTP. Please try again.'
            ]);
        }

        $otpToken->markAsVerified();

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        return redirect()->route('dashboard')
            ->with('status', 'Email verified successfully.');
    }
}
