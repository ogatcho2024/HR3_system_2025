<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class OTPController extends Controller
{
    public function showForm()
    {
        return view('auth.verify-otp');
    }

    public function verify(Request $request)
    {
        $userOtp = preg_replace('/\D/', '', $request->otp);
        $storedOtp = session('verification_code');
        $expiry = session('code_expiry', 0);

        if ((string)$userOtp === (string)$storedOtp && time() < $expiry) {
            session()->forget(['verification_code', 'code_expiry']);
            return redirect()->route('dashboard')->with('success', 'OTP verified successfully!');
        } elseif (time() >= $expiry) {
            return back()->with('error', 'OTP expired. Please request a new code.');
        } else {
            return back()->with('error', 'Invalid OTP. Please try again.');
        }
    }

    public function resend()
    {
        $otp = rand(100000, 999999);
        $expiry = time() + 300;

        session(['verification_code' => $otp, 'code_expiry' => $expiry]);

        // Send via email using PHPMailer (setup below)
        Mail::raw("Your OTP is: $otp", function ($message) {
            $message->to(Auth::user()->email)
                    ->subject('Your OTP Code');
        });

        return response('OTP has been resent!');
    }
}

