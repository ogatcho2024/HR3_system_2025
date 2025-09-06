@extends('dashboard')

@section('title', 'All Attendance Activities')

@section('content')

<div class="p-4 bg-gray-300">
    <!-- Header -->
    <div class="mb-6 md:mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h3 class="text-2xl md:text-3xl font-bold text-gray-900">All Attendance Activities</h3>
                <p class="text-gray-600 mt-1 md:mt-2 text-sm md:text-base">Comprehensive view of all clock in/out activities and attendance changes</p>
            </div>
            <div class="flex items-center">
                <a href="{{ route('attendanceTimeTracking') }}" class="bg-gray-600 text-white px-3 py-2 md:px-4 rounded-lg hover:bg-gray-700 transition-colors flex items-center space-x-2 text-sm md:text-base">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    <span class="hidden sm:inline">Back to Attendance</span>
                    <span class="sm:hidden">Back</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="bg-white rounded-lg shadow-lg p-4 md:p-6 mb-6">
        <h4 class="text-lg font-semibold text-gray-900 mb-4">Filter Activities</h4>
        
        <form method="GET" action="{{ route('attendance.all-activities') }}" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Search Input -->
                <div class="sm:col-span-2 lg:col-span-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search Employee</label>
                    <input type="search" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Employee name or email..." 
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <!-- Date From -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                    <input type="date" 
                           name="date_from" 
                           value="{{ request('date_from') }}" 
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <!-- Date To -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                    <input type="date" 
                           name="date_to" 
                           value="{{ request('date_to') }}" 
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <!-- Activity Type -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Activity Type</label>
                    <select name="activity_type" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">All Activities</option>
                        <option value="clock_in" {{ request('activity_type') === 'clock_in' ? 'selected' : '' }}>Clock In</option>
                        <option value="clock_out" {{ request('activity_type') === 'clock_out' ? 'selected' : '' }}>Clock Out</option>
                        <option value="break_start" {{ request('activity_type') === 'break_start' ? 'selected' : '' }}>Break Start</option>
                        <option value="break_end" {{ request('activity_type') === 'break_end' ? 'selected' : '' }}>Break End</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Department Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                    <select name="department" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">All Departments</option>
                        @foreach($departments as $department)
                            <option value="{{ $department }}" {{ request('department') === $department ? 'selected' : '' }}>
                                {{ $department }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter Actions -->
                <div class="sm:col-span-1 lg:col-span-3 flex flex-col sm:flex-row items-stretch sm:items-end gap-2">
                    <button type="submit" class="flex-1 sm:flex-none bg-blue-600 text-white px-4 md:px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors flex items-center justify-center space-x-2 text-sm md:text-base">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.707A1 1 0 013 7V4z"></path>
                        </svg>
                        <span>Apply Filters</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Activities Content -->
    <div class="bg-white rounded-lg shadow-lg">
        <div class="p-4 md:p-6 border-b border-gray-200">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <h4 class="text-lg font-semibold text-gray-900">Activity Log</h4>
                <div class="text-sm text-gray-500">
                    {{ $activities->total() }} total activities
                </div>
            </div>
        </div>

        <!-- Desktop/Tablet Table View -->
        <div class="hidden md:block overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
                            <th class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                            <th class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">Department</th>
                            <th class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Activity</th>
                            <th class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden xl:table-cell">Details</th>
                            <th class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">Created By</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($activities as $activity)
                            @php
                                // Determine which activities occurred for this record
                                $activityList = [];
                                if ($activity->clock_in_time) {
                                    $activityList[] = [
                                        'type' => 'clock_in',
                                        'time' => $activity->clock_in_time,
                                        'label' => 'Clock In',
                                        'color' => 'green'
                                    ];
                                }
                                if ($activity->break_start) {
                                    $activityList[] = [
                                        'type' => 'break_start',
                                        'time' => $activity->break_start,
                                        'label' => 'Break Start',
                                        'color' => 'blue'
                                    ];
                                }
                                if ($activity->break_end) {
                                    $activityList[] = [
                                        'type' => 'break_end',
                                        'time' => $activity->break_end,
                                        'label' => 'Break End',
                                        'color' => 'blue'
                                    ];
                                }
                                if ($activity->clock_out_time) {
                                    $activityList[] = [
                                        'type' => 'clock_out',
                                        'time' => $activity->clock_out_time,
                                        'label' => 'Clock Out',
                                        'color' => 'red'
                                    ];
                                }
                            @endphp

                            @foreach($activityList as $activityItem)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <!-- Date & Time -->
                                    <td class="px-3 md:px-6 py-3 md:py-4 whitespace-nowrap">
                                        <div class="text-xs md:text-sm font-medium text-gray-900">
                                            {{ $activity->date->format('M d, Y') }}
                                        </div>
                                        <div class="text-xs md:text-sm text-gray-500">
                                            {{ \Carbon\Carbon::createFromTimeString($activityItem['time'])->format('h:i A') }}
                                        </div>
                                    </td>

                                    <!-- Employee Info -->
                                    <td class="px-3 md:px-6 py-3 md:py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-8 h-8 md:w-10 md:h-10 bg-{{ $activityItem['color'] }}-500 rounded-full flex items-center justify-center text-white font-medium text-xs md:text-sm">
                                                {{ strtoupper(substr($activity->user->name ?? 'N/A', 0, 2)) }}
                                            </div>
                                            <div class="ml-2 md:ml-4">
                                                <div class="text-xs md:text-sm font-medium text-gray-900 truncate max-w-[120px] md:max-w-none">{{ $activity->user->name ?? 'Unknown User' }}</div>
                                                <div class="text-xs text-gray-500 truncate max-w-[120px] md:max-w-none lg:block hidden">{{ $activity->user->email ?? 'N/A' }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Department -->
                                    <td class="px-3 md:px-6 py-3 md:py-4 whitespace-nowrap text-xs md:text-sm text-gray-900 hidden lg:table-cell">
                                        {{ $activity->user->employee->department ?? 'N/A' }}
                                    </td>

                                    <!-- Activity Type -->
                                    <td class="px-3 md:px-6 py-3 md:py-4 whitespace-nowrap">
                                        <div class="flex items-center space-x-2">
                                            @if($activityItem['type'] === 'clock_in')
                                                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                                                    </svg>
                                                </div>
                                            @elseif($activityItem['type'] === 'clock_out')
                                                <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                                                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                                                    </svg>
                                                </div>
                                            @elseif($activityItem['type'] === 'break_start' || $activityItem['type'] === 'break_end')
                                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                </div>
                                            @endif
                                            <span class="text-sm font-medium text-gray-900">{{ $activityItem['label'] }}</span>
                                        </div>
                                    </td>

                                    <!-- Details -->
                                    <td class="px-3 md:px-6 py-3 md:py-4 whitespace-nowrap hidden xl:table-cell">
                                        <div class="text-xs md:text-sm text-gray-900">
                                            @if($activity->hours_worked && $activityItem['type'] === 'clock_out')
                                                Hours worked: {{ number_format($activity->hours_worked, 2) }}h
                                            @elseif($activityItem['type'] === 'break_start' && $activity->break_end)
                                                @php
                                                    $breakDuration = \Carbon\Carbon::createFromTimeString($activity->break_start)
                                                        ->diffInMinutes(\Carbon\Carbon::createFromTimeString($activity->break_end));
                                                @endphp
                                                Break duration: {{ floor($breakDuration / 60) }}h {{ $breakDuration % 60 }}m
                                            @elseif($activityItem['type'] === 'break_start')
                                                Break in progress
                                            @else
                                                -
                                            @endif
                                        </div>
                                        @if($activity->notes)
                                            <div class="text-xs text-gray-500 mt-1">{{ $activity->notes }}</div>
                                        @endif
                                    </td>

                                    <!-- Status -->
                                    <td class="px-3 md:px-6 py-3 md:py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @if($activity->status === 'present') bg-green-100 text-green-800
                                            @elseif($activity->status === 'late') bg-yellow-100 text-yellow-800
                                            @elseif($activity->status === 'absent') bg-red-100 text-red-800
                                            @elseif($activity->status === 'on_break') bg-blue-100 text-blue-800
                                            @else bg-gray-100 text-gray-800
                                            @endif">
                                            @if($activity->status === 'on_break')
                                                On Break
                                            @else
                                                {{ ucfirst($activity->status) }}
                                            @endif
                                        </span>
                                    </td>

                                    <!-- Created By -->
                                    <td class="px-3 md:px-6 py-3 md:py-4 whitespace-nowrap text-xs md:text-sm text-gray-500 hidden lg:table-cell">
                                        {{ $activity->createdBy->name ?? 'System' }}
                                    </td>
                                </tr>
                            @endforeach
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <div class="text-gray-500">
                                        <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                        </svg>
                                        <p class="text-lg font-medium mb-1">No activities found</p>
                                        <p class="text-sm">Try adjusting your filter criteria or date range.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Mobile Card View -->
        <div class="md:hidden">
            @forelse($activities as $activity)
                @php
                    // Determine which activities occurred for this record
                    $activityList = [];
                    if ($activity->clock_in_time) {
                        $activityList[] = [
                            'type' => 'clock_in',
                            'time' => $activity->clock_in_time,
                            'label' => 'Clock In',
                            'color' => 'green'
                        ];
                    }
                    if ($activity->break_start) {
                        $activityList[] = [
                            'type' => 'break_start',
                            'time' => $activity->break_start,
                            'label' => 'Break Start',
                            'color' => 'blue'
                        ];
                    }
                    if ($activity->break_end) {
                        $activityList[] = [
                            'type' => 'break_end',
                            'time' => $activity->break_end,
                            'label' => 'Break End',
                            'color' => 'blue'
                        ];
                    }
                    if ($activity->clock_out_time) {
                        $activityList[] = [
                            'type' => 'clock_out',
                            'time' => $activity->clock_out_time,
                            'label' => 'Clock Out',
                            'color' => 'red'
                        ];
                    }
                @endphp

                @foreach($activityList as $activityItem)
                    <div class="p-4 border-b border-gray-100 hover:bg-gray-50 transition-colors">
                        <div class="flex items-start space-x-4">
                            <!-- Activity Icon -->
                            <div class="flex-shrink-0">
                                @if($activityItem['type'] === 'clock_in')
                                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                                        </svg>
                                    </div>
                                @elseif($activityItem['type'] === 'clock_out')
                                    <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                                        </svg>
                                    </div>
                                @elseif($activityItem['type'] === 'break_start' || $activityItem['type'] === 'break_end')
                                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                @endif
                            </div>

                            <!-- Activity Content -->
                            <div class="flex-1 min-w-0">
                                <!-- Header Row -->
                                <div class="flex items-start justify-between mb-2">
                                    <div>
                                        <h5 class="font-medium text-gray-900 text-sm">{{ $activityItem['label'] }}</h5>
                                        <p class="text-xs text-gray-500 mt-1">
                                            {{ $activity->date->format('M d, Y') }} at {{ \Carbon\Carbon::createFromTimeString($activityItem['time'])->format('h:i A') }}
                                        </p>
                                    </div>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full flex-shrink-0
                                        @if($activity->status === 'present') bg-green-100 text-green-800
                                        @elseif($activity->status === 'late') bg-yellow-100 text-yellow-800
                                        @elseif($activity->status === 'absent') bg-red-100 text-red-800
                                        @elseif($activity->status === 'on_break') bg-blue-100 text-blue-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        @if($activity->status === 'on_break')
                                            On Break
                                        @else
                                            {{ ucfirst($activity->status) }}
                                        @endif
                                    </span>
                                </div>

                                <!-- Employee Info -->
                                <div class="flex items-center space-x-3 mb-3">
                                    <div class="w-8 h-8 bg-{{ $activityItem['color'] }}-500 rounded-full flex items-center justify-center text-white font-medium text-xs">
                                        {{ strtoupper(substr($activity->user->name ?? 'N/A', 0, 2)) }}
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium text-gray-900 truncate">{{ $activity->user->name ?? 'Unknown User' }}</p>
                                        <p class="text-xs text-gray-500 truncate">{{ $activity->user->employee->department ?? 'N/A' }}</p>
                                    </div>
                                </div>

                                <!-- Details -->
                                <div class="text-xs text-gray-600">
                                    @if($activity->hours_worked && $activityItem['type'] === 'clock_out')
                                        <span class="bg-gray-100 px-2 py-1 rounded">Hours worked: {{ number_format($activity->hours_worked, 2) }}h</span>
                                    @elseif($activityItem['type'] === 'break_start' && $activity->break_end)
                                        @php
                                            $breakDuration = \Carbon\Carbon::createFromTimeString($activity->break_start)
                                                ->diffInMinutes(\Carbon\Carbon::createFromTimeString($activity->break_end));
                                        @endphp
                                        <span class="bg-blue-100 px-2 py-1 rounded">Break duration: {{ floor($breakDuration / 60) }}h {{ $breakDuration % 60 }}m</span>
                                    @elseif($activityItem['type'] === 'break_start')
                                        <span class="bg-yellow-100 px-2 py-1 rounded">Break in progress</span>
                                    @endif
                                    
                                    @if($activity->notes)
                                        <div class="mt-2 text-xs text-gray-500 bg-gray-50 px-2 py-1 rounded">{{ $activity->notes }}</div>
                                    @endif
                                    
                                    @if($activity->createdBy)
                                        <div class="mt-2">
                                            <span class="text-xs text-gray-400">Created by: {{ $activity->createdBy->name ?? 'System' }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @empty
                <div class="p-8 text-center text-gray-500">
                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                    </svg>
                    <p class="text-lg font-medium mb-1">No activities found</p>
                    <p class="text-sm">Try adjusting your filter criteria or date range.</p>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($activities->hasPages())
            <div class="px-4 md:px-6 py-4 border-t border-gray-200">
                {{ $activities->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>

@endsection
