<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class GoogleController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback(Request $request)
    {
        // Check if the user cancelled the screen or if Google didn't return a 'code'
        if ($request->has('error') || !$request->has('code')) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Google authentication was cancelled. Please try again.']);
        }

        try {
            // Fetch the user safely inside a try-catch block
            $googleUser = Socialite::driver('google')->stateless()->user();

            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                // If the user exists but doesn't have a google_id, link it
                $user->update([
                    'google_id' => $googleUser->getId(),
                ]);
            } else {
                // If the user doesn't exist, create a new one
                $user = User::Create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'password' => bcrypt(Str::random(24)),
                ]);
                $user->markEmailAsVerified();
            }


            Auth::login($user);

            return redirect('/dashboard');
        } catch (\Exception $e) {
            // Fallback if Google's token exchange endpoint errors out or times out
            return redirect()->route('login')
                ->withErrors(['email' => 'Unable to connect with Google. Please try again later.']);
        }
    }
}
