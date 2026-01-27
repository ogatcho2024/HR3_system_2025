<?php

namespace App\Http\Controllers;

use App\Models\ShiftTemplate;
use App\Models\ShiftAssignment;
use App\Models\ShiftRequest;
use App\Models\Employee;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ShiftManagementController extends Controller
{
    /**
     * Display the shift management page
     */
    public function index(Request $request)
    {
        $shiftTemplates = ShiftTemplate::active()
            ->orderBy('name')
            ->get();
            
        // Get the active tab from the request, default to 'overview'
        $activeTab = $request->get('tab', 'overview');
        
        // Validate that the tab is one of the allowed tabs
        $allowedTabs = ['overview', 'shifts', 'schedule', 'assignments', 'requests'];
        if (!in_array($activeTab, $allowedTabs)) {
            $activeTab = 'overview';
        }
        
        // Calculate real statistics for overview
        $stats = [
            'total_shifts' => $shiftTemplates->count(),
            'active_shifts' => $shiftTemplates->where('status', 'active')->count(),
            'total_assigned_employees' => $shiftTemplates->sum('assigned_employees_count'),
            'pending_requests' => ShiftRequest::pending()->count(),
            'coverage_rate' => $this->calculateCoverageRate($shiftTemplates),
        ];
        
        // Get recent activities (mock data for now - would come from activity log)
        $recentActivities = $this->getRecentActivities();
        
        // Get employee assignments (real data from database)
        $employeeAssignments = $this->getEmployeeAssignments();
        
        // Get shift calendar data for the current week
        $currentWeekStart = Carbon::now()->startOfWeek();
        $currentWeekEnd = Carbon::now()->endOfWeek();
        $shiftCalendarData = $this->getShiftCalendarData($currentWeekStart, $currentWeekEnd);
        
        // Get pending shift requests
        $pendingShiftRequests = $this->getPendingShiftRequests();
        
        // Get all employees for the dropdown (simple approach)
        $availableEmployees = $this->getEmployeesForDropdown();
        
        // Get all active departments for dropdowns
        $departments = Department::active()->orderBy('department_name')->get();
            
        return view('workScheduleShiftManagement', compact('shiftTemplates', 'activeTab', 'stats', 'recentActivities', 'employeeAssignments', 'shiftCalendarData', 'pendingShiftRequests', 'availableEmployees', 'departments'));
    }

    /**
     * Get shift templates (API endpoint)
     */
    public function getShiftTemplates()
    {
        $shiftTemplates = ShiftTemplate::active()
            ->orderBy('name')
            ->get()
            ->map(function ($shift) {
                return [
                    'id' => $shift->id,
                    'name' => $shift->name,
                    'start_time' => $shift->start_time,
                    'end_time' => $shift->end_time,
                    'time_range' => $shift->time_range,
                    'days' => $shift->days,
                    'formatted_days' => $shift->formatted_days,
                    'department' => $shift->department,
                    'description' => $shift->description,
                    'status' => $shift->status,
                    'assigned_employees_count' => $shift->assigned_employees_count
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $shiftTemplates
        ]);
    }

    /**
     * Store a new shift template
     */
    public function store(Request $request)
    {
        // Basic validation rules
        $rules = [
            'name' => 'required|string|max:255|unique:shift_templates,name',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'schedule_type' => 'required|in:weekly,dates',
            'department' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000'
        ];
        
        // Conditional validation based on schedule type
        if ($request->schedule_type === 'weekly') {
            $rules['days'] = 'required|array|min:1';
            $rules['days.*'] = 'in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday';
        } elseif ($request->schedule_type === 'dates') {
            $rules['selected_dates'] = 'required|array|min:1';
            $rules['selected_dates.*'] = 'date_format:Y-m-d|after_or_equal:today';
        }
        
        $validator = Validator::make($request->all(), $rules);

        // Custom validation for time comparison
        if (!$validator->fails()) {
            $startTime = \Carbon\Carbon::createFromFormat('H:i', $request->start_time);
            $endTime = \Carbon\Carbon::createFromFormat('H:i', $request->end_time);
            
            // For night shifts (end time is next day), this is allowed
            // Only validate that they are not the exact same time
            if ($startTime->format('H:i') === $endTime->format('H:i')) {
                $validator->after(function ($validator) {
                    $validator->errors()->add('end_time', 'The end time must be different from the start time.');
                });
            }
        }

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $shiftTemplate = ShiftTemplate::create([
                'name' => $request->name,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'days' => $request->schedule_type === 'weekly' ? $request->days : [],
                'schedule_type' => $request->schedule_type,
                'selected_dates' => $request->schedule_type === 'dates' ? $request->selected_dates : [],
                'department' => $request->department,
                'description' => $request->description,
                'status' => 'active'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Shift template created successfully',
                'data' => [
                    'id' => $shiftTemplate->id,
                    'name' => $shiftTemplate->name,
                    'start_time' => $shiftTemplate->start_time,
                    'end_time' => $shiftTemplate->end_time,
                    'time_range' => $shiftTemplate->time_range,
                    'days' => $shiftTemplate->days,
                    'formatted_days' => $shiftTemplate->formatted_days,
                    'schedule_type' => $shiftTemplate->schedule_type ?? 'weekly',
                    'selected_dates' => $shiftTemplate->selected_dates ?? [],
                    'department' => $shiftTemplate->department,
                    'description' => $shiftTemplate->description,
                    'status' => $shiftTemplate->status,
                    'assigned_employees_count' => $shiftTemplate->assigned_employees_count
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating shift template: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error creating shift template. Please try again.'
            ], 500);
        }
    }

    /**
     * Get a specific shift template
     */
    public function show($id)
    {
        $shiftTemplate = ShiftTemplate::find($id);

        if (!$shiftTemplate) {
            return response()->json([
                'success' => false,
                'message' => 'Shift template not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $shiftTemplate->id,
                'name' => $shiftTemplate->name,
                'start_time' => $shiftTemplate->start_time,
                'end_time' => $shiftTemplate->end_time,
                'days' => $shiftTemplate->days,
                'department' => $shiftTemplate->department,
                'description' => $shiftTemplate->description,
                'status' => $shiftTemplate->status
            ]
        ]);
    }

    /**
     * Update a shift template
     */
    public function update(Request $request, $id)
    {
        $shiftTemplate = ShiftTemplate::find($id);

        if (!$shiftTemplate) {
            return response()->json([
                'success' => false,
                'message' => 'Shift template not found'
            ], 404);
        }

        // Basic validation rules
        $rules = [
            'name' => 'required|string|max:255|unique:shift_templates,name,' . $id,
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'schedule_type' => 'required|in:weekly,dates',
            'department' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000'
        ];
        
        // Conditional validation based on schedule type
        if ($request->schedule_type === 'weekly') {
            $rules['days'] = 'required|array|min:1';
            $rules['days.*'] = 'in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday';
        } elseif ($request->schedule_type === 'dates') {
            $rules['selected_dates'] = 'required|array|min:1';
            $rules['selected_dates.*'] = 'date_format:Y-m-d|after_or_equal:today';
        }
        
        $validator = Validator::make($request->all(), $rules);

        // Custom validation for time comparison
        if (!$validator->fails()) {
            $startTime = \Carbon\Carbon::createFromFormat('H:i', $request->start_time);
            $endTime = \Carbon\Carbon::createFromFormat('H:i', $request->end_time);
            
            // For night shifts (end time is next day), this is allowed
            // Only validate that they are not the exact same time
            if ($startTime->format('H:i') === $endTime->format('H:i')) {
                $validator->after(function ($validator) {
                    $validator->errors()->add('end_time', 'The end time must be different from the start time.');
                });
            }
        }

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $shiftTemplate->update([
                'name' => $request->name,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'days' => $request->schedule_type === 'weekly' ? $request->days : [],
                'schedule_type' => $request->schedule_type,
                'selected_dates' => $request->schedule_type === 'dates' ? $request->selected_dates : [],
                'department' => $request->department,
                'description' => $request->description
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Shift template updated successfully',
                'data' => [
                    'id' => $shiftTemplate->id,
                    'name' => $shiftTemplate->name,
                    'start_time' => $shiftTemplate->start_time,
                    'end_time' => $shiftTemplate->end_time,
                    'time_range' => $shiftTemplate->time_range,
                    'days' => $shiftTemplate->days,
                    'formatted_days' => $shiftTemplate->formatted_days,
                    'schedule_type' => $shiftTemplate->schedule_type ?? 'weekly',
                    'selected_dates' => $shiftTemplate->selected_dates ?? [],
                    'department' => $shiftTemplate->department,
                    'description' => $shiftTemplate->description,
                    'status' => $shiftTemplate->status,
                    'assigned_employees_count' => $shiftTemplate->assigned_employees_count
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating shift template: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error updating shift template. Please try again.'
            ], 500);
        }
    }

    /**
     * Delete a shift template
     */
    public function destroy($id)
    {
        $shiftTemplate = ShiftTemplate::find($id);

        if (!$shiftTemplate) {
            return response()->json([
                'success' => false,
                'message' => 'Shift template not found'
            ], 404);
        }

        try {
            // Automatically remove all shift assignments for this template
            $assignmentCount = ShiftAssignment::where('shift_template_id', $id)->count();
            
            if ($assignmentCount > 0) {
                ShiftAssignment::where('shift_template_id', $id)->delete();
            }
            
            // Delete the shift template
            $shiftTemplate->delete();

            $message = $assignmentCount > 0 
                ? "Shift template deleted successfully. {$assignmentCount} assignment(s) removed."
                : 'Shift template deleted successfully';

            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting shift template: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error deleting shift template. Please try again.'
            ], 500);
        }
    }

    /**
     * Toggle shift template status
     */
    public function toggleStatus($id)
    {
        $shiftTemplate = ShiftTemplate::find($id);

        if (!$shiftTemplate) {
            return response()->json([
                'success' => false,
                'message' => 'Shift template not found'
            ], 404);
        }

        try {
            $shiftTemplate->status = $shiftTemplate->status === 'active' ? 'inactive' : 'active';
            $shiftTemplate->save();

            return response()->json([
                'success' => true,
                'message' => 'Shift template status updated successfully',
                'data' => [
                    'id' => $shiftTemplate->id,
                    'status' => $shiftTemplate->status
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error toggling shift template status: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error updating shift template status. Please try again.'
            ], 500);
        }
    }
    
    /**
     * Calculate coverage rate based on shift templates
     */
    private function calculateCoverageRate($shiftTemplates)
    {
        if ($shiftTemplates->count() === 0) {
            return 0;
        }
        
        $totalShifts = $shiftTemplates->count();
        $coveredShifts = $shiftTemplates->where('assigned_employees_count', '>', 0)->count();
        
        return $totalShifts > 0 ? round(($coveredShifts / $totalShifts) * 100) : 0;
    }
    
    /**
     * Get recent activities (mock data for now)
     * In a real application, this would come from an activity log table
     */
    private function getRecentActivities()
    {
        // This is mock data. In a real application, you would query an activity log table
        return [
            [
                'type' => 'approval',
                'icon' => 'check',
                'color' => 'green',
                'message' => 'Schedule approved for <strong>Marketing Team</strong>',
                'time' => '2 hours ago'
            ],
            [
                'type' => 'creation',
                'icon' => 'plus',
                'color' => 'blue',
                'message' => 'New shift template <strong>Weekend Support</strong> created',
                'time' => '4 hours ago'
            ],
            [
                'type' => 'request',
                'icon' => 'exclamation',
                'color' => 'yellow',
                'message' => 'Schedule change request from <strong>John Smith</strong>',
                'time' => '6 hours ago'
            ]
        ];
    }
    
    /**
     * Get employee shift assignments from database
     */
    private function getEmployeeAssignments()
    {
        try {
            // Get unique assignments grouped by employee and shift template
            $assignments = ShiftAssignment::with(['employee.user', 'shiftTemplate'])
                ->select('employee_id', 'shift_template_id', 'status')
                ->selectRaw('MIN(assignment_date) as start_date')
                ->selectRaw('MAX(assignment_date) as end_date')
                ->selectRaw('COUNT(*) as assignment_count')
                ->groupBy('employee_id', 'shift_template_id', 'status')
                ->get()
                ->map(function ($assignment) {
                    if (!$assignment->employee || !$assignment->employee->user || !$assignment->shiftTemplate) {
                        return null;
                    }
                    
                    $employee = $assignment->employee;
                    $user = $employee->user;
                    $shiftTemplate = $assignment->shiftTemplate;
                    
                    // Generate schedule description
                    $scheduleDesc = $shiftTemplate->formatted_days . ', ' . $shiftTemplate->time_range;
                    
                    return [
                        'id' => $assignment->employee_id . '_' . $assignment->shift_template_id, // Composite ID
                        'employee' => [
                            'name' => $user->name,
                            'email' => $user->email ?? '',
                            'department' => $employee->department ?? 'No Department',
                            'avatar' => null, // You can add avatar logic later
                            'initials' => $this->getInitials($user->name),
                            'avatar_color' => $this->getAvatarColor($user->name)
                        ],
                        'shift' => [
                            'name' => $shiftTemplate->name,
                            'time_range' => $shiftTemplate->time_range,
                            'color' => $this->getShiftColor($shiftTemplate->name)
                        ],
                        'schedule' => $scheduleDesc,
                        'status' => $assignment->status,
                        'assignment_count' => $assignment->assignment_count,
                        'start_date' => Carbon::parse($assignment->start_date)->format('M j, Y'),
                        'end_date' => Carbon::parse($assignment->end_date)->format('M j, Y')
                    ];
                })
                ->filter() // Remove null entries
                ->take(20) // Limit to prevent overwhelming the UI
                ->values();
                
            return $assignments->toArray();
        } catch (\Exception $e) {
            Log::error('Error fetching employee assignments: ' . $e->getMessage());
            
            // Return empty array on error
            return [];
        }
    }

    /**
     * Get shift calendar data for a specific date range
     */
    public function getShiftCalendarData($startDate, $endDate)
    {
        // Get all active shift templates
        $shiftTemplates = ShiftTemplate::active()->get();
        
        // Get all shift assignments for the date range
        $assignments = ShiftAssignment::with(['employee.user', 'shiftTemplate'])
            ->inDateRange($startDate, $endDate)
            ->byStatus(['scheduled', 'confirmed'])
            ->get();
        
        // Create a structure for each day of the week
        $weekDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $calendarData = [];
        
        foreach ($shiftTemplates as $shiftTemplate) {
            $shiftData = [
                'id' => $shiftTemplate->id,
                'name' => $shiftTemplate->name,
                'time_range' => $shiftTemplate->time_range,
                'color' => $this->getShiftColor($shiftTemplate->name),
                'days' => []
            ];
            
            // Initialize days with empty data
            foreach ($weekDays as $day) {
                $shiftData['days'][$day] = [
                    'total_count' => 0,
                    'departments' => []
                ];
            }
            
            // Fill in actual assignment data
            foreach ($assignments as $assignment) {
                if ($assignment->shift_template_id === $shiftTemplate->id) {
                    $dayOfWeek = $assignment->assignment_date->format('l');
                    $department = $assignment->employee->department ?? 'Other';
                    
                    $shiftData['days'][$dayOfWeek]['total_count']++;
                    
                    if (!isset($shiftData['days'][$dayOfWeek]['departments'][$department])) {
                        $shiftData['days'][$dayOfWeek]['departments'][$department] = [
                            'count' => 0,
                            'employees' => [],
                            'color' => $this->getDepartmentColor($department)
                        ];
                    }
                    
                    $shiftData['days'][$dayOfWeek]['departments'][$department]['count']++;
                    $shiftData['days'][$dayOfWeek]['departments'][$department]['employees'][] = [
                        'name' => $assignment->employee->user->name ?? 'Unknown',
                        'initials' => $this->getInitials($assignment->employee->user->name ?? 'UN')
                    ];
                }
            }
            
            $calendarData[] = $shiftData;
        }
        
        return $calendarData;
    }

    /**
     * Get API endpoint for shift calendar data
     */
    public function getShiftCalendarDataApi(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfWeek());
        $endDate = $request->get('end_date', Carbon::now()->endOfWeek());
        $department = $request->get('department');
        $shiftTemplate = $request->get('shift_template');
        
        $startDate = Carbon::parse($startDate);
        $endDate = Carbon::parse($endDate);
        
        $calendarData = $this->getShiftCalendarData($startDate, $endDate);
        
        // Apply filters if provided
        if ($department) {
            foreach ($calendarData as &$shift) {
                foreach ($shift['days'] as &$day) {
                    $day['departments'] = array_filter($day['departments'], function($key) use ($department) {
                        return strtolower($key) === strtolower($department);
                    }, ARRAY_FILTER_USE_KEY);
                    
                    $day['total_count'] = array_sum(array_column($day['departments'], 'count'));
                }
            }
        }
        
        if ($shiftTemplate) {
            $calendarData = array_filter($calendarData, function($shift) use ($shiftTemplate) {
                return $shift['id'] == $shiftTemplate;
            });
        }
        
        return response()->json([
            'success' => true,
            'data' => array_values($calendarData),
            'week_range' => [
                'start' => $startDate->format('M j'),
                'end' => $endDate->format('M j, Y')
            ]
        ]);
    }

    /**
     * Get color for shift based on name/type
     */
    private function getShiftColor($shiftName)
    {
        $colors = [
            'morning' => 'blue',
            'evening' => 'green',
            'night' => 'purple',
            'weekend' => 'yellow'
        ];
        
        $lowerName = strtolower($shiftName);
        foreach ($colors as $keyword => $color) {
            if (strpos($lowerName, $keyword) !== false) {
                return $color;
            }
        }
        
        return 'gray';
    }

    /**
     * Get color for department
     */
    private function getDepartmentColor($department)
    {
        $colors = [
            'IT' => 'blue',
            'Marketing' => 'green',
            'HR' => 'purple',
            'Finance' => 'indigo',
            'Operations' => 'orange',
            'Security' => 'red',
            'Maintenance' => 'gray'
        ];
        
        return $colors[$department] ?? 'gray';
    }

    /**
     * Get initials from name
     */
    private function getInitials($name)
    {
        $words = explode(' ', trim($name));
        $initials = '';
        
        foreach ($words as $word) {
            if (!empty($word)) {
                $initials .= strtoupper(substr($word, 0, 1));
            }
        }
        
        return substr($initials, 0, 2); // Limit to 2 characters
    }
    
    /**
     * Get all employees for assignment dropdown (API endpoint)
     * Excludes employees who are already assigned to any shift
     */
    public function getEmployeesForAssignment()
    {
        try {
            // Get IDs of employees who already have shift assignments
            $assignedEmployeeIds = ShiftAssignment::distinct('employee_id')
                ->pluck('employee_id')
                ->toArray();
            
            $employees = Employee::with('user')
                ->whereHas('user')
                ->where('status', 'active')
                ->whereNotIn('id', $assignedEmployeeIds)
                ->get()
                ->map(function ($employee) {
                    $fullName = trim($employee->user->name . ' ' . ($employee->user->lastname ?? ''));
                    return [
                        'id' => $employee->id,
                        'user_id' => $employee->user_id,
                        'name' => $fullName,
                        'first_name' => $employee->user->name,
                        'last_name' => $employee->user->lastname ?? '',
                        'email' => $employee->user->email ?? '',
                        'department' => $employee->department ?? 'No Department',
                        'position' => $employee->position ?? 'No Position',
                        'employee_id' => $employee->employee_id ?? 'N/A',
                        'initials' => $this->getInitials($fullName),
                        'avatar_color' => $this->getAvatarColor($fullName)
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $employees
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching employees for assignment: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error fetching employees. Please try again.'
            ], 500);
        }
    }
    
    /**
     * Get employees for dropdown (simple method for blade template)
     * Excludes employees who are already assigned to any shift
     */
    private function getEmployeesForDropdown()
    {
        try {
            // Get IDs of employees who already have shift assignments
            $assignedEmployeeIds = ShiftAssignment::distinct('employee_id')
                ->pluck('employee_id')
                ->toArray();
            
            return Employee::with('user')
                ->whereHas('user')
                ->where('status', 'active')
                ->whereNotIn('id', $assignedEmployeeIds)
                ->get()
                ->map(function ($employee) {
                    $fullName = trim($employee->user->name . ' ' . ($employee->user->lastname ?? ''));
                    return [
                        'id' => $employee->id,
                        'name' => $fullName,
                        'employee_id' => $employee->employee_id ?? 'N/A',
                        'department' => $employee->department ?? 'No Department',
                        'position' => $employee->position ?? 'No Position',
                    ];
                });
        } catch (\Exception $e) {
            Log::error('Error fetching employees for dropdown: ' . $e->getMessage());
            return collect(); // Return empty collection on error
        }
    }
    
    /**
     * Store a new shift assignment
     */
    public function storeAssignment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'shift_template_id' => 'required|exists:shift_templates,id',
            'start_date' => 'required|date|after_or_equal:today',
            'notes' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $employee = Employee::find($request->employee_id);
            $shiftTemplate = ShiftTemplate::find($request->shift_template_id);
            
            if (!$employee || !$shiftTemplate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee or shift template not found'
                ], 404);
            }
            
            // Check if employee is already assigned to any shift
            $existingAssignment = ShiftAssignment::where('employee_id', $request->employee_id)
                ->with('shiftTemplate')
                ->first();
            
            if ($existingAssignment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee is already assigned to "' . $existingAssignment->shiftTemplate->name . '". Please remove the existing assignment first or reassign the employee.'
                ], 422);
            }

            // Create shift assignment based on the template schedule type
            $startDate = Carbon::parse($request->start_date);
            $assignments = [];
            
            if ($shiftTemplate->schedule_type === 'weekly' && !empty($shiftTemplate->days)) {
                // Create assignments for the next 4 weeks for weekly schedules
                $endDate = $startDate->copy()->addWeeks(4);
                
                for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                    $dayName = $date->format('l'); // Monday, Tuesday, etc.
                    
                    if (in_array($dayName, $shiftTemplate->days)) {
                        $assignments[] = [
                            'employee_id' => $request->employee_id,
                            'shift_template_id' => $request->shift_template_id,
                            'assignment_date' => $date->toDateString(),
                            'status' => 'scheduled',
                            'notes' => $request->notes,
                            'assigned_by' => auth()->id() ?? 1,
                            'created_at' => now(),
                            'updated_at' => now()
                        ];
                    }
                }
            } elseif ($shiftTemplate->schedule_type === 'dates' && !empty($shiftTemplate->selected_dates)) {
                // Create assignments for specific dates
                foreach ($shiftTemplate->selected_dates as $dateString) {
                    $date = Carbon::parse($dateString);
                    if ($date->gte($startDate)) {
                        $assignments[] = [
                            'employee_id' => $request->employee_id,
                            'shift_template_id' => $request->shift_template_id,
                            'assignment_date' => $date->toDateString(),
                            'status' => 'scheduled',
                            'notes' => $request->notes,
                            'assigned_by' => auth()->id() ?? 1,
                            'created_at' => now(),
                            'updated_at' => now()
                        ];
                    }
                }
            } else {
                // Fallback: create single assignment for start date
                $assignments[] = [
                    'employee_id' => $request->employee_id,
                    'shift_template_id' => $request->shift_template_id,
                    'assignment_date' => $startDate->toDateString(),
                    'status' => 'scheduled',
                    'notes' => $request->notes,
                    'assigned_by' => auth()->id() ?? 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
            
            if (!empty($assignments)) {
                ShiftAssignment::insert($assignments);
                
                // Update the assigned employees count on the shift template
                $shiftTemplate->increment('assigned_employees_count');
                
                return response()->json([
                    'success' => true,
                    'message' => 'Employee assigned to shift successfully',
                    'data' => [
                        'assignments_created' => count($assignments),
                        'employee_name' => $employee->user->name,
                        'shift_name' => $shiftTemplate->name
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No valid dates found for assignment'
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error('Error creating shift assignment: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error creating shift assignment. Please try again.'
            ], 500);
        }
    }
    
    /**
     * Update a shift assignment
     */
    public function updateAssignment(Request $request, $assignmentId)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'shift_template_id' => 'required|exists:shift_templates,id',
            'start_date' => 'required|date|after_or_equal:today',
            'notes' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // For composite ID structure (employee_id + shift_template_id)
            $parts = explode('_', $assignmentId);
            if (count($parts) >= 2) {
                $employeeId = $parts[0];
                $shiftTemplateId = $parts[1];
                
                // Find existing assignments for this employee and shift template
                $assignments = ShiftAssignment::where('employee_id', $employeeId)
                    ->where('shift_template_id', $shiftTemplateId)
                    ->get();
                
                if ($assignments->isEmpty()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Assignment not found'
                    ], 404);
                }
                
                // Update the notes for all related assignments
                ShiftAssignment::where('employee_id', $employeeId)
                    ->where('shift_template_id', $shiftTemplateId)
                    ->update(['notes' => $request->notes]);
                
                // Get updated assignment data for response
                $employee = Employee::with('user')->find($request->employee_id);
                $shiftTemplate = ShiftTemplate::find($request->shift_template_id);
                
                $updatedAssignment = [
                    'id' => $assignmentId,
                    'employee' => [
                        'id' => $employee->id,
                        'name' => $employee->user->name,
                        'email' => $employee->user->email ?? '',
                        'department' => $employee->department ?? 'No Department',
                        'avatar' => null,
                        'initials' => $this->getInitials($employee->user->name),
                        'avatar_color' => $this->getAvatarColor($employee->user->name)
                    ],
                    'shift' => [
                        'id' => $shiftTemplate->id,
                        'name' => $shiftTemplate->name,
                        'time_range' => $shiftTemplate->time_range,
                        'color' => $this->getShiftColor($shiftTemplate->name)
                    ],
                    'schedule' => $shiftTemplate->formatted_days . ', ' . $shiftTemplate->time_range,
                    'start_date' => Carbon::parse($request->start_date)->format('M j, Y'),
                    'notes' => $request->notes
                ];
                
                return response()->json([
                    'success' => true,
                    'message' => 'Assignment updated successfully',
                    'data' => $updatedAssignment
                ]);
            } else {
                // Handle regular assignment ID
                $assignment = ShiftAssignment::with(['employee.user', 'shiftTemplate'])->find($assignmentId);
                
                if (!$assignment) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Assignment not found'
                    ], 404);
                }
                
                $assignment->update([
                    'notes' => $request->notes
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Assignment updated successfully',
                    'data' => $assignment
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error updating shift assignment: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error updating assignment. Please try again.'
            ], 500);
        }
    }
    
    /**
     * Remove a shift assignment
     */
    public function removeAssignment($assignmentId)
    {
        try {
            // Handle composite ID structure (employee_id + shift_template_id)
            $parts = explode('_', $assignmentId);
            if (count($parts) >= 2) {
                $employeeId = $parts[0];
                $shiftTemplateId = $parts[1];
                
                // Find and delete all assignments for this employee and shift template
                $assignments = ShiftAssignment::with(['employee.user', 'shiftTemplate'])
                    ->where('employee_id', $employeeId)
                    ->where('shift_template_id', $shiftTemplateId)
                    ->get();
                
                if ($assignments->isEmpty()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Assignment not found'
                    ], 404);
                }
                
                $shiftTemplate = $assignments->first()->shiftTemplate;
                $employeeName = $assignments->first()->employee->user->name ?? 'Unknown Employee';
                
                // Delete all related assignments
                ShiftAssignment::where('employee_id', $employeeId)
                    ->where('shift_template_id', $shiftTemplateId)
                    ->delete();
                
                // Decrease the assigned employees count
                if ($shiftTemplate && $shiftTemplate->assigned_employees_count > 0) {
                    $shiftTemplate->decrement('assigned_employees_count');
                }
                
                return response()->json([
                    'success' => true,
                    'message' => "Assignment for {$employeeName} removed successfully"
                ]);
            } else {
                // Handle regular assignment ID
                $assignment = ShiftAssignment::with(['employee.user', 'shiftTemplate'])->find($assignmentId);
                
                if (!$assignment) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Assignment not found'
                    ], 404);
                }
                
                $shiftTemplate = $assignment->shiftTemplate;
                $employeeName = $assignment->employee->user->name ?? 'Unknown Employee';
                
                $assignment->delete();
                
                // Decrease the assigned employees count
                if ($shiftTemplate && $shiftTemplate->assigned_employees_count > 0) {
                    $shiftTemplate->decrement('assigned_employees_count');
                }
                
                return response()->json([
                    'success' => true,
                    'message' => "Assignment for {$employeeName} removed successfully"
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error removing shift assignment: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error removing assignment. Please try again.'
            ], 500);
        }
    }
    
    /**
     * Reassign an employee from one shift to another
     */
    public function reassignEmployee(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'new_shift_template_id' => 'required|exists:shift_templates,id',
            'start_date' => 'required|date|after_or_equal:today',
            'notes' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $employee = Employee::with('user')->find($request->employee_id);
            $newShiftTemplate = ShiftTemplate::find($request->new_shift_template_id);
            
            if (!$employee || !$newShiftTemplate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee or shift template not found'
                ], 404);
            }
            
            // Find existing assignment
            $existingAssignment = ShiftAssignment::where('employee_id', $request->employee_id)
                ->with('shiftTemplate')
                ->first();
            
            if (!$existingAssignment) {
                return response()->json([
                    'success' => false,
                    'message' => 'No existing assignment found for this employee'
                ], 404);
            }
            
            $oldShiftTemplate = $existingAssignment->shiftTemplate;
            
            // Check if trying to reassign to the same shift
            if ($existingAssignment->shift_template_id == $request->new_shift_template_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee is already assigned to this shift'
                ], 422);
            }
            
            // Delete all old assignments
            ShiftAssignment::where('employee_id', $request->employee_id)->delete();
            
            // Decrease the count on old shift template
            if ($oldShiftTemplate && $oldShiftTemplate->assigned_employees_count > 0) {
                $oldShiftTemplate->decrement('assigned_employees_count');
            }
            
            // Create new assignments based on the template schedule type
            $startDate = Carbon::parse($request->start_date);
            $assignments = [];
            
            if ($newShiftTemplate->schedule_type === 'weekly' && !empty($newShiftTemplate->days)) {
                // Create assignments for the next 4 weeks for weekly schedules
                $endDate = $startDate->copy()->addWeeks(4);
                
                for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                    $dayName = $date->format('l');
                    
                    if (in_array($dayName, $newShiftTemplate->days)) {
                        $assignments[] = [
                            'employee_id' => $request->employee_id,
                            'shift_template_id' => $request->new_shift_template_id,
                            'assignment_date' => $date->toDateString(),
                            'status' => 'scheduled',
                            'notes' => $request->notes,
                            'assigned_by' => auth()->id() ?? 1,
                            'created_at' => now(),
                            'updated_at' => now()
                        ];
                    }
                }
            } elseif ($newShiftTemplate->schedule_type === 'dates' && !empty($newShiftTemplate->selected_dates)) {
                // Create assignments for specific dates
                foreach ($newShiftTemplate->selected_dates as $dateString) {
                    $date = Carbon::parse($dateString);
                    if ($date->gte($startDate)) {
                        $assignments[] = [
                            'employee_id' => $request->employee_id,
                            'shift_template_id' => $request->new_shift_template_id,
                            'assignment_date' => $date->toDateString(),
                            'status' => 'scheduled',
                            'notes' => $request->notes,
                            'assigned_by' => auth()->id() ?? 1,
                            'created_at' => now(),
                            'updated_at' => now()
                        ];
                    }
                }
            } else {
                // Fallback: create single assignment for start date
                $assignments[] = [
                    'employee_id' => $request->employee_id,
                    'shift_template_id' => $request->new_shift_template_id,
                    'assignment_date' => $startDate->toDateString(),
                    'status' => 'scheduled',
                    'notes' => $request->notes,
                    'assigned_by' => auth()->id() ?? 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
            
            if (!empty($assignments)) {
                ShiftAssignment::insert($assignments);
                
                // Increment the count on new shift template
                $newShiftTemplate->increment('assigned_employees_count');
                
                return response()->json([
                    'success' => true,
                    'message' => "Employee reassigned from '{$oldShiftTemplate->name}' to '{$newShiftTemplate->name}' successfully",
                    'data' => [
                        'assignments_created' => count($assignments),
                        'employee_name' => $employee->user->name,
                        'old_shift_name' => $oldShiftTemplate->name,
                        'new_shift_name' => $newShiftTemplate->name
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No valid dates found for reassignment'
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error('Error reassigning employee: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error reassigning employee. Please try again.'
            ], 500);
        }
    }
    
    /**
     * Get avatar color based on name hash
     */
    private function getAvatarColor($name)
    {
        $colors = ['blue', 'green', 'purple', 'red', 'indigo', 'pink', 'yellow', 'teal', 'orange', 'cyan'];
        $colorIndex = abs(crc32($name)) % count($colors);
        return $colors[$colorIndex];
    }
    
    /**
     * Get pending shift requests with user and employee details
     */
    private function getPendingShiftRequests()
    {
        try {
            $pendingRequests = ShiftRequest::with(['user', 'user.employee', 'swapWithUser'])
                ->pending()
                ->orderBy('created_at', 'desc')
                ->limit(10) // Limit to latest 10 pending requests
                ->get()
                ->map(function ($request) {
                    $user = $request->user;
                    $employee = $user->employee ?? null;
                    
                    return [
                        'id' => $request->id,
                        'user' => [
                            'id' => $user->id,
                            'name' => $user->name,
                            'email' => $user->email,
                            'initials' => $this->getInitials($user->name),
                            'avatar_color' => $this->getAvatarColor($user->name),
                            'department' => $employee->department ?? 'No Department'
                        ],
                        'request_type' => ucfirst(str_replace('_', ' ', $request->request_type)),
                        'requested_date' => $request->requested_date->format('M j, Y'),
                        'current_start_time' => $request->current_start_time ? $request->current_start_time : null,
                        'current_end_time' => $request->current_end_time ? $request->current_end_time : null,
                        'requested_start_time' => $request->requested_start_time ? $request->requested_start_time : null,
                        'requested_end_time' => $request->requested_end_time ? $request->requested_end_time : null,
                        'swap_with_user' => $request->swapWithUser ? [
                            'id' => $request->swapWithUser->id,
                            'name' => $request->swapWithUser->name
                        ] : null,
                        'reason' => $request->reason,
                        'status' => $request->status,
                        'status_badge_color' => $request->status_badge_color,
                        'created_at' => $request->created_at->diffForHumans(),
                        'readable_request' => $this->formatShiftRequestDescription($request)
                    ];
                });
                
            return $pendingRequests->toArray();
        } catch (\Exception $e) {
            Log::error('Error fetching pending shift requests: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Format shift request into readable description
     */
    private function formatShiftRequestDescription($request)
    {
        switch ($request->request_type) {
            case 'swap':
                if ($request->swapWithUser) {
                    return "Swap shift with <span class=\"font-medium\">{$request->swapWithUser->name}</span>";
                }
                return "Swap shift request";
                
            case 'cover':
                return "Request shift coverage";
                
            case 'overtime':
                return "Request for <span class=\"font-medium\">Overtime</span>";
                
            case 'schedule_change':
                if ($request->current_start_time && $request->requested_start_time) {
                    $currentTime = date('g:i A', strtotime($request->current_start_time));
                    $requestedTime = date('g:i A', strtotime($request->requested_start_time));
                    return "Change from <span class=\"font-medium\">{$currentTime}</span> to <span class=\"font-medium\">{$requestedTime}</span>";
                }
                return "Schedule change request";
                
            default:
                return ucfirst(str_replace('_', ' ', $request->request_type)) . " request";
        }
    }
    
    /**
     * Approve a shift request
     */
    public function approveShiftRequest(Request $request, $id)
    {
        try {
            $shiftRequest = ShiftRequest::find($id);
            
            if (!$shiftRequest) {
                return back()->with('error', 'Shift request not found');
            }
            
            if ($shiftRequest->status !== 'pending') {
                return back()->with('error', 'This request has already been processed');
            }
            
            $shiftRequest->update([
                'status' => 'approved',
                'approved_by' => auth()->id() ?? 1,
                'approved_at' => now(),
                'manager_comments' => $request->input('comments', 'Approved')
            ]);
            
            return back()->with('success', 'Shift request approved successfully!');
            
        } catch (\Exception $e) {
            Log::error('Error approving shift request: ' . $e->getMessage());
            return back()->with('error', 'Error approving shift request. Please try again.');
        }
    }
    
    /**
     * Reject a shift request
     */
    public function rejectShiftRequest(Request $request, $id)
    {
        try {
            $shiftRequest = ShiftRequest::find($id);
            
            if (!$shiftRequest) {
                return back()->with('error', 'Shift request not found');
            }
            
            if ($shiftRequest->status !== 'pending') {
                return back()->with('error', 'This request has already been processed');
            }
            
            $shiftRequest->update([
                'status' => 'rejected',
                'approved_by' => auth()->id() ?? 1,
                'approved_at' => now(),
                'manager_comments' => $request->input('comments', 'Rejected')
            ]);
            
            return back()->with('success', 'Shift request rejected successfully!');
            
        } catch (\Exception $e) {
            Log::error('Error rejecting shift request: ' . $e->getMessage());
            return back()->with('error', 'Error rejecting shift request. Please try again.');
        }
    }
    
    /**
     * Create a new shift request manually (Admin only)
     */
    public function createShiftRequest(Request $request)
    {
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'request_type' => 'required|string|in:time_off,shift_change,swap',
            'requested_date' => 'required|date',
            'requested_start_time' => 'required|date_format:H:i',
            'requested_end_time' => 'required|date_format:H:i',
            'reason' => 'required|string|max:1000',
            'status' => 'required|in:pending,approved,rejected',
            'manager_comments' => 'nullable|string|max:1000',
        ]);
        
        try {
            $shiftRequest = ShiftRequest::create([
                'user_id' => $validatedData['user_id'],
                'request_type' => $validatedData['request_type'],
                'requested_date' => $validatedData['requested_date'],
                'requested_start_time' => $validatedData['requested_start_time'],
                'requested_end_time' => $validatedData['requested_end_time'],
                'reason' => $validatedData['reason'],
                'status' => $validatedData['status'],
                'manager_comments' => $validatedData['manager_comments'] ?? null,
                'approved_by' => ($validatedData['status'] !== 'pending') ? auth()->id() : null,
                'approved_at' => ($validatedData['status'] !== 'pending') ? now() : null,
            ]);
            
            return redirect()->route('workScheduleShiftManagement', ['tab' => 'requests'])
                ->with('success', 'Shift request created successfully!');
                
        } catch (\Exception $e) {
            Log::error('Error creating shift request: ' . $e->getMessage());
            return back()->with('error', 'Error creating shift request. Please try again.')
                ->withInput();
        }
    }
}
