<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\OtpVerificationMail;
use App\Models\OtpToken;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OtpPasswordResetController extends Controller
{
    /**
     * Show the forgot password form (email submission).
     */
    public function forgotPassword(): View
    {
        return view('auth.otp.forgot-password');
    }

    /**
     * Send OTP to the user's email.
     */
    public function sendOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ], [
            'email.exists' => 'This email address is not registered with us.',
        ]);

        $email = $request->email;

        // Invalidate previous OTPs for this email
        OtpToken::where('email', $email)->delete();

        // Generate 6-digit OTP
        $otp = random_int(100000, 999999);
        $expiresIn = 10; // minutes

        // Create OTP token
        OtpToken::create([
            'email' => $email,
            'otp' => (string) $otp,
            'expires_at' => now()->addMinutes($expiresIn),
            'attempts' => 0,
            'verified' => false,
            'type' => 'password_reset',
        ]);

        // Send OTP via email
        Mail::to($email)->send(new OtpVerificationMail((string) $otp, 'password_reset', $expiresIn));

        return redirect()->route('otp.verify-form', ['email' => $email])
            ->with('status', 'OTP sent to your email. Check your inbox.');
    }

    /**
     * Show the OTP verification form.
     */
    public function verifyOtpForm(Request $request): View
    {
        $email = $request->email;

        // Verify email was provided and has a pending OTP
        if (!$email || !OtpToken::where('email', $email)->where('verified', false)->exists()) {
            return redirect()->route('otp.forgot-password')
                ->with('error', 'Invalid or expired request. Please try again.');
        }

        return view('auth.otp.verify-otp', ['email' => $email]);
    }

    /**
     * Verify the OTP.
     */
    public function verifyOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'otp' => ['required', 'digits:6'],
        ]);

        $email = $request->email;
        $otpToken = OtpToken::where('email', $email)
            ->where('verified', false)
            ->latest()
            ->first();
        

        // Check if OTP token exists
        if (!$otpToken) {
            throw ValidationException::withMessages([
                'otp' => 'No pending OTP request found. Please request a new one.',
            ]);
        }

        // Check if OTP is expired
        if ($otpToken->isExpired()) {
            $otpToken->delete();
            throw ValidationException::withMessages([
                'otp' => 'OTP has expired. Please request a new one.',
            ]);
        }

        // Check max attempts
        if ($otpToken->hasMaxAttempts()) {
            $otpToken->delete();
            throw ValidationException::withMessages([
                'otp' => 'Too many attempts. Please request a new OTP.',
            ]);
        }

        // Check if OTP matches
        if ($otpToken->otp !== $request->otp) {
            $otpToken->incrementAttempts();
            throw ValidationException::withMessages([
                'otp' => 'Invalid OTP. Please try again.',
            ]);
        }

        // Mark OTP as verified
        $otpToken->markAsVerified();

        return redirect()->route('otp.reset-form', ['email' => $email])
            ->with('status', 'OTP verified. Set your new password.');
    }

    /**
     * Show the reset password form.
     */
    public function resetForm(Request $request): View
    {
        $email = $request->email;

        // Verify email has a verified OTP
        if (!$email || !OtpToken::where('email', $email)->where('verified', true)->exists()) {
            return redirect()->route('otp.forgot-password')
                ->with('error', 'Invalid or expired request. Please try again.');
        }

        return view('auth.otp.reset-password', ['email' => $email]);
    }

    /**
     * Store the new password.
     */
    public function resetPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $email = $request->email;

        // Verify OTP token is verified
        $otpToken = OtpToken::where('email', $email)
            ->where('verified', true)
            ->latest()
            ->first();

        if (!$otpToken) {
            throw ValidationException::withMessages([
                'email' => 'Invalid request. Please start over.',
            ]);
        }

        // Update user password
        $user = User::where('email', $email)->first();
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Clean up OTP token
        $otpToken->delete();

        return redirect()->route('login')
            ->with('status', 'Password reset successfully! You can now log in with your new password.');
    }
}
