<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; color: #111; }
        .header { margin-bottom: 12px; }
        .title { font-size: 16px; font-weight: bold; }
        .filters { margin-top: 4px; color: #555; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; vertical-align: top; }
        th { background: #f3f4f6; font-weight: bold; }
        .footer { margin-top: 10px; font-size: 10px; color: #555; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">{{ $title }}</div>
        <div class="filters">
            Date Range: {{ $filters['start_date'] }} to {{ $filters['end_date'] }} (Last {{ $filters['days'] }} days)
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Date/Time</th>
                <th>User</th>
                <th>Action</th>
                <th>Description</th>
                <th>IP Address</th>
                <th>Affected Table</th>
                <th>Affected ID</th>
            </tr>
        </thead>
        <tbody>
            @foreach($logs as $log)
            <tr>
                <td>{{ $log->id }}</td>
                <td>{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                <td>{{ $log->user_name }}</td>
                <td>{{ $log->action_label }}</td>
                <td>{{ $log->description }}</td>
                <td>{{ $log->ip_address }}</td>
                <td>{{ $log->affected_table ?? 'N/A' }}</td>
                <td>{{ $log->affected_record_id ?? 'N/A' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Generated: {{ $generated_at->format('Y-m-d H:i:s') }} | System: {{ $system_name }}
    </div>
</body>
</html>
