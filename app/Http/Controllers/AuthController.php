<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use App\Models\User;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\LoginAttempt;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function showMainPage()
    {
        $today = now()->toDateString();
        
        // Get total employees count
        $totalEmployees = Employee::active()->count();
        
        // Get today's attendance statistics
        $todayAttendance = Attendance::where('date', $today)
            ->whereIn('status', ['present', 'late'])
            ->count();
            
        // Get pending leave requests count (assuming there's a leave_requests table)
        $pendingLeaves = \App\Models\LeaveRequest::where('status', 'pending')->count() ?? 24;
        
        // Get open positions count (can be a static value or from a positions table)
        $openPositions = 8; // Static for now, can be made dynamic
        
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
            'pendingLeaves',
            'openPositions',
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

        // If user doesn't exist
        if (!$user) {
            // If already throttled, show throttle message instead of specific error
            if ($isThrottled) {
                return back()->withErrors([
                    'throttle' => "Account temporarily locked due to multiple failed attempts. Please try again in {$throttleTime} minutes."
                ])->withInput($request->only('email'));
            }
            
            // Record failed attempt for non-existent email
            LoginAttempt::recordFailedAttempt($email, $ipAddress);
            RateLimiter::hit('login-attempts:' . $ipAddress . ':' . $email, 300); // 5 minutes
            
            return back()->withErrors(['email' => 'Email is not registered!'])->withInput();
        }

        // If password is wrong
        if (!Hash::check($request->password, $user->password)) {
            // If already throttled, show throttle message instead of specific error
            if ($isThrottled) {
                return back()->withErrors([
                    'throttle' => "Account temporarily locked due to multiple failed attempts. Please try again in {$throttleTime} minutes."
                ])->withInput($request->only('email'));
            }
            
            // Record failed attempt for wrong password
            LoginAttempt::recordFailedAttempt($email, $ipAddress);
            RateLimiter::hit('login-attempts:' . $ipAddress . ':' . $email, 300); // 5 minutes
            
            return back()->withErrors(['password' => 'Incorrect password!'])->withInput();
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
        
        // Role-based redirection
        if ($user->account_type === 'admin' || $user->account_type === '1') {
            // Admin users go to admin dashboard
            return redirect()->route('dashboard')->with('success', 'Welcome back, Admin!');
        } else {
            // Regular employees go to employee dashboard
            return redirect()->route('employee.dashboard')->with('success', 'Welcome back!');
        }
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
        Auth::logout();                      // Logs the user out
        $request->session()->invalidate();  // Invalidate the session
        $request->session()->regenerateToken(); // Prevent CSRF reuse

        return redirect('/login');
    }
}