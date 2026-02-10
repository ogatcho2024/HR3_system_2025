<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Yearly Attendance Report - {{ $year }}</title>
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
            width: 16.66%;
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
            page-break-inside: avoid;
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
        
        .highlight-good {
            background-color: #D1FAE5;
            padding: 2px 4px;
            border-radius: 4px;
            color: #065F46;
        }
        
        .highlight-bad {
            background-color: #FEE2E2;
            padding: 2px 4px;
            border-radius: 4px;
            color: #991B1B;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Yearly Attendance Report</h1>
        <h2>{{ $year }}</h2>
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
                <span class="stat-value">{{ $totalOvertime }}</span>
                <div class="stat-label">Overtime Hours</div>
            </div>
            <div class="stat-item">
                <span class="stat-value">{{ $avgAttendanceRate }}%</span>
                <div class="stat-label">Avg Attendance</div>
            </div>
            <div class="stat-item">
                <span class="stat-value">18</span>
                <div class="stat-label">Holiday Days</div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Year Highlights</div>
        <div style="background-color: #F9FAFB; padding: 15px; border: 1px solid #E5E7EB;">
            @if($bestMonth)
            <p><strong>Best Month:</strong> 
                <span class="highlight-good">{{ $bestMonth['month'] }} ({{ $bestMonth['rate'] }}% attendance)</span>
            </p>
            @endif
            @if($worstMonth)
            <p><strong>Needs Improvement:</strong> 
                <span class="highlight-bad">{{ $worstMonth['month'] }} ({{ $worstMonth['rate'] }}% attendance)</span>
            </p>
            @endif
            <p><strong>Overall Performance:</strong> {{ $avgAttendanceRate }}% average attendance rate</p>
        </div>
    </div>

    <div class="two-column">
        <div class="column">
            @if(count($monthlyStats) > 0)
            <div class="section">
                <div class="section-title">Monthly Breakdown</div>
                <table>
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th>Attendance Rate</th>
                            <th>Hours</th>
                            <th>Overtime</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($monthlyStats as $month)
                        <tr>
                            <td>{{ $month['month'] }}</td>
                            <td>{{ $month['rate'] }}%</td>
                            <td>{{ $month['hours'] }}</td>
                            <td>{{ $month['overtime'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
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
                            <th>Avg Rate</th>
                            <th>Total Hours</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($departmentStats->take(10) as $dept)
                        <tr>
                            <td>{{ $dept['name'] }}</td>
                            <td>{{ $dept['employees'] }}</td>
                            <td>{{ $dept['rate'] }}%</td>
                            <td>{{ $dept['totalHours'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>

    <div class="section">
        <div class="section-title">Annual Summary</div>
        <div style="background-color: #F9FAFB; padding: 15px; border: 1px solid #E5E7EB;">
            <p><strong>Total Working Days:</strong> {{ $workingDays }} days (excluding weekends)</p>
            <p><strong>Average Annual Attendance:</strong> {{ $avgAttendanceRate }}%</p>
            <p><strong>Total Overtime:</strong> {{ $totalOvertime }} hours</p>
            <p><strong>Total Employee Hours:</strong> {{ $totalHours }} hours</p>
        </div>
    </div>

    <div class="footer">
        <p>HR Management System - Yearly Attendance Report</p>
        <p>This report was automatically generated on {{ $generatedAt->format('F j, Y \a\t g:i A') }}</p>
    </div>
</body>
</html>
