@extends('dashboard')

@section('title', 'Employee Self Service')

@section('content')
<div class="p-6">
    <!-- Header -->
    <div class="mb-6">
        <h3 class="text-3xl font-bold text-gray-900">Employee Self Service</h3>
        <p class="text-gray-600 mt-2">Welcome back, {{ Auth::user()->name }}!</p>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Pending Leave Requests</p>
                    <p class="text-2xl font-semibold text-yellow-600">{{ $pendingLeaveRequests }}</p>
                </div>
                <div class="p-3 bg-yellow-100 rounded-full">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Draft Timesheets</p>
                    <p class="text-2xl font-semibold text-blue-600">{{ $draftTimesheets }}</p>
                </div>
                <div class="p-3 bg-blue-100 rounded-full">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
            </div>
        </div>

        @if($employee)
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Department</p>
                    <p class="text-2xl font-semibold text-green-600">{{ $employee->department ?? 'N/A' }}</p>
                </div>
                <div class="p-3 bg-green-100 rounded-full">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Employment Type</p>
                    <p class="text-2xl font-semibold text-purple-600">{{ ucfirst($employee->employment_type) }}</p>
                </div>
                <div class="p-3 bg-purple-100 rounded-full">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
            </div>
        </div>
        @else
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Employee Profile</p>
                    <p class="text-sm text-red-600">Not Set Up</p>
                </div>
                <div class="p-3 bg-red-100 rounded-full">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 md:col-span-3">
            <div class="text-center">
                <p class="text-gray-600 mb-4">Your employee profile is not set up. Please contact HR to complete your profile.</p>
                <a href="{{ route('employee.profile') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    View Profile
                </a>
            </div>
        </div>
        @endif
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Quick Actions</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="{{ route('employee.leave-requests.create') }}" class="flex items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                <div class="p-2 bg-blue-600 rounded-md mr-3">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="font-medium text-gray-900">Request Leave</h3>
                    <p class="text-sm text-gray-600">Submit a new leave request</p>
                </div>
            </a>

            <a href="{{ route('employee.timesheets.create') }}" class="flex items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                <div class="p-2 bg-green-600 rounded-md mr-3">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="font-medium text-gray-900">Add Timesheet</h3>
                    <p class="text-sm text-gray-600">Log your work hours</p>
                </div>
            </a>

            <a href="{{ route('employee.profile') }}" class="flex items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors">
                <div class="p-2 bg-purple-600 rounded-md mr-3">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="font-medium text-gray-900">Update Profile</h3>
                    <p class="text-sm text-gray-600">Manage your information</p>
                </div>
            </a>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Recent Leave Requests -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">Recent Leave Requests</h2>
                    <a href="{{ route('employee.leave-requests') }}" class="text-blue-600 hover:text-blue-700 text-sm">View All</a>
                </div>
            </div>
            <div class="p-6">
                @if($recentLeaveRequests->count() > 0)
                    <div class="space-y-4">
                        @foreach($recentLeaveRequests as $leave)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div>
                                    <p class="font-medium text-gray-900">{{ ucfirst($leave->leave_type) }} Leave</p>
                                    <p class="text-sm text-gray-600">{{ $leave->start_date->format('M d') }} - {{ $leave->end_date->format('M d, Y') }}</p>
                                </div>
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $leave->status_badge_color }}">
                                    {{ ucfirst($leave->status) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-center py-4">No leave requests yet.</p>
                @endif
            </div>
        </div>

        <!-- Recent Timesheets -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">Recent Timesheets</h2>
                    <a href="{{ route('employee.timesheets') }}" class="text-blue-600 hover:text-blue-700 text-sm">View All</a>
                </div>
            </div>
            <div class="p-6">
                @if($recentTimesheets->count() > 0)
                    <div class="space-y-4">
                        @foreach($recentTimesheets as $timesheet)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div>
                                    <p class="font-medium text-gray-900">{{ $timesheet->work_date->format('M d, Y') }}</p>
                                    <p class="text-sm text-gray-600">{{ $timesheet->hours_worked ?? 0 }} hours worked</p>
                                </div>
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $timesheet->status_badge_color }}">
                                    {{ ucfirst($timesheet->status) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-center py-4">No timesheets yet.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
