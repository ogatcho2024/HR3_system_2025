<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
            $email = $googleUser->getEmail();

            // Check if the email exists in your users table
            $existingUser = User::where('email', $email)->first();

            if ($existingUser) {
                // Log the user in
                Auth::login($existingUser);
                if (!Auth::check()) {
                    return redirect('/login');
                }

                return response()
                    ->view('dashboard')
                    ->header('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate')
                    ->header('Pragma', 'no-cache')
                    ->header('Expires', 'Sat, 01 Jan 1990 00:00:00 GMT');
            } else {
                // Optionally: Flash message or redirect to register
                return redirect()->route('login')->withErrors([
                    'msg' => 'No account associated with this Google email. Please contact admin or register first.'
                ]);
            }

        } catch (\Exception $e) {
            return redirect()->route('login')->withErrors([
                'msg' => 'OAuth login failed: ' . $e->getMessage()
            ]);
        }
    }
}
