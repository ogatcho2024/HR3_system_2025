@extends('dashboard')

@section('title', 'Attendance Reports')

@section('content')
<div class="w-full p-3 sm:px-4">

    <!-- Breadcrumbs -->
    @include('partials.breadcrumbs', ['breadcrumbs' => [
        ['label' => 'Reports', 'url' => route('reports.index')],
        ['label' => 'Attendance Reports', 'url' => route('reports.attendance')]
    ]])

    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-3xl font-bold text-gray-900">Attendance Reports</h3>
                <p class="text-gray-600 mt-2">Timesheets, attendance tracking, and patterns analysis</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('reports.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                    ← Back to Reports
                </a>
                <button onclick="window.print()" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                    <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H3a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H7a2 2 0 00-2 2v4a2 2 0 002 2z"></path>
                    </svg>
                    Print Report
                </button>
                <a href="{{ route('reports.attendance.export', request()->query()) }}" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                    <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Export CSV
                </a>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <!-- Total Attendance Records -->
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm">Total Records</p>
                    <p class="text-2xl font-bold">{{ $stats['total_records'] ?? 0 }}</p>
                    <p class="text-blue-200 text-xs mt-1">All time</p>
                </div>
                <div class="p-2 bg-blue-400 rounded-full">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Present Today -->
        <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm">Present Today</p>
                    <p class="text-2xl font-bold">{{ $stats['present_today'] ?? 0 }}</p>
                    <p class="text-green-200 text-xs mt-1">{{ now()->format('M d, Y') }}</p>
                </div>
                <div class="p-2 bg-green-400 rounded-full">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Late Today -->
        <div class="bg-gradient-to-r from-orange-500 to-orange-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-orange-100 text-sm">Late Today</p>
                    <p class="text-2xl font-bold">{{ $stats['late_today'] ?? 0 }}</p>
                    <p class="text-orange-200 text-xs mt-1">{{ now()->format('M d, Y') }}</p>
                </div>
                <div class="p-2 bg-orange-400 rounded-full">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Absent Today -->
        <div class="bg-gradient-to-r from-red-500 to-red-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-red-100 text-sm">Absent Today</p>
                    <p class="text-2xl font-bold">{{ $stats['absent_today'] ?? 0 }}</p>
                    <p class="text-red-200 text-xs mt-1">{{ now()->format('M d, Y') }}</p>
                </div>
                <div class="p-2 bg-red-400 rounded-full">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Total Hours This Month</p>
                    <p class="text-2xl font-bold text-purple-600">{{ number_format($stats['total_hours_this_month'] ?? 0, 1) }}</p>
                    <p class="text-gray-500 text-xs mt-1">hours worked</p>
                </div>
                <div class="p-3 bg-purple-100 rounded-full">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-indigo-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Average Hours Per Day</p>
                    <p class="text-2xl font-bold text-indigo-600">{{ number_format($stats['avg_hours_per_day'] ?? 0, 1) }}</p>
                    <p class="text-gray-500 text-xs mt-1">this month</p>
                </div>
                <div class="p-3 bg-indigo-100 rounded-full">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-teal-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Attendance Rate</p>
                    <p class="text-2xl font-bold text-teal-600">{{ $stats['attendance_rate_this_month'] ?? 0 }}%</p>
                    <p class="text-gray-500 text-xs mt-1">this month</p>
                </div>
                <div class="p-3 bg-teal-100 rounded-full">
                    <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Productivity Metrics -->
    @if(isset($productivityMetrics))
    <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
        <h4 class="text-lg font-semibold text-gray-900 mb-4">Productivity Metrics</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <div class="text-center">
                <div class="text-2xl font-bold text-blue-600">{{ number_format($productivityMetrics['total_hours']) }}</div>
                <div class="text-sm text-gray-600">Total Hours</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-purple-600">{{ number_format($productivityMetrics['overtime_hours']) }}</div>
                <div class="text-sm text-gray-600">Overtime Hours</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-green-600">{{ $productivityMetrics['avg_clock_in'] }}</div>
                <div class="text-sm text-gray-600">Avg Clock In</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-orange-600">{{ $productivityMetrics['avg_clock_out'] }}</div>
                <div class="text-sm text-gray-600">Avg Clock Out</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-indigo-600">{{ $productivityMetrics['productivity_score'] }}%</div>
                <div class="text-sm text-gray-600">Productivity Score</div>
            </div>
        </div>
    </div>
    @endif

    <!-- Departmental Breakdown -->
    @if(isset($departmentalStats) && count($departmentalStats) > 0)
    <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
        <h4 class="text-lg font-semibold text-gray-900 mb-4">Department Breakdown</h4>
        <div class="overflow-x-auto">
            <table class="min-w-full table-auto">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employees</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Attendance Rate</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Hours</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Avg Hours/Employee</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($departmentalStats as $dept)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $dept['department'] }}</td>
                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">{{ $dept['employees'] }}</td>
                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($dept['attendance_rate'] >= 90) bg-green-100 text-green-800
                                @elseif($dept['attendance_rate'] >= 75) bg-yellow-100 text-yellow-800
                                @else bg-red-100 text-red-800 @endif">
                                {{ $dept['attendance_rate'] }}%
                            </span>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($dept['total_hours'], 1) }}</td>
                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($dept['avg_hours_per_employee'], 1) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
        <h4 class="text-lg font-semibold text-gray-900 mb-4">Filters</h4>
        <form method="GET" action="{{ route('reports.attendance') }}" class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-6 gap-4">
            <div>
                <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
            </div>

            <div>
                <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select name="status" id="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <option value="">All Statuses</option>
                    <option value="present" {{ request('status') == 'present' ? 'selected' : '' }}>Present</option>
                    <option value="late" {{ request('status') == 'late' ? 'selected' : '' }}>Late</option>
                    <option value="absent" {{ request('status') == 'absent' ? 'selected' : '' }}>Absent</option>
                    <option value="on_break" {{ request('status') == 'on_break' ? 'selected' : '' }}>On Break</option>
                </select>
            </div>

            <div>
                <label for="department" class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                <select name="department" id="department" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <option value="">All Departments</option>
                    @foreach($departments as $department)
                        <option value="{{ $department }}" {{ request('department') == $department ? 'selected' : '' }}>
                            {{ $department }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="lg:col-span-2 flex justify-end space-x-3">
                <a href="{{ route('reports.attendance') }}" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
                    Clear Filters
                </a>
                <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                    Apply Filters
                </button>
            </div>
        </form>
    </div>

    <!-- Attendance Records Table -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h4 class="text-lg font-semibold text-gray-900">
                Attendance Records 
                <span class="text-sm font-normal text-gray-600">
                    ({{ isset($attendances) ? $attendances->total() : 0 }} records)
                </span>
            </h4>
        </div>

        @if(isset($attendances) && $attendances->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Employee
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Date
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Hours Worked
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Time In/Out
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Department
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Break Time
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($attendances as $attendance)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            @php
                                                $colors = ['blue', 'green', 'purple', 'red', 'indigo', 'pink', 'yellow', 'teal'];
                                                $colorIndex = abs(crc32($attendance->user->name ?? 'Unknown')) % count($colors);
                                                $color = $colors[$colorIndex];
                                            @endphp
                                            <div class="h-10 w-10 rounded-full bg-{{ $color }}-500 flex items-center justify-center">
                                                <span class="text-sm font-medium text-white">
                                                    {{ strtoupper(substr($attendance->user->name ?? 'U', 0, 2)) }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $attendance->user->name ?? 'Unknown User' }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ $attendance->user->email ?? 'N/A' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $attendance->date ? \Carbon\Carbon::parse($attendance->date)->format('M d, Y') : 'N/A' }}
                                    <div class="text-xs text-gray-500">
                                        {{ $attendance->date ? \Carbon\Carbon::parse($attendance->date)->format('l') : '' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    @if($attendance->hours_worked)
                                        <div class="flex items-center">
                                            <span class="text-lg font-semibold text-purple-600">{{ number_format($attendance->hours_worked, 2) }}</span>
                                            <span class="text-sm text-gray-500 ml-1">hrs</span>
                                        </div>
                                    @else
                                        <span class="text-gray-400">Not calculated</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <div>
                                        @if($attendance->clock_in_time)
                                            <div class="text-sm text-gray-900">
                                                In: {{ \Carbon\Carbon::createFromTimeString($attendance->clock_in_time)->format('h:i A') }}
                                            </div>
                                        @endif
                                        @if($attendance->clock_out_time)
                                            <div class="text-xs text-gray-500">
                                                Out: {{ \Carbon\Carbon::createFromTimeString($attendance->clock_out_time)->format('h:i A') }}
                                            </div>
                                        @endif
                                        @if(!$attendance->clock_in_time && !$attendance->clock_out_time)
                                            <span class="text-gray-400">Not clocked in</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                        @if($attendance->status == 'present') bg-green-100 text-green-800
                                        @elseif($attendance->status == 'late') bg-orange-100 text-orange-800
                                        @elseif($attendance->status == 'absent') bg-red-100 text-red-800
                                        @elseif($attendance->status == 'on_break') bg-blue-100 text-blue-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                        @if($attendance->status == 'on_break')
                                            On Break
                                        @else
                                            {{ ucfirst($attendance->status ?? 'Unknown') }}
                                        @endif
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $attendance->user->employee->department ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    @if($attendance->break_start && $attendance->break_end)
                                        @php
                                            $breakDuration = \Carbon\Carbon::createFromTimeString($attendance->break_start)
                                                ->diffInMinutes(\Carbon\Carbon::createFromTimeString($attendance->break_end));
                                        @endphp
                                        <div class="text-sm text-gray-900">
                                            {{ floor($breakDuration / 60) }}h {{ $breakDuration % 60 }}m
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ \Carbon\Carbon::createFromTimeString($attendance->break_start)->format('h:i A') }} - 
                                            {{ \Carbon\Carbon::createFromTimeString($attendance->break_end)->format('h:i A') }}
                                        </div>
                                    @elseif($attendance->break_start)
                                        <div class="text-sm text-orange-600">In progress</div>
                                        <div class="text-xs text-gray-500">
                                            Started: {{ \Carbon\Carbon::createFromTimeString($attendance->break_start)->format('h:i A') }}
                                        </div>
                                    @else
                                        <span class="text-gray-400">No break</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if(isset($attendances) && $attendances->hasPages())
                <div class="px-6 py-3 border-t border-gray-200 bg-gray-50">
                    {{ $attendances->withQueryString()->links() }}
                </div>
            @endif
        @else
            <div class="px-6 py-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900">No attendance records found</h3>
                <p class="mt-2 text-sm text-gray-500">
                    No attendance records match the current filter criteria.
                </p>
            </div>
        @endif
    </div>

    <!-- Summary Stats -->
    @if(isset($attendances) && $attendances->count() > 0)
        <div class="mt-6 bg-white rounded-lg shadow-lg p-6">
            <h4 class="text-lg font-semibold text-gray-900 mb-4">Summary Statistics</h4>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="text-center">
                    <div class="text-2xl font-bold text-purple-600">
                        {{ number_format($attendances->sum('hours_worked'), 1) }}
                    </div>
                    <div class="text-sm text-gray-600">Total Hours</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-blue-600">
                        {{ number_format($attendances->where('hours_worked', '>', 0)->avg('hours_worked'), 1) }}
                    </div>
                    <div class="text-sm text-gray-600">Average Hours/Day</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600">
                        {{ $attendances->where('hours_worked', '>=', 8)->count() }}
                    </div>
                    <div class="text-sm text-gray-600">Full Days (8+ hrs)</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-orange-600">
                        {{ $attendances->whereIn('status', ['present', 'late'])->count() }}
                    </div>
                    <div class="text-sm text-gray-600">Productive Days</div>
                </div>
            </div>
        </div>
    @endif

    <!-- Attendance Trends Chart -->
    @if(isset($attendanceTrends) && count($attendanceTrends) > 0)
        <div class="mt-6 bg-white rounded-lg shadow-lg p-6">
            <h4 class="text-lg font-semibold text-gray-900 mb-4">7-Day Attendance Trend</h4>
            <div class="grid grid-cols-7 gap-2">
                @foreach($attendanceTrends as $trend)
                    <div class="text-center">
                        <div class="text-xs font-medium text-gray-600 mb-2">{{ $trend['day_name'] }}</div>
                        <div class="text-xs text-gray-500 mb-2">{{ $trend['formatted_date'] }}</div>
                        <div class="bg-gray-100 rounded-lg p-3">
                            <div class="text-lg font-bold text-{{ $trend['attendance_rate'] >= 90 ? 'green' : ($trend['attendance_rate'] >= 80 ? 'yellow' : 'red') }}-600">
                                {{ $trend['attendance_rate'] }}%
                            </div>
                            <div class="text-xs text-gray-500 mt-1">
                                P: {{ $trend['present'] }} | L: {{ $trend['late'] }} | A: {{ $trend['absent'] }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
