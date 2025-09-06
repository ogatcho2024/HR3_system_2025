<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

// Attendance API Routes
Route::prefix('attendance')->group(function () {
    
    // Overview data endpoint
    Route::get('/overview-data', function () {
        return response()->json([
            'success' => true,
            'data' => [
                'present' => 142,
                'late' => 8,
                'absent' => 12,
                'onBreak' => 15,
                'totalEmployees' => 150,
                'avgCheckIn' => '08:24',
                'weeklyOvertime' => 24,
                'hoursPercentage' => 89,
                'expectedHours' => 8,
                'minutesEarly' => 5,
                'productivityRate' => 94,
                'overtimeChange' => 2.3,
                'onTime' => 134,
                'lateModerate' => 6,
                'lateExtreme' => 2
            ]
        ]);
    });

    // Recent activities endpoint
    Route::get('/recent-activities', function (Request $request) {
        $limit = $request->get('limit', 10);
        
        return response()->json([
            'success' => true,
            'activities' => [
                [
                    'id' => 1,
                    'employee_name' => 'John Smith',
                    'action' => 'clock_in',
                    'action_display' => 'Clocked In',
                    'time_ago' => '2 minutes ago',
                    'department' => 'IT',
                    'status' => 'on_time',
                    'status_display' => 'On Time'
                ],
                [
                    'id' => 2,
                    'employee_name' => 'Sarah Johnson',
                    'action' => 'clock_out',
                    'action_display' => 'Clocked Out',
                    'time_ago' => '8 minutes ago',
                    'department' => 'Marketing',
                    'status' => 'completed',
                    'duration' => '8.5 hrs'
                ],
                [
                    'id' => 3,
                    'employee_name' => 'Mike Davis',
                    'action' => 'break_start',
                    'action_display' => 'Started Break',
                    'time_ago' => '15 minutes ago',
                    'department' => 'Finance',
                    'status' => 'break',
                    'status_display' => 'On Break'
                ],
                [
                    'id' => 4,
                    'employee_name' => 'Emily Brown',
                    'action' => 'manual_entry',
                    'action_display' => 'Manual Entry',
                    'time_ago' => '22 minutes ago',
                    'department' => 'HR',
                    'status' => 'manual',
                    'status_display' => 'Manual'
                ],
                [
                    'id' => 5,
                    'employee_name' => 'Robert Taylor',
                    'action' => 'break_end',
                    'action_display' => 'Ended Break',
                    'time_ago' => '28 minutes ago',
                    'department' => 'Operations',
                    'status' => 'working',
                    'duration' => '30 min'
                ]
            ]
        ]);
    });

    // Department performance endpoint
    Route::get('/department-performance', function () {
        return response()->json([
            'success' => true,
            'departments' => [
                [
                    'id' => 1,
                    'name' => 'IT Department',
                    'attendance_rate' => 96,
                    'present' => 24,
                    'total' => 25
                ],
                [
                    'id' => 2,
                    'name' => 'Marketing',
                    'attendance_rate' => 92,
                    'present' => 23,
                    'total' => 25
                ],
                [
                    'id' => 3,
                    'name' => 'Finance',
                    'attendance_rate' => 98,
                    'present' => 24,
                    'total' => 25
                ],
                [
                    'id' => 4,
                    'name' => 'Human Resources',
                    'attendance_rate' => 94,
                    'present' => 23,
                    'total' => 25
                ],
                [
                    'id' => 5,
                    'name' => 'Operations',
                    'attendance_rate' => 88,
                    'present' => 22,
                    'total' => 25
                ]
            ]
        ]);
    });

    // Real-time data endpoint
    Route::get('/real-time-data', function (Request $request) {
        $status = $request->get('status', 'all');
        
        $employees = [
            [
                'id' => 1,
                'name' => 'John Smith',
                'position' => 'Software Developer',
                'department' => 'IT',
                'status' => 'present',
                'checkIn' => '08:15',
                'hours' => '7.5',
                'avatar' => 'JS',
                'color' => 'blue'
            ],
            [
                'id' => 2,
                'name' => 'Sarah Johnson',
                'position' => 'Marketing Manager',
                'department' => 'Marketing',
                'status' => 'late',
                'checkIn' => '08:45',
                'hours' => '7.0',
                'avatar' => 'SJ',
                'color' => 'green'
            ],
            [
                'id' => 3,
                'name' => 'Mike Davis',
                'position' => 'Financial Analyst',
                'department' => 'Finance',
                'status' => 'break',
                'checkIn' => '08:00',
                'hours' => '6.0',
                'avatar' => 'MD',
                'color' => 'purple'
            ],
            [
                'id' => 4,
                'name' => 'Emily Brown',
                'position' => 'HR Specialist',
                'department' => 'HR',
                'status' => 'absent',
                'checkIn' => null,
                'hours' => null,
                'avatar' => 'EB',
                'color' => 'red'
            ],
            [
                'id' => 5,
                'name' => 'Robert Taylor',
                'position' => 'Operations Lead',
                'department' => 'Operations',
                'status' => 'present',
                'checkIn' => '07:55',
                'hours' => '8.0',
                'avatar' => 'RT',
                'color' => 'indigo'
            ]
        ];

        // Filter employees by status if specified
        if ($status !== 'all') {
            $employees = array_filter($employees, function($emp) use ($status) {
                return $emp['status'] === $status;
            });
        }

        return response()->json([
            'employees' => array_values($employees),
            'stats' => [
                'total' => 150,
                'present' => 120,
                'late' => 15,
                'absent' => 10,
                'break' => 5
            ]
        ]);
    });

    // Analytics data endpoint
    Route::get('/analytics-data', function (Request $request) {
        $period = $request->get('period', 'daily');
        
        $data = [];
        
        switch ($period) {
            case 'daily':
                $data = [
                    'date' => now()->format('F j, Y'),
                    'totalEmployees' => 150,
                    'present' => 142,
                    'late' => 8,
                    'absent' => 12,
                    'onBreak' => 15,
                    'avgCheckIn' => '08:24',
                    'overtime' => 24,
                    'undertime' => 3
                ];
                break;
            case 'weekly':
                $data = [
                    'weekOf' => 'Week of ' . now()->startOfWeek()->format('M j'),
                    'totalHours' => 5280,
                    'avgDaily' => ['present' => 140, 'late' => 8, 'absent' => 10],
                    'bestDay' => 'Wednesday',
                    'worstDay' => 'Monday',
                    'overtimeHours' => 120,
                    'undertimeHours' => 15
                ];
                break;
            case 'monthly':
                $data = [
                    'month' => now()->format('F Y'),
                    'workingDays' => 22,
                    'totalHours' => 26400,
                    'avgAttendance' => '93.5%',
                    'perfectAttendance' => 45,
                    'lateInstances' => 156,
                    'absentDays' => 89,
                    'overtimeHours' => 480
                ];
                break;
            case 'yearly':
                $data = [
                    'year' => now()->format('Y'),
                    'workingDays' => 260,
                    'totalHours' => 312000,
                    'avgAttendance' => '91.8%',
                    'bestMonth' => 'June',
                    'worstMonth' => 'January',
                    'totalOvertime' => 5760,
                    'holidaysPaid' => 12
                ];
                break;
        }
        
        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    });

    // PDF Export endpoints (returning simple responses for now)
    Route::get('/export-daily-pdf', function (Request $request) {
        // Return a simple PDF response or redirect to a PDF generation service
        return response()->json([
            'error' => 'PDF generation not implemented yet. This would generate a daily attendance report PDF.'
        ], 501);
    });

    Route::get('/export-weekly-pdf', function (Request $request) {
        return response()->json([
            'error' => 'PDF generation not implemented yet. This would generate a weekly attendance report PDF.'
        ], 501);
    });

    Route::get('/export-monthly-pdf', function (Request $request) {
        return response()->json([
            'error' => 'PDF generation not implemented yet. This would generate a monthly attendance report PDF.'
        ], 501);
    });

    Route::get('/export-yearly-pdf', function (Request $request) {
        return response()->json([
            'error' => 'PDF generation not implemented yet. This would generate a yearly attendance report PDF.'
        ], 501);
    });
});
