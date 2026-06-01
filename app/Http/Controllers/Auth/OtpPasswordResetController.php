<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\OtpToken;
use App\Models\User;
use App\Notifications\OtpPasswordResetNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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

        // Find user and send OTP notification
        $user = User::where('email', $email)->first();
        if ($user) {
            $user->notify(new OtpPasswordResetNotification((string) $otp, $expiresIn));
        }

        return redirect()->route('otp.verify-form', ['email' => $email])
            ->with('status', 'OTP sent to your email. Check your inbox.');
    }

    /**
     * Show the OTP verification form.
     */
    /**
     * Show the OTP verification form.
     */
    public function verifyOtpForm(Request $request): RedirectResponse|View
    {
        $email = $request->email;

        // Ambil token pending yang paling baru untuk email ini
        $otpToken = OtpToken::where('email', $email)
            ->where('type', 'password_reset')
            ->where('verified', false)
            ->latest()
            ->first();

        // Verify email was provided and has a pending OTP
        if (!$email || !$otpToken) {
            return redirect()->route('otp.forgot-password')
                ->with('error', 'OTP verification failed. Please start the password reset process again.');
        }

        $expiresAt = $otpToken ? $otpToken->expires_at : null;

        return view('auth.otp.verify-otp', [
            'email' => $email,
            'expiresAt' => $expiresAt,
        ]);
    }
    /**
     * Verify the OTP.
     */
    /**
     * Verify the OTP.
     */
    public function verifyOTP(Request $request): RedirectResponse
    {
        // 1. Pastikan email juga divalidasi karena dikirim dari form hidden/input
        $request->validate([
            'email' => ['required', 'email'],
            'otp' => ['required', 'digits:6'],
        ]);

        // 2. Ambil email dari request payload, bukan dari $request->user()
        $email = $request->email;

        $otpToken = OtpToken::where('email', $email)
            ->where('type', 'password_reset')
            ->where('verified', false)
            ->latest()
            ->first();

        // Check if OTP token exists
        if (! $otpToken) {
            throw ValidationException::withMessages([
                'otp' => 'No pending OTP request found. Please request a new one.'
            ]);
        }

        // Check if OTP is expired
        if ($otpToken->isExpired()) {
            $otpToken->delete();
            throw ValidationException::withMessages([
                'otp' => 'OTP has expired. Please request a new one.'
            ]);
        }

        // Cek awal jika memang token dari awal sudah max attempts
        if ($otpToken->hasMaxAttempts()) {
            $otpToken->delete();
            throw ValidationException::withMessages([
                'otp' => 'Too many incorrect attempts. Please resend the code or start over.'
            ]);
        }

        // Cek kecocokan OTP
        if ($otpToken->otp !== $request->otp) {
            $otpToken->incrementAttempts();
            
            // Langsung cek di tempat setelah increment
            if ($otpToken->hasMaxAttempts()) {
                $otpToken->delete();
                throw ValidationException::withMessages([
                    'otp' => 'Too many incorrect attempts. Please resend the code or start over.'
                ]);
            }

            $attemptsLeft = 5 - $otpToken->attempts;
            throw ValidationException::withMessages([
                'otp' => "Invalid OTP. You have {$attemptsLeft} attempts remaining."
            ]);
        }

        // Jika OTP benar, tandai terverifikasi
        $otpToken->markAsVerified();

        // Alirkan ke halaman reset password baru (bukan ke dashboard!)
        return redirect()->route('otp.reset-form', ['email' => $email])
            ->with('status', 'OTP verified. Set your new password.');
    }

    /**
     * Show the reset password form.
     */
    public function resetForm(Request $request): View
    {
        $email = $request->email;

        $otpToken = OtpToken::where('email', $email)
            ->where('verified', true)
            ->latest()
            ->first();

        // JIKA token tidak ada ATAU token ternyata sudah expired, tolak!
        if (!$email || !$otpToken || $otpToken->isExpired()) {
            if ($otpToken) {
                $otpToken->delete(); // Hapus jika terbukti expired
            }
            return redirect()->route('otp.forgot-password')
                ->with('error', 'OTP verification failed or session expired. Please start the password reset process again.');
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

        if (!$otpToken || $otpToken->isExpired()) {
            if ($otpToken) {
                $otpToken->delete();
            }
            throw ValidationException::withMessages([
                'email' => 'Your session has expired. Please start over.',
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
