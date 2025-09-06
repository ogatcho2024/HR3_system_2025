@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="min-h-screen bg-gray-100">
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Admin Dashboard</h1>
                <p class="text-gray-600 mt-1">Manage employee profiles, alerts, and pending requests</p>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Pending Leave Requests Card -->
                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-yellow-500">
                    <div class="flex items-center">
                        <div class="p-2 bg-yellow-100 rounded-lg">
                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Pending Leave Requests</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $pendingLeaveRequests }}</p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('admin.leave-requests.pending') }}" class="text-sm text-yellow-600 hover:text-yellow-800 font-medium">
                            View all pending requests →
                        </a>
                    </div>
                </div>

                <!-- Pending Shift Requests Card -->
                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
                    <div class="flex items-center">
                        <div class="p-2 bg-blue-100 rounded-lg">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Pending Shift Requests</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $pendingShiftRequests }}</p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('admin.shift-requests.pending') }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                            View all shift requests →
                        </a>
                    </div>
                </div>

                <!-- Incomplete Profiles Card -->
                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-red-500">
                    <div class="flex items-center">
                        <div class="p-2 bg-red-100 rounded-lg">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Employee Profiles Not Set Up</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $incompleteProfiles }}</p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('admin.employees', ['profile_status' => 'incomplete']) }}" class="text-sm text-red-600 hover:text-red-800 font-medium">
                            Set up profiles →
                        </a>
                    </div>
                </div>

                <!-- Active Alerts Card -->
                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
                    <div class="flex items-center">
                        <div class="p-2 bg-green-100 rounded-lg">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5-5-5h5v-12a1 1 0 011-1h2a1 1 0 011 1v12z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Active Alerts</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $activeAlerts }}</p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('admin.alerts') }}" class="text-sm text-green-600 hover:text-green-800 font-medium">
                            Manage alerts →
                        </a>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Recent Leave Requests -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">Recent Leave Requests</h2>
                        <a href="{{ route('admin.leave-requests.pending') }}" class="text-sm text-blue-600 hover:text-blue-800">View all</a>
                    </div>
                    <div class="space-y-3">
                        @forelse($recentLeaveRequests as $request)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-900">{{ $request->user->name }} {{ $request->user->lastname }}</p>
                                <p class="text-sm text-gray-600">{{ $request->leave_type }} - {{ $request->days_requested }} days</p>
                                <p class="text-xs text-gray-500">{{ $request->start_date->format('M j') }} to {{ $request->end_date->format('M j') }}</p>
                            </div>
                            <span class="px-2 py-1 text-xs rounded-full {{ $request->status_badge_color }}">
                                {{ ucfirst($request->status) }}
                            </span>
                        </div>
                        @empty
                        <p class="text-gray-500 text-center py-4">No pending leave requests</p>
                        @endforelse
                    </div>
                </div>

                <!-- Recent Shift Requests -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">Recent Shift Requests</h2>
                        <a href="{{ route('admin.shift-requests.pending') }}" class="text-sm text-blue-600 hover:text-blue-800">View all</a>
                    </div>
                    <div class="space-y-3">
                        @forelse($recentShiftRequests as $request)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-900">{{ $request->user->name }} {{ $request->user->lastname }}</p>
                                <p class="text-sm text-gray-600">{{ ucfirst(str_replace('_', ' ', $request->request_type)) }}</p>
                                <p class="text-xs text-gray-500">{{ $request->requested_date->format('M j, Y') }}</p>
                            </div>
                            <span class="px-2 py-1 text-xs rounded-full {{ $request->status_badge_color }}">
                                {{ ucfirst($request->status) }}
                            </span>
                        </div>
                        @empty
                        <p class="text-gray-500 text-center py-4">No pending shift requests</p>
                        @endforelse
                    </div>
                </div>

                <!-- Incomplete Employee Profiles -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">Profiles Need Setup</h2>
                        <a href="{{ route('admin.employees', ['profile_status' => 'incomplete']) }}" class="text-sm text-blue-600 hover:text-blue-800">View all</a>
                    </div>
                    <div class="space-y-3">
                        @forelse($recentIncompleteProfiles as $user)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-900">{{ $user->name }} {{ $user->lastname }}</p>
                                <p class="text-sm text-gray-600">{{ $user->email }}</p>
                                <p class="text-xs text-gray-500">Registered {{ $user->created_at->diffForHumans() }}</p>
                            </div>
                            <a href="{{ route('admin.employees.profile-setup', $user) }}" class="text-sm bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700">
                                Setup
                            </a>
                        </div>
                        @empty
                        <p class="text-gray-500 text-center py-4">All profiles are complete!</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="mt-8 bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <a href="{{ route('admin.employees') }}" class="flex items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                        <svg class="w-8 h-8 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <div>
                            <p class="font-medium text-gray-900">Manage Employees</p>
                            <p class="text-sm text-gray-600">View and setup employee profiles</p>
                        </div>
                    </a>

                    <a href="{{ route('admin.alerts.create') }}" class="flex items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                        <svg class="w-8 h-8 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <p class="font-medium text-gray-900">Create Alert</p>
                            <p class="text-sm text-gray-600">Send system-wide notification</p>
                        </div>
                    </a>

                    <a href="{{ route('admin.leave-requests.pending') }}" class="flex items-center p-4 bg-yellow-50 rounded-lg hover:bg-yellow-100 transition-colors">
                        <svg class="w-8 h-8 text-yellow-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        <div>
                            <p class="font-medium text-gray-900">Review Requests</p>
                            <p class="text-sm text-gray-600">Approve or reject leave requests</p>
                        </div>
                    </a>

                    <a href="{{ route('admin.alerts') }}" class="flex items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors">
                        <svg class="w-8 h-8 text-purple-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5-5-5h5v-12a1 1 0 011-1h2a1 1 0 011 1v12z"></path>
                        </svg>
                        <div>
                            <p class="font-medium text-gray-900">Manage Alerts</p>
                            <p class="text-sm text-gray-600">View and edit system alerts</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
