<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Leave Reports & Analytics - {{ $dateRange ?? 'All Time' }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
            color: #333;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .header p {
            font-size: 14px;
            color: #666;
        }
        .stats-section {
            margin: 20px 0;
        }
        .stats-table {
            width: 100%;
            margin-bottom: 20px;
        }
        .stats-table td {
            width: 25%;
            text-align: center;
            padding: 15px;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
        }
        .stats-number {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .stats-label {
            font-size: 11px;
            color: #666;
        }
        .blue { color: #2563eb; }
        .yellow { color: #d97706; }
        .green { color: #059669; }
        .red { color: #dc2626; }
        .section {
            margin-bottom: 30px;
        }
        .section h2 {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 15px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
            font-size: 11px;
        }
        td {
            font-size: 11px;
        }
        .status-approved { background-color: #d1fae5; color: #065f46; padding: 2px 6px; }
        .status-pending { background-color: #fef3c7; color: #92400e; padding: 2px 6px; }
        .status-rejected { background-color: #fee2e2; color: #991b1b; padding: 2px 6px; }
        .summary-table {
            width: 100%;
            margin-top: 20px;
        }
        .summary-table td {
            width: 33.33%;
            text-align: center;
            padding: 15px;
            border: 1px solid #ddd;
        }
        .summary-number {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .summary-label {
            font-size: 11px;
            color: #666;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .no-data {
            text-align: center;
            padding: 40px 20px;
            color: #666;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>Leave Reports & Analytics</h1>
        <p>Generated on {{ now()->format('F j, Y \a\t g:i A') }}</p>
        @if(isset($dateRange))
        <p>Report Period: {{ $dateRange }}</p>
        @endif
    </div>

    <!-- Statistics Overview -->
    <div class="section">
        <h2>Overview Statistics</h2>
        <table class="stats-table">
            <tr>
                <td>
                    <div class="stats-number blue">{{ $totalRequests }}</div>
                    <div class="stats-label">Total Requests</div>
                </td>
                <td>
                    <div class="stats-number yellow">{{ $pendingRequests }}</div>
                    <div class="stats-label">Pending Requests</div>
                </td>
                <td>
                    <div class="stats-number green">{{ $approvedRequests }}</div>
                    <div class="stats-label">Approved Requests</div>
                </td>
                <td>
                    <div class="stats-number red">{{ $rejectedRequests }}</div>
                    <div class="stats-label">Rejected Requests</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Recent Leave Requests -->
    <div class="section">
        <h2>Leave Requests Details</h2>
        @if($recentRequests->isNotEmpty())
        <table>
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Leave Type</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Days</th>
                    <th>Status</th>
                    <th>Applied Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentRequests as $request)
                <tr>
                    <td>
                        <strong>{{ $request->user->name ?? 'N/A' }} {{ $request->user->lastname ?? '' }}</strong><br>
                        <small style="color: #6b7280;">{{ $request->user->email ?? 'N/A' }}</small>
                    </td>
                    <td>{{ $request->leave_type ?? 'N/A' }}</td>
                    <td>
                        @if($request->start_date)
                            {{ \Carbon\Carbon::parse($request->start_date)->format('M d, Y') }}
                        @else
                            N/A
                        @endif
                    </td>
                    <td>
                        @if($request->end_date)
                            {{ \Carbon\Carbon::parse($request->end_date)->format('M d, Y') }}
                        @else
                            N/A
                        @endif
                    </td>
                    <td>{{ $request->days_requested ?? 0 }}</td>
                    <td>
                        <span class="status-badge 
                            @if($request->status == 'approved') status-approved
                            @elseif($request->status == 'pending') status-pending
                            @elseif($request->status == 'rejected') status-rejected
                            @endif">
                            {{ ucfirst($request->status ?? 'N/A') }}
                        </span>
                    </td>
                    <td>
                        @if($request->created_at)
                            {{ $request->created_at->format('M d, Y') }}
                        @else
                            N/A
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="no-data">
            <p><strong>No leave requests found</strong></p>
            <p>No leave requests match the current criteria or date range.</p>
        </div>
        @endif
    </div>

    <!-- Analytics Summary -->
    <div class="section">
        <h2>Analytics Summary</h2>
        <table class="summary-table">
            <tr>
                <td>
                    <div class="summary-number" style="color: #7c3aed;">{{ number_format($totalDays) }}</div>
                    <div class="summary-label">Total Leave Days</div>
                </td>
                <td>
                    <div class="summary-number" style="color: #059669;">{{ $approvalRate }}%</div>
                    <div class="summary-label">Approval Rate</div>
                </td>
                <td>
                    <div class="summary-number" style="color: #d97706;">{{ $avgDays }}</div>
                    <div class="summary-label">Avg Days per Request</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Additional Insights -->
    @if(isset($departmentBreakdown) && $departmentBreakdown->isNotEmpty())
    <div class="section">
        <h2>Department Breakdown</h2>
        <table>
            <thead>
                <tr>
                    <th>Department</th>
                    <th>Total Requests</th>
                    <th>Approved</th>
                    <th>Pending</th>
                    <th>Rejected</th>
                    <th>Total Days</th>
                </tr>
            </thead>
            <tbody>
                @foreach($departmentBreakdown as $dept)
                <tr>
                    <td><strong>{{ $dept->department ?? 'Not Assigned' }}</strong></td>
                    <td>{{ $dept->total_requests ?? 0 }}</td>
                    <td class="green">{{ $dept->approved ?? 0 }}</td>
                    <td class="yellow">{{ $dept->pending ?? 0 }}</td>
                    <td class="red">{{ $dept->rejected ?? 0 }}</td>
                    <td>{{ $dept->total_days ?? 0 }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p>© {{ date('Y') }} Human Resources Management System | Generated on {{ now()->format('Y-m-d H:i:s') }}</p>
    </div>
</body>
</html>