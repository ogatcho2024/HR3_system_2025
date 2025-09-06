<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Daily Attendance Report - {{ $date->format('Y-m-d') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #4F46E5;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .header h1 {
            color: #4F46E5;
            margin: 0;
            font-size: 24px;
        }
        
        .header h2 {
            color: #6B7280;
            margin: 5px 0;
            font-size: 16px;
            font-weight: normal;
        }
        
        .stats-grid {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }
        
        .stats-row {
            display: table-row;
        }
        
        .stat-item {
            display: table-cell;
            width: 25%;
            text-align: center;
            padding: 15px;
            border: 1px solid #E5E7EB;
            background-color: #F9FAFB;
        }
        
        .stat-value {
            font-size: 18px;
            font-weight: bold;
            color: #4F46E5;
            display: block;
        }
        
        .stat-label {
            font-size: 11px;
            color: #6B7280;
            text-transform: uppercase;
            margin-top: 5px;
        }
        
        .section {
            margin-bottom: 25px;
        }
        
        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #374151;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 1px solid #D1D5DB;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        th, td {
            padding: 8px 12px;
            text-align: left;
            border-bottom: 1px solid #E5E7EB;
        }
        
        th {
            background-color: #F3F4F6;
            font-weight: bold;
            color: #374151;
            font-size: 11px;
            text-transform: uppercase;
        }
        
        td {
            font-size: 12px;
        }
        
        .status-present { color: #059669; font-weight: bold; }
        .status-late { color: #DC2626; font-weight: bold; }
        .status-absent { color: #6B7280; font-weight: bold; }
        .status-on_break { color: #D97706; font-weight: bold; }
        
        .department-stats {
            display: table;
            width: 100%;
        }
        
        .department-row {
            display: table-row;
        }
        
        .department-cell {
            display: table-cell;
            width: 50%;
            padding: 10px;
            vertical-align: top;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #6B7280;
            border-top: 1px solid #E5E7EB;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Daily Attendance Report</h1>
        <h2>{{ $date->format('l, F j, Y') }}</h2>
        <p style="margin: 10px 0; color: #6B7280;">Generated on {{ $generatedAt->format('F j, Y \a\t g:i A') }}</p>
    </div>

    <div class="stats-grid">
        <div class="stats-row">
            <div class="stat-item">
                <span class="stat-value">{{ $totalEmployees }}</span>
                <div class="stat-label">Total Employees</div>
            </div>
            <div class="stat-item">
                <span class="stat-value">{{ $present }}</span>
                <div class="stat-label">Present</div>
            </div>
            <div class="stat-item">
                <span class="stat-value">{{ $late }}</span>
                <div class="stat-label">Late</div>
            </div>
            <div class="stat-item">
                <span class="stat-value">{{ $absent }}</span>
                <div class="stat-label">Absent</div>
            </div>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stats-row">
            <div class="stat-item">
                <span class="stat-value">{{ $attendanceRate }}%</span>
                <div class="stat-label">Attendance Rate</div>
            </div>
            <div class="stat-item">
                <span class="stat-value">{{ $totalHours }}</span>
                <div class="stat-label">Total Hours</div>
            </div>
            <div class="stat-item">
                <span class="stat-value">{{ $overtimeHours }}</span>
                <div class="stat-label">Overtime Hours</div>
            </div>
            <div class="stat-item">
                <span class="stat-value">{{ $onBreak }}</span>
                <div class="stat-label">On Break</div>
            </div>
        </div>
    </div>

    @if($attendances->count() > 0)
    <div class="section">
        <div class="section-title">Employee Attendance Details</div>
        <table>
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Department</th>
                    <th>Clock In</th>
                    <th>Clock Out</th>
                    <th>Break</th>
                    <th>Hours</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($attendances as $attendance)
                <tr>
                    <td>{{ $attendance->user->name }}</td>
                    <td>{{ $attendance->user->employee->department ?? 'No Department' }}</td>
                    <td>{{ $attendance->clock_in_time ?? '-' }}</td>
                    <td>{{ $attendance->clock_out_time ?? '-' }}</td>
                    <td>
                        @if($attendance->break_start && $attendance->break_end)
                            {{ $attendance->break_start }} - {{ $attendance->break_end }}
                        @elseif($attendance->break_start)
                            {{ $attendance->break_start }} - In Progress
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $attendance->hours_worked ? number_format($attendance->hours_worked, 1) : '-' }}</td>
                    <td class="status-{{ $attendance->status }}">
                        {{ ucfirst(str_replace('_', ' ', $attendance->status)) }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if($departmentStats->count() > 0)
    <div class="section">
        <div class="section-title">Department Performance</div>
        <table>
            <thead>
                <tr>
                    <th>Department</th>
                    <th>Present</th>
                    <th>Total Employees</th>
                    <th>Attendance Rate</th>
                </tr>
            </thead>
            <tbody>
                @foreach($departmentStats as $dept)
                <tr>
                    <td>{{ $dept['name'] }}</td>
                    <td>{{ $dept['present'] }}</td>
                    <td>{{ $dept['total'] }}</td>
                    <td>{{ $dept['rate'] }}%</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="footer">
        <p>HR Management System - Daily Attendance Report</p>
        <p>This report was automatically generated on {{ $generatedAt->format('F j, Y \a\t g:i A') }}</p>
    </div>
</body>
</html>
