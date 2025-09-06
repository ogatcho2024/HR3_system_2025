@extends('dashboard')

@section('title', 'Employee Portal')

@section('content')
<div class="min-h-screen bg-gray-100">
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-3xl font-bold text-gray-900">Employee Portal</h3>
                        <p class="text-gray-600 mt-1">Self-service portal for employee management and requests</p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('employee-management.dashboard') }}" class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">
                            Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>

            <!-- Quick Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Total Employees</p>
                            <p class="text-2xl font-semibold text-blue-600">{{ $totalEmployees }}</p>
                        </div>
                        <div class="p-3 bg-blue-100 rounded-full">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

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
                            <p class="text-2xl font-semibold text-green-600">{{ $draftTimesheets }}</p>
                        </div>
                        <div class="p-3 bg-green-100 rounded-full">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Active Alerts</p>
                            <p class="text-2xl font-semibold text-red-600">{{ $activeAlerts }}</p>
                        </div>
                        <div class="p-3 bg-red-100 rounded-full">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4.8 12.5c-.4 0-.8-.3-.8-.8s.3-.8.8-.8.8.3.8.8-.4.8-.8.8zm0-3c-.4 0-.8-.3-.8-.8s.3-.8.8-.8.8.3.8.8-.4.8-.8.8zm0-3c-.4 0-.8-.3-.8-.8s.3-.8.8-.8.8.3.8.8-.4.8-.8.8z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Employee Services Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <!-- Profile Management -->
                <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
                    <div class="flex items-center mb-4">
                        <div class="p-3 bg-blue-100 rounded-full mr-4">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Profile Management</h3>
                            <p class="text-sm text-gray-600">Update your personal information</p>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <a href="{{ route('employee.profile') }}" class="block w-full text-left px-4 py-2 text-sm text-blue-600 hover:bg-blue-50 rounded-md transition-colors">
                            View/Edit Profile
                        </a>
                        <button onclick="showProfileQuickView()" class="block w-full text-left px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-md transition-colors">
                            Quick Profile View
                        </button>
                    </div>
                </div>

                <!-- Leave Management -->
                <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
                    <div class="flex items-center mb-4">
                        <div class="p-3 bg-green-100 rounded-full mr-4">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Leave Management</h3>
                            <p class="text-sm text-gray-600">Manage your leave requests</p>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <a href="{{ route('employee.leave-requests.create') }}" class="block w-full text-left px-4 py-2 text-sm text-green-600 hover:bg-green-50 rounded-md transition-colors">
                            Request Leave
                        </a>
                        <a href="{{ route('employee.leave-requests') }}" class="block w-full text-left px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-md transition-colors">
                            View Leave History
                        </a>
                    </div>
                </div>

                <!-- Timesheet Management -->
                <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
                    <div class="flex items-center mb-4">
                        <div class="p-3 bg-purple-100 rounded-full mr-4">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Timesheet Management</h3>
                            <p class="text-sm text-gray-600">Log and manage work hours</p>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <a href="{{ route('employee.timesheets.create') }}" class="block w-full text-left px-4 py-2 text-sm text-purple-600 hover:bg-purple-50 rounded-md transition-colors">
                            Add Timesheet
                        </a>
                        <a href="{{ route('employee.timesheets') }}" class="block w-full text-left px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-md transition-colors">
                            View Timesheets
                        </a>
                    </div>
                </div>

                <!-- Attendance Tracking -->
                <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
                    <div class="flex items-center mb-4">
                        <div class="p-3 bg-indigo-100 rounded-full mr-4">
                            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Attendance Tracking</h3>
                            <p class="text-sm text-gray-600">Clock in/out and view attendance</p>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <button onclick="clockIn()" class="block w-full text-left px-4 py-2 text-sm text-indigo-600 hover:bg-indigo-50 rounded-md transition-colors">
                            Clock In/Out
                        </button>
                        <a href="{{ route('attendance.all-activities') }}" class="block w-full text-left px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-md transition-colors">
                            View Attendance History
                        </a>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
                    <div class="flex items-center mb-4">
                        <div class="p-3 bg-orange-100 rounded-full mr-4">
                            <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Quick Actions</h3>
                            <p class="text-sm text-gray-600">Common employee tasks</p>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <button onclick="showEmergencyContacts()" class="block w-full text-left px-4 py-2 text-sm text-orange-600 hover:bg-orange-50 rounded-md transition-colors">
                            Emergency Contacts
                        </button>
                        <button onclick="showCompanyPolicies()" class="block w-full text-left px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-md transition-colors">
                            Company Policies
                        </button>
                    </div>
                </div>

                <!-- Help & Support -->
                <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
                    <div class="flex items-center mb-4">
                        <div class="p-3 bg-teal-100 rounded-full mr-4">
                            <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Help & Support</h3>
                            <p class="text-sm text-gray-600">Get assistance and support</p>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <button onclick="showHelpDesk()" class="block w-full text-left px-4 py-2 text-sm text-teal-600 hover:bg-teal-50 rounded-md transition-colors">
                            Contact HR
                        </button>
                        <button onclick="showFAQ()" class="block w-full text-left px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-md transition-colors">
                            FAQ
                        </button>
                    </div>
                </div>
            </div>

            <!-- Recent Activity and Employee Information -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Recent Activity -->
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Recent Activity</h2>
                    </div>
                    <div class="p-6">
                        @if($recentActivities->count() > 0)
                            <div class="space-y-4">
                                @foreach($recentActivities as $activity)
                                    <div class="flex items-start space-x-3">
                                        <div class="flex-shrink-0">
                                            <div class="w-2 h-2 bg-blue-400 rounded-full mt-2"></div>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm text-gray-900">{{ $activity['description'] }}</p>
                                            <p class="text-xs text-gray-500">{{ $activity['time'] }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 text-center py-4">No recent activity.</p>
                        @endif
                    </div>
                </div>

                <!-- Employee Quick Info -->
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Employee Information</h2>
                    </div>
                    <div class="p-6">
                        @if($currentEmployee)
                            <div class="space-y-4">
                                <div class="flex items-center space-x-4">
                                    @if(Auth::user()->photo)
                                        <img class="h-16 w-16 rounded-full" src="{{ asset('storage/' . Auth::user()->photo) }}" alt="{{ Auth::user()->name }}">
                                    @else
                                        <div class="h-16 w-16 rounded-full bg-gray-300 flex items-center justify-center">
                                            <span class="text-lg font-medium text-gray-700">{{ substr(Auth::user()->name, 0, 1) }}</span>
                                        </div>
                                    @endif
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900">{{ Auth::user()->name }} {{ Auth::user()->lastname }}</h3>
                                        <p class="text-sm text-gray-600">{{ $currentEmployee->position ?? 'Position not set' }}</p>
                                        <p class="text-sm text-gray-600">{{ $currentEmployee->department ?? 'Department not set' }}</p>
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <p class="text-gray-600">Employee ID:</p>
                                        <p class="font-medium">{{ $currentEmployee->employee_id ?? 'Not set' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-600">Employment Type:</p>
                                        <p class="font-medium">{{ ucfirst(str_replace('_', ' ', $currentEmployee->employment_type ?? 'Not set')) }}</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-600">Hire Date:</p>
                                        <p class="font-medium">{{ $currentEmployee->hire_date ? $currentEmployee->hire_date->format('M j, Y') : 'Not set' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-600">Status:</p>
                                        <span class="px-2 py-1 text-xs font-medium rounded-full 
                                            {{ $currentEmployee->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ ucfirst($currentEmployee->status ?? 'Unknown') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="text-center py-8">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">Profile Not Complete</h3>
                                <p class="mt-1 text-sm text-gray-500">Your employee profile needs to be set up.</p>
                                <a href="{{ route('employee.profile') }}" class="mt-3 inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700">
                                    Complete Profile
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Company Announcements & Alerts -->
            @if($activeAlerts > 0)
            <div class="bg-white rounded-lg shadow mb-8">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Company Announcements</h2>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        @foreach($companyAlerts as $alert)
                            <div class="border-l-4 {{ $alert->type === 'urgent' ? 'border-red-400 bg-red-50' : ($alert->type === 'warning' ? 'border-yellow-400 bg-yellow-50' : 'border-blue-400 bg-blue-50') }} p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        @if($alert->type === 'urgent')
                                            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                            </svg>
                                        @elseif($alert->type === 'warning')
                                            <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                            </svg>
                                        @else
                                            <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                            </svg>
                                        @endif
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium {{ $alert->type === 'urgent' ? 'text-red-800' : ($alert->type === 'warning' ? 'text-yellow-800' : 'text-blue-800') }}">
                                            {{ $alert->title }}
                                        </h3>
                                        <div class="mt-1 text-sm {{ $alert->type === 'urgent' ? 'text-red-700' : ($alert->type === 'warning' ? 'text-yellow-700' : 'text-blue-700') }}">
                                            <p>{{ $alert->message }}</p>
                                        </div>
                                        <div class="mt-2 text-xs text-gray-500">
                                            Posted: {{ $alert->created_at->format('M j, Y g:i A') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Profile Quick View Modal -->
<div id="profileModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Quick Profile View</h3>
                <button onclick="closeModal('profileModal')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            @if($currentEmployee)
                <div class="space-y-3 text-sm">
                    <div><strong>Name:</strong> {{ Auth::user()->name }} {{ Auth::user()->lastname }}</div>
                    <div><strong>Email:</strong> {{ Auth::user()->email }}</div>
                    <div><strong>Phone:</strong> {{ Auth::user()->phone ?? 'Not set' }}</div>
                    <div><strong>Department:</strong> {{ $currentEmployee->department ?? 'Not set' }}</div>
                    <div><strong>Position:</strong> {{ $currentEmployee->position ?? 'Not set' }}</div>
                    <div><strong>Manager:</strong> {{ $currentEmployee->manager_name ?? 'Not set' }}</div>
                    <div><strong>Work Location:</strong> {{ $currentEmployee->work_location ?? 'Not set' }}</div>
                </div>
            @else
                <p class="text-gray-600">Profile information not available. Please complete your profile setup.</p>
            @endif
            <div class="mt-6">
                <a href="{{ route('employee.profile') }}" class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 inline-block text-center">
                    Edit Full Profile
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Emergency Contacts Modal -->
<div id="emergencyModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Emergency Contacts</h3>
                <button onclick="closeModal('emergencyModal')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="space-y-4">
                <div class="border-l-4 border-red-400 bg-red-50 p-4">
                    <h4 class="font-medium text-red-800">Emergency Services</h4>
                    <p class="text-sm text-red-700">Fire/Police/Medical: 911</p>
                </div>
                
                <div class="border-l-4 border-blue-400 bg-blue-50 p-4">
                    <h4 class="font-medium text-blue-800">HR Department</h4>
                    <p class="text-sm text-blue-700">Phone: (555) 123-4567</p>
                    <p class="text-sm text-blue-700">Email: hr@company.com</p>
                </div>
                
                @if($currentEmployee && $currentEmployee->emergency_contact_name)
                <div class="border-l-4 border-green-400 bg-green-50 p-4">
                    <h4 class="font-medium text-green-800">Personal Emergency Contact</h4>
                    <p class="text-sm text-green-700">{{ $currentEmployee->emergency_contact_name }}</p>
                    @if($currentEmployee->emergency_contact_phone)
                        <p class="text-sm text-green-700">{{ $currentEmployee->emergency_contact_phone }}</p>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Help Desk Modal -->
<div id="helpModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Contact HR Support</h3>
                <button onclick="closeModal('helpModal')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="space-y-4">
                <div>
                    <h4 class="font-medium text-gray-900 mb-2">HR Department Contact</h4>
                    <div class="space-y-1 text-sm text-gray-600">
                        <p><strong>Phone:</strong> (555) 123-4567</p>
                        <p><strong>Email:</strong> hr@company.com</p>
                        <p><strong>Office Hours:</strong> Monday - Friday, 8:00 AM - 5:00 PM</p>
                    </div>
                </div>
                
                <div>
                    <h4 class="font-medium text-gray-900 mb-2">Common Issues</h4>
                    <ul class="text-sm text-gray-600 space-y-1">
                        <li>• Password reset requests</li>
                        <li>• Benefits inquiries</li>
                        <li>• Payroll questions</li>
                        <li>• Policy clarifications</li>
                        <li>• Technical support</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showProfileQuickView() {
    document.getElementById('profileModal').classList.remove('hidden');
}

function showEmergencyContacts() {
    document.getElementById('emergencyModal').classList.remove('hidden');
}

function showHelpDesk() {
    document.getElementById('helpModal').classList.remove('hidden');
}

function showCompanyPolicies() {
    alert('Company policies feature will be implemented soon.');
}

function showFAQ() {
    alert('FAQ section will be implemented soon.');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}

function clockIn() {
    if (confirm('Are you sure you want to clock in?')) {
        fetch('{{ route("attendance.clock-in") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({})
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Successfully clocked in!');
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Unable to clock in'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while clocking in.');
        });
    }
}

// Close modals when clicking outside
window.onclick = function(event) {
    const modals = ['profileModal', 'emergencyModal', 'helpModal'];
    modals.forEach(modalId => {
        const modal = document.getElementById(modalId);
        if (event.target === modal) {
            modal.classList.add('hidden');
        }
    });
}
</script>
@endsection
