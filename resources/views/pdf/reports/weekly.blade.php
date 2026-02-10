<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Weekly Attendance Report - {{ $startDate->format('M j') }} to {{ $endDate->format('M j, Y') }}</title>
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
            width: 20%;
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
        
        .two-column {
            display: table;
            width: 100%;
        }
        
        .column {
            display: table-cell;
            width: 50%;
            padding: 0 10px;
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
        <h1>Weekly Attendance Report</h1>
        <h2>{{ $startDate->format('F j') }} - {{ $endDate->format('F j, Y') }}</h2>
        @if(!empty($filterSummary))
            <p style="margin: 6px 0; color: #6B7280;">Filter: {{ $filterSummary }}</p>
        @endif
        <p style="margin: 10px 0; color: #6B7280;">Generated on {{ $generatedAt->format('F j, Y \a\t g:i A') }}</p>
    </div>

    <div class="stats-grid">
        <div class="stats-row">
            <div class="stat-item">
                <span class="stat-value">{{ $totalEmployees }}</span>
                <div class="stat-label">Total Employees</div>
            </div>
            <div class="stat-item">
                <span class="stat-value">{{ $workingDays }}</span>
                <div class="stat-label">Working Days</div>
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
                <span class="stat-value">{{ $avgAttendanceRate }}%</span>
                <div class="stat-label">Avg Attendance</div>
            </div>
        </div>
    </div>

    <div class="two-column">
        <div class="column">
            <div class="section">
                <div class="section-title">Daily Breakdown</div>
                <table>
                    <thead>
                        <tr>
                            <th>Day</th>
                            <th>Present</th>
                            <th>Absent</th>
                            <th>Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($dailyStats as $day)
                        <tr>
                            <td>{{ $day['date'] }}</td>
                            <td>{{ $day['present'] }}</td>
                            <td>{{ $day['absent'] }}</td>
                            <td>{{ $day['rate'] }}%</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="column">
            @if($departmentStats->count() > 0)
            <div class="section">
                <div class="section-title">Department Performance</div>
                <table>
                    <thead>
                        <tr>
                            <th>Department</th>
                            <th>Employees</th>
                            <th>Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($departmentStats as $dept)
                        <tr>
                            <td>{{ $dept['name'] }}</td>
                            <td>{{ $dept['employees'] }}</td>
                            <td>{{ $dept['rate'] }}%</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>

    <div class="footer">
        <p>HR Management System - Weekly Attendance Report</p>
        <p>This report was automatically generated on {{ $generatedAt->format('F j, Y \a\t g:i A') }}</p>
    </div>
</body>
</html>
