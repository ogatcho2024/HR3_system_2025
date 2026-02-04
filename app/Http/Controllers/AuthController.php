<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;
use App\Models\User;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\LoginAttempt;
use App\Models\LeaveRequest;
use App\Models\ShiftRequest;
use App\Services\AuditLogService;
use Carbon\Carbon;

class AuthController extends Controller
{
    protected $auditLog;

    public function __construct(AuditLogService $auditLog)
    {
        $this->auditLog = $auditLog;
    }
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function showMainPage()
    {
        $today = now()->toDateString();
        
        // Get total employees count
        $totalEmployees = Employee::active()->count();
        
        // Get today's attendance statistics (total who clocked in today)
        $todayAttendance = Attendance::where('date', $today)
            ->whereIn('status', ['present', 'late'])
            ->count();
            
        // Get pending requests count from both leave_requests and shift_requests tables
        $pendingLeaveRequests = LeaveRequest::where('status', 'pending')->count();
        $pendingShiftRequests = ShiftRequest::where('status', 'pending')->count();
        $pendingRequests = $pendingLeaveRequests + $pendingShiftRequests;
        
        // Get detailed attendance breakdown for today
        $attendanceStats = [
            'present' => Attendance::where('date', $today)->where('status', 'present')->count(),
            'late' => Attendance::where('date', $today)->where('status', 'late')->count(),
            'absent' => Attendance::where('date', $today)->where('status', 'absent')->count(),
            'on_break' => Attendance::where('date', $today)->where('status', 'on_break')->count(),
        ];
        
        // Calculate attendance percentage
        $attendancePercentage = $totalEmployees > 0 ? 
            round((($attendanceStats['present'] + $attendanceStats['late']) / $totalEmployees) * 100, 1) : 0;
        
        return view('dashb', compact(
            'totalEmployees',
            'todayAttendance', 
            'pendingRequests',
            'attendanceStats',
            'attendancePercentage'
        ));
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $email = $request->email;
        $ipAddress = $request->ip();
        
        // Check if request is throttled (from middleware)
        $isThrottled = $request->input('_is_throttled', false);
        $throttleTime = $request->input('_throttle_time', 0);

        $user = User::where('email', $email)->first();

        // Check credentials
        $credentialsValid = $user && Hash::check($request->password, $user->password);

        // If user doesn't exist or password is wrong
        if (!$credentialsValid) {
            // If already throttled, show throttle message instead of specific error
            if ($isThrottled) {
                return back()->withErrors([
                    'throttle' => "Account temporarily locked due to multiple failed attempts. Please try again in {$throttleTime} minutes."
                ])->withInput($request->only('email'));
            }
            
            // Record failed attempt
            LoginAttempt::recordFailedAttempt($email, $ipAddress);
            RateLimiter::hit('login-attempts:' . $ipAddress . ':' . $email, 300); // 5 minutes
            
            // Log failed login attempt
            $attemptCount = LoginAttempt::getAttemptCount($email, $ipAddress);
            $this->auditLog->logFailedLogin($email, $attemptCount);
            
            // Generic error message (better for security - doesn't reveal if email exists)
            return back()->withErrors(['email' => 'Invalid email or password.'])->withInput($request->only('email'));
        }
        
        // Login successful - even if throttled, correct credentials should work

        session([
            'email' => $user->email,
            'name' => $user->name,
            'lastname' => $user->lastname,
            'photo' => $user->photo,
            'account_type' => $user->account_type,
        ]);

        /*if ($user->account_type === "1" || $user->account_type === "2") {
            return redirect('dashboard')->with('success', 'Login successful!');
        } else {
            return redirect('login')->with('acc_banned', true);
        }*/

        // Clear login attempts on successful login
        LoginAttempt::clearAttempts($email, $ipAddress);
        RateLimiter::clear('login-attempts:' . $ipAddress . ':' . $email);
        
        Auth::login($user);
        
        // Force refresh user from database to get latest values
        $user->refresh();
        
        // Get OTP enabled configuration (default to true for security)
        $otpEnabled = config('auth.otp_enabled', true);
        $userRequires2FA = $user->require_2fa ?? true; // Default to true if null
        
        // Comprehensive logging for debugging
        logger()->info('=== OTP DECISION POINT ===', [
            'user_id' => $user->id,
            'email' => $user->email,
            'require_2fa_db_value' => $user->require_2fa,
            'require_2fa_resolved' => $userRequires2FA,
            'otp_enabled_config' => $otpEnabled,
            'environment' => app()->environment(),
            'session_driver' => config('session.driver'),
        ]);
        
        // Check if 2FA is required (explicit boolean check)
        if ($otpEnabled && $userRequires2FA === true) {
            logger()->info('OTP TRIGGERED: Generating OTP for user', ['user_id' => $user->id]);
            
            // Generate OTP
            $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
            $expiresAt = Carbon::now()->addMinutes(5);
            
            // Save OTP to user record - use save() instead of update() for reliability
            $user->otp_code = $otp;
            $user->otp_expires_at = $expiresAt;
            $user->otp_verified = false;
            $user->save();
            
            // Verify the save was successful
            $user->refresh();
            logger()->info('OTP saved to database', [
                'user_id' => $user->id,
                'otp_set' => !empty($user->otp_code),
                'otp_verified' => $user->otp_verified,
                'expires_at' => $user->otp_expires_at,
            ]);
            
            // Send OTP via email synchronously (not queued)
            try {
                Mail::to($user->email)->send(new OtpMail(
                    $otp,
                    $user->name . ' ' . $user->lastname,
                    $expiresAt
                ));
                
                logger()->info('OTP email sent successfully', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'otp_code' => $otp,
                ]);
            } catch (\Exception $e) {
                logger()->error('CRITICAL: Failed to send OTP email', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                // Still redirect to OTP page - user can request resend
            }
            
            // Store session flag for OTP requirement
            session()->put('requires_otp_verification', true);
            session()->put('otp_user_id', $user->id);
            session()->save(); // Force session save
            
            logger()->info('REDIRECTING to OTP verification page', ['user_id' => $user->id]);
            
            // Redirect to OTP verification page
            return redirect()->route('otp.show')
                ->with('success', 'OTP sent to your email. Please verify to continue.')
                ->with('otp_email', $user->email);
        }
        
        // OTP not required - proceed with normal login
        logger()->info('OTP BYPASSED: User does not require 2FA', [
            'user_id' => $user->id,
            'require_2fa' => $userRequires2FA,
            'otp_enabled' => $otpEnabled,
        ]);
        
        // Mark as verified for users who don't need OTP
        $user->otp_verified = true;
        $user->save();
        
        // Log successful login
        $this->auditLog->logLogin($user);
        
        // Role-based redirection
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

    /**
     * Get remaining block time for AJAX requests
     */
    public function getBlockTime(Request $request)
    {
        $email = $request->input('email');
        $ipAddress = $request->ip();
        
        if (LoginAttempt::isBlocked($email, $ipAddress)) {
            $seconds = LoginAttempt::getBlockTimeRemainingSeconds($email, $ipAddress);
            return response()->json([
                'blocked' => true,
                'seconds_remaining' => $seconds,
                'minutes_remaining' => ceil($seconds / 60)
            ]);
        }
        
        return response()->json([
            'blocked' => false,
            'seconds_remaining' => 0,
            'minutes_remaining' => 0
        ]);
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        
        // Log logout before user session is destroyed
        if ($user) {
            $this->auditLog->logLogout($user);
            
            // Reset OTP verification status on logout
            $user->update([
                'otp_verified' => false,
                'otp_code' => null,
                'otp_expires_at' => null,
            ]);
        }
        
        Auth::logout();                      // Logs the user out
        $request->session()->invalidate();  // Invalidate the session
        $request->session()->regenerateToken(); // Prevent CSRF reuse

        return redirect('/login');
    }
}
