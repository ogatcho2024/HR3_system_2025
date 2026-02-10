<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'Timesheet Report' }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #111; }
        .header { margin-bottom: 16px; }
        .title { font-size: 18px; font-weight: bold; margin-bottom: 4px; }
        .meta { font-size: 12px; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f3f4f6; }
        .right { text-align: right; }
        .center { text-align: center; }
        .footer { margin-top: 16px; font-size: 11px; color: #666; text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">{{ $title ?? 'Timesheet Report' }}</div>
        <div class="meta">
            Date Range: {{ $date_range ?? '-' }}<br>
            Report Type: {{ $report_type ?? '-' }}<br>
            Generated: {{ $generated_at ?? '-' }}<br>
            System: {{ $system_name ?? 'HR System' }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 12%;">Date</th>
                <th style="width: 18%;">Employee</th>
                <th style="width: 22%;">Project</th>
                <th style="width: 26%;">Task</th>
                <th style="width: 10%;" class="center">Hours</th>
                <th style="width: 12%;" class="center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    <td>{{ $row['date'] ?? '' }}</td>
                    <td>{{ $row['employee'] ?? '—' }}</td>
                    <td>{{ $row['project'] ?? '' }}</td>
                    <td>{{ $row['task'] ?? '' }}</td>
                    <td class="center">{{ $row['hours'] ?? '0.00' }}</td>
                    <td class="center">{{ ucfirst($row['status'] ?? 'draft') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="center">No data available for the selected range.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        {{ $system_name ?? 'HR System' }} &mdash; Generated {{ $generated_at ?? '' }}
    </div>
</body>
</html>
