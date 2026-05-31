<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Mail\OtpVerificationMail;
use App\Models\OtpToken;
use Illuminate\Support\Facades\Mail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
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

        Mail::to($user->email)
            ->send(new OtpVerificationMail((string) $otp, 'email_verification', $expiresIn));

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('otp.email.verify-form')
            ->with('status', 'OTP sent to your email. Please enter it to verify your address.');
    }
}

