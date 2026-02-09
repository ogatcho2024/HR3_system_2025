@extends('dashboard')

@section('title', 'Leave Management')

@section('content')
<div class="p-6 bg-gray-300" x-data="leaveManagement()">
    <!-- Breadcrumbs -->
    @include('partials.breadcrumbs', ['breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => route('dashb')],
        ['label' => 'Leave Management', 'url' => route('leaveManagement')]
    ]])
    
    <!-- Header -->
    <div class="mb-6">
        <h3 class="text-3xl font-bold text-gray-900">Leave Management</h3>
        <p class="text-gray-600 mt-2">Manage employee leave requests, policies, and balances</p>
    </div>

    <!-- Navigation Tabs -->
    <div class="mb-6">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8">
                <button @click="activeTab = 'dashboard'" 
                        :class="activeTab === 'dashboard' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                    Dashboard
                </button>
                <button @click="activeTab = 'pending'" 
                        :class="activeTab === 'pending' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                    Pending Requests
                    <span class="ml-2 bg-red-100 text-red-600 py-0.5 px-2 rounded-full text-xs">{{ $pendingCount ?? 0 }}</span>
                </button>
                <button @click="activeTab = 'all-requests'" 
                        :class="activeTab === 'all-requests' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                    All Requests
                </button>
                <button @click="activeTab = 'balances'" 
                        :class="activeTab === 'balances' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                    Leave Balances
                </button>
                <button @click="activeTab = 'calendar'" 
                        :class="activeTab === 'calendar' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                    Calendar
                </button>
                <button @click="activeTab = 'reports'" 
                        :class="activeTab === 'reports' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                    Reports
                </button>
            </nav>
        </div>
    </div>

    <!-- Dashboard Tab -->
    <div x-show="activeTab === 'dashboard'" x-cloak>
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Total Requests</p>
                        <p class="text-2xl font-semibold text-blue-600">{{ $totalRequests ?? 0 }}</p>
                    </div>
                    <div class="p-3 bg-blue-100 rounded-full">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Pending Requests</p>
                        <p class="text-2xl font-semibold text-yellow-600">{{ $pendingRequests ?? 0 }}</p>
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
                        <p class="text-sm text-gray-600">Approved Requests</p>
                        <p class="text-2xl font-semibold text-green-600">{{ $approvedRequests ?? 0 }}</p>
                    </div>
                    <div class="p-3 bg-green-100 rounded-full">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Rejected Requests</p>
                        <p class="text-2xl font-semibold text-red-600">{{ $rejectedRequests ?? 0 }}</p>
                    </div>
                    <div class="p-3 bg-red-100 rounded-full">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts and Recent Requests -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Leave Requests by Type Chart -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Requests by Leave Type</h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center p-3 bg-blue-50 rounded-lg">
                        <span class="text-sm font-medium text-gray-700">Vacation</span>
                        <span class="text-sm font-semibold text-blue-600">45 requests</span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-green-50 rounded-lg">
                        <span class="text-sm font-medium text-gray-700">Sick Leave</span>
                        <span class="text-sm font-semibold text-green-600">23 requests</span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-purple-50 rounded-lg">
                        <span class="text-sm font-medium text-gray-700">Personal</span>
                        <span class="text-sm font-semibold text-purple-600">18 requests</span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-yellow-50 rounded-lg">
                        <span class="text-sm font-medium text-gray-700">Emergency</span>
                        <span class="text-sm font-semibold text-yellow-600">8 requests</span>
                    </div>
                </div>
            </div>

            <!-- Recent Requests -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Leave Requests</h3>
                <div class="space-y-3">
                    <!-- Sample recent requests -->
                    <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                        <div>
                            <p class="text-sm font-medium text-gray-900">John Doe</p>
                            <p class="text-xs text-gray-500">Vacation • Dec 20-25, 2024</p>
                        </div>
                        <span class="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">Pending</span>
                    </div>
                    <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                        <div>
                            <p class="text-sm font-medium text-gray-900">Sarah Johnson</p>
                            <p class="text-xs text-gray-500">Sick Leave • Dec 18, 2024</p>
                        </div>
                        <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">Approved</span>
                    </div>
                    <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                        <div>
                            <p class="text-sm font-medium text-gray-900">Mike Wilson</p>
                            <p class="text-xs text-gray-500">Personal • Dec 15, 2024</p>
                        </div>
                        <span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">Rejected</span>
                    </div>
                </div>
                <div class="mt-4">
                    <button @click="activeTab = 'all-requests'" class="text-sm text-blue-600 hover:text-blue-700">View all requests →</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Requests Tab -->
    <div x-show="activeTab === 'pending'" x-cloak>
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Pending Leave Requests</h3>
                <p class="text-sm text-gray-600 mt-1">Review and approve/reject employee leave requests</p>
            </div>
            
            <!-- Pending Requests Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Leave Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dates</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Days</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requested</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <!-- Sample pending request -->
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center">
                                        <span class="text-xs font-medium text-gray-600">JD</span>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900">John Doe</p>
                                        <p class="text-xs text-gray-500">IT Department</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">Vacation</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                Dec 20 - Dec 25, 2024
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                5
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                2 hours ago
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                                <button class="px-3 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700" 
                                        @click="showApprovalModal = true; selectedRequest = 'John Doe - Vacation'">
                                    Approve
                                </button>
                                <button class="px-3 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700"
                                        @click="showRejectionModal = true; selectedRequest = 'John Doe - Vacation'">
                                    Reject
                                </button>
                                <button class="px-3 py-1 bg-gray-600 text-white text-xs rounded hover:bg-gray-700">
                                    View
                                </button>
                            </td>
                        </tr>
                        <!-- Add more sample rows as needed -->
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                No pending leave requests at this time.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- All Requests Tab -->
    <div x-show="activeTab === 'all-requests'" x-cloak>
        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Filter Requests</h3>
            <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Leave Type</label>
                    <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Types</option>
                        <option value="vacation">Vacation</option>
                        <option value="sick">Sick Leave</option>
                        <option value="personal">Personal</option>
                        <option value="emergency">Emergency</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                    <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Departments</option>
                        <option value="IT">Information Technology</option>
                        <option value="Marketing">Marketing</option>
                        <option value="HR">Human Resources</option>
                        <option value="Finance">Finance</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                    <input type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                    <input type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input type="text" placeholder="Employee name..." class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            <div class="mt-4">
                <button class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Apply Filters</button>
                <button class="ml-2 px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">Clear</button>
            </div>
        </div>

        <!-- All Requests Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Leave Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dates</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Days</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Submitted</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <!-- Sample data - in real implementation, this would be from the controller -->
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center">
                                        <span class="text-xs font-medium text-gray-600">JD</span>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900">John Doe</p>
                                        <p class="text-xs text-gray-500">IT Department</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">Vacation</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                Dec 20 - Dec 25, 2024
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                5
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">Pending</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                Dec 15, 2024
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                                <button class="action-btn action-btn--view">View</button>
                                <button class="action-btn action-btn--approve">Approve</button>
                                <button class="action-btn action-btn--reject">Reject</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Leave Balances Tab -->
    <div x-show="activeTab === 'balances'" x-cloak>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-semibold text-gray-900">Employee Leave Balances</h3>
                <select class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="2024">2024</option>
                    <option value="2023">2023</option>
                </select>
            </div>
            
            <!-- Leave Balance Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-blue-50 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-blue-700 mb-2">Total Vacation Days</h4>
                    <p class="text-2xl font-bold text-blue-900">450</p>
                    <p class="text-xs text-blue-600">Across all employees</p>
                </div>
                <div class="bg-green-50 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-green-700 mb-2">Used Days</h4>
                    <p class="text-2xl font-bold text-green-900">287</p>
                    <p class="text-xs text-green-600">64% utilization</p>
                </div>
                <div class="bg-yellow-50 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-yellow-700 mb-2">Pending Days</h4>
                    <p class="text-2xl font-bold text-yellow-900">43</p>
                    <p class="text-xs text-yellow-600">Awaiting approval</p>
                </div>
                <div class="bg-purple-50 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-purple-700 mb-2">Available Days</h4>
                    <p class="text-2xl font-bold text-purple-900">120</p>
                    <p class="text-xs text-purple-600">Can be used</p>
                </div>
            </div>

            <!-- Employee Balance Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vacation</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sick Leave</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Personal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Available</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center">
                                        <span class="text-xs font-medium text-gray-600">JD</span>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900">John Doe</p>
                                        <p class="text-xs text-gray-500">IT Department</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div class="text-sm">
                                    <span class="text-green-600">8</span> / 20 days
                                    <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                                        <div class="bg-green-600 h-2 rounded-full" style="width: 40%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div class="text-sm">
                                    <span class="text-blue-600">2</span> / 10 days
                                    <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                                        <div class="bg-blue-600 h-2 rounded-full" style="width: 20%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div class="text-sm">
                                    <span class="text-purple-600">1</span> / 5 days
                                    <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                                        <div class="bg-purple-600 h-2 rounded-full" style="width: 20%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                24 days
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Calendar Tab -->
    <div x-show="activeTab === 'calendar'" x-cloak>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-semibold text-gray-900">Leave Calendar</h3>
                <div class="flex space-x-2">
                    <select class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option>December 2024</option>
                        <option>January 2025</option>
                    </select>
                </div>
            </div>
            
            <!-- Calendar View -->
            <div class="grid grid-cols-7 gap-1 mb-4">
                <!-- Days of week headers -->
                <div class="p-2 text-center text-xs font-medium text-gray-500">Sun</div>
                <div class="p-2 text-center text-xs font-medium text-gray-500">Mon</div>
                <div class="p-2 text-center text-xs font-medium text-gray-500">Tue</div>
                <div class="p-2 text-center text-xs font-medium text-gray-500">Wed</div>
                <div class="p-2 text-center text-xs font-medium text-gray-500">Thu</div>
                <div class="p-2 text-center text-xs font-medium text-gray-500">Fri</div>
                <div class="p-2 text-center text-xs font-medium text-gray-500">Sat</div>
                
                <!-- Calendar days would be generated dynamically -->
                <!-- Sample calendar days -->
                <div class="p-2 h-20 border border-gray-200 text-sm text-gray-400"></div>
                <div class="p-2 h-20 border border-gray-200 text-sm text-gray-400"></div>
                <div class="p-2 h-20 border border-gray-200 text-sm text-gray-400"></div>
                <div class="p-2 h-20 border border-gray-200 text-sm text-gray-400"></div>
                <div class="p-2 h-20 border border-gray-200 text-sm">
                    <div class="font-medium">1</div>
                </div>
                <div class="p-2 h-20 border border-gray-200 text-sm">
                    <div class="font-medium">2</div>
                </div>
                <div class="p-2 h-20 border border-gray-200 text-sm">
                    <div class="font-medium">3</div>
                </div>
                <!-- Continue for full month... -->
                <!-- Sample day with leave -->
                <div class="p-2 h-20 border border-gray-200 text-sm">
                    <div class="font-medium">15</div>
                    <div class="mt-1">
                        <div class="text-xs bg-blue-100 text-blue-800 px-1 py-0.5 rounded truncate">John - Vacation</div>
                    </div>
                </div>
            </div>
            
            <!-- Calendar Legend -->
            <div class="flex flex-wrap gap-4 mt-6 text-sm">
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-blue-100 rounded mr-2"></div>
                    <span class="text-gray-700">Vacation</span>
                </div>
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-green-100 rounded mr-2"></div>
                    <span class="text-gray-700">Sick Leave</span>
                </div>
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-purple-100 rounded mr-2"></div>
                    <span class="text-gray-700">Personal</span>
                </div>
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-yellow-100 rounded mr-2"></div>
                    <span class="text-gray-700">Emergency</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Reports Tab -->
    <div x-show="activeTab === 'reports'" x-cloak>
        <div class="space-y-6">
            <!-- Report Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4">Monthly Leave Report</h4>
                    <p class="text-gray-600 text-sm mb-4">Comprehensive monthly leave statistics and trends</p>
                    <button class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm">Generate Report</button>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4">Department Analysis</h4>
                    <p class="text-gray-600 text-sm mb-4">Leave patterns and usage by department</p>
                    <button class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm">Generate Report</button>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4">Employee Leave History</h4>
                    <p class="text-gray-600 text-sm mb-4">Individual employee leave records and patterns</p>
                    <button class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 text-sm">Generate Report</button>
                </div>
            </div>
            
            <!-- Quick Stats -->
            <div class="bg-white rounded-lg shadow p-6">
                <h4 class="text-lg font-semibold text-gray-900 mb-4">Leave Analytics</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <h5 class="text-sm font-medium text-blue-700">Average Leave Days/Employee</h5>
                        <p class="text-2xl font-bold text-blue-900 mt-1">18.5</p>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg">
                        <h5 class="text-sm font-medium text-green-700">Most Popular Leave Type</h5>
                        <p class="text-lg font-bold text-green-900 mt-1">Vacation (65%)</p>
                    </div>
                    <div class="bg-yellow-50 p-4 rounded-lg">
                        <h5 class="text-sm font-medium text-yellow-700">Peak Leave Month</h5>
                        <p class="text-lg font-bold text-yellow-900 mt-1">December</p>
                    </div>
                    <div class="bg-purple-50 p-4 rounded-lg">
                        <h5 class="text-sm font-medium text-purple-700">Approval Rate</h5>
                        <p class="text-2xl font-bold text-purple-900 mt-1">94%</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Approval Modal -->
    <div x-show="showApprovalModal" 
         x-cloak 
         class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
         @click="showApprovalModal = false">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white"
             @click.stop>
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Approve Leave Request</h3>
                <p class="text-sm text-gray-600 mb-4" x-text="selectedRequest"></p>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Comments (Optional)</label>
                    <textarea class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500" rows="3" placeholder="Add any comments..."></textarea>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400"
                            @click="showApprovalModal = false">
                        Cancel
                    </button>
                    <button class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700"
                            @click="showApprovalModal = false">
                        Approve Request
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Rejection Modal -->
    <div x-show="showRejectionModal" 
         x-cloak 
         class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
         @click="showRejectionModal = false">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white"
             @click.stop>
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Reject Leave Request</h3>
                <p class="text-sm text-gray-600 mb-4" x-text="selectedRequest"></p>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Reason for Rejection *</label>
                    <textarea class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500" rows="3" placeholder="Please provide a reason for rejection..." required></textarea>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400"
                            @click="showRejectionModal = false">
                        Cancel
                    </button>
                    <button class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
                            @click="showRejectionModal = false">
                        Reject Request
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Alpine.js Component -->
<script>
    function leaveManagement() {
        return {
            activeTab: 'dashboard',
            showApprovalModal: false,
            showRejectionModal: false,
            selectedRequest: '',
            
            init() {
                // Initialize component
            }
        }
    }
</script>

<style>
    [x-cloak] {
        display: none !important;
    }
</style>
@endsection
