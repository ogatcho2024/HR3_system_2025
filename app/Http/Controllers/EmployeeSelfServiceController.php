<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\Timesheet;
use App\Http\Requests\StoreLeaveRequestRequest;
use App\Http\Requests\StoreTimesheetRequest;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class EmployeeSelfServiceController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->middleware('auth');
        $this->notificationService = $notificationService;
    }

    /**
     * Display the employee self-service dashboard.
     */
    public function index(): View
    {
        $user = Auth::user();
        $employee = $user->employee;
        
        // Get recent leave requests and timesheets
        $recentLeaveRequests = $user->leaveRequests()->latest()->take(5)->get();
        $recentTimesheets = $user->timesheets()->latest()->take(5)->get();
        
        // Get pending items count
        $pendingLeaveRequests = $user->leaveRequests()->pending()->count();
        $draftTimesheets = $user->timesheets()->draft()->count();
        
        return view('employee-self-service.dashboard', compact(
            'employee', 
            'recentLeaveRequests', 
            'recentTimesheets',
            'pendingLeaveRequests',
            'draftTimesheets'
        ));
    }

    /**
     * Display the employee profile page.
     */
    public function profile(): View
    {
        $user = Auth::user();
        $employee = $user->employee;
        
        return view('employee-self-service.profile', compact('employee', 'user'));
    }

    /**
     * Update the employee profile.
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $employee = $user->employee;
        
        $validatedData = $request->validate([
            'phone' => 'nullable|string|max:20',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
        ]);
        
        // Update user fields
        $user->update([
            'phone' => $validatedData['phone'] ?? $user->phone,
        ]);
        
        // Update employee fields
        if ($employee) {
            $employee->update([
                'emergency_contact_name' => $validatedData['emergency_contact_name'],
                'emergency_contact_phone' => $validatedData['emergency_contact_phone'],
                'address' => $validatedData['address'],
            ]);
        }
        
        return redirect()->route('employee.profile')
            ->with('success', 'Profile updated successfully!');
    }

    /**
     * Display leave requests.
     */
    public function leaveRequests(): View
    {
        $user = Auth::user();
        $leaveRequests = $user->leaveRequests()->latest()->paginate(10);
        
        return view('employee-self-service.leave-requests.index', compact('leaveRequests'));
    }

    /**
     * Show the form for creating a new leave request.
     */
    public function createLeaveRequest(): View
    {
        return view('employee-self-service.leave-requests.create');
    }

    /**
     * Store a newly created leave request.
     */
    public function storeLeaveRequest(StoreLeaveRequestRequest $request): RedirectResponse
    {
        $user = Auth::user();
        
        $validatedData = $request->validated();
        $validatedData['user_id'] = $user->id;
        
        // Calculate days requested
        $startDate = Carbon::parse($validatedData['start_date']);
        $endDate = Carbon::parse($validatedData['end_date']);
        $validatedData['days_requested'] = $startDate->diffInDays($endDate) + 1;
        
        // Handle file upload
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('leave-attachments', 'public');
            $validatedData['attachment_path'] = $path;
        }
        
        $leaveRequest = LeaveRequest::create($validatedData);
        
        // Create notification for leave request submission
        $this->notificationService->createLeaveRequestNotification(
            $user,
            'submitted',
            [
                'leave_type' => $leaveRequest->leave_type,
                'start_date' => $leaveRequest->start_date->format('Y-m-d'),
                'end_date' => $leaveRequest->end_date->format('Y-m-d'),
                'days_requested' => $leaveRequest->days_requested,
            ]
        );
        
        return redirect()->route('employee.leave-requests')
            ->with('success', 'Leave request submitted successfully!');
    }

    /**
     * Display timesheets.
     */
    public function timesheets(Request $request): View
    {
        $user = Auth::user();
        $query = $user->timesheets();
        
        // Apply status filter
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }
        
        // Apply month filter
        if ($request->filled('month')) {
            $month = $request->get('month');
            $year = $request->get('year', now()->year);
            $query->whereMonth('work_date', $month)
                  ->whereYear('work_date', $year);
        }
        
        $timesheets = $query->latest('work_date')->paginate(10);
        
        return view('employee-self-service.timesheets.index', compact('timesheets'));
    }

    /**
     * Show the form for creating a new timesheet.
     */
    public function createTimesheet(): View
    {
        return view('employee-self-service.timesheets.create');
    }

    /**
     * Store a newly created timesheet.
     */
    public function storeTimesheet(StoreTimesheetRequest $request): RedirectResponse
    {
        $user = Auth::user();
        
        $validatedData = $request->validated();
        $validatedData['user_id'] = $user->id;
        
        // Calculate hours worked if clock times are provided
        if ($validatedData['clock_in_time'] && $validatedData['clock_out_time']) {
            $clockIn = Carbon::createFromTimeString($validatedData['clock_in_time']);
            $clockOut = Carbon::createFromTimeString($validatedData['clock_out_time']);
            
            $totalMinutes = $clockOut->diffInMinutes($clockIn);
            
            // Subtract break time if provided
            if (isset($validatedData['break_start']) && isset($validatedData['break_end'])) {
                $breakStart = Carbon::createFromTimeString($validatedData['break_start']);
                $breakEnd = Carbon::createFromTimeString($validatedData['break_end']);
                $breakMinutes = $breakEnd->diffInMinutes($breakStart);
                $totalMinutes -= $breakMinutes;
            }
            
            $validatedData['hours_worked'] = round($totalMinutes / 60, 2);
        }
        
        $timesheet = Timesheet::create($validatedData);
        
        // Create notification if timesheet is submitted (not draft)
        if ($validatedData['status'] === 'submitted') {
            $this->notificationService->createTimesheetNotification(
                $user,
                'submitted',
                [
                    'work_date' => $timesheet->work_date->format('Y-m-d'),
                    'hours_worked' => $timesheet->hours_worked ?? 0,
                    'project_name' => $timesheet->project_name,
                ]
            );
        }
        
        return redirect()->route('employee.timesheets')
            ->with('success', 'Timesheet saved successfully!');
    }

    /**
     * Show the form for editing a timesheet.
     */
    public function editTimesheet(Timesheet $timesheet): View
    {
        // Ensure user can only edit their own timesheets
        if ($timesheet->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to timesheet.');
        }
        
        // Only allow editing of draft timesheets
        if ($timesheet->status !== 'draft') {
            return redirect()->route('employee.timesheets')
                ->with('error', 'You can only edit draft timesheets.');
        }
        
        return view('employee-self-service.timesheets.edit', compact('timesheet'));
    }

    /**
     * Update the specified timesheet.
     */
    public function updateTimesheet(StoreTimesheetRequest $request, Timesheet $timesheet): RedirectResponse
    {
        // Ensure user can only update their own timesheets
        if ($timesheet->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to timesheet.');
        }
        
        // Only allow updating of draft timesheets
        if ($timesheet->status !== 'draft') {
            return redirect()->route('employee.timesheets')
                ->with('error', 'You can only edit draft timesheets.');
        }
        
        $validatedData = $request->validated();
        
        // Calculate hours worked if clock times are provided
        if ($validatedData['clock_in_time'] && $validatedData['clock_out_time']) {
            $clockIn = Carbon::createFromTimeString($validatedData['clock_in_time']);
            $clockOut = Carbon::createFromTimeString($validatedData['clock_out_time']);
            
            $totalMinutes = $clockOut->diffInMinutes($clockIn);
            
            // Subtract break time if provided
            if (isset($validatedData['break_start']) && isset($validatedData['break_end'])) {
                $breakStart = Carbon::createFromTimeString($validatedData['break_start']);
                $breakEnd = Carbon::createFromTimeString($validatedData['break_end']);
                $breakMinutes = $breakEnd->diffInMinutes($breakStart);
                $totalMinutes -= $breakMinutes;
            }
            
            $validatedData['hours_worked'] = round($totalMinutes / 60, 2);
        }
        
        $timesheet->update($validatedData);
        
        return redirect()->route('employee.timesheets')
            ->with('success', 'Timesheet updated successfully!');
    }

    /**
     * Submit a timesheet for approval.
     */
    public function submitTimesheet(Timesheet $timesheet): RedirectResponse
    {
        // Ensure user can only submit their own timesheets
        if ($timesheet->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to timesheet.');
        }
        
        // Only allow submitting draft timesheets
        if ($timesheet->status !== 'draft') {
            return redirect()->route('employee.timesheets')
                ->with('error', 'This timesheet has already been submitted.');
        }
        
        $timesheet->update([
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);
        
        // Create notification for timesheet submission
        $this->notificationService->createTimesheetNotification(
            $user,
            'submitted',
            [
                'work_date' => $timesheet->work_date->format('Y-m-d'),
                'hours_worked' => $timesheet->hours_worked ?? 0,
                'project_name' => $timesheet->project_name,
            ]
        );
        
        return redirect()->route('employee.timesheets')
            ->with('success', 'Timesheet submitted for approval!');
    }
}
