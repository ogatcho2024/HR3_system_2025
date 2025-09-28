@extends('dashboard')

@section('title', 'Recent Activities')

@section('content')
<div class="p-6">
    <!-- Breadcrumbs -->
    @include('partials.breadcrumbs', ['breadcrumbs' => [
        ['label' => 'Recent Activities', 'url' => route('activities')]
    ]])
    
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-3xl font-bold text-gray-900">Recent Activities</h3>
                <p class="text-gray-600 mt-2">Detailed view of all recent activities in the system</p>
            </div>
            <div class="flex items-center space-x-4">
                <div class="bg-white rounded-lg shadow px-4 py-3 text-center">
                    <p class="text-sm text-gray-600">Current Time</p>
                    <p id="current-time" class="text-xl font-bold text-blue-600"></p>
                    <p id="current-date" class="text-xs text-gray-500"></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-gray-100 rounded-lg shadow-lg p-6 mb-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Activity Type</label>
                <select class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option>All Activities</option>
                    <option>Employee Actions</option>
                    <option>Leave Requests</option>
                    <option>Payroll Updates</option>
                    <option>Department Changes</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date Range</label>
                <select class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option>Last 24 Hours</option>
                    <option>Last 7 Days</option>
                    <option>Last 30 Days</option>
                    <option>Custom Range</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                <select class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option>All Departments</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->department_code }}">{{ $department->department_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition-colors">
                    Apply Filters
                </button>
            </div>
        </div>
    </div>

    <!-- Activity Timeline -->
    <div class="bg-gray-100 rounded-lg shadow-lg mb-8">
        <div class="px-6 py-4 border-b bg-gray-200 border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Activity Timeline</h3>
        </div>
        <div class="p-6">
            <div class="space-y-6">
                <!-- Date Group -->
                <div>
                    <div class="flex items-center mb-4">
                        <div class="flex-grow border-t border-gray-300"></div>
                        <span class="flex-shrink mx-4 text-gray-500 font-medium">Today</span>
                        <div class="flex-grow border-t border-gray-300"></div>
                    </div>
                    
                    <!-- Activity Item -->
                    <div class="flex items-start space-x-4 pb-6">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="bg-white rounded-lg shadow p-4">
                                <div class="flex justify-between">
                                    <p class="text-sm font-medium text-gray-900">New employee added</p>
                                    <span class="text-xs text-gray-500">2 hours ago</span>
                                </div>
                                <p class="text-sm text-gray-700 mt-1">John Smith was added to the Marketing department</p>
                                <div class="mt-2 flex items-center text-xs text-gray-500">
                                    <span>By: Admin User</span>
                                    <span class="mx-2">•</span>
                                    <span>Department: Marketing</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Activity Item -->
                    <div class="flex items-start space-x-4 pb-6">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="bg-white rounded-lg shadow p-4">
                                <div class="flex justify-between">
                                    <p class="text-sm font-medium text-gray-900">Leave request submitted</p>
                                    <span class="text-xs text-gray-500">4 hours ago</span>
                                </div>
                                <p class="text-sm text-gray-700 mt-1">Sarah Johnson submitted a leave request for August 25-27</p>
                                <div class="mt-2 flex items-center text-xs text-gray-500">
                                    <span>By: Sarah Johnson</span>
                                    <span class="mx-2">•</span>
                                    <span>Type: Vacation</span>
                                    <span class="mx-2">•</span>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        Pending
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Date Group -->
                <div>
                    <div class="flex items-center mb-4">
                        <div class="flex-grow border-t border-gray-300"></div>
                        <span class="flex-shrink mx-4 text-gray-500 font-medium">Yesterday</span>
                        <div class="flex-grow border-t border-gray-300"></div>
                    </div>
                    
                    <!-- Activity Item -->
                    <div class="flex items-start space-x-4 pb-6">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="bg-white rounded-lg shadow p-4">
                                <div class="flex justify-between">
                                    <p class="text-sm font-medium text-gray-900">Payroll processed</p>
                                    <span class="text-xs text-gray-500">1 day ago</span>
                                </div>
                                <p class="text-sm text-gray-700 mt-1">Monthly payroll for July 2025 processed successfully</p>
                                <div class="mt-2 flex items-center text-xs text-gray-500">
                                    <span>By: Finance Department</span>
                                    <span class="mx-2">•</span>
                                    <span>Amount: $245,680.00</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Activity Item -->
                    <div class="flex items-start space-x-4 pb-6">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="bg-white rounded-lg shadow p-4">
                                <div class="flex justify-between">
                                    <p class="text-sm font-medium text-gray-900">Department budget updated</p>
                                    <span class="text-xs text-gray-500">2 days ago</span>
                                </div>
                                <p class="text-sm text-gray-700 mt-1">IT department budget increased by 15% for Q3 2025</p>
                                <div class="mt-2 flex items-center text-xs text-gray-500">
                                    <span>By: Michael Chen</span>
                                    <span class="mx-2">•</span>
                                    <span>Department: IT</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Activity Item -->
                    <div class="flex items-start space-x-4 pb-6">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="bg-white rounded-lg shadow p-4">
                                <div class="flex justify-between">
                                    <p class="text-sm font-medium text-gray-900">Team meeting scheduled</p>
                                    <span class="text-xs text-gray-500">2 days ago</span>
                                </div>
                                <p class="text-sm text-gray-700 mt-1">Weekly HR team meeting scheduled for August 24 at 10:00 AM</p>
                                <div class="mt-2 flex items-center text-xs text-gray-500">
                                    <span>By: HR Department</span>
                                    <span class="mx-2">•</span>
                                    <span>Location: Conference Room B</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Date Group -->
                <div>
                    <div class="flex items-center mb-4">
                        <div class="flex-grow border-t border-gray-300"></div>
                        <span class="flex-shrink mx-4 text-gray-500 font-medium">August 20, 2025</span>
                        <div class="flex-grow border-t border-gray-300"></div>
                    </div>
                    
                    <!-- Activity Item -->
                    <div class="flex items-start space-x-4 pb-6">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="bg-white rounded-lg shadow p-4">
                                <div class="flex justify-between">
                                    <p class="text-sm font-medium text-gray-900">Security alert</p>
                                    <span class="text-xs text-gray-500">3 days ago</span>
                                </div>
                                <p class="text-sm text-gray-700 mt-1">Unusual login activity detected for user account</p>
                                <div class="mt-2 flex items-center text-xs text-gray-500">
                                    <span>By: System</span>
                                    <span class="mx-2">•</span>
                                    <span>User: john.doe@example.com</span>
                                    <span class="mx-2">•</span>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Resolved
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Load More Button -->
            <div class="mt-8 text-center">
                <button class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Load More Activities
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function updateDashboardTime() {
        // Create a date object for Philippine time (UTC+8)
        const now = new Date();
        
        // Format time options
        const timeOptions = {
            timeZone: 'Asia/Manila',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: true
        };
        
        // Format date options
        const dateOptions = {
            timeZone: 'Asia/Manila',
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        };
        
        // Get formatted time and date
        const timeString = now.toLocaleString('en-PH', timeOptions);
        const dateString = now.toLocaleString('en-PH', dateOptions);
        
        // Update the elements
        const timeElement = document.getElementById('current-time');
        const dateElement = document.getElementById('current-date');
        
        if (timeElement) {
            timeElement.textContent = timeString;
        }
        
        if (dateElement) {
            dateElement.textContent = dateString;
        }
    }
    
    // Initialize time display when DOM is loaded
    document.addEventListener('DOMContentLoaded', function() {
        updateDashboardTime();
        
        // Update time every second
        setInterval(updateDashboardTime, 1000);
    });
</script>
@endsection