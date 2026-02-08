@extends('dashboard-user')

@section('title', 'Employee Dashboard')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Welcome Header -->
    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center space-y-4 md:space-y-0">
                <div class="flex items-center space-x-4">
                    @if($user->photo)
                        <img class="h-16 w-16 rounded-full border-4 border-white shadow-md" src="{{ asset('storage/' . $user->photo) }}" alt="{{ $user->name }}">
                    @else
                        <div class="h-16 w-16 rounded-full bg-white flex items-center justify-center border-4 border-white shadow-md">
                            <span class="text-2xl font-bold text-blue-600">{{ substr($user->name, 0, 1) }}</span>
                        </div>
                    @endif
                    <div class="text-black font-bold">
                        <h1 class="text-2xl md:text-3xl font-bold">Welcome back, {{ $user->name }}!</h1>
                        <div class="flex flex-col md:flex-row md:items-center md:space-x-4 text-gray-600 mt-1">
                            <p class="text-sm">{{ $employee->position ?? 'Employee' }} • {{ $employee->department ?? 'N/A' }}</p>
                            @if($employee && $employee->employee_id)
                            <p class="text-sm">ID: {{ $employee->employee_id }}</p>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="text-gray-500 text-right">
                    <p class="text-sm text-gray-500">{{ $stats['current_month'] }}</p>
                    <p class="text-lg font-semibold">{{ $today->format('l, F j, Y') }}</p>
                    <p class="text-sm text-gray-500">{{ $now->format('g:i A') }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Today's Attendance Card -->
        <div class="mb-8">
            <div id="attendanceCard" class="bg-white rounded-lg shadow-md overflow-hidden border-l-4 
                @if($todayLeave) border-orange-500
                @elseif($todayAttendance && $todayAttendance->status == 'present') border-green-500
                @elseif($todayAttendance && $todayAttendance->status == 'late') border-orange-500
                @elseif($todayAttendance && $todayAttendance->status == 'on_break') border-blue-500
                @elseif($todayAttendance && $todayAttendance->status == 'absent') border-red-500
                @else border-gray-400
                @endif">
                <div class="px-6 py-5">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-bold text-gray-900">Today's Attendance</h2>
                        <span id="attendanceBadge" class="px-3 py-1 rounded-full text-sm font-semibold
                            @if($todayLeave) bg-orange-100 text-orange-800
                            @elseif($todayAttendance && $todayAttendance->status == 'present') bg-green-100 text-green-800
                            @elseif($todayAttendance && $todayAttendance->status == 'late') bg-orange-100 text-orange-800
                            @elseif($todayAttendance && $todayAttendance->status == 'on_break') bg-blue-100 text-blue-800
                            @elseif($todayAttendance && $todayAttendance->status == 'absent') bg-red-100 text-red-800
                            @else bg-gray-100 text-gray-800
                            @endif">
                            @if($todayLeave)
                                On Leave
                            @elseif($todayAttendance)
                                {{ ucfirst($todayAttendance->status) }}
                            @else
                                Not Yet Timed In
                            @endif
                        </span>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div class="text-center">
                            <p class="text-sm text-gray-500 mb-1">Time In</p>
                            <p id="attendanceTimeIn" class="text-2xl font-bold text-gray-900">
                                @if($todayAttendance && $todayAttendance->clock_in_time)
                                    {{ \Carbon\Carbon::parse($todayAttendance->clock_in_time)->format('g:i A') }}
                                @else
                                    --:--
                                @endif
                            </p>
                        </div>
                        <div class="text-center">
                            <p class="text-sm text-gray-500 mb-1">Time Out</p>
                            <p id="attendanceTimeOut" class="text-2xl font-bold text-gray-900">
                                @if($todayAttendance && $todayAttendance->clock_out_time)
                                    {{ \Carbon\Carbon::parse($todayAttendance->clock_out_time)->format('g:i A') }}
                                @else
                                    --:--
                                @endif
                            </p>
                        </div>
                        <div class="text-center">
                            <p class="text-sm text-gray-500 mb-1">Hours Worked</p>
                            <p id="attendanceHours" class="text-2xl font-bold text-gray-900">
                                @if($todayAttendance && $todayAttendance->clock_in_time && $todayAttendance->clock_out_time)
                                    {{ number_format($todayAttendance->hours_worked ?? 0, 1) }}h
                                @else
                                    0h
                                @endif
                            </p>
                        </div>
                        <div class="text-center">
                            <p class="text-sm text-gray-500 mb-1">Status</p>
                            <p id="attendanceStatus" class="text-lg font-semibold text-gray-900">
                                @if($todayLeave)
                                    {{ ucfirst(str_replace('_', ' ', $todayLeave->leave_type)) }}
                                @elseif($todayAttendance)
                                    @if($todayAttendance->status === 'late' || $todayAttendance->is_late)
                                        <span class="text-orange-600">Late</span>
                                    @elseif($todayAttendance->status === 'on_break')
                                        <span class="text-blue-600">On Break</span>
                                    @else
                                        <span class="text-green-600">On Time</span>
                                    @endif
                                @else
                                    Pending
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Leave Balance by Type -->
            <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
                <h3 class="text-sm font-medium text-gray-500 mb-3">Leave Balance</h3>
                <div class="space-y-2">
                    @foreach($stats['leave_balance_detailed'] as $type => $balance)
                    <div class="flex justify-between items-center">
                        <span class="text-xs text-gray-600">{{ ucfirst(str_replace('_', ' ', $type)) }}</span>
                        <span class="text-sm font-semibold 
                            @if($balance['remaining'] <= 2) text-red-600
                            @elseif($balance['remaining'] <= 5) text-orange-600
                            @else text-green-600
                            @endif">
                            {{ $balance['remaining'] }}/{{ $balance['total'] }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Pending Leave Requests -->
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow border-l-4 border-blue-500">
                <h3 class="text-sm font-medium text-gray-600 mb-2">Pending Leave Requests</h3>
                <p class="text-4xl font-bold text-blue-600">{{ $stats['pending_leave_requests'] }}</p>
                <a href="{{ route('employee.leave-requests') }}" class="text-sm text-blue-700 hover:text-blue-900 font-medium mt-2 inline-block">View all →</a>
            </div>

            <!-- Hours This Week -->
            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow border-l-4 border-green-500">
                <h3 class="text-sm font-medium text-gray-600 mb-2">Hours This Week</h3>
                <p class="text-4xl font-bold text-green-600">{{ $stats['hours_this_week'] }}h</p>
                <p class="text-sm text-green-700 mt-2">{{ \Carbon\Carbon::now()->startOfWeek()->format('M j') }} - {{ \Carbon\Carbon::now()->endOfWeek()->format('M j') }}</p>
            </div>

            <!-- Pending Shift Requests -->
            <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow border-l-4 border-purple-500">
                <h3 class="text-sm font-medium text-gray-600 mb-2">Pending Shift Requests</h3>
                <p class="text-4xl font-bold text-purple-600">{{ $stats['pending_shift_requests'] }}</p>
                <a href="{{ route('employee.shift-requests') }}" class="text-sm text-purple-700 hover:text-purple-900 font-medium mt-2 inline-block">View all →</a>
            </div>
        </div>

        <!-- Today's Shift/Schedule -->
        <div class="mb-8">
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-900">Today's Schedule</h2>
                </div>
                <div class="px-6 py-5">
                    @if($todayLeave)
                        <div class="text-center py-8">
                            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-orange-100 mb-4">
                                <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">On Leave Today</h3>
                            <p class="text-gray-600 mt-2">{{ ucfirst(str_replace('_', ' ', $todayLeave->leave_type)) }}</p>
                            <p class="text-sm text-gray-500 mt-1">{{ $todayLeave->start_date->format('M j') }} - {{ $todayLeave->end_date->format('M j, Y') }}</p>
                        </div>
                    @elseif($todayShift)
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="text-center">
                                <p class="text-sm text-gray-500 mb-1">Shift</p>
                                <p class="text-lg font-bold text-gray-900">{{ $todayShift->shiftTemplate->name ?? 'Regular Shift' }}</p>
                            </div>
                            <div class="text-center">
                                <p class="text-sm text-gray-500 mb-1">Time</p>
                                <p class="text-lg font-bold text-gray-900">
                                    {{ $todayShift->shiftTemplate->start_time ?? '9:00 AM' }} - {{ $todayShift->shiftTemplate->end_time ?? '5:00 PM' }}
                                </p>
                            </div>
                            <div class="text-center">
                                <p class="text-sm text-gray-500 mb-1">Status</p>
                                <p class="text-lg font-bold text-gray-900">{{ ucfirst($todayShift->status ?? 'Scheduled') }}</p>
                            </div>
                        </div>
                    @elseif($today->isWeekend())
                        <div class="text-center py-8">
                            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-blue-100 mb-4">
                                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">Rest Day</h3>
                            <p class="text-gray-600 mt-2">Enjoy your {{ $today->format('l') }}!</p>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <p class="text-gray-600">Regular working hours: 9:00 AM - 5:00 PM</p>
                            <a href="{{ route('employee.work-schedule') }}" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium mt-2 inline-block">View full schedule →</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Recent Activity -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="px-6 py-4 bg-gray-200 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-900">Recent Activity</h2>
                </div>
                <div class="px-6 py-4">
                    <!-- Recent Attendance -->
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">Attendance Records</h3>
                    <div class="space-y-3 mb-6">
                        @forelse($recentAttendanceRecords->take(3) as $record)
                        <div class="flex items-center justify-between py-2 border-b border-gray-100">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $record->date->format('M j, Y') }}</p>
                                <p class="text-xs text-gray-500">
                                    @if($record->clock_in)
                                        {{ \Carbon\Carbon::parse($record->clock_in)->format('g:i A') }} - 
                                        {{ $record->clock_out ? \Carbon\Carbon::parse($record->clock_out)->format('g:i A') : 'Ongoing' }}
                                    @endif
                                </p>
                            </div>
                            <span class="px-2 py-1 rounded-full text-xs font-semibold
                                @if($record->status == 'present') bg-green-100 text-green-800
                                @elseif($record->status == 'absent') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ ucfirst($record->status) }}
                            </span>
                        </div>
                        @empty
                        <p class="text-sm text-gray-500 py-4 text-center">No attendance records yet</p>
                        @endforelse
                    </div>

                    <!-- Recent Leave Requests -->
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">Leave Requests</h3>
                    <div class="space-y-3 mb-6">
                        @forelse($recentLeaveRequests as $request)
                        <div class="flex items-center justify-between py-2 border-b border-gray-100">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ ucfirst(str_replace('_', ' ', $request->leave_type)) }}</p>
                                <p class="text-xs text-gray-500">{{ $request->start_date->format('M j') }} - {{ $request->end_date->format('M j, Y') }}</p>
                            </div>
                            <span class="px-2 py-1 rounded-full text-xs font-semibold
                                @if($request->status == 'approved') bg-green-100 text-green-800
                                @elseif($request->status == 'rejected') bg-red-100 text-red-800
                                @else bg-yellow-100 text-yellow-800
                                @endif">
                                {{ ucfirst($request->status) }}
                            </span>
                        </div>
                        @empty
                        <p class="text-sm text-gray-500 py-4 text-center">No leave requests yet</p>
                        @endforelse
                    </div>

                    <!-- Recent Shift Requests -->
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">Shift Requests</h3>
                    <div class="space-y-3">
                        @forelse($recentShiftRequests as $request)
                        <div class="flex items-center justify-between py-2 border-b border-gray-100">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ ucfirst(str_replace('_', ' ', $request->request_type)) }}</p>
                                <p class="text-xs text-gray-500">{{ $request->request_date->format('M j, Y') }}</p>
                            </div>
                            <span class="px-2 py-1 rounded-full text-xs font-semibold
                                @if($request->status == 'approved') bg-green-100 text-green-800
                                @elseif($request->status == 'rejected') bg-red-100 text-red-800
                                @else bg-yellow-100 text-yellow-800
                                @endif">
                                {{ ucfirst($request->status) }}
                            </span>
                        </div>
                        @empty
                        <p class="text-sm text-gray-500 py-4 text-center">No shift requests yet</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Announcements -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="px-6 py-4 bg-gray-200 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-900">Announcements</h2>
                </div>
                <div class="px-6 py-4">
                    <div class="space-y-4">
                        @forelse($announcements as $announcement)
                        <div class="border-l-4 pl-4 py-3
                            @if($announcement->priority == 'urgent') border-blue-500 bg-blue-50
                            @elseif($announcement->priority == 'high') border-blue-500 bg-blue-50
                            @else border-blue-500 bg-blue-50
                            @endif">
                            <div class="flex items-start justify-between">
                                <h3 class="text-sm font-semibold text-gray-900">{{ $announcement->title }}</h3>
                                @if($announcement->priority == 'urgent')
                                <span class="px-2 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800 ml-2">URGENT</span>
                                @endif
                            </div>
                            <p class="text-sm text-gray-700 mt-2">{{ Str::limit($announcement->message, 150) }}</p>
                            <p class="text-xs text-gray-500 mt-2">{{ $announcement->created_at->diffForHumans() }}</p>
                        </div>
                        @empty
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                            </svg>
                            <p class="text-sm text-gray-500 mt-2">No announcements at this time</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-900">Quick Actions</h2>
            </div>
            <div class="px-6 py-6">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <a href="{{ route('employee.leave-requests') }}" class="flex flex-col items-center justify-center p-6 border-2 border-gray-200 rounded-lg hover:border-indigo-500 hover:shadow-md transition-all group">
                        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mb-3 group-hover:bg-green-200 transition-colors">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-gray-900">File Leave</span>
                    </a>

                    <a href="{{ route('employee.attendance') }}" class="flex flex-col items-center justify-center p-6 border-2 border-gray-200 rounded-lg hover:border-indigo-500 hover:shadow-md transition-all group">
                        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mb-3 group-hover:bg-blue-200 transition-colors">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-gray-900">View Attendance</span>
                    </a>

                    <a href="{{ route('employee.shift-requests') }}" class="flex flex-col items-center justify-center p-6 border-2 border-gray-200 rounded-lg hover:border-indigo-500 hover:shadow-md transition-all group">
                        <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mb-3 group-hover:bg-purple-200 transition-colors">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-gray-900">Request Shift</span>
                    </a>

                    <a href="{{ route('employee.profile') }}" class="flex flex-col items-center justify-center p-6 border-2 border-gray-200 rounded-lg hover:border-indigo-500 hover:shadow-md transition-all group">
                        <div class="w-12 h-12 bg-pink-100 rounded-full flex items-center justify-center mb-3 group-hover:bg-pink-200 transition-colors">
                            <svg class="w-6 h-6 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-gray-900">Edit Profile</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

<script>
    function attendanceClasses(status, hasLeave) {
        if (hasLeave) {
            return {
                card: 'border-orange-500',
                badge: 'bg-orange-100 text-orange-800',
                badgeText: 'On Leave',
                statusText: 'On Leave'
            };
        }
        if (!status) {
            return {
                card: 'border-gray-400',
                badge: 'bg-gray-100 text-gray-800',
                badgeText: 'Not Yet Timed In',
                statusText: 'Pending'
            };
        }
        if (status === 'present') {
            return {
                card: 'border-green-500',
                badge: 'bg-green-100 text-green-800',
                badgeText: 'Present',
                statusText: 'On Time'
            };
        }
        if (status === 'late') {
            return {
                card: 'border-orange-500',
                badge: 'bg-orange-100 text-orange-800',
                badgeText: 'Late',
                statusText: 'Late'
            };
        }
        if (status === 'on_break') {
            return {
                card: 'border-blue-500',
                badge: 'bg-blue-100 text-blue-800',
                badgeText: 'On Break',
                statusText: 'On Break'
            };
        }
        if (status === 'absent') {
            return {
                card: 'border-red-500',
                badge: 'bg-red-100 text-red-800',
                badgeText: 'Absent',
                statusText: 'Absent'
            };
        }
        return {
            card: 'border-gray-400',
            badge: 'bg-gray-100 text-gray-800',
            badgeText: status.charAt(0).toUpperCase() + status.slice(1),
            statusText: status.charAt(0).toUpperCase() + status.slice(1)
        };
    }

    function applyAttendanceUI(data) {
        const card = document.getElementById('attendanceCard');
        const badge = document.getElementById('attendanceBadge');
        const timeInEl = document.getElementById('attendanceTimeIn');
        const timeOutEl = document.getElementById('attendanceTimeOut');
        const hoursEl = document.getElementById('attendanceHours');
        const statusEl = document.getElementById('attendanceStatus');

        if (!card || !badge || !timeInEl || !timeOutEl || !hoursEl || !statusEl) {
            return;
        }

        const classes = attendanceClasses(data.status, data.has_leave);

        card.classList.remove('border-orange-500', 'border-green-500', 'border-red-500', 'border-gray-400', 'border-blue-500');
        card.classList.add(classes.card);

        badge.classList.remove(
            'bg-orange-100', 'text-orange-800',
            'bg-green-100', 'text-green-800',
            'bg-red-100', 'text-red-800',
            'bg-gray-100', 'text-gray-800',
            'bg-blue-100', 'text-blue-800'
        );
        badge.classList.add(...classes.badge.split(' '));
        badge.textContent = classes.badgeText;

        timeInEl.textContent = data.time_in || '--:--';
        timeOutEl.textContent = data.time_out || '--:--';
        hoursEl.textContent = (data.hours_worked ?? 0) + 'h';

        if (data.has_leave && data.leave_type) {
            statusEl.textContent = data.leave_type.replace(/_/g, ' ');
        } else if (data.status) {
            if (data.status === 'late' || data.is_late) {
                statusEl.innerHTML = '<span class="text-orange-600">Late</span>';
            } else if (data.status === 'on_break') {
                statusEl.innerHTML = '<span class="text-blue-600">On Break</span>';
            } else {
                statusEl.innerHTML = '<span class="text-green-600">On Time</span>';
            }
        } else {
            statusEl.textContent = 'Pending';
        }
    }

    async function refreshTodayAttendance() {
        try {
            const response = await fetch('{{ route('employee.today-attendance') }}', {
                headers: { 'Accept': 'application/json' }
            });
            if (!response.ok) {
                return;
            }
            const result = await response.json();
            if (result.success) {
                applyAttendanceUI(result.data);
            }
        } catch (error) {
            console.error('[Employee Dashboard] Failed to refresh attendance', error);
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        refreshTodayAttendance();
        setInterval(refreshTodayAttendance, 15000);
    });
</script>
