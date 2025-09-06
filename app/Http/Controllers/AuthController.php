<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Employee;
use App\Models\Attendance;

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

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'Email is not registered!'])->withInput();
        }

        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Incorrect password!'])->withInput();
        }

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

    public function logout(Request $request)
    {
        Auth::logout();                      // Logs the user out
        $request->session()->invalidate();  // Invalidate the session
        $request->session()->regenerateToken(); // Prevent CSRF reuse

        return redirect('/login');
    }
}