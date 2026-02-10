<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Daily Attendance Report - {{ $date->format('Y-m-d') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #333;
            padding-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Daily Attendance Report</h1>
        <h2>{{ $date->format('l, F j, Y') }}</h2>
        @if(!empty($filterSummary))
            <p>Filter: {{ $filterSummary }}</p>
        @endif
        <p>Generated on: {{ $generatedAt->format('F j, Y g:i A') }}</p>
    </div>

    <div style="margin: 20px 0;">
        <h3>Summary</h3>
        <p><strong>Total Employees:</strong> {{ $totalEmployees }}</p>
        <p><strong>Present:</strong> {{ $present }}</p>
        <p><strong>Late:</strong> {{ $late }}</p>
        <p><strong>Absent:</strong> {{ $absent }}</p>
        <p><strong>On Break:</strong> {{ $onBreak }}</p>
        <p><strong>Attendance Rate:</strong> {{ $attendanceRate }}%</p>
        <p><strong>Total Hours:</strong> {{ $totalHours }}</p>
        <p><strong>Overtime Hours:</strong> {{ $overtimeHours }}</p>
    </div>

    @if($attendances && $attendances->count() > 0)
    <div>
        <h3>Employee Details</h3>
        <table>
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Department</th>
                    <th>Clock In</th>
                    <th>Clock Out</th>
                    <th>Hours</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($attendances as $attendance)
                <tr>
                    <td>{{ optional($attendance->user)->name ?: 'Unknown' }}</td>
                    <td>{{ optional(optional($attendance->user)->employee)->department ?: 'No Department' }}</td>
                    <td>{{ $attendance->clock_in_time ?: '-' }}</td>
                    <td>{{ $attendance->clock_out_time ?: '-' }}</td>
                    <td>{{ $attendance->hours_worked ? number_format($attendance->hours_worked, 1) : '-' }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $attendance->status ?: 'unknown')) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <p><strong>No attendance records found for this date.</strong></p>
    @endif

    @if($departmentStats && $departmentStats->count() > 0)
    <div style="margin-top: 30px;">
        <h3>Department Performance</h3>
        <table>
            <thead>
                <tr>
                    <th>Department</th>
                    <th>Present</th>
                    <th>Total</th>
                    <th>Rate</th>
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

    <div style="margin-top: 50px; text-align: center; font-size: 10px; color: #666;">
        <p>HR Management System - Daily Attendance Report</p>
        <p>Report generated automatically on {{ $generatedAt->format('F j, Y g:i A') }}</p>
    </div>
</body>
</html>
