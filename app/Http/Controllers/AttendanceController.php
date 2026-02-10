<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\ShiftRequest;
use App\Models\ShiftAssignment;
use App\Models\ShiftTemplate;
use App\Models\Timesheet;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use App\Services\AttendanceStatusService;
use App\Services\TimesheetSyncService;

class AttendanceController extends Controller
{
    private TimesheetSyncService $timesheetSyncService;

    public function __construct(TimesheetSyncService $timesheetSyncService)
    {
        $this->timesheetSyncService = $timesheetSyncService;
    }
    private function parseTimeValue(string $value): Carbon
    {
        if (preg_match('/\d{4}-\d{2}-\d{2}/', $value)) {
            return Carbon::parse($value);
        }

        $format = substr_count($value, ':') === 2 ? 'H:i:s' : 'H:i';
        return Carbon::createFromFormat($format, $value);
    }

    private function formatTimeValue(?string $value): ?string
    {
        if (empty($value)) {
            return null;
        }
        return $this->parseTimeValue($value)->format('H:i');
    }

    /**
     * Display attendance records with search and filter
     */
    public function index(Request $request): View
    {
        $query = Attendance::with('user');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%");
            });
        }

        // Filter by date
        if ($request->filled('date')) {
            $query->where('date', $request->get('date'));
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        $attendances = $query->latest()->paginate(15);

        return view('attendance.index', compact('attendances'));
    }

    /**
     * Show manual entry form
     */
    public function create(): View
    {
        $employees = Employee::with('user')
            ->active()
            ->get()
            ->map(function ($employee) {
                return (object) [
                    'id' => $employee->user_id, // Use user_id for the form
                    'name' => $employee->user->name ?? 'Unknown',
                    'employee_id' => $employee->employee_id,
                    'department' => $employee->department,
                    'position' => $employee->position
                ];
            });
            
        return view('attendance.create', compact('employees'));
    }

    /**
     * Store new attendance record
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'clock_in_time' => ['nullable', 'regex:/^\\d{2}:\\d{2}(:\\d{2})?$/'],
            'clock_out_time' => ['nullable', 'regex:/^\\d{2}:\\d{2}(:\\d{2})?$/'],
            'break_start' => ['nullable', 'regex:/^\\d{2}:\\d{2}(:\\d{2})?$/'],
            'break_end' => ['nullable', 'regex:/^\\d{2}:\\d{2}(:\\d{2})?$/'],
            'status' => 'required|in:present,late,absent,on_break',
            'notes' => 'nullable|string|max:500',
        ]);

        $validatedData['created_by'] = Auth::id();
        
        // Check if entry already exists for this user and date
        $existingEntry = Attendance::where('user_id', $validatedData['user_id'])
            ->whereDate('date', $validatedData['date'])
            ->first();
            
        if ($existingEntry) {
            // Update existing record instead of failing
            // Merge new data with existing data (don't overwrite with null values)
            $updateData = [];
            foreach ($validatedData as $key => $value) {
                if ($value !== null && $value !== '') {
                    $updateData[$key] = $value;
                }
            }
            
            // Calculate hours worked if both times are available
            $clockIn = $updateData['clock_in_time'] ?? $existingEntry->clock_in_time;
            $clockOut = $updateData['clock_out_time'] ?? $existingEntry->clock_out_time;
            $breakStart = $updateData['break_start'] ?? $existingEntry->break_start;
            $breakEnd = $updateData['break_end'] ?? $existingEntry->break_end;
            
            if ($clockIn && $clockOut) {
                $updateData['hours_worked'] = Attendance::calculateHoursFromTimes(
                    $clockIn,
                    $clockOut,
                    $breakStart,
                    $breakEnd
                );
                $updateData['overtime_hours'] = max(0, $updateData['hours_worked'] - 8.0);

                Log::info('[Attendance] store update calc', [
                    'attendance_id' => $existingEntry->id,
                    'clock_in_time' => $clockIn,
                    'clock_out_time' => $clockOut,
                    'break_start' => $breakStart,
                    'break_end' => $breakEnd,
                    'hours_worked' => $updateData['hours_worked'],
                    'overtime_hours' => $updateData['overtime_hours'],
                ]);
            }
            
            $existingEntry->update($updateData);
            $attendance = $existingEntry->refresh();
            
        } else {
            // Create new record
            // Calculate hours worked if clock in/out times are provided
            if (!empty($validatedData['clock_in_time']) && !empty($validatedData['clock_out_time'])) {
                $validatedData['hours_worked'] = Attendance::calculateHoursFromTimes(
                    $validatedData['clock_in_time'],
                    $validatedData['clock_out_time'],
                    $validatedData['break_start'] ?? null,
                    $validatedData['break_end'] ?? null
                );
                $validatedData['overtime_hours'] = max(0, $validatedData['hours_worked'] - 8.0);

                Log::info('[Attendance] store create calc', [
                    'clock_in_time' => $validatedData['clock_in_time'],
                    'clock_out_time' => $validatedData['clock_out_time'],
                    'break_start' => $validatedData['break_start'] ?? null,
                    'break_end' => $validatedData['break_end'] ?? null,
                    'hours_worked' => $validatedData['hours_worked'],
                    'overtime_hours' => $validatedData['overtime_hours'],
                ]);
            }

            $attendance = Attendance::create($validatedData);
        }
        
        // Sync to timesheet
        $this->syncAttendanceToTimesheet($attendance);

        // Return JSON response for AJAX requests
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Attendance record created successfully!',
                'data' => $attendance->load('user')
            ]);
        }

        return redirect()->route('attendanceTimeTracking')
            ->with('success', 'Attendance record created successfully!');
    }

    /**
     * Store manual attendance entry for the authenticated user (AJAX).
     */
    public function storeSelfManualEntry(Request $request): \Illuminate\Http\JsonResponse
    {
        $validatedData = $request->validate([
            'date' => 'required|date',
            'clock_in_time' => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'clock_out_time' => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'break_start' => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'break_end' => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'status' => 'required|in:present,late,absent,on_break',
            'notes' => 'nullable|string|max:500',
        ]);

        $userId = Auth::id();
        $validatedData['user_id'] = $userId;
        $validatedData['created_by'] = $userId;

        $existingEntry = Attendance::where('user_id', $userId)
            ->whereDate('date', $validatedData['date'])
            ->first();

        $clockIn = $validatedData['clock_in_time'] ?? null;
        $clockOut = $validatedData['clock_out_time'] ?? null;

        if ($existingEntry && $clockIn && $existingEntry->clock_in_time) {
            return response()->json([
                'success' => false,
                'message' => 'Time In already exists for this date.',
                'errors' => ['clock_in_time' => ['Time In already exists for this date.']],
            ], 422);
        }

        $effectiveClockIn = $clockIn ?: ($existingEntry->clock_in_time ?? null);
        if ($clockOut && !$effectiveClockIn) {
            return response()->json([
                'success' => false,
                'message' => 'Time Out cannot be set without an existing Time In.',
                'errors' => ['clock_out_time' => ['Time Out cannot be set without an existing Time In.']],
            ], 422);
        }

        if ($clockIn && $clockOut) {
            $minutes = Attendance::calculateHoursFromTimes($clockIn, $clockOut) * 60;
            if ($minutes <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Time Out must be later than Time In.',
                    'errors' => ['clock_out_time' => ['Time Out must be later than Time In.']],
                ], 422);
            }
        }

        if ($existingEntry) {
            $updateData = [];
            foreach ($validatedData as $key => $value) {
                if ($value !== null && $value !== '') {
                    $updateData[$key] = $value;
                }
            }

            if (!empty($updateData['clock_in_time']) && !empty($updateData['clock_out_time'])) {
                $updateData['hours_worked'] = Attendance::calculateHoursFromTimes(
                    $updateData['clock_in_time'],
                    $updateData['clock_out_time'],
                    $updateData['break_start'] ?? $existingEntry->break_start,
                    $updateData['break_end'] ?? $existingEntry->break_end
                );
                $updateData['overtime_hours'] = max(0, $updateData['hours_worked'] - 8.0);
            }

            $existingEntry->update($updateData);
            $attendance = $existingEntry->refresh();
        } else {
            if (!empty($validatedData['clock_in_time']) && !empty($validatedData['clock_out_time'])) {
                $validatedData['hours_worked'] = Attendance::calculateHoursFromTimes(
                    $validatedData['clock_in_time'],
                    $validatedData['clock_out_time'],
                    $validatedData['break_start'] ?? null,
                    $validatedData['break_end'] ?? null
                );
                $validatedData['overtime_hours'] = max(0, $validatedData['hours_worked'] - 8.0);
            }
            $attendance = Attendance::create($validatedData);
        }

        $this->syncAttendanceToTimesheet($attendance);

        return response()->json([
            'success' => true,
            'message' => 'Attendance entry saved successfully!',
            'data' => [
                'id' => $attendance->id,
                'date' => $attendance->date ? $attendance->date->toDateString() : null,
                'status' => $attendance->status,
                'clock_in_time' => $this->formatTimeValue($attendance->clock_in_time),
                'clock_out_time' => $this->formatTimeValue($attendance->clock_out_time),
                'break_start' => $this->formatTimeValue($attendance->break_start),
                'break_end' => $this->formatTimeValue($attendance->break_end),
                'hours_worked' => $attendance->hours_worked,
                'overtime_hours' => $attendance->overtime_hours,
                'notes' => $attendance->notes,
            ],
        ]);
    }

    /**
     * Store manual attendance entry for Admin/Super Admin (AJAX).
     */
    public function storeAdminManualEntry(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        if (!$user || (!$user->isAdmin() && !$user->isSuperAdmin())) {
            abort(403, 'Unauthorized access. Admin or Super Admin only.');
        }

        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'clock_in_time' => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'clock_out_time' => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'break_start' => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'break_end' => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'status' => 'required|in:present,late,absent,on_break',
            'notes' => 'nullable|string|max:500',
        ]);

        $validatedData['created_by'] = $user->id;

        $existingEntry = Attendance::where('user_id', $validatedData['user_id'])
            ->whereDate('date', $validatedData['date'])
            ->first();

        $clockIn = $validatedData['clock_in_time'] ?? null;
        $clockOut = $validatedData['clock_out_time'] ?? null;

        if ($existingEntry && $clockIn && $existingEntry->clock_in_time) {
            return response()->json([
                'success' => false,
                'message' => 'Time In already exists for this date.',
                'errors' => ['clock_in_time' => ['Time In already exists for this date.']],
            ], 422);
        }

        $effectiveClockIn = $clockIn ?: ($existingEntry->clock_in_time ?? null);
        if ($clockOut && !$effectiveClockIn) {
            return response()->json([
                'success' => false,
                'message' => 'Time Out cannot be set without an existing Time In.',
                'errors' => ['clock_out_time' => ['Time Out cannot be set without an existing Time In.']],
            ], 422);
        }

        if ($clockIn && $clockOut) {
            $minutes = Attendance::calculateHoursFromTimes($clockIn, $clockOut) * 60;
            if ($minutes <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Time Out must be later than Time In.',
                    'errors' => ['clock_out_time' => ['Time Out must be later than Time In.']],
                ], 422);
            }
        }

        if ($existingEntry) {
            $updateData = [];
            foreach ($validatedData as $key => $value) {
                if ($value !== null && $value !== '') {
                    $updateData[$key] = $value;
                }
            }

            if (!empty($updateData['clock_in_time']) && !empty($updateData['clock_out_time'])) {
                $updateData['hours_worked'] = Attendance::calculateHoursFromTimes(
                    $updateData['clock_in_time'],
                    $updateData['clock_out_time'],
                    $updateData['break_start'] ?? $existingEntry->break_start,
                    $updateData['break_end'] ?? $existingEntry->break_end
                );
                $updateData['overtime_hours'] = max(0, $updateData['hours_worked'] - 8.0);
            }

            $existingEntry->update($updateData);
            $attendance = $existingEntry->refresh();
        } else {
            if (!empty($validatedData['clock_in_time']) && !empty($validatedData['clock_out_time'])) {
                $validatedData['hours_worked'] = Attendance::calculateHoursFromTimes(
                    $validatedData['clock_in_time'],
                    $validatedData['clock_out_time'],
                    $validatedData['break_start'] ?? null,
                    $validatedData['break_end'] ?? null
                );
                $validatedData['overtime_hours'] = max(0, $validatedData['hours_worked'] - 8.0);
            }
            $attendance = Attendance::create($validatedData);
        }

        $this->syncAttendanceToTimesheet($attendance);

        return response()->json([
            'success' => true,
            'message' => 'Attendance entry saved successfully!',
            'data' => [
                'id' => $attendance->id,
                'date' => $attendance->date ? $attendance->date->toDateString() : null,
                'status' => $attendance->status,
                'clock_in_time' => $this->formatTimeValue($attendance->clock_in_time),
                'clock_out_time' => $this->formatTimeValue($attendance->clock_out_time),
                'break_start' => $this->formatTimeValue($attendance->break_start),
                'break_end' => $this->formatTimeValue($attendance->break_end),
                'hours_worked' => $attendance->hours_worked,
                'overtime_hours' => $attendance->overtime_hours,
                'notes' => $attendance->notes,
            ],
        ]);
    }

    /**
     * Return active employees for manual entry dropdown (Admin/Super Admin only).
     */
    public function getActiveEmployees(): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        if (!$user || (!$user->isAdmin() && !$user->isSuperAdmin())) {
            abort(403, 'Unauthorized access. Admin or Super Admin only.');
        }

        $employees = Employee::with('user')
            ->active()
            ->get()
            ->map(function ($employee) {
                $name = trim(($employee->user->name ?? '') . ' ' . ($employee->user->lastname ?? ''));
                return [
                    'user_id' => $employee->user_id,
                    'employee_id' => $employee->employee_id,
                    'name' => $name !== '' ? $name : 'Unknown',
                    'department' => $employee->department,
                    'position' => $employee->position,
                ];
            });

        return response()->json([
            'success' => true,
            'employees' => $employees,
        ]);
    }

    /**
     * Show edit form for attendance record
     */
    public function edit(Attendance $attendance): View
    {
        $employees = Employee::with('user')
            ->active()
            ->get()
            ->map(function ($employee) {
                return (object) [
                    'id' => $employee->user_id, // Use user_id for the form
                    'name' => $employee->user->name ?? 'Unknown',
                    'employee_id' => $employee->employee_id,
                    'department' => $employee->department,
                    'position' => $employee->position
                ];
            });
            
        return view('attendance.edit', compact('attendance', 'employees'));
    }

    /**
     * Update attendance record
     */
    public function update(Request $request, Attendance $attendance)
    {
        try {
            // For JSON requests (AJAX), only validate the fields that can be updated
            if ($request->expectsJson()) {
                $validatedData = $request->validate([
                    'time_start' => ['nullable', 'regex:/^\\d{2}:\\d{2}(:\\d{2})?$/'],
                    'time_end' => ['nullable', 'regex:/^\\d{2}:\\d{2}(:\\d{2})?$/'], 
                    'status' => 'nullable|in:draft,submitted,approved,rejected,present,late,absent,on_break',
                    'notes' => 'nullable|string|max:500',
                ]);
                
                // Map frontend field names to database field names
                $updateData = [];
                if (isset($validatedData['time_start'])) {
                    $updateData['clock_in_time'] = $validatedData['time_start'];
                }
                if (isset($validatedData['time_end'])) {
                    $updateData['clock_out_time'] = $validatedData['time_end'];
                }
                // Remove status update since the column doesn't exist in attendances table
                // if (isset($validatedData['status'])) {
                //     $updateData['status'] = $validatedData['status'];
                // }
                if (isset($validatedData['notes'])) {
                    $updateData['notes'] = $validatedData['notes'];
                }
                
            } else {
                // For form-based requests, validate all fields
                $validatedData = $request->validate([
                    'user_id' => 'required|exists:users,id',
                    'date' => 'required|date',
                    'clock_in_time' => ['nullable', 'regex:/^\\d{2}:\\d{2}(:\\d{2})?$/'],
                    'clock_out_time' => ['nullable', 'regex:/^\\d{2}:\\d{2}(:\\d{2})?$/'],
                    'break_start' => ['nullable', 'regex:/^\\d{2}:\\d{2}(:\\d{2})?$/'],
                    'break_end' => ['nullable', 'regex:/^\\d{2}:\\d{2}(:\\d{2})?$/'],
                    'status' => 'required|in:present,late,absent,on_break',
                    'notes' => 'nullable|string|max:500',
                ]);
                $updateData = $validatedData;
            }

            // Calculate hours worked if both clock in and out times are provided
            $clockIn = $updateData['clock_in_time'] ?? $attendance->clock_in_time;
            $clockOut = $updateData['clock_out_time'] ?? $attendance->clock_out_time;
            $breakStart = $updateData['break_start'] ?? $attendance->break_start;
            $breakEnd = $updateData['break_end'] ?? $attendance->break_end;
            
            if (!empty($clockIn) && !empty($clockOut)) {
                $updateData['hours_worked'] = Attendance::calculateHoursFromTimes(
                    $clockIn,
                    $clockOut,
                    $breakStart,
                    $breakEnd
                );
                $updateData['overtime_hours'] = max(0, $updateData['hours_worked'] - 8.0);

                Log::info('[Attendance] update calc', [
                    'attendance_id' => $attendance->id,
                    'clock_in_time' => $clockIn,
                    'clock_out_time' => $clockOut,
                    'break_start' => $breakStart,
                    'break_end' => $breakEnd,
                    'hours_worked' => $updateData['hours_worked'],
                    'overtime_hours' => $updateData['overtime_hours'],
                ]);
            }

            $updated = $attendance->update($updateData);
            $attendance->refresh();
            $changed = $attendance->wasChanged();
            if (!$updated || !$changed) {
                Log::warning('[Attendance] update no changes', [
                    'attendance_id' => $attendance->id,
                    'update_data' => $updateData,
                    'updated' => $updated,
                    'changed' => $changed,
                ]);
            }
            
            // Sync to timesheet
            $this->syncAttendanceToTimesheet($attendance);

            // Return JSON response for AJAX requests
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Attendance record updated successfully!',
                    'data' => $attendance->load('user'),
                    'changed' => $changed
                ]);
            }

            return redirect()->route('attendanceTimeTracking')
                ->with('success', 'Attendance record updated successfully!');
                
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update attendance record: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Failed to update attendance record: ' . $e->getMessage());
        }
    }

    /**
     * Delete attendance record
     */
    public function destroy(Attendance $attendance)
    {
        try {
            $attendance->delete();
            
            // Return JSON for AJAX requests
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Attendance record deleted successfully!'
                ]);
            }

            return redirect()->route('attendanceTimeTracking')
                ->with('success', 'Attendance record deleted successfully!');
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete attendance record: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Failed to delete attendance record: ' . $e->getMessage());
        }
    }

    /**
     * Show a single attendance record (JSON for modal view).
     */
    public function show(Request $request, Attendance $attendance)
    {
        if (!$request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'JSON request required.'
            ], 406);
        }

        $attendance->load(['user.employee', 'createdBy']);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $attendance->id,
                'user_id' => $attendance->user_id,
                'employee_name' => $attendance->user->name ?? 'Unknown',
                'employee_id' => $attendance->user->employee->employee_id ?? null,
                'department' => $attendance->user->employee->department ?? null,
                'position' => $attendance->user->employee->position ?? null,
                'date' => $attendance->date ? $attendance->date->toDateString() : null,
                'status' => $attendance->status,
                'clock_in_time' => $this->formatTimeValue($attendance->clock_in_time),
                'clock_out_time' => $this->formatTimeValue($attendance->clock_out_time),
                'break_start' => $this->formatTimeValue($attendance->break_start),
                'break_end' => $this->formatTimeValue($attendance->break_end),
                'hours_worked' => $attendance->hours_worked,
                'overtime_hours' => $attendance->overtime_hours,
                'notes' => $attendance->notes,
                'created_by' => $attendance->createdBy->name ?? null,
                'created_at' => $attendance->created_at ? $attendance->created_at->toDateTimeString() : null,
                'updated_at' => $attendance->updated_at ? $attendance->updated_at->toDateTimeString() : null,
            ]
        ]);
    }

    /**
     * Clock in an employee
     */
    public function clockIn(Request $request): RedirectResponse
    {
        $user_id = $request->get('user_id', Auth::id());
        
        // Check if already clocked in today
        $existing = Attendance::where('user_id', $user_id)
            ->where('date', now()->toDateString())
            ->first();

        if ($existing && $existing->clock_in_time) {
            return redirect()->back()->with('error', 'Already clocked in today!');
        }

        $clockInTime = now()->format('H:i');
        
        if ($existing) {
            $existing->update([
                'clock_in_time' => $clockInTime,
                'status' => 'present'
            ]);
        } else {
            $existing = Attendance::create([
                'user_id' => $user_id,
                'date' => now()->toDateString(),
                'clock_in_time' => $clockInTime,
                'status' => 'present',
                'created_by' => Auth::id()
            ]);
        }
        
        // Create or update timesheet entry
        $this->syncAttendanceToTimesheet($existing);

        return redirect()->back()->with('success', 'Clocked in successfully!');
    }

    /**
     * Clock out an employee
     */
    public function clockOut(Request $request): RedirectResponse
    {
        $user_id = $request->get('user_id', Auth::id());
        
        $attendance = Attendance::where('user_id', $user_id)
            ->where('date', now()->toDateString())
            ->first();

        if (!$attendance || !$attendance->clock_in_time) {
            return redirect()->back()->with('error', 'Not clocked in today!');
        }

        if ($attendance->clock_out_time) {
            return redirect()->back()->with('error', 'Already clocked out today!');
        }

        $attendance->update([
            'clock_out_time' => now()->format('H:i'),
        ]);
        
        // Calculate hours worked and update timesheet
        $attendance->refresh();
        $this->syncAttendanceToTimesheet($attendance);

        return redirect()->back()->with('success', 'Clocked out successfully!');
    }

    /**
     * Start break for an employee
     */
    public function startBreak(Request $request): RedirectResponse
    {
        $user_id = $request->get('user_id', Auth::id());
        
        $attendance = Attendance::where('user_id', $user_id)
            ->where('date', now()->toDateString())
            ->first();

        if (!$attendance || !$attendance->clock_in_time) {
            return redirect()->back()->with('error', 'Not clocked in today!');
        }

        $attendance->update([
            'break_start' => now()->format('H:i'),
            'status' => 'on_break'
        ]);

        return redirect()->back()->with('success', 'Break started successfully!');
    }

    /**
     * End break for an employee
     */
    public function endBreak(Request $request): RedirectResponse
    {
        $user_id = $request->get('user_id', Auth::id());
        
        $attendance = Attendance::where('user_id', $user_id)
            ->where('date', now()->toDateString())
            ->first();

        if (!$attendance || !$attendance->break_start || $attendance->break_end) {
            return redirect()->back()->with('error', 'Break not started or already ended!');
        }

        $attendance->update([
            'break_end' => now()->format('H:i'),
            'status' => 'present'
        ]);

        return redirect()->back()->with('success', 'Break ended successfully!');
    }

    /**
     * Get real-time employee data for the tracking dashboard
     */
    public function getRealTimeData(Request $request)
    {
        try {
            $today = now()->toDateString();
            $statusService = new AttendanceStatusService();
            
            // Get all employees with their today's attendance status
            $employees = \App\Models\Employee::with(['user'])
                ->active()
                ->get()
                ->map(function ($employee) use ($today, $statusService) {
                    try {
                        // Skip if no user relationship
                        if (!$employee->user) {
                            return null;
                        }
                        
                        $result = $statusService->resolveForEmployee($employee, Carbon::parse($today));
                        $todayAttendance = $result['attendance'];
                        $status = $result['status'];
                        $checkIn = $todayAttendance ? $todayAttendance->clock_in_time : null;
                        $hoursWorked = $todayAttendance ? $todayAttendance->hours_worked : null;
                        
                        // Generate initials for avatar
                        $name = $employee->user->name ?? 'Unknown';
                        $nameParts = explode(' ', $name);
                        $initials = '';
                        foreach ($nameParts as $part) {
                            if (!empty($part)) {
                                $initials .= strtoupper(substr($part, 0, 1));
                            }
                        }
                        if (strlen($initials) > 2) {
                            $initials = substr($initials, 0, 2);
                        }
                        if (empty($initials)) {
                            $initials = 'U';
                        }
                        
                        // Assign colors based on hash of name for consistency
                        $colors = ['blue', 'green', 'purple', 'red', 'indigo', 'pink', 'yellow', 'teal', 'orange', 'cyan'];
                        $colorIndex = abs(crc32($name)) % count($colors);
                        
                        return [
                            'id' => $employee->id,
                            'user_id' => $employee->user_id,
                            'attendance_id' => $todayAttendance ? $todayAttendance->id : null,
                            'name' => $name,
                            'position' => $employee->position ?? 'No Position',
                            'department' => $employee->department ?? 'No Department',
                            'status' => $status,
                            'checkIn' => $this->formatTimeValue($checkIn),
                            'hours' => $hoursWorked ? number_format($hoursWorked, 1) : null,
                            'avatar' => $initials,
                            'color' => $colors[$colorIndex]
                        ];
                    } catch (\Exception $e) {
                        // Skip this employee if there's an error
                        \Log::error('Error processing employee ' . $employee->id . ': ' . $e->getMessage());
                        return null;
                    }
                })
                ->filter(); // Remove null values
                
            // Calculate statistics
            $stats = [
                'total' => $employees->count(),
                'present' => $employees->whereIn('status', ['present', 'break'])->count(),
                'late' => $employees->where('status', 'late')->count(),
                'absent' => $employees->where('status', 'absent')->count(),
                'break' => $employees->where('status', 'break')->count(),
                'no_schedule' => $employees->where('status', 'no_schedule')->count(),
                'on_leave' => $employees->where('status', 'on_leave')->count(),
                'scheduled' => $employees->where('status', 'scheduled')->count(),
            ];
            
            // Filter by status if requested
            if ($request->filled('status') && $request->status !== 'all') {
                if ($request->status === 'present') {
                    // Present filter should include both 'present' and 'break' employees
                    $employees = $employees->whereIn('status', ['present', 'break']);
                } elseif ($request->status === 'break') {
                    // Break filter shows only employees currently on break
                    $employees = $employees->where('status', 'break');
                } else {
                    // Other filters (late, absent) work as normal
                    $employees = $employees->where('status', $request->status);
                }
            }
            
            return response()->json([
                'employees' => $employees->values(),
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in getRealTimeData: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to load employee data',
                'message' => $e->getMessage(),
                'employees' => [],
                'stats' => [
                    'total' => 0,
                    'present' => 0,
                    'late' => 0,
                    'absent' => 0,
                    'break' => 0,
                ]
            ], 500);
        }
    }

    /**
     * Get today's attendance details for a specific employee (Clock In/Out modal).
     */
    public function getEmployeeTodayDetails(\Illuminate\Http\Request $request, \App\Models\Employee $employee)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        // RBAC: Employee can only view their own data
        if ($user->isEmployee()) {
            if (!$employee->user_id || $employee->user_id !== $user->id) {
                return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
            }
        }

        $today = now()->toDateString();
        $attendance = Attendance::where('user_id', $employee->user_id)
            ->whereDate('date', $today)
            ->first();

        $hoursWorked = null;
        $overtime = null;
        if ($attendance) {
            if ($attendance->clock_in_time && $attendance->clock_out_time) {
                $hoursWorked = number_format($attendance->calculateHours(), 2);
                $overtime = number_format($attendance->calculateOvertime(), 2);
            } elseif ($attendance->hours_worked !== null) {
                $hoursWorked = number_format((float) $attendance->hours_worked, 2);
                $overtime = $attendance->overtime_hours !== null ? number_format((float) $attendance->overtime_hours, 2) : null;
            }
        }

        $logs = \App\Models\QrAttendanceLog::forEmployee($employee->id)
            ->orderBy('scanned_at', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'type' => $log->log_type,
                    'time' => $log->scanned_at ? $log->scanned_at->format('Y-m-d H:i:s') : null,
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'data' => [
                'employee' => [
                    'id' => $employee->id,
                    'name' => $employee->user ? $employee->user->name . ' ' . $employee->user->lastname : 'Unknown',
                    'employee_id' => $employee->employee_id,
                    'department' => $employee->department,
                    'position' => $employee->position,
                ],
                'attendance' => [
                    'date' => $today,
                    'status' => $attendance ? $attendance->status : null,
                    'time_in' => $attendance && $attendance->clock_in_time ? \Carbon\Carbon::parse($attendance->clock_in_time)->format('H:i:s') : null,
                    'time_out' => $attendance && $attendance->clock_out_time ? \Carbon\Carbon::parse($attendance->clock_out_time)->format('H:i:s') : null,
                    'break_start' => $attendance && $attendance->break_start ? \Carbon\Carbon::parse($attendance->break_start)->format('H:i:s') : null,
                    'break_end' => $attendance && $attendance->break_end ? \Carbon\Carbon::parse($attendance->break_end)->format('H:i:s') : null,
                    'total_hours' => $hoursWorked,
                    'overtime' => $overtime,
                ],
            ],
            'logs' => $logs,
        ]);
    }

    /**
     * Dashboard method to display main HR dashboard with real-time attendance data
     */
    public function dashboard(): View
    {
        $today = now()->toDateString();
        
        // Get total active employees
        $totalEmployees = \App\Models\Employee::active()->count();
        
        $statusService = new AttendanceStatusService();
        $employees = \App\Models\Employee::with('user')->active()->get();
        $statusCounts = [
            'present' => 0,
            'late' => 0,
            'absent' => 0,
            'on_break' => 0,
            'no_schedule' => 0,
            'on_leave' => 0,
            'scheduled' => 0,
        ];

        foreach ($employees as $employee) {
            if (!$employee->user) {
                continue;
            }
            $result = $statusService->resolveForEmployee($employee, Carbon::parse($today));
            $status = $result['status'];
            if ($status === 'break') {
                $statusCounts['on_break']++;
            } elseif (array_key_exists($status, $statusCounts)) {
                $statusCounts[$status]++;
            }
        }

        $attendanceStats = [
            'present' => $statusCounts['present'],
            'late' => $statusCounts['late'],
            'absent' => $statusCounts['absent'],
            'on_break' => $statusCounts['on_break'],
            'no_schedule' => $statusCounts['no_schedule'],
            'on_leave' => $statusCounts['on_leave'],
            'scheduled' => $statusCounts['scheduled'],
        ];
        
        // Calculate today's attendance (present + late + on_break)
        $todayAttendance = $attendanceStats['present'] + $attendanceStats['late'] + $attendanceStats['on_break'];
        
        // Calculate attendance percentage
        $attendancePercentage = $totalEmployees > 0 ? round(($todayAttendance / $totalEmployees) * 100, 1) : 0;
        
        $pendingLeaveRequests = LeaveRequest::where('status', 'pending')->count();
        $pendingShiftRequests = ShiftRequest::where('status', 'pending')->count();
        $pendingRequests = $pendingLeaveRequests + $pendingShiftRequests;

        // Recent activities (leave + shift + attendance)
        $recentActivities = collect();

        $recentLeaveRequests = LeaveRequest::with('user')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        foreach ($recentLeaveRequests as $request) {
            $name = trim(($request->user->name ?? '') . ' ' . ($request->user->lastname ?? '')) ?: 'Employee';
            $status = $request->status ?? 'pending';
            $recentActivities->push([
                'color' => $status === 'approved' ? 'green' : ($status === 'rejected' ? 'red' : 'yellow'),
                'icon' => $status === 'approved' ? 'check' : ($status === 'rejected' ? 'x' : 'clock'),
                'message' => 'Leave request from <strong>' . e($name) . '</strong> (' . e($status) . ')',
                'time' => optional($request->created_at)->diffForHumans() ?? 'just now',
                'timestamp' => $request->created_at,
            ]);
        }

        $recentShiftRequests = ShiftRequest::with('user')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        foreach ($recentShiftRequests as $request) {
            $name = trim(($request->user->name ?? '') . ' ' . ($request->user->lastname ?? '')) ?: 'Employee';
            $status = $request->status ?? 'pending';
            $type = ucfirst(str_replace('_', ' ', $request->request_type));
            $recentActivities->push([
                'color' => $status === 'approved' ? 'green' : ($status === 'rejected' ? 'red' : 'yellow'),
                'icon' => $status === 'approved' ? 'check' : ($status === 'rejected' ? 'x' : 'clock'),
                'message' => 'Shift request (' . e($type) . ') from <strong>' . e($name) . '</strong> (' . e($status) . ')',
                'time' => optional($request->created_at)->diffForHumans() ?? 'just now',
                'timestamp' => $request->created_at,
            ]);
        }

        $recentAttendance = Attendance::with('user')
            ->orderByDesc('updated_at')
            ->limit(5)
            ->get();

        foreach ($recentAttendance as $attendance) {
            $name = trim(($attendance->user->name ?? '') . ' ' . ($attendance->user->lastname ?? '')) ?: 'Employee';
            $recentActivities->push([
                'color' => 'blue',
                'icon' => 'user',
                'message' => 'Attendance updated for <strong>' . e($name) . '</strong>',
                'time' => optional($attendance->updated_at)->diffForHumans() ?? 'just now',
                'timestamp' => $attendance->updated_at,
            ]);
        }

        $recentActivities = $recentActivities
            ->sortByDesc('timestamp')
            ->take(5)
            ->values()
            ->map(function ($item) {
                unset($item['timestamp']);
                return $item;
            })
            ->toArray();

        // Upcoming events (approved leaves + upcoming assignments)
        $upcomingEvents = collect();

        $upcomingLeaves = LeaveRequest::with('user')
            ->where('status', 'approved')
            ->whereDate('start_date', '>=', $today)
            ->orderBy('start_date')
            ->limit(5)
            ->get();

        foreach ($upcomingLeaves as $leave) {
            $name = trim(($leave->user->name ?? '') . ' ' . ($leave->user->lastname ?? '')) ?: 'Employee';
            $eventDate = Carbon::parse($leave->start_date);
            $upcomingEvents->push([
                'date_label' => $eventDate->format('M j, Y'),
                'day' => $eventDate->format('d'),
                'time' => 'All day',
                'title' => 'Leave: ' . e($name),
                'color' => 'red',
                'event_date' => $eventDate->toDateString(),
            ]);
        }

        $upcomingAssignments = ShiftAssignment::with(['employee.user', 'shiftTemplate'])
            ->whereDate('assignment_date', '>=', $today)
            ->orderBy('assignment_date')
            ->limit(5)
            ->get();

        foreach ($upcomingAssignments as $assignment) {
            $employeeName = trim(($assignment->employee->user->name ?? '') . ' ' . ($assignment->employee->user->lastname ?? '')) ?: 'Employee';
            $shiftName = $assignment->shiftTemplate->name ?? 'Shift';
            $timeRange = $assignment->shiftTemplate && $assignment->shiftTemplate->start_time
                ? Carbon::parse($assignment->shiftTemplate->start_time)->format('g:i A')
                : 'TBD';
            $eventDate = Carbon::parse($assignment->assignment_date);

            $upcomingEvents->push([
                'date_label' => $eventDate->format('M j, Y'),
                'day' => $eventDate->format('d'),
                'time' => $timeRange,
                'title' => 'Shift: ' . e($shiftName) . ' (' . e($employeeName) . ')',
                'color' => 'blue',
                'event_date' => $eventDate->toDateString(),
            ]);
        }

        $upcomingEvents = $upcomingEvents
            ->sortBy('event_date')
            ->take(3)
            ->values()
            ->map(function ($event) {
                unset($event['event_date']);
                return $event;
            })
            ->toArray();
        
        return view('dashb', compact(
            'totalEmployees',
            'todayAttendance', 
            'pendingRequests',
            'attendanceStats',
            'attendancePercentage',
            'recentActivities',
            'upcomingEvents'
        ));
    }

    /**
     * Display all attendance activities with search and filter
     */
    public function getAllActivities(Request $request): View
    {
        $query = Attendance::with(['user.employee', 'createdBy'])
            ->where(function($q) {
                $q->whereNotNull('clock_in_time')
                  ->orWhereNotNull('clock_out_time')
                  ->orWhereNotNull('break_start')
                  ->orWhereNotNull('break_end');
            });

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->get('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->get('date_to'));
        }

        // Filter by activity type
        if ($request->filled('activity_type')) {
            $activityType = $request->get('activity_type');
            switch ($activityType) {
                case 'clock_in':
                    $query->whereNotNull('clock_in_time');
                    break;
                case 'clock_out':
                    $query->whereNotNull('clock_out_time');
                    break;
                case 'break_start':
                    $query->whereNotNull('break_start');
                    break;
                case 'break_end':
                    $query->whereNotNull('break_end');
                    break;
            }
        }

        // Filter by department
        if ($request->filled('department')) {
            $department = $request->get('department');
            $query->whereHas('user.employee', function ($q) use ($department) {
                $q->where('department', $department);
            });
        }

        // Order by most recent
        $activities = $query->orderBy('updated_at', 'desc')
                           ->orderBy('date', 'desc')
                           ->paginate(50);

        // Get unique departments for filter from employees table
        $departments = \App\Models\Employee::whereNotNull('department')
                          ->distinct()
                          ->pluck('department')
                          ->sort()
                          ->values();

        return view('attendance.activities', compact('activities', 'departments'));
    }
    
    /**
     * Get analytics data for Reports & Analytics tab
     */
    public function getAnalyticsData(Request $request)
    {
        $period = $request->get('period', 'daily'); // daily, weekly, monthly, yearly
        $today = Carbon::now();
        [$employeeIds, $totalEmployees, $departments, $employees] = $this->resolveAnalyticsFilters($request);

        switch ($period) {
            case 'daily':
                $data = $this->getDailyAnalytics($today, $employeeIds, $totalEmployees);
                break;
            case 'weekly':
                $data = $this->getWeeklyAnalytics($today, $employeeIds, $totalEmployees);
                break;
            case 'monthly':
                $data = $this->getMonthlyAnalytics($today, $employeeIds, $totalEmployees);
                break;
            case 'yearly':
                $data = $this->getYearlyAnalytics($today, $employeeIds, $totalEmployees);
                break;
            default:
                $data = $this->getDailyAnalytics($today, $employeeIds, $totalEmployees);
                break;
        }

        return response()->json([
            'success' => true,
            'data' => array_merge($data, [
                'departments' => $departments,
                'employees' => $employees,
            ]),
        ]);
    }

    private function resolveAnalyticsFilters(Request $request): array
    {
        $department = $request->get('department', 'all');
        $employee = $request->get('employee', 'all');

        $departments = Employee::active()
            ->whereNotNull('department')
            ->distinct()
            ->orderBy('department')
            ->pluck('department')
            ->values();

        $employeeOptionsQuery = Employee::active()
            ->whereNotNull('user_id')
            ->with('user');

        if (!empty($department) && $department !== 'all') {
            $employeeOptionsQuery->where('department', $department);
        }

        $employees = $employeeOptionsQuery
            ->get()
            ->map(function (Employee $employee) {
                $name = trim(($employee->user->name ?? '') . ' ' . ($employee->user->lastname ?? ''));
                if ($name === '') {
                    $name = $employee->employee_id ?: ('User #' . $employee->user_id);
                }
                return [
                    'id' => $employee->user_id,
                    'name' => $name,
                ];
            })
            ->sortBy('name')
            ->values()
            ->all();

        $filterQuery = Employee::active()->whereNotNull('user_id');
        if (!empty($department) && $department !== 'all') {
            $filterQuery->where('department', $department);
        }
        if (!empty($employee) && $employee !== 'all') {
            $filterQuery->where('user_id', $employee);
        }

        $employeeIds = $filterQuery->pluck('user_id')->filter()->values()->all();
        $totalEmployees = count($employeeIds);

        return [$employeeIds, $totalEmployees, $departments, $employees];
    }

    private function buildAnalyticsFilterSummary(Request $request): string
    {
        $department = $request->get('department', 'all');
        $employee = $request->get('employee', 'all');

        $parts = [];
        if (!empty($department) && $department !== 'all') {
            $parts[] = 'Department: ' . $department;
        }

        if (!empty($employee) && $employee !== 'all') {
            $user = User::find($employee);
            if ($user) {
                $name = trim(($user->name ?? '') . ' ' . ($user->lastname ?? ''));
                $parts[] = 'Employee: ' . ($name !== '' ? $name : ('User #' . $user->id));
            } else {
                $parts[] = 'Employee: User #' . $employee;
            }
        }

        if (empty($parts)) {
            return 'All employees';
        }

        return implode(' | ', $parts);
    }
    
    /**
     * Get daily analytics data
     */
    private function getDailyAnalytics(Carbon $date, array $employeeIds, int $totalEmployees)
    {
        $dateString = $date->toDateString();

        // Get today's attendance data
        $todayAttendance = Attendance::whereDate('date', $dateString);
        if (!empty($employeeIds)) {
            $todayAttendance->whereIn('user_id', $employeeIds);
        } else {
            $todayAttendance->whereRaw('1 = 0');
        }
        $todayAttendance = $todayAttendance->get();

        $present = $todayAttendance->where('status', 'present')->count();
        $late = $todayAttendance->where('status', 'late')->count();
        $onBreak = $todayAttendance->where('status', 'on_break')->count();
        $absent = max(0, $totalEmployees - ($present + $late + $onBreak));

        // Calculate average check-in time
        $avgCheckInQuery = Attendance::whereDate('date', $dateString)
            ->whereNotNull('clock_in_time');
        if (!empty($employeeIds)) {
            $avgCheckInQuery->whereIn('user_id', $employeeIds);
        } else {
            $avgCheckInQuery->whereRaw('1 = 0');
        }
        $avgCheckIn = $avgCheckInQuery
            ->selectRaw('AVG(TIME_TO_SEC(clock_in_time)) as avg_seconds')
            ->first();
            
        $avgCheckInTime = $avgCheckIn && $avgCheckIn->avg_seconds ? 
            gmdate('H:i', $avgCheckIn->avg_seconds) : '08:00';
            
        // Get overtime and undertime data
        $overtimeHours = $todayAttendance->sum('overtime_hours');
        $undertimeCases = $todayAttendance->where('hours_worked', '<', 8)->where('status', '!=', 'absent')->count();
        
        return [
            'date' => $date->format('l, F j, Y'),
            'totalEmployees' => $totalEmployees,
            'present' => $present,
            'late' => $late,
            'absent' => $absent,
            'onBreak' => $onBreak,
            'avgCheckIn' => $avgCheckInTime,
            'overtime' => round($overtimeHours, 1),
            'undertime' => $undertimeCases
        ];
    }
    
    /**
     * Get weekly analytics data
     */
    private function getWeeklyAnalytics(Carbon $date, array $employeeIds, int $totalEmployees)
    {
        $startOfWeek = $date->copy()->startOfWeek();
        $endOfWeek = $date->copy()->endOfWeek();
        
        // Get weekly attendance data
        $weeklyAttendanceQuery = Attendance::whereBetween('date', [$startOfWeek, $endOfWeek]);
        if (!empty($employeeIds)) {
            $weeklyAttendanceQuery->whereIn('user_id', $employeeIds);
        } else {
            $weeklyAttendanceQuery->whereRaw('1 = 0');
        }
        $weeklyAttendance = $weeklyAttendanceQuery->get();
        
        // Calculate daily averages
        $workingDays = 0;
        $dailyStats = [];
        $totalHours = $weeklyAttendance->sum('hours_worked');
        $overtimeHours = $weeklyAttendance->sum('overtime_hours');
        
        for ($day = $startOfWeek->copy(); $day->lte($endOfWeek); $day->addDay()) {
            if (!$day->isWeekend()) {
                $workingDays++;
                $dayAttendance = $weeklyAttendance->where('date', $day->toDateString());
                $dayPresentLate = $dayAttendance->whereIn('status', ['present', 'late'])->count();
                $dayOnBreak = $dayAttendance->where('status', 'on_break')->count();
                $dayAbsent = max(0, $totalEmployees - ($dayPresentLate + $dayOnBreak));
                $dailyStats[] = [
                    'day' => $day->format('l'),
                    'present' => $dayPresentLate,
                    'absent' => $dayAbsent,
                    'rate' => $totalEmployees > 0 ? round(($dayPresentLate / $totalEmployees) * 100, 1) : 0
                ];
            }
        }
        
        // Find best and worst days
        $bestDay = collect($dailyStats)->sortByDesc('rate')->first();
        $worstDay = collect($dailyStats)->sortBy('rate')->first();
        
        // Calculate averages
        $presentLateCount = $weeklyAttendance->whereIn('status', ['present', 'late'])->count();
        $onBreakCount = $weeklyAttendance->where('status', 'on_break')->count();
        $avgDaily = [
            'present' => $workingDays > 0 ? round($presentLateCount / $workingDays) : 0,
            'late' => $workingDays > 0 ? round($weeklyAttendance->where('status', 'late')->count() / $workingDays) : 0,
            'absent' => $workingDays > 0 ? round((($totalEmployees * $workingDays) - ($presentLateCount + $onBreakCount)) / $workingDays) : 0
        ];

        return [
            'weekOf' => $startOfWeek->format('F j') . ' - ' . $endOfWeek->format('F j, Y'),
            'totalHours' => round($totalHours, 0),
            'avgDaily' => $avgDaily,
            'bestDay' => $bestDay ? $bestDay['day'] . ' (' . $bestDay['rate'] . '%)' : 'N/A',
            'worstDay' => $worstDay ? $worstDay['day'] . ' (' . $worstDay['rate'] . '%)' : 'N/A',
            'overtimeHours' => round($overtimeHours, 0),
            'undertimeHours' => 0, // Calculate based on your business logic
            'dailyBreakdown' => $dailyStats
        ];
    }
    
    /**
     * Get monthly analytics data
     */
    private function getMonthlyAnalytics(Carbon $date, array $employeeIds, int $totalEmployees)
    {
        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();
        
        // Get monthly attendance data
        $monthlyAttendanceQuery = Attendance::whereBetween('date', [$startOfMonth, $endOfMonth]);
        if (!empty($employeeIds)) {
            $monthlyAttendanceQuery->whereIn('user_id', $employeeIds);
        } else {
            $monthlyAttendanceQuery->whereRaw('1 = 0');
        }
        $monthlyAttendance = $monthlyAttendanceQuery->get();
        
        // Calculate working days in the month
        $workingDays = 0;
        for ($day = $startOfMonth->copy(); $day->lte($endOfMonth); $day->addDay()) {
            if (!$day->isWeekend()) {
                $workingDays++;
            }
        }
        
        // Calculate statistics
        $totalHours = $monthlyAttendance->sum('hours_worked');
        $overtimeHours = $monthlyAttendance->sum('overtime_hours');
        $lateInstances = $monthlyAttendance->where('status', 'late')->count();
        $actualAttendance = $monthlyAttendance->whereIn('status', ['present', 'late', 'on_break'])->count();
        $absentDays = max(0, ($totalEmployees * $workingDays) - $actualAttendance);
        
        // Calculate average attendance rate
        $expectedAttendance = $totalEmployees * $workingDays;
        $avgAttendance = $expectedAttendance > 0 ? round(($actualAttendance / $expectedAttendance) * 100, 1) : 0;
        
        // Calculate perfect attendance (employees with full attendance this month)
        if (empty($employeeIds)) {
            $perfectAttendance = 0;
        } else {
            $perfectAttendance = Employee::active()
                ->whereIn('user_id', $employeeIds)
                ->whereHas('attendances', function ($query) use ($startOfMonth, $endOfMonth, $workingDays) {
                    $query->whereBetween('date', [$startOfMonth, $endOfMonth])
                        ->where('status', 'present')
                        ->groupBy('user_id')
                        ->havingRaw('COUNT(*) = ?', [$workingDays]);
                })->count();
        }

        return [
            'month' => $date->format('F Y'),
            'workingDays' => $workingDays,
            'totalHours' => round($totalHours, 0),
            'avgAttendance' => $avgAttendance . '%',
            'perfectAttendance' => $perfectAttendance,
            'lateInstances' => $lateInstances,
            'absentDays' => $absentDays,
            'overtimeHours' => round($overtimeHours, 0)
        ];
    }
    
    /**
     * Get yearly analytics data
     */
    private function getYearlyAnalytics(Carbon $date, array $employeeIds, int $totalEmployees)
    {
        $startOfYear = $date->copy()->startOfYear();
        $endOfYear = $date->copy()->endOfYear();
        
        // Get yearly attendance data
        $yearlyAttendanceQuery = Attendance::whereBetween('date', [$startOfYear, $endOfYear]);
        if (!empty($employeeIds)) {
            $yearlyAttendanceQuery->whereIn('user_id', $employeeIds);
        } else {
            $yearlyAttendanceQuery->whereRaw('1 = 0');
        }
        $yearlyAttendance = $yearlyAttendanceQuery->get();
        
        // Calculate working days in the year
        $workingDays = 0;
        for ($day = $startOfYear->copy(); $day->lte($endOfYear); $day->addDay()) {
            if (!$day->isWeekend()) {
                $workingDays++;
            }
        }
        
        // Calculate statistics
        $totalHours = $yearlyAttendance->sum('hours_worked');
        $totalOvertime = $yearlyAttendance->sum('overtime_hours');
        
        // Calculate average attendance rate
        $expectedAttendance = $totalEmployees * $workingDays;
        $actualAttendance = $yearlyAttendance->whereIn('status', ['present', 'late', 'on_break'])->count();
        $avgAttendance = $expectedAttendance > 0 ? round(($actualAttendance / $expectedAttendance) * 100, 1) : 0;
        
        // Find best and worst months
        $monthlyStats = [];
        for ($month = $startOfYear->copy(); $month->lte($endOfYear); $month->addMonth()) {
            $monthAttendance = $yearlyAttendance->filter(function ($record) use ($month) {
                return Carbon::parse($record->date)->month === $month->month;
            });
            
            $monthWorkingDays = 0;
            $monthStart = $month->copy()->startOfMonth();
            $monthEnd = $month->copy()->endOfMonth();
            
            for ($day = $monthStart->copy(); $day->lte($monthEnd); $day->addDay()) {
                if (!$day->isWeekend()) {
                    $monthWorkingDays++;
                }
            }
            
            $monthExpected = $totalEmployees * $monthWorkingDays;
            $monthActual = $monthAttendance->whereIn('status', ['present', 'late', 'on_break'])->count();
            $monthRate = $monthExpected > 0 ? round(($monthActual / $monthExpected) * 100, 1) : 0;
            
            $monthlyStats[] = [
                'month' => $month->format('F'),
                'rate' => $monthRate
            ];
        }
        
        $bestMonth = collect($monthlyStats)->sortByDesc('rate')->first();
        $worstMonth = collect($monthlyStats)->sortBy('rate')->first();
        
        return [
            'year' => $date->format('Y'),
            'workingDays' => $workingDays,
            'totalHours' => round($totalHours, 0),
            'avgAttendance' => $avgAttendance . '%',
            'bestMonth' => $bestMonth ? $bestMonth['month'] . ' (' . $bestMonth['rate'] . '%)' : 'N/A',
            'worstMonth' => $worstMonth ? $worstMonth['month'] . ' (' . $worstMonth['rate'] . '%)' : 'N/A',
            'totalOvertime' => round($totalOvertime, 0),
            'holidaysPaid' => 18 // This would come from a holidays/leave table
        ];
    }

    /**
     * Get overview dashboard data
     */
    public function getOverviewData(Request $request)
    {
        try {
            $today = now()->toDateString();
            $employees = Employee::with('user')->active()->get();
            $totalEmployees = $employees->count();
            $statusService = new AttendanceStatusService();

            $present = 0;
            $late = 0;
            $onBreak = 0;
            $absent = 0;
            $noSchedule = 0;
            $onLeave = 0;
            $scheduled = 0;
            $todayAttendance = collect();

            foreach ($employees as $employee) {
                if (!$employee->user) {
                    continue;
                }
                $result = $statusService->resolveForEmployee($employee, Carbon::parse($today));
                $status = $result['status'];
                if ($result['attendance']) {
                    $todayAttendance->push($result['attendance']);
                }

                if ($status === 'present') {
                    $present++;
                } elseif ($status === 'late') {
                    $late++;
                } elseif ($status === 'break') {
                    $onBreak++;
                } elseif ($status === 'absent') {
                    $absent++;
                } elseif ($status === 'no_schedule') {
                    $noSchedule++;
                } elseif ($status === 'on_leave') {
                    $onLeave++;
                } elseif ($status === 'scheduled') {
                    $scheduled++;
                }
            }
            
            // Calculate total hours logged today
            $totalHoursToday = $todayAttendance->sum('hours_worked') ?: 0;
            
            // Calculate expected hours (8 hours per employee who should be working)
            $expectedHours = ($present + $late + $onBreak) * 8;
            $hoursPercentage = $expectedHours > 0 ? round(($totalHoursToday / $expectedHours) * 100, 1) : 0;
            
            // Calculate average check-in time
            $avgCheckIn = Attendance::whereDate('date', $today)
                ->whereNotNull('clock_in_time')
                ->selectRaw('AVG(TIME_TO_SEC(clock_in_time)) as avg_seconds')
                ->first();
                
            $avgCheckInTime = $avgCheckIn && $avgCheckIn->avg_seconds ? 
                gmdate('H:i', $avgCheckIn->avg_seconds) : '08:00';
            
            // Calculate how many minutes early on average
            $expectedCheckIn = '09:00'; // Assuming 9 AM is the standard check-in time
            $expectedSeconds = 9 * 3600; // 9 AM in seconds
            $minutesEarly = $avgCheckIn && $avgCheckIn->avg_seconds ? 
                round(($expectedSeconds - $avgCheckIn->avg_seconds) / 60) : 0;
            
            // Get this week's overtime
            $startOfWeek = now()->startOfWeek();
            $endOfWeek = now()->endOfWeek();
            $weeklyOvertime = Attendance::whereBetween('date', [$startOfWeek, $endOfWeek])
                ->sum('overtime_hours') ?: 0;
            
            // Calculate last week's overtime for comparison
            $lastWeekStart = now()->subWeek()->startOfWeek();
            $lastWeekEnd = now()->subWeek()->endOfWeek();
            $lastWeekOvertime = Attendance::whereBetween('date', [$lastWeekStart, $lastWeekEnd])
                ->sum('overtime_hours') ?: 0;
            
            $overtimeChange = $lastWeekOvertime > 0 ? 
                round((($weeklyOvertime - $lastWeekOvertime) / $lastWeekOvertime) * 100, 1) : 0;
            
            // Calculate productivity rate (based on hours worked vs expected)
            $expectedTotalHours = $totalEmployees * 8; // 8 hours per employee
            $actualHours = $todayAttendance->sum('hours_worked') ?: 0;
            $productivityRate = $expectedTotalHours > 0 ? 
                min(100, round(($actualHours / $expectedTotalHours) * 100, 1)) : 0;
            
            // Calculate weekly trends
            $lastWeekAttendance = Attendance::whereBetween('date', [$lastWeekStart, $lastWeekEnd])
                ->whereIn('status', ['present', 'late'])
                ->count();
            $thisWeekAttendance = Attendance::whereBetween('date', [$startOfWeek, $endOfWeek])
                ->whereIn('status', ['present', 'late'])
                ->count();
            
            $attendanceTrend = $lastWeekAttendance > 0 ? 
                round((($thisWeekAttendance - $lastWeekAttendance) / $lastWeekAttendance) * 100, 1) : 0;
            
            // Calculate average arrival improvement (comparing to last week)
            $thisWeekAvgCheckIn = Attendance::whereBetween('date', [$startOfWeek, $endOfWeek])
                ->whereNotNull('clock_in_time')
                ->selectRaw('AVG(TIME_TO_SEC(clock_in_time)) as avg_seconds')
                ->first();
            
            $lastWeekAvgCheckIn = Attendance::whereBetween('date', [$lastWeekStart, $lastWeekEnd])
                ->whereNotNull('clock_in_time')
                ->selectRaw('AVG(TIME_TO_SEC(clock_in_time)) as avg_seconds')
                ->first();
            
            $arrivalImprovement = 0;
            if ($thisWeekAvgCheckIn && $lastWeekAvgCheckIn && 
                $thisWeekAvgCheckIn->avg_seconds && $lastWeekAvgCheckIn->avg_seconds) {
                $arrivalImprovement = round(($lastWeekAvgCheckIn->avg_seconds - $thisWeekAvgCheckIn->avg_seconds) / 60);
            }
            
            // Calculate attendance breakdown for Today's Summary
            $onTimeEmployees = $todayAttendance->filter(function ($record) {
                if (!$record->clock_in_time) return false;
                $clockInTime = $this->parseTimeValue($record->clock_in_time);
                $expectedTime = $this->parseTimeValue('09:00'); // 9 AM standard time
                return $clockInTime->lessThanOrEqualTo($expectedTime);
            })->count();
            
            $lateModerate = $todayAttendance->filter(function ($record) {
                if (!$record->clock_in_time) return false;
                $clockInTime = $this->parseTimeValue($record->clock_in_time);
                $expectedTime = $this->parseTimeValue('09:00'); // 9 AM standard time
                $minutesLate = $clockInTime->diffInMinutes($expectedTime, false);
                return $minutesLate >= 5 && $minutesLate <= 15;
            })->count();
            
            $lateExtreme = $todayAttendance->filter(function ($record) {
                if (!$record->clock_in_time) return false;
                $clockInTime = $this->parseTimeValue($record->clock_in_time);
                $expectedTime = $this->parseTimeValue('09:00'); // 9 AM standard time
                $minutesLate = $clockInTime->diffInMinutes($expectedTime, false);
                return $minutesLate > 15;
            })->count();
            
            // Calculate real clock in/out counts from database
            $clockedInToday = Attendance::whereDate('date', $today)
                ->whereNotNull('clock_in_time')
                ->whereNull('clock_out_time') // Still working (haven't clocked out yet)
                ->count();
            
            $clockedOutToday = Attendance::whereDate('date', $today)
                ->whereNotNull('clock_in_time')
                ->whereNotNull('clock_out_time') // Finished work (clocked out)
                ->count();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'totalEmployees' => $totalEmployees,
                    'present' => $present,
                    'late' => $late,
                    'onBreak' => $onBreak,
                    'absent' => $absent,
                    'noSchedule' => $noSchedule,
                    'onLeave' => $onLeave,
                    'scheduled' => $scheduled,
                    'totalHours' => round($totalHoursToday, 1),
                    'expectedHours' => $expectedHours,
                    'hoursPercentage' => $hoursPercentage,
                    'avgCheckIn' => $avgCheckInTime,
                    'minutesEarly' => max(0, $minutesEarly),
                    'weeklyOvertime' => round($weeklyOvertime, 1),
                    'overtimeChange' => $overtimeChange,
                    'productivityRate' => $productivityRate,
                    'attendanceTrend' => $attendanceTrend,
                    'arrivalImprovement' => $arrivalImprovement,
                    // Additional breakdown for Today's Summary
                    'onTime' => $onTimeEmployees,
                    'lateModerate' => $lateModerate, // 5-15 min late
                    'lateExtreme' => $lateExtreme,    // 15+ min late
                    // Real clock in/out counts
                    'clockedInToday' => $clockedInToday,
                    'clockedOutToday' => $clockedOutToday
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch overview data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get recent activities data
     */
    public function getRecentActivities(Request $request)
    {
        try {
            $limit = $request->get('limit', 10);
            
            // Get recent attendance records with activity timestamps
            $recentRecords = Attendance::with(['user.employee'])
                ->where('date', '>=', now()->subDays(1)->toDateString()) // Last 24 hours
                ->orderBy('updated_at', 'desc')
                ->limit($limit * 2) // Get more to filter activities
                ->get();
            
            $activities = collect();
            
            foreach ($recentRecords as $record) {
                if (!$record->user || !$record->user->employee) continue;
                
                // Clock In activity
                if ($record->clock_in_time) {
                    $activities->push([
                        'id' => 'in_' . $record->id,
                        'employee' => $record->user->name,
                        'action' => 'Clock In',
                        'time' => $this->getTimeAgo($record->updated_at),
                        'department' => $record->user->employee->department ?? 'No Department',
                        'type' => 'in',
                        'status' => $record->status === 'late' ? 'Late' : 'On Time',
                        'timestamp' => $record->updated_at
                    ]);
                }
                
                // Clock Out activity
                if ($record->clock_out_time) {
                    $activities->push([
                        'id' => 'out_' . $record->id,
                        'employee' => $record->user->name,
                        'action' => 'Clock Out',
                        'time' => $this->getTimeAgo($record->updated_at),
                        'department' => $record->user->employee->department ?? 'No Department',
                        'type' => 'out',
                        'status' => $record->hours_worked ? round($record->hours_worked, 1) . ' hrs' : 'N/A',
                        'timestamp' => $record->updated_at
                    ]);
                }
                
                // Break Start activity
                if ($record->break_start) {
                    $activities->push([
                        'id' => 'break_start_' . $record->id,
                        'employee' => $record->user->name,
                        'action' => 'Break Start',
                        'time' => $this->getTimeAgo($record->updated_at),
                        'department' => $record->user->employee->department ?? 'No Department',
                        'type' => 'break',
                        'status' => 'Break',
                        'timestamp' => $record->updated_at
                    ]);
                }
                
                // Break End activity
                if ($record->break_end) {
                    $activities->push([
                        'id' => 'break_end_' . $record->id,
                        'employee' => $record->user->name,
                        'action' => 'Break End',
                        'time' => $this->getTimeAgo($record->updated_at),
                        'department' => $record->user->employee->department ?? 'No Department',
                        'type' => 'break_end',
                        'status' => 'Resumed',
                        'timestamp' => $record->updated_at
                    ]);
                }
                
                // Manual entries (created by someone other than the employee)
                if ($record->created_by && $record->created_by !== $record->user_id) {
                    $activities->push([
                        'id' => 'manual_' . $record->id,
                        'employee' => $record->user->name,
                        'action' => 'Manual Entry',
                        'time' => $this->getTimeAgo($record->created_at),
                        'department' => $record->user->employee->department ?? 'No Department',
                        'type' => 'manual',
                        'status' => 'Manual',
                        'timestamp' => $record->created_at
                    ]);
                }
            }
            
            // Sort by timestamp and limit
            $recentActivities = $activities->sortByDesc('timestamp')->take($limit)->values();
            
            return response()->json([
                'success' => true,
                'activities' => $recentActivities
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch recent activities: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get department performance data
     */
    public function getDepartmentPerformance(Request $request)
    {
        try {
            $today = now()->toDateString();
            
            // Get all departments with employee counts and today's attendance
            $departments = Employee::active()
                ->whereNotNull('department')
                ->select('department')
                ->groupBy('department')
                ->get()
                ->map(function ($dept) use ($today) {
                    $departmentName = $dept->department;
                    
                    // Get total employees in department
                    $totalInDepartment = Employee::active()
                        ->where('department', $departmentName)
                        ->count();
                    
                    // Get today's attendance for this department
                    $presentToday = Attendance::where('date', $today)
                        ->whereHas('user.employee', function ($query) use ($departmentName) {
                            $query->where('department', $departmentName);
                        })
                        ->whereIn('status', ['present', 'late', 'on_break'])
                        ->count();
                    
                    $attendanceRate = $totalInDepartment > 0 ? 
                        round(($presentToday / $totalInDepartment) * 100, 1) : 0;
                    
                    return [
                        'name' => $departmentName,
                        'total' => $totalInDepartment,
                        'present' => $presentToday,
                        'rate' => $attendanceRate
                    ];
                })
                ->sortByDesc('rate')
                ->values();
            
            return response()->json([
                'success' => true,
                'departments' => $departments
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch department performance: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper function to calculate time ago
     */
    private function getTimeAgo($datetime)
    {
        $carbon = Carbon::parse($datetime);
        $now = Carbon::now();
        
        $diff = $now->diffInMinutes($carbon);
        
        if ($diff < 1) {
            return 'Just now';
        } elseif ($diff < 60) {
            return $diff . ' minute' . ($diff > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 1440) { // Less than 24 hours
            $hours = floor($diff / 60);
            return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
        } else {
            $days = floor($diff / 1440);
            return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
        }
    }

    /**
     * Export daily attendance report as PDF
     */
    public function exportDailyPDF(Request $request)
    {
        try {
            $date = $request->get('date', now()->toDateString());
            $carbonDate = Carbon::parse($date);
            [$employeeIds, $totalEmployees] = $this->resolveAnalyticsFilters($request);
            $filterSummary = $this->buildAnalyticsFilterSummary($request);
            
            // Check if we have any employees - if not, create a basic report
            if ($totalEmployees === 0) {
                // Create a minimal report for empty data
                $data = [
                    'date' => $carbonDate,
                    'totalEmployees' => 0,
                    'present' => 0,
                    'late' => 0,
                    'absent' => 0,
                    'onBreak' => 0,
                    'totalHours' => 0,
                    'overtimeHours' => 0,
                    'attendanceRate' => 0,
                    'attendances' => collect([]),
                    'departmentStats' => collect([]),
                    'filterSummary' => $filterSummary,
                    'generatedAt' => now()
                ];
                
                $pdf = PDF::loadView('pdf.reports.daily-simple', $data);
                $pdf->setPaper('A4', 'portrait');
                
                $dompdf = $pdf->getDomPDF();
                $dompdf->getOptions()->setChroot(realpath(base_path()));
                $dompdf->getOptions()->setIsRemoteEnabled(false);
                $dompdf->getOptions()->setDefaultFont('Arial');
                
                $filename = 'daily-report-' . $carbonDate->format('Y-m-d') . '.pdf';
                return $pdf->download($filename);
            }
            
            // Get daily attendance data - only include records with valid users
            $todayAttendanceQuery = Attendance::with(['user.employee'])
                ->where('date', $date)
                ->whereHas('user'); // Ensure user relationship exists
            if (!empty($employeeIds)) {
                $todayAttendanceQuery->whereIn('user_id', $employeeIds);
            } else {
                $todayAttendanceQuery->whereRaw('1 = 0');
            }
            $todayAttendance = $todayAttendanceQuery
                ->orderByRaw('clock_in_time IS NULL, clock_in_time ASC')
                ->get();
            
            // Calculate statistics
            $present = $todayAttendance->where('status', 'present')->count();
            $late = $todayAttendance->where('status', 'late')->count();
            $onBreak = $todayAttendance->where('status', 'on_break')->count();
            $absent = max(0, $totalEmployees - ($present + $late + $onBreak));
            $totalHours = $todayAttendance->sum('hours_worked') ?: 0;
            $overtimeHours = $todayAttendance->sum('overtime_hours') ?: 0;
            
            // Calculate dynamic department stats
            $departmentStats = Employee::active()
                ->whereIn('user_id', $employeeIds)
                ->whereNotNull('department')
                ->get()
                ->groupBy('department')
                ->map(function ($employees, $department) use ($date, $employeeIds) {
                    $totalInDept = $employees->count();
                    $presentInDept = Attendance::where('date', $date)
                        ->whereIn('user_id', $employeeIds)
                        ->whereHas('user.employee', function ($query) use ($department) {
                            $query->where('department', $department);
                        })
                        ->whereIn('status', ['present', 'late', 'on_break'])
                        ->count();
                    
                    $rate = $totalInDept > 0 ? round(($presentInDept / $totalInDept) * 100, 1) : 0;
                    
                    return [
                        'name' => $department,
                        'present' => $presentInDept,
                        'total' => $totalInDept,
                        'rate' => $rate
                    ];
                })
                ->sortByDesc('rate')
                ->values();
            
            $data = [
                'date' => $carbonDate,
                'totalEmployees' => $totalEmployees,
                'present' => $present,
                'late' => $late,
                'absent' => $absent,
                'onBreak' => $onBreak,
                'totalHours' => round($totalHours, 1),
                'overtimeHours' => round($overtimeHours, 1),
                'attendanceRate' => $totalEmployees > 0 ? round((($present + $late + $onBreak) / $totalEmployees) * 100, 1) : 0,
                'attendances' => $todayAttendance,
                'departmentStats' => $departmentStats,
                'filterSummary' => $filterSummary,
                'generatedAt' => now()
            ];
            
            // Add debug parameter to show data instead of generating PDF
            if ($request->has('debug')) {
                return response()->json([
                    'data' => $data,
                    'attendance_count' => $todayAttendance->count(),
                    'department_count' => $departmentStats->count(),
                    'message' => 'Data structure looks good. PDF generation should work.'
                ]);
            }
            
            // Ensure we have safe data for PDF generation
            if (!$data['date'] || !$data['generatedAt']) {
                throw new \Exception('Invalid date data for PDF generation');
            }
            
            // Configure PDF options for production - use simple template for testing
            $pdf = PDF::loadView('pdf.reports.daily-simple', $data);
            $pdf->setPaper('A4', 'portrait');
            
            // Set DomPDF options to fix common issues
            $dompdf = $pdf->getDomPDF();
            $dompdf->getOptions()->setChroot(realpath(base_path()));
            $dompdf->getOptions()->setIsRemoteEnabled(false);
            $dompdf->getOptions()->setIsHtml5ParserEnabled(true);
            $dompdf->getOptions()->setIsPhpEnabled(false);
            $dompdf->getOptions()->setDefaultFont('Arial');
            
            $filename = 'daily-report-' . $carbonDate->format('Y-m-d') . '.pdf';
            
            return $pdf->download($filename);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to generate daily PDF report: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * Export weekly attendance report as PDF
     */
    public function exportWeeklyPDF(Request $request)
    {
        try {
            $date = $request->get('date', now()->toDateString());
            $carbonDate = Carbon::parse($date);
            $startOfWeek = $carbonDate->copy()->startOfWeek();
            $endOfWeek = $carbonDate->copy()->endOfWeek();
            [$employeeIds, $totalEmployees] = $this->resolveAnalyticsFilters($request);
            $filterSummary = $this->buildAnalyticsFilterSummary($request);
            
            // Get weekly attendance data
            $weeklyAttendanceQuery = Attendance::with(['user.employee'])
                ->whereBetween('date', [$startOfWeek, $endOfWeek]);
            if (!empty($employeeIds)) {
                $weeklyAttendanceQuery->whereIn('user_id', $employeeIds);
            } else {
                $weeklyAttendanceQuery->whereRaw('1 = 0');
            }
            $weeklyAttendance = $weeklyAttendanceQuery
                ->orderBy('date')
                ->orderBy('clock_in_time')
                ->get();
            
            // Calculate daily statistics
            $dailyStats = [];
            $totalHours = $weeklyAttendance->sum('hours_worked');
            $overtimeHours = $weeklyAttendance->sum('overtime_hours');
            $workingDays = 0;
            
            for ($day = $startOfWeek->copy(); $day->lte($endOfWeek); $day->addDay()) {
                if (!$day->isWeekend()) {
                    $workingDays++;
                    $dayAttendance = $weeklyAttendance->where('date', $day->toDateString());
                    $presentCount = $dayAttendance->whereIn('status', ['present', 'late'])->count();
                    $onBreakCount = $dayAttendance->where('status', 'on_break')->count();
                    
                    $dailyStats[] = [
                        'date' => $day->format('l, M j'),
                        'present' => $presentCount,
                        'absent' => max(0, $totalEmployees - ($presentCount + $onBreakCount)),
                        'rate' => $totalEmployees > 0 ? round(($presentCount / $totalEmployees) * 100, 1) : 0
                    ];
                }
            }
            
            // Department performance
            $departmentStats = Employee::active()
                ->whereIn('user_id', $employeeIds)
                ->whereNotNull('department')
                ->get()
                ->groupBy('department')
                ->map(function ($employees, $department) use ($startOfWeek, $endOfWeek, $workingDays, $employeeIds) {
                    $totalInDept = $employees->count();
                    $presentDays = Attendance::whereBetween('date', [$startOfWeek, $endOfWeek])
                        ->whereIn('user_id', $employeeIds)
                        ->whereHas('user.employee', function ($query) use ($department) {
                            $query->where('department', $department);
                        })
                        ->whereIn('status', ['present', 'late'])
                        ->count();
                    
                    $expectedDays = $totalInDept * $workingDays;
                    $rate = $expectedDays > 0 ? round(($presentDays / $expectedDays) * 100, 1) : 0;
                    
                    return [
                        'name' => $department,
                        'employees' => $totalInDept,
                        'rate' => $rate
                    ];
                });
            
            $data = [
                'startDate' => $startOfWeek,
                'endDate' => $endOfWeek,
                'totalEmployees' => $totalEmployees,
                'workingDays' => $workingDays,
                'totalHours' => round($totalHours, 1),
                'overtimeHours' => round($overtimeHours, 1),
                'dailyStats' => $dailyStats,
                'departmentStats' => $departmentStats,
                'avgAttendanceRate' => $dailyStats ? round(collect($dailyStats)->avg('rate'), 1) : 0,
                'filterSummary' => $filterSummary,
                'generatedAt' => now()
            ];
            
            // Configure PDF options for production
            $pdf = PDF::loadView('pdf.reports.weekly', $data);
            $pdf->setPaper('A4', 'portrait');
            
            // Set DomPDF options to fix common issues
            $dompdf = $pdf->getDomPDF();
            $dompdf->getOptions()->setChroot(realpath(base_path()));
            $dompdf->getOptions()->setIsRemoteEnabled(false);
            $dompdf->getOptions()->setIsHtml5ParserEnabled(true);
            $dompdf->getOptions()->setIsPhpEnabled(false);
            $dompdf->getOptions()->setDefaultFont('Arial');
            
            $filename = 'weekly-report-' . $startOfWeek->format('Y-m-d') . '-to-' . $endOfWeek->format('Y-m-d') . '.pdf';
            
            return $pdf->download($filename);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to generate weekly PDF report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export monthly attendance report as PDF
     */
    public function exportMonthlyPDF(Request $request)
    {
        try {
            $date = $request->get('date', now()->toDateString());
            $carbonDate = Carbon::parse($date);
            $startOfMonth = $carbonDate->copy()->startOfMonth();
            $endOfMonth = $carbonDate->copy()->endOfMonth();
            [$employeeIds, $totalEmployees] = $this->resolveAnalyticsFilters($request);
            $filterSummary = $this->buildAnalyticsFilterSummary($request);
            
            // Get monthly attendance data
            $monthlyAttendanceQuery = Attendance::with(['user.employee'])
                ->whereBetween('date', [$startOfMonth, $endOfMonth]);
            if (!empty($employeeIds)) {
                $monthlyAttendanceQuery->whereIn('user_id', $employeeIds);
            } else {
                $monthlyAttendanceQuery->whereRaw('1 = 0');
            }
            $monthlyAttendance = $monthlyAttendanceQuery->get();
            
            // Calculate working days
            $workingDays = 0;
            for ($day = $startOfMonth->copy(); $day->lte($endOfMonth); $day->addDay()) {
                if (!$day->isWeekend()) {
                    $workingDays++;
                }
            }
            
            // Calculate statistics
            $totalHours = $monthlyAttendance->sum('hours_worked');
            $overtimeHours = $monthlyAttendance->sum('overtime_hours');
            $totalPresent = $monthlyAttendance->whereIn('status', ['present', 'late', 'on_break'])->count();
            $expectedAttendance = $totalEmployees * $workingDays;
            $avgAttendanceRate = $expectedAttendance > 0 ? round(($totalPresent / $expectedAttendance) * 100, 1) : 0;
            
            // Perfect attendance employees
            if (empty($employeeIds)) {
                $perfectAttendanceEmployees = collect();
            } else {
                $perfectAttendanceEmployees = Employee::active()
                    ->with('user')
                    ->whereIn('user_id', $employeeIds)
                    ->whereHas('user.attendances', function ($query) use ($startOfMonth, $endOfMonth, $workingDays) {
                        $query->whereBetween('date', [$startOfMonth, $endOfMonth])
                            ->where('status', 'present')
                            ->groupBy('user_id')
                            ->havingRaw('COUNT(*) = ?', [$workingDays]);
                    })
                    ->get();
            }
            
            // Top performers by hours
            $topPerformers = $monthlyAttendance
                ->groupBy('user_id')
                ->map(function ($records, $userId) {
                    $user = User::with('employee')->find($userId);
                    return [
                        'name' => $user->name ?? 'Unknown',
                        'department' => $user->employee->department ?? 'No Department',
                        'hours' => round($records->sum('hours_worked'), 1),
                        'days_present' => $records->whereIn('status', ['present', 'late'])->count()
                    ];
                })
                ->sortByDesc('hours')
                ->take(10);
            
            // Department performance
            $departmentStats = Employee::active()
                ->whereIn('user_id', $employeeIds)
                ->whereNotNull('department')
                ->get()
                ->groupBy('department')
                ->map(function ($employees, $department) use ($startOfMonth, $endOfMonth, $workingDays, $employeeIds) {
                    $totalInDept = $employees->count();
                    $presentDays = Attendance::whereBetween('date', [$startOfMonth, $endOfMonth])
                        ->whereIn('user_id', $employeeIds)
                        ->whereHas('user.employee', function ($query) use ($department) {
                            $query->where('department', $department);
                        })
                        ->whereIn('status', ['present', 'late'])
                        ->count();
                    
                    $expectedDays = $totalInDept * $workingDays;
                    $rate = $expectedDays > 0 ? round(($presentDays / $expectedDays) * 100, 1) : 0;
                    
                    return [
                        'name' => $department,
                        'employees' => $totalInDept,
                        'rate' => $rate,
                        'totalHours' => round(Attendance::whereBetween('date', [$startOfMonth, $endOfMonth])
                            ->whereIn('user_id', $employeeIds)
                            ->whereHas('user.employee', function ($query) use ($department) {
                                $query->where('department', $department);
                            })
                            ->sum('hours_worked'), 1)
                    ];
                });
            
            $data = [
                'month' => $carbonDate->format('F Y'),
                'startDate' => $startOfMonth,
                'endDate' => $endOfMonth,
                'totalEmployees' => $totalEmployees,
                'workingDays' => $workingDays,
                'totalHours' => round($totalHours, 1),
                'overtimeHours' => round($overtimeHours, 1),
                'avgAttendanceRate' => $avgAttendanceRate,
                'perfectAttendanceCount' => $perfectAttendanceEmployees->count(),
                'perfectAttendanceEmployees' => $perfectAttendanceEmployees,
                'topPerformers' => $topPerformers,
                'departmentStats' => $departmentStats,
                'lateInstances' => $monthlyAttendance->where('status', 'late')->count(),
                'filterSummary' => $filterSummary,
                'generatedAt' => now()
            ];
            
            // Configure PDF options for production
            $pdf = PDF::loadView('pdf.reports.monthly', $data);
            $pdf->setPaper('A4', 'portrait');
            
            // Set DomPDF options to fix common issues
            $dompdf = $pdf->getDomPDF();
            $dompdf->getOptions()->setChroot(realpath(base_path()));
            $dompdf->getOptions()->setIsRemoteEnabled(false);
            $dompdf->getOptions()->setIsHtml5ParserEnabled(true);
            $dompdf->getOptions()->setIsPhpEnabled(false);
            $dompdf->getOptions()->setDefaultFont('Arial');
            
            $filename = 'monthly-report-' . $carbonDate->format('Y-m') . '.pdf';
            
            return $pdf->download($filename);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to generate monthly PDF report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export yearly attendance report as PDF
     */
    public function exportYearlyPDF(Request $request)
    {
        try {
            $date = $request->get('date', now()->toDateString());
            $carbonDate = Carbon::parse($date);
            $startOfYear = $carbonDate->copy()->startOfYear();
            $endOfYear = $carbonDate->copy()->endOfYear();
            [$employeeIds, $totalEmployees] = $this->resolveAnalyticsFilters($request);
            $filterSummary = $this->buildAnalyticsFilterSummary($request);
            
            // Get yearly attendance data
            $yearlyAttendanceQuery = Attendance::with(['user.employee'])
                ->whereBetween('date', [$startOfYear, $endOfYear]);
            if (!empty($employeeIds)) {
                $yearlyAttendanceQuery->whereIn('user_id', $employeeIds);
            } else {
                $yearlyAttendanceQuery->whereRaw('1 = 0');
            }
            $yearlyAttendance = $yearlyAttendanceQuery->get();
            
            // Calculate working days
            $workingDays = 0;
            for ($day = $startOfYear->copy(); $day->lte($endOfYear); $day->addDay()) {
                if (!$day->isWeekend()) {
                    $workingDays++;
                }
            }
            
            // Calculate statistics
            $totalHours = $yearlyAttendance->sum('hours_worked');
            $totalOvertime = $yearlyAttendance->sum('overtime_hours');
            $totalPresent = $yearlyAttendance->whereIn('status', ['present', 'late', 'on_break'])->count();
            $expectedAttendance = $totalEmployees * $workingDays;
            $avgAttendanceRate = $expectedAttendance > 0 ? round(($totalPresent / $expectedAttendance) * 100, 1) : 0;
            
            // Monthly breakdown
            $monthlyStats = [];
            for ($month = $startOfYear->copy(); $month->lte($endOfYear); $month->addMonth()) {
                $monthAttendance = $yearlyAttendance->filter(function ($record) use ($month) {
                    return Carbon::parse($record->date)->month === $month->month;
                });
                
                $monthWorkingDays = 0;
                $monthStart = $month->copy()->startOfMonth();
                $monthEnd = $month->copy()->endOfMonth();
                
                for ($day = $monthStart->copy(); $day->lte($monthEnd); $day->addDay()) {
                    if (!$day->isWeekend()) {
                        $monthWorkingDays++;
                    }
                }
                
                $monthExpected = $totalEmployees * $monthWorkingDays;
                $monthActual = $monthAttendance->whereIn('status', ['present', 'late', 'on_break'])->count();
                $monthRate = $monthExpected > 0 ? round(($monthActual / $monthExpected) * 100, 1) : 0;
                
                $monthlyStats[] = [
                    'month' => $month->format('F'),
                    'rate' => $monthRate,
                    'hours' => round($monthAttendance->sum('hours_worked'), 0),
                    'overtime' => round($monthAttendance->sum('overtime_hours'), 0)
                ];
            }
            
            // Department performance
            $departmentStats = Employee::active()
                ->whereIn('user_id', $employeeIds)
                ->whereNotNull('department')
                ->get()
                ->groupBy('department')
                ->map(function ($employees, $department) use ($startOfYear, $endOfYear, $workingDays, $employeeIds) {
                    $totalInDept = $employees->count();
                    $presentDays = Attendance::whereBetween('date', [$startOfYear, $endOfYear])
                        ->whereIn('user_id', $employeeIds)
                        ->whereHas('user.employee', function ($query) use ($department) {
                            $query->where('department', $department);
                        })
                        ->whereIn('status', ['present', 'late'])
                        ->count();
                    
                    $expectedDays = $totalInDept * $workingDays;
                    $rate = $expectedDays > 0 ? round(($presentDays / $expectedDays) * 100, 1) : 0;
                    
                    return [
                        'name' => $department,
                        'employees' => $totalInDept,
                        'rate' => $rate,
                        'totalHours' => round(Attendance::whereBetween('date', [$startOfYear, $endOfYear])
                            ->whereIn('user_id', $employeeIds)
                            ->whereHas('user.employee', function ($query) use ($department) {
                                $query->where('department', $department);
                            })
                            ->sum('hours_worked'), 0)
                    ];
                });
            
            // Find best and worst months
            $bestMonth = collect($monthlyStats)->sortByDesc('rate')->first();
            $worstMonth = collect($monthlyStats)->sortBy('rate')->first();
            
            $data = [
                'year' => $carbonDate->format('Y'),
                'startDate' => $startOfYear,
                'endDate' => $endOfYear,
                'totalEmployees' => $totalEmployees,
                'workingDays' => $workingDays,
                'totalHours' => round($totalHours, 0),
                'totalOvertime' => round($totalOvertime, 0),
                'avgAttendanceRate' => $avgAttendanceRate,
                'monthlyStats' => $monthlyStats,
                'departmentStats' => $departmentStats,
                'bestMonth' => $bestMonth,
                'worstMonth' => $worstMonth,
                'filterSummary' => $filterSummary,
                'generatedAt' => now()
            ];
            
            // Configure PDF options for production
            $pdf = PDF::loadView('pdf.reports.yearly', $data);
            $pdf->setPaper('A4', 'portrait');
            
            // Set DomPDF options to fix common issues
            $dompdf = $pdf->getDomPDF();
            $dompdf->getOptions()->setChroot(realpath(base_path()));
            $dompdf->getOptions()->setIsRemoteEnabled(false);
            $dompdf->getOptions()->setIsHtml5ParserEnabled(true);
            $dompdf->getOptions()->setIsPhpEnabled(false);
            $dompdf->getOptions()->setDefaultFont('Arial');
            
            $filename = 'yearly-report-' . $carbonDate->format('Y') . '.pdf';
            
            return $pdf->download($filename);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to generate yearly PDF report: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Debug PDF generation
     */
    public function debugPDF(Request $request)
    {
        try {
            // Test with minimal data first
            $data = [
                'date' => Carbon::now(),
                'totalEmployees' => 2,
                'present' => 1,
                'late' => 0,
                'absent' => 1,
                'onBreak' => 0,
                'totalHours' => 8.0,
                'overtimeHours' => 0.0,
                'attendanceRate' => 50.0,
                'attendances' => collect([]),
                'departmentStats' => collect([]),
                'generatedAt' => now()
            ];
            
            // Configure PDF options for production
            $pdf = PDF::loadView('pdf.reports.daily', $data);
            $pdf->setPaper('A4', 'portrait');
            
            // Set DomPDF options to fix common issues
            $dompdf = $pdf->getDomPDF();
            $dompdf->getOptions()->setChroot(realpath(base_path()));
            $dompdf->getOptions()->setIsRemoteEnabled(false);
            $dompdf->getOptions()->setIsHtml5ParserEnabled(true);
            $dompdf->getOptions()->setIsPhpEnabled(false);
            $dompdf->getOptions()->setDefaultFont('Arial');
            
            return $pdf->download('debug-daily-report.pdf');
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Debug PDF error: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
    
    /**
     * Get employee timesheets data from timesheets table
     */
    public function getEmployeeTimesheets(Request $request)
    {
        try {
            $startDate = $request->get('start_date', now()->startOfWeek()->toDateString());
            $endDate = $request->get('end_date', now()->endOfWeek()->toDateString());
            $search = $request->get('search', '');
            $department = $request->get('department', '');
            
            // Build query for timesheets with employee data
            $query = Timesheet::with(['user.employee'])
                ->whereBetween('work_date', [$startDate, $endDate])
                ->whereHas('user'); // Ensure user relationship exists
            
            // Apply search filter
            if (!empty($search)) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%");
                });
            }
            
            // Apply department filter
            if (!empty($department)) {
                $query->whereHas('user.employee', function ($q) use ($department) {
                    $q->where('department', $department);
                });
            }
            
            // Get timesheets
            $timesheets = $query->orderBy('work_date', 'desc')
                ->orderBy('user_id')
                ->get();
            
            // Transform timesheet data to match UI structure
            $timesheetData = $timesheets->map(function ($timesheet) {
                $user = $timesheet->user;
                $employee = $user->employee ?? null;
                
                return [
                    'id' => $timesheet->id,
                    'employee_id' => $timesheet->user_id,
                    'employee' => $user ? $user->name : 'Unknown',
                    'department' => $employee->department ?? 'No Department',
                    'position' => $employee->position ?? 'No Position',
                    'date' => $timesheet->work_date,
                    'time_start' => $timesheet->clock_in_time ? $this->formatTimeValue($timesheet->clock_in_time) : '--',
                    'time_end' => $timesheet->clock_out_time ? $this->formatTimeValue($timesheet->clock_out_time) : '--',
                    'overtime_hours' => $timesheet->overtime_hours ?? 0,
                    'total_hours' => $timesheet->hours_worked ?? 0,
                    'status' => $timesheet->status,
                    'break_start' => $timesheet->break_start,
                    'break_end' => $timesheet->break_end,
                    'notes' => $timesheet->work_description
                ];
            });
            
            // Get unique departments for filter dropdown
            $departments = Employee::active()
                ->whereNotNull('department')
                ->distinct()
                ->pluck('department')
                ->sort()
                ->values();
            
            // Calculate statistics
            $stats = [
                'total_timesheets' => $timesheetData->count(),
                'total_hours' => round($timesheetData->sum('total_hours'), 1),
                'total_overtime' => round($timesheetData->sum('overtime_hours'), 1),
                'total_employees' => $timesheetData->unique('employee_id')->count(),
                'pending_approval' => $timesheetData->where('status', 'submitted')->count(),
                'approved' => $timesheetData->where('status', 'approved')->count()
            ];
            
            return response()->json([
                'success' => true,
                'data' => $timesheetData->values(),
                'departments' => $departments,
                'stats' => $stats,
                'filters' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'search' => $search,
                    'department' => $department
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load employee timesheets: ' . $e->getMessage(),
                'data' => [],
                'departments' => [],
                'stats' => [
                    'total_timesheets' => 0,
                    'total_hours' => 0,
                    'total_overtime' => 0,
                    'total_employees' => 0,
                    'pending_approval' => 0,
                    'approved' => 0
                ]
            ], 500);
        }
    }
    
    /**
     * Sync attendance data to timesheet
     */
    private function syncAttendanceToTimesheet(Attendance $attendance)
    {
        return $this->timesheetSyncService->syncFromAttendance($attendance);
    }
}
