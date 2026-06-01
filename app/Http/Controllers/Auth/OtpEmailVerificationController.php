<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\OtpToken;
use App\Models\User;
use App\Notifications\OtpEmailVerificationNotification;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class OtpEmailVerificationController extends Controller
{
    public function showForm(Request $request)
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        // Get the pending OTP token to calculate remaining time
        $otpToken = OtpToken::where('email', $user->email)
            ->where('type', 'email_verification')
            ->where('verified', false)
            ->latest()
            ->first();

        $expiresAt = $otpToken ? $otpToken->expires_at : null;

        return view('auth.otp.verify-email', [
            'email' => $user->email,
            'expiresAt' => $expiresAt,
        ]);
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
            return back()->withErrors([
                'otp' => 'OTP has expired. Please request a new one.'
            ]);
        }

        if ($otpToken->hasMaxAttempts()) {
            $otpToken->delete();
            return back()->withErrors([
                'otp' => 'Too many incorrect attempts. Please resend the code or start over.'
            ]);
        }

        if ($otpToken->otp !== $request->otp) {
            $otpToken->incrementAttempts();
            $attemptsLeft = 5 - $otpToken->attempts;
            $message = $attemptsLeft > 0 
                ? "Invalid OTP. You have {$attemptsLeft} attempts remaining."
                : 'Invalid OTP. Please try again.';
            
            throw ValidationException::withMessages([
                'otp' => $message
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

    public function resendOtp(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        // Delete any pending OTPs for this user
        OtpToken::where('email', $user->email)
            ->where('type', 'email_verification')
            ->delete();

        // Generate new OTP
        $otp = random_int(100000, 999999);
        $expiresIn = 5; // minutes

        OtpToken::create([
            'email' => $user->email,
            'otp' => (string) $otp,
            'expires_at' => now()->addMinutes($expiresIn),
            'attempts' => 0,
            'verified' => false,
            'type' => 'email_verification',
        ]);

        $user->notify(new OtpEmailVerificationNotification((string) $otp, $expiresIn));

        return back()->with('status', 'New verification code sent to your email.');
    }

    public function deleteAccountAndRegister(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        $email = $user->email;

        // Delete OTP tokens
        OtpToken::where('email', $email)
            ->where('type', 'email_verification')
            ->delete();

        // Log out and delete the user
        Auth::logout();
        $user->delete();

        return redirect()->route('register')
            ->with('status', 'Account deleted. You can now register again with the same email.');
    }
}

