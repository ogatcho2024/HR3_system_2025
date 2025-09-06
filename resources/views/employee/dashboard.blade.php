@extends('dashboard-user')

@section('title', 'Employee Dashboard')

@section('content')
<div class="min-h-screen bg-gray-100">
    <!-- Header -->
    <div class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        @if($user->photo)
                            <img class="h-12 w-12 rounded-full" src="{{ asset('storage/' . $user->photo) }}" alt="{{ $user->name }}">
                        @else
                            <div class="h-12 w-12 rounded-full bg-indigo-500 flex items-center justify-center">
                                <span class="text-lg font-medium text-white">{{ substr($user->name, 0, 1) }}</span>
                            </div>
                        @endif
                    </div>
                    <div class="ml-4">
                        <h1 class="text-2xl font-bold text-gray-900">Welcome back, {{ $user->name }}!</h1>
                        <p class="text-gray-600">{{ $employee->position ?? 'Employee' }} • {{ $employee->department ?? 'Department' }}</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-500">{{ $stats['current_month'] }}</p>
                    <p class="text-lg font-semibold text-gray-900">{{ now()->format('l, F j, Y') }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Leave Balance -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4m-4 8a4 4 0 01-4-4V7a4 4 0 118 0v4a4 4 0 01-4 4z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Leave Balance</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ $stats['leave_balance']['remaining'] }}/{{ $stats['leave_balance']['total'] }} days</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Attendance This Month -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Attendance</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ $stats['total_attendance_days'] }} days</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pending Requests -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Pending Requests</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ $stats['pending_leave_requests'] + $stats['pending_shift_requests'] }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Action -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Quick Action</dt>
                                <dd class="text-lg font-medium text-gray-900">
                                    <a href="{{ route('employee.leave-requests') }}" class="text-purple-600 hover:text-purple-900">Request Leave</a>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Features Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
            <!-- Feature Cards Column 1 -->
            <div class="space-y-6">
                <!-- Leave Requests -->
                <div class="bg-white overflow-hidden shadow rounded-lg hover:shadow-lg transition-shadow">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4m-4 8a4 4 0 01-4-4V7a4 4 0 118 0v4a4 4 0 01-4 4z"></path>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <h3 class="text-lg font-medium text-gray-900">Leave Requests</h3>
                                <p class="mt-1 text-sm text-gray-500">Submit and track your leave applications</p>
                            </div>
                        </div>
                        <div class="mt-5">
                            <a href="{{ route('employee.leave-requests') }}" class="w-full bg-green-50 border border-transparent rounded-md py-2 px-4 inline-flex justify-center text-sm font-medium text-green-700 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                Manage Leave Requests
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Shift Requests -->
                <div class="bg-white overflow-hidden shadow rounded-lg hover:shadow-lg transition-shadow">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <h3 class="text-lg font-medium text-gray-900">Shift Requests</h3>
                                <p class="mt-1 text-sm text-gray-500">Request shift changes or swaps</p>
                            </div>
                        </div>
                        <div class="mt-5">
                            <a href="{{ route('employee.shift-requests') }}" class="w-full bg-blue-50 border border-transparent rounded-md py-2 px-4 inline-flex justify-center text-sm font-medium text-blue-700 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Manage Shift Requests
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Work Schedule -->
                <div class="bg-white overflow-hidden shadow rounded-lg hover:shadow-lg transition-shadow">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4m-4 8a4 4 0 01-4-4V7a4 4 0 118 0v4a4 4 0 01-4 4z"></path>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <h3 class="text-lg font-medium text-gray-900">Work Schedule</h3>
                                <p class="mt-1 text-sm text-gray-500">View your upcoming shifts and schedule</p>
                            </div>
                        </div>
                        <div class="mt-5">
                            <a href="{{ route('employee.work-schedule') }}" class="w-full bg-indigo-50 border border-transparent rounded-md py-2 px-4 inline-flex justify-center text-sm font-medium text-indigo-700 hover:bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                View Schedule
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Feature Cards Column 2 -->
            <div class="space-y-6">
                <!-- Reimbursements -->
                <div class="bg-white overflow-hidden shadow rounded-lg hover:shadow-lg transition-shadow">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <h3 class="text-lg font-medium text-gray-900">Reimbursements</h3>
                                <p class="mt-1 text-sm text-gray-500">Claim expenses and track reimbursements</p>
                            </div>
                        </div>
                        <div class="mt-5">
                            <a href="{{ route('employee.reimbursements') }}" class="w-full bg-purple-50 border border-transparent rounded-md py-2 px-4 inline-flex justify-center text-sm font-medium text-purple-700 hover:bg-purple-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                                Manage Reimbursements
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Payroll & Payslips -->
                <div class="bg-white overflow-hidden shadow rounded-lg hover:shadow-lg transition-shadow">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <h3 class="text-lg font-medium text-gray-900">Payroll & Payslips</h3>
                                <p class="mt-1 text-sm text-gray-500">View and download your payslips</p>
                            </div>
                        </div>
                        <div class="mt-5">
                            <a href="{{ route('employee.payroll') }}" class="w-full bg-yellow-50 border border-transparent rounded-md py-2 px-4 inline-flex justify-center text-sm font-medium text-yellow-700 hover:bg-yellow-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                                View Payroll
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Profile Management -->
                <div class="bg-white overflow-hidden shadow rounded-lg hover:shadow-lg transition-shadow">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <h3 class="text-lg font-medium text-gray-900">Profile Management</h3>
                                <p class="mt-1 text-sm text-gray-500">Update your personal information</p>
                            </div>
                        </div>
                        <div class="mt-5">
                            <a href="{{ route('employee.profile') }}" class="w-full bg-red-50 border border-transparent rounded-md py-2 px-4 inline-flex justify-center text-sm font-medium text-red-700 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                Manage Profile
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activities Column -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Activities</h3>
                    <div class="flow-root">
                        <ul class="-mb-8">
                            @foreach($recentActivities as $index => $activity)
                            <li>
                                <div class="relative pb-8">
                                    @if(!$loop->last)
                                    <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                    @endif
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span class="h-8 w-8 rounded-full bg-{{ $activity['type'] === 'leave' ? 'green' : ($activity['type'] === 'attendance' ? 'blue' : 'yellow') }}-500 flex items-center justify-center ring-8 ring-white">
                                                <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    @if($activity['icon'] === 'calendar')
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4m-4 8a4 4 0 01-4-4V7a4 4 0 118 0v4a4 4 0 01-4 4z"></path>
                                                    @elseif($activity['icon'] === 'clock')
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    @else
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                    @endif
                                                </svg>
                                            </span>
                                        </div>
                                        <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">{{ $activity['title'] }}</p>
                                                <p class="text-sm text-gray-500">{{ $activity['description'] }}</p>
                                            </div>
                                            <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                {{ $activity['date']->diffForHumans() }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attendance & Performance Analytics -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Attendance & Performance</h3>
                    <a href="{{ route('employee.attendance') }}" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">View Details →</a>
                </div>
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-600">95.2%</div>
                        <div class="text-sm text-gray-500">Attendance Rate</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-blue-600">88.5%</div>
                        <div class="text-sm text-gray-500">Punctuality</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-purple-600">12.5h</div>
                        <div class="text-sm text-gray-500">Overtime</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-yellow-600">8.7/10</div>
                        <div class="text-sm text-gray-500">Performance</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
