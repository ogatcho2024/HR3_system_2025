@extends('dashboard-user')

@section('title', 'My Work Schedule')

@section('content')
<div class="min-h-screen bg-gray-100">
    <!-- Header -->
    <div class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">My Work Schedule</h1>
                    <p class="text-gray-600">View your upcoming shifts and work schedule</p>
                </div>
                <div class="flex space-x-4">
                    <a href="{{ route('employee.dashboard') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        ← Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Current Week Overview -->
        <div class="bg-white shadow rounded-xl mb-8">
            <div class="px-6 py-4 border-b border-gray-200 flex flex-col md:flex-row md:items-center md:justify-between gap-2">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">This Week</h3>
                    <p class="text-sm text-gray-500">{{ now()->startOfWeek()->format('M j') }} - {{ now()->endOfWeek()->format('M j, Y') }}</p>
                </div>
                <div class="text-xs text-gray-500 uppercase tracking-wide">Weekly Schedule</div>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-7 gap-3">
                    @for($i = 0; $i < 7; $i++)
                        @php
                            $day = now()->startOfWeek()->addDays($i);
                            $daySchedule = $schedule->where('date', $day->format('Y-m-d'))->first();
                            $isToday = $day->isToday();
                        @endphp
                        <div class="rounded-lg border {{ $isToday ? 'border-blue-200 bg-blue-50/60' : 'border-gray-200 bg-gray-50' }} p-3 text-center">
                            <div class="text-xs font-semibold {{ $isToday ? 'text-blue-700' : 'text-gray-500' }}">{{ $day->format('D') }}</div>
                            <div class="text-lg font-bold {{ $isToday ? 'text-blue-900' : 'text-gray-900' }}">{{ $day->format('j') }}</div>
                            @if($daySchedule)
                                <div class="mt-2 rounded-md bg-white border border-gray-200 px-2 py-1">
                                    <div class="text-xs font-semibold text-gray-900">{{ $daySchedule['shift'] }}</div>
                                    <div class="text-xs text-gray-500">{{ $daySchedule['time'] }}</div>
                                </div>
                            @else
                                <div class="mt-2 rounded-md border border-dashed border-gray-300 px-2 py-1">
                                    <div class="text-xs text-gray-500">No shift</div>
                                </div>
                            @endif
                            @if($isToday)
                                <div class="mt-2 text-[10px] font-semibold text-blue-700">Today</div>
                            @endif
                        </div>
                    @endfor
                </div>
            </div>
        </div>

        <!-- Upcoming Schedule -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Upcoming Schedule</h3>
                <p class="text-sm text-gray-500 mt-1">Your scheduled shifts for the next two weeks</p>
            </div>
            
            @if($schedule->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Shift</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($schedule as $shift)
                        <tr class="{{ is_object($shift['date']) && $shift['date']->isToday() ? 'bg-blue-50' : '' }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ is_object($shift['date']) ? $shift['date']->format('M j, Y') : date('M j, Y', strtotime($shift['date'])) }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ is_object($shift['date']) ? $shift['date']->format('l') : date('l', strtotime($shift['date'])) }}
                                            @if(is_object($shift['date']) && $shift['date']->isToday())
                                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                    Today
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-8 w-8">
                                        <div class="h-8 w-8 bg-indigo-100 rounded-full flex items-center justify-center">
                                            <svg class="h-4 w-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900">{{ $shift['shift'] }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $shift['time'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $shift['location'] ?? 'Office' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php $status = $shift['status'] ?? 'scheduled'; @endphp
                                @if($status == 'scheduled')
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Scheduled</span>
                                @elseif($status == 'completed')
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Completed</span>
                                @elseif($status == 'cancelled')
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Cancelled</span>
                                @else
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">{{ ucfirst($status) }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                @if($status == 'scheduled' && is_object($shift['date']) && $shift['date']->isFuture())
                                    <a href="{{ route('employee.shift-requests') }}" class="text-indigo-600 hover:text-indigo-900">Request Change</a>
                                @else
                                    <span class="text-gray-400">View Details</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4m-4 8a4 4 0 01-4-4V7a4 4 0 118 0v4a4 4 0 01-4 4z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No scheduled shifts</h3>
                <p class="mt-1 text-sm text-gray-500">Your work schedule will appear here once shifts are assigned.</p>
            </div>
            @endif
        </div>

        <!-- Quick Actions -->
        <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <h3 class="text-lg font-medium text-gray-900">Request Shift Change</h3>
                            <p class="mt-1 text-sm text-gray-500">Submit a request to modify your schedule</p>
                        </div>
                    </div>
                    <div class="mt-5">
                        <a href="{{ route('employee.shift-requests') }}" class="w-full bg-indigo-50 border border-transparent rounded-md py-2 px-4 inline-flex justify-center text-sm font-medium text-indigo-700 hover:bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Submit Request
                        </a>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4m-4 8a4 4 0 01-4-4V7a4 4 0 118 0v4a4 4 0 01-4 4z"></path>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <h3 class="text-lg font-medium text-gray-900">Request Leave</h3>
                            <p class="mt-1 text-sm text-gray-500">Apply for time off or vacation days</p>
                        </div>
                    </div>
                    <div class="mt-5">
                        <a href="{{ route('employee.leave-requests') }}" class="w-full bg-green-50 border border-transparent rounded-md py-2 px-4 inline-flex justify-center text-sm font-medium text-green-700 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            Apply for Leave
                        </a>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <h3 class="text-lg font-medium text-gray-900">View Attendance</h3>
                            <p class="mt-1 text-sm text-gray-500">Check your attendance records</p>
                        </div>
                    </div>
                    <div class="mt-5">
                        <a href="{{ route('employee.attendance') }}" class="w-full bg-blue-50 border border-transparent rounded-md py-2 px-4 inline-flex justify-center text-sm font-medium text-blue-700 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            View Records
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
