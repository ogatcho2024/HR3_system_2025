<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Timesheet;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TimesheetController extends Controller
{
    /**
     * Get employee timesheets for the Employees Timesheet tab
     */
    public function getEmployeeTimesheets(Request $request): JsonResponse
    {
        try {
            // Get date range from request, default to current week
            $startDate = $request->get('start_date', now()->startOfWeek()->toDateString());
            $endDate = $request->get('end_date', now()->endOfWeek()->toDateString());
            
            // Get search parameters
            $search = $request->get('search', '');
            $department = $request->get('department', '');
            
            // Base query for timesheets with user and employee relationships
            $query = Timesheet::with(['user.employee'])
                ->whereBetween('work_date', [$startDate, $endDate]);
            
            // Apply search filter
            if (!empty($search)) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('email', 'LIKE', "%{$search}%");
                });
            }
            
            // Apply department filter
            if (!empty($department)) {
                $query->whereHas('user.employee', function ($q) use ($department) {
                    $q->where('department', $department);
                });
            }
            
            // Get timesheets ordered by date and employee name
            $timesheets = $query->orderBy('work_date', 'desc')
                ->orderBy('user_id')
                ->get();
            
            // Format the data for display
            $formattedTimesheets = $timesheets->map(function ($timesheet) {
                $user = $timesheet->user;
                $employee = $user ? $user->employee : null;
                
                return [
                    'id' => $timesheet->id,
                    'employee' => $user ? $user->name : 'Unknown',
                    'employee_id' => $user ? $user->id : null,
                    'department' => $employee ? $employee->department : 'No Department',
                    'position' => $employee ? $employee->position : 'No Position',
                    'date' => $timesheet->work_date,
                    'time_start' => $timesheet->clock_in_time ? 
                        Carbon::createFromTimeString($timesheet->clock_in_time)->format('H:i') : '--',
                    'time_end' => $timesheet->clock_out_time ? 
                        Carbon::createFromTimeString($timesheet->clock_out_time)->format('H:i') : '--',
                    'overtime_hours' => $timesheet->overtime_hours ? 
                        number_format($timesheet->overtime_hours, 2) : '0.00',
                    'total_hours' => $timesheet->hours_worked ? 
                        number_format($timesheet->hours_worked, 2) : '0.00',
                    'status' => $timesheet->status,
                    'project_name' => $timesheet->project_name ?: 'General Work',
                    'work_description' => $timesheet->work_description ?: 'Daily work activities',
                    'break_hours' => $this->calculateBreakHours($timesheet),
                ];
            });
            
            // Get unique departments for filter
            $departments = Employee::whereNotNull('department')
                ->distinct()
                ->pluck('department')
                ->sort()
                ->values();
            
            return response()->json([
                'success' => true,
                'data' => $formattedTimesheets,
                'departments' => $departments,
                'date_range' => [
                    'start' => $startDate,
                    'end' => $endDate
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch employee timesheets: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get timesheet summary statistics
     */
    public function getTimesheetStats(Request $request): JsonResponse
    {
        try {
            $startDate = $request->get('start_date', now()->startOfWeek()->toDateString());
            $endDate = $request->get('end_date', now()->endOfWeek()->toDateString());
            
            $stats = [
                'total_employees' => Employee::active()->count(),
                'total_timesheets' => Timesheet::whereBetween('work_date', [$startDate, $endDate])->count(),
                'total_hours' => Timesheet::whereBetween('work_date', [$startDate, $endDate])->sum('hours_worked'),
                'total_overtime' => Timesheet::whereBetween('work_date', [$startDate, $endDate])->sum('overtime_hours'),
                'pending_approval' => Timesheet::whereBetween('work_date', [$startDate, $endDate])
                    ->where('status', 'submitted')->count(),
                'approved' => Timesheet::whereBetween('work_date', [$startDate, $endDate])
                    ->where('status', 'approved')->count(),
            ];
            
            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch timesheet statistics: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Update a timesheet entry
     */
    public function updateTimesheet(Request $request, Timesheet $timesheet): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'clock_in_time' => 'nullable|date_format:H:i',
                'clock_out_time' => 'nullable|date_format:H:i',
                'break_start' => 'nullable|date_format:H:i',
                'break_end' => 'nullable|date_format:H:i',
                'project_name' => 'nullable|string|max:255',
                'work_description' => 'nullable|string|max:500',
            ]);
            
            // Calculate hours worked if both times are provided
            if (!empty($validatedData['clock_in_time']) && !empty($validatedData['clock_out_time'])) {
                $hoursWorked = $timesheet->calculateHours();
                $validatedData['hours_worked'] = $hoursWorked;
                
                // Calculate overtime (anything over 8 hours)
                $regularHours = 8.0;
                $overtimeHours = max(0, $hoursWorked - $regularHours);
                $validatedData['overtime_hours'] = $overtimeHours;
            }
            
            $timesheet->update($validatedData);
            
            return response()->json([
                'success' => true,
                'message' => 'Timesheet updated successfully',
                'timesheet' => $timesheet->fresh()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update timesheet: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Delete a timesheet entry
     */
    public function deleteTimesheet(Timesheet $timesheet): JsonResponse
    {
        try {
            $timesheet->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Timesheet deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete timesheet: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Display the timesheet management dashboard
     */
    public function managementDashboard(): View
    {
        try {
            // Get current week dates
            $currentWeekStart = now()->startOfWeek();
            $currentWeekEnd = now()->endOfWeek();
            
            // Calculate Quick Stats Overview
            $stats = [
                'pendingApprovals' => Timesheet::where('status', 'submitted')->count(),
                'totalEmployees' => Employee::active()->count(),
                'overtimeHours' => round(Timesheet::whereBetween('work_date', [$currentWeekStart, $currentWeekEnd])
                    ->sum('overtime_hours'), 0),
                'weeklyHours' => round(Timesheet::whereBetween('work_date', [$currentWeekStart, $currentWeekEnd])
                    ->sum('hours_worked'), 0)
            ];
            
            // Get overview stats for current week
            $overviewStats = [
                'submitted' => Timesheet::whereBetween('work_date', [$currentWeekStart, $currentWeekEnd])
                    ->where('status', 'submitted')->count(),
                'pending' => Timesheet::whereBetween('work_date', [$currentWeekStart, $currentWeekEnd])
                    ->where('status', 'submitted')->count(),
                'overdue' => Timesheet::where('work_date', '<', now()->subDays(7))
                    ->where('status', 'draft')->count()
            ];
            
            // Get pending approvals data
            $pendingApprovals = Timesheet::with(['user', 'user.employee'])
                ->where('status', 'submitted')
                ->orderBy('submitted_at', 'desc')
                ->get()
                ->map(function ($timesheet) {
                    $user = $timesheet->user;
                    $employee = $user->employee ?? null;
                    
                    return [
                        'id' => $timesheet->id,
                        'employee' => $user->name,
                        'department' => $employee->department ?? 'No Department',
                        'position' => $employee->position ?? 'No Position',
                        'work_date' => $timesheet->work_date->format('M j, Y'),
                        'project_name' => $timesheet->project_name ?? 'General Work',
                        'clock_in' => $timesheet->clock_in_time ? Carbon::createFromTimeString($timesheet->clock_in_time)->format('H:i') : '--',
                        'clock_out' => $timesheet->clock_out_time ? Carbon::createFromTimeString($timesheet->clock_out_time)->format('H:i') : '--',
                        'totalHours' => number_format($timesheet->hours_worked ?? 0, 2),
                        'overtimeHours' => number_format($timesheet->overtime_hours ?? 0, 2),
                        'priority' => $timesheet->overtime_hours > 10 ? 'High' : ($timesheet->overtime_hours > 5 ? 'Medium' : 'Low'),
                        'submittedAt' => $timesheet->submitted_at ? $timesheet->submitted_at->diffForHumans() : 'Recently',
                        'selected' => false
                    ];
                });
                
            return view('timeSheetManagement', compact('stats', 'overviewStats', 'pendingApprovals'));
            
        } catch (\Exception $e) {
            // Fallback to default values if there's an error
            $stats = [
                'pendingApprovals' => 0,
                'totalEmployees' => Employee::count(),
                'overtimeHours' => 0,
                'weeklyHours' => 0
            ];
            
            $overviewStats = [
                'submitted' => 0,
                'pending' => 0,
                'overdue' => 0
            ];
            
            $pendingApprovals = [];
            
            return view('timeSheetManagement', compact('stats', 'overviewStats', 'pendingApprovals'));
        }
    }
    
    /**
     * Approve a timesheet
     */
    public function approveTimesheet(Timesheet $timesheet): JsonResponse
    {
        try {
            if ($timesheet->status !== 'submitted') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only submitted timesheets can be approved'
                ], 422);
            }
            
            $timesheet->update([
                'status' => 'approved',
                'approved_at' => now(),
                'approved_by' => Auth::id() ?? 1
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Timesheet approved successfully',
                'timesheet' => $timesheet->fresh()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve timesheet: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Reject a timesheet
     */
    public function rejectTimesheet(Request $request, Timesheet $timesheet): JsonResponse
    {
        try {
            if ($timesheet->status !== 'submitted') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only submitted timesheets can be rejected'
                ], 422);
            }
            
            $timesheet->update([
                'status' => 'rejected',
                'manager_comments' => $request->input('reason', 'No reason provided'),
                'approved_at' => now(),
                'approved_by' => Auth::id() ?? 1
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Timesheet rejected successfully',
                'timesheet' => $timesheet->fresh()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject timesheet: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Bulk approve timesheets
     */
    public function bulkApproveTimesheets(Request $request): JsonResponse
    {
        try {
            $timesheetIds = $request->input('timesheet_ids', []);
            
            if (empty($timesheetIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No timesheets selected for approval'
                ], 422);
            }
            
            $updated = Timesheet::whereIn('id', $timesheetIds)
                ->where('status', 'submitted')
                ->update([
                    'status' => 'approved',
                    'approved_at' => now(),
                    'approved_by' => Auth::id() ?? 1
                ]);
                
            return response()->json([
                'success' => true,
                'message' => "Successfully approved {$updated} timesheets",
                'approved_count' => $updated
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to bulk approve timesheets: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Bulk reject timesheets
     */
    public function bulkRejectTimesheets(Request $request): JsonResponse
    {
        try {
            $timesheetIds = $request->input('timesheet_ids', []);
            $reason = $request->input('reason', 'Bulk rejection');
            
            if (empty($timesheetIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No timesheets selected for rejection'
                ], 422);
            }
            
            $updated = Timesheet::whereIn('id', $timesheetIds)
                ->where('status', 'submitted')
                ->update([
                    'status' => 'rejected',
                    'manager_comments' => $reason,
                    'approved_at' => now(),
                    'approved_by' => Auth::id() ?? 1
                ]);
                
            return response()->json([
                'success' => true,
                'message' => "Successfully rejected {$updated} timesheets",
                'rejected_count' => $updated
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to bulk reject timesheets: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Calculate break hours from break start and end times
     */
    private function calculateBreakHours(Timesheet $timesheet): string
    {
        if (!$timesheet->break_start || !$timesheet->break_end) {
            return '0.00';
        }
        
        try {
            $breakStart = Carbon::createFromTimeString($timesheet->break_start);
            $breakEnd = Carbon::createFromTimeString($timesheet->break_end);
            $breakMinutes = $breakEnd->diffInMinutes($breakStart);
            
            return number_format($breakMinutes / 60, 2);
        } catch (\Exception $e) {
            return '0.00';
        }
    }
}
