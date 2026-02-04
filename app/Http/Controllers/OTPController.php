<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;
use App\Services\AuditLogService;
use Carbon\Carbon;

class OTPController extends Controller
{
    protected $auditLog;

    public function __construct(AuditLogService $auditLog)
    {
        $this->auditLog = $auditLog;
    }
    public function showForm()
    {
        // Ensure user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please login first.');
        }

        $user = Auth::user();

        // If already verified, redirect to appropriate dashboard
        if ($user->otp_verified) {
            return $this->redirectToDashboard($user);
        }

        return view('auth.verify-otp');
    }

    public function verify(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|size:6'
        ]);

        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login')->with('error', 'Session expired. Please login again.');
        }

        $userOtp = preg_replace('/\D/', '', $request->otp);

        // Check if OTP matches and is not expired
        if ($user->otp_code === $userOtp) {
            if ($user->otp_expires_at && Carbon::parse($user->otp_expires_at)->isFuture()) {
                // OTP is valid, mark as verified
                $user->update([
                    'otp_verified' => true,
                    'otp_code' => null,
                    'otp_expires_at' => null,
                ]);

                // Log successful OTP verification
                $this->auditLog->logOtpVerified($user);
                
                // Also log the successful login now that 2FA is complete
                $this->auditLog->logLogin($user);

                // Redirect to appropriate dashboard
                return $this->redirectToDashboard($user);
            } else {
                // Log OTP failure - expired
                $this->auditLog->logOtpFailed($user, 'OTP expired');
                return back()->with('error', 'OTP has expired. Please request a new code.');
            }
        } else {
            // Log OTP failure - invalid code
            $this->auditLog->logOtpFailed($user, 'Invalid OTP code');
            return back()->with('error', 'Invalid OTP. Please try again.');
        }
    }

    public function resend()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }

        // Generate new OTP
        $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = Carbon::now()->addMinutes(5);

        // Update user with new OTP
        $user->update([
            'otp_code' => $otp,
            'otp_expires_at' => $expiresAt,
        ]);

        // Send OTP via email
        try {
            Mail::to($user->email)->send(new OtpMail(
                $otp,
                $user->name . ' ' . $user->lastname,
                $expiresAt
            ));

            logger()->info('OTP resent successfully to: ' . $user->email);
            return response()->json(['success' => true, 'message' => 'OTP has been resent to your email']);
        } catch (\Exception $e) {
            logger()->error('Failed to resend OTP: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to send OTP. Please try again.'], 500);
        }
    }

    /**
     * Redirect user to appropriate dashboard based on account type
     */
    private function redirectToDashboard($user)
    {
        $defaultRoute = $user->isEmployee() ? route('employee.dashboard') : route('dashboard');
        $welcomeMessage = $user->isSuperAdmin()
            ? 'Welcome back, Super Admin!'
            : ($user->isAdmin() ? 'Welcome back, Admin!' : 'Welcome back!');

        // Prevent non-employees from being redirected to /employee/*
        $intended = session('url.intended');
        if (!$user->isEmployee() && $intended && str_starts_with($intended, url('/employee'))) {
            session()->forget('url.intended');
        }

        return redirect()->intended($defaultRoute)->with('success', $welcomeMessage);
    }
}
