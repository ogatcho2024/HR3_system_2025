@extends('dashboard')

@section('title', 'Attendance & Time Tracking')

@push('styles')
<style>
    [x-cloak] { display: none !important; }
</style>
@endpush

@section('content')
<div class="py-2 px-3 md:p-6 max-w-full bg-gray-300" x-data="attendanceTracker()" x-init="init()">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-3xl font-bold text-gray-900">Attendance & Time Tracking</h3>
            </div>
        </div>
    </div>

    <!-- Overview Tab -->
    <div x-show="activeTab === 'overview'" class="space-y-6">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            <!-- Present Today -->
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg shadow-md p-4 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-100 text-lg">Present Today</p>
                        <p class="text-2xl font-bold" x-text="attendanceData.todayPresent || 0"></p>
                        <p class="text-blue-200 text-xs mt-0.5">Present employees</p>
                    </div>
                    <div class="p-2 bg-blue-400 rounded-full">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Late Arrivals -->
            <div class="bg-blue-500 rounded-lg shadow-md p-4 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-100 text-lg">Late Arrivals</p>
                        <p class="text-2xl font-bold" x-text="attendanceData.todayLate || 0"></p>
                        <p class="text-blue-200 text-xs mt-0.5">Late employees</p>
                    </div>
                    <div class="p-2 bg-blue-400 rounded-full">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- On Break -->
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg shadow-md p-4 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-100 text-lg">On Break</p>
                        <p class="text-2xl font-bold" x-text="attendanceData.onBreak || 0"></p>
                        <p class="text-blue-200 text-xs mt-0.5">On break employees</p>
                    </div>
                    <div class="p-2 bg-blue-400 rounded-full">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Absent Today -->
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg shadow-md p-4 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-100 text-lg">Absent Today</p>
                        <p class="text-2xl font-bold" x-text="attendanceData.todayAbsent || 0"></p>
                        <p class="text-blue-200 text-xs mt-0.5">Absent employees</p>
                    </div>
                    <div class="p-2 bg-blue-400 rounded-full">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Overtime Today -->
            <div class="bg-blue-500 to-blue-600 rounded-lg shadow-md p-4 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-100 text-lg">Overtime Today</p>
                        <p class="text-2xl font-bold" x-text="attendanceData.overtimeToday || 0"></p>
                        <p class="text-blue-200 text-xs mt-0.5">Overtime hours</p>
                    </div>
                    <div class="p-2 bg-blue-400 rounded-full">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Employees -->
            <div class="bg-blue-500 to-blue-600 rounded-lg shadow-md p-4 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-100 text-lg">Total Employees</p>
                        <p class="text-2xl font-bold" x-text="attendanceData.totalEmployees || 0"></p>
                        <p class="text-blue-200 text-xs mt-0.5">Company workforce</p>
                    </div>
                    <div class="p-2 bg-blue-400 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7 m10 0v-2c0-.656-.126-1.283-.356-1.857 M7 20H2v-2a3 3 0 015.356-1.857 M7 20v-2c0-.656.126-1.283.356-1.857 m0 0a5.002 5.002 0 019.288 0 M15 7a3 3 0 11-6 0 3 3 0 016 0 m6 3a2 2 0 11-4 0 2 2 0 014 0 M7 10a2 2 0 11-4 0 2 2 0 014 0" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Time Clock Management Section -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-xl font-bold text-gray-900 mb-6">Clock In/Out Overview</h3><br>
            
            <!-- Clock In/Out Statistics Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Clocked In Employees -->
                <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl p-6 text-white">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <svg class="w-10 h-10 text-green-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                            </svg>
                        </div>
                        <div class="text-right">
                            <p class="text-green-100 text-lg">Clocked In</p>
                        </div>
                    </div>
                    <div class="text-4xl font-bold mb-2" x-text="attendanceData.clockedIn || 0"></div>
                    <div class="text-xs text-green-200">Currently working</div>
                </div>

                <!-- Clocked Out Employees -->
                <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-xl p-6 text-white">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <svg class="w-10 h-10 text-red-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                            </svg>
                        </div>
                        <div class="text-right">
                            <p class="text-red-100 text-lg">Clocked Out</p>
                        </div>
                    </div>
                    <div class="text-4xl font-bold mb-2" x-text="attendanceData.clockedOut || 0"></div>
                    <div class="text-xs text-red-200">Ended shift today</div>
                </div>

                <!-- Your Status Card -->
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-6 text-white">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <div class="w-10 h-10 bg-blue-200 rounded-full flex items-center justify-center">
                                <div class="w-4 h-4 bg-blue-600 rounded-full animate-pulse"></div>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-blue-100 text-lg">Your Status</p>
                        </div>
                    </div>
                    <div class="text-xl font-bold mb-2">Clocked In</div>
                    <div class="text-xs text-blue-200">Since 08:15 AM</div>
                </div>
            </div>

            <!-- Enhanced Performance Dashboard -->
            <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
                <!-- Today's Performance Summary -->
                <div class="lg:col-span-1 xl:col-span-1 shadow-lg p-6 space-y-4">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4">Today's Performance</h4>
                    <div class="grid grid-cols-1 gap-4">
                        <div class="bg-blue-50 to-blue-100 rounded-lg p-4 border-l-4 border-blue-500">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-700">Total Hours Logged</span>
                                <span class="text-2xl font-bold text-blue-600" x-text="overviewData.totalHours || '0'">0</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-3 mb-2">
                                <div class="bg-blue-500 h-3 rounded-full transition-all duration-300" :style="'width: ' + (overviewData.hoursPercentage || 0) + '%'"></div>
                            </div>
                            <p class="text-xs text-gray-600" x-text="(overviewData.hoursPercentage || 0) + '% of expected hours (Target: ' + (overviewData.expectedHours || 0) + ' hrs)'">0% of expected hours</p>
                        </div>
                        
                        <div class="bg-green-50 to-green-100 rounded-lg p-4 border-l-4 border-green-500">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-700">Average Check-in</span>
                                <span class="text-2xl font-bold text-green-600" x-text="(overviewData.avgCheckIn || '08:00') + ' AM'">08:00 AM</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                </svg>
                                <p class="text-xs text-green-600 font-medium" x-text="(overviewData.minutesEarly || 0) + ' minutes early average'">0 minutes early average</p>
                            </div>
                        </div>

                        <div class="bg-purple-100 to-purple-100 rounded-lg p-4 border-l-4 border-purple-500">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-700">Overtime Hours</span>
                                <span class="text-2xl font-bold text-purple-600" x-text="overviewData.weeklyOvertime || '0'">0</span>
                            </div>
                            <p class="text-xs text-gray-600" x-text="'This week (' + (overviewData.overtimeChange >= 0 ? '+' : '') + (overviewData.overtimeChange || 0) + '% from last week)'">This week (0% from last week)</p>
                        </div>
                        
                        <div class="bg-indigo-50 to-indigo-100 rounded-lg p-4 border-l-4 border-indigo-500">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-700">Productivity Rate</span>
                                <span class="text-2xl font-bold text-indigo-600" x-text="(overviewData.productivityRate || 0) + '%'">0%</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <p class="text-xs text-indigo-600 font-medium" x-text="(overviewData.productivityRate || 0) >= 90 ? 'Above 90% target' : 'Below 90% target'">Below 90% target</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Weekly Trends Card -->
                    <div class="bg-gradient-to-r from-gray-800 to-gray-900 rounded-lg p-4 text-white">
                        <h5 class="text-md font-medium text-gray-100 mb-3 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            Weekly Trends
                        </h5>
                    </div>
                </div>

                <!-- Today's Attendance Summary -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Today's Attendance Summary</h3>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                            <div class="flex items-center space-x-3">
                                <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                                <span class="font-medium text-gray-900">On Time</span>
                            </div>
                            <span class="text-lg font-bold text-green-600" x-text="overviewData.onTime || '0'">0</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg">
                            <div class="flex items-center space-x-3">
                                <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                                <span class="font-medium text-gray-900">Late (5-15 min)</span>
                            </div>
                            <span class="text-lg font-bold text-yellow-600" x-text="overviewData.lateModerate || '0'">0</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-orange-50 rounded-lg">
                            <div class="flex items-center space-x-3">
                                <div class="w-3 h-3 bg-orange-500 rounded-full"></div>
                                <span class="font-medium text-gray-900">Very Late (15+ min)</span>
                            </div>
                            <span class="text-lg font-bold text-orange-600" x-text="overviewData.lateExtreme || '0'">0</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg">
                            <div class="flex items-center space-x-3">
                                <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                                <span class="font-medium text-gray-900">Absent</span>
                            </div>
                            <span class="text-lg font-bold text-red-600" x-text="overviewData.absent || '0'">0</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Real-time Tracking Tab -->
    <div x-show="activeTab === 'realtime'" class="space-y-6">
        <!-- Filter Bar -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Live Attendance Monitor</h3>
                <div class="flex items-center space-x-2">
                    <div class="w-3 h-3 bg-green-400 rounded-full animate-pulse"></div>
                    <span class="text-sm text-gray-600">Live</span>
                </div>
            </div>
            <div class="flex flex-wrap gap-4 items-center">
                <div class="flex space-x-2">
                    <button @click="selectedFilter = 'all'" 
                        :class="selectedFilter === 'all' ? 'bg-gray-800 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'" 
                        class="px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                        <span>All</span>
                        <span class="ml-1" x-text="'(' + (statsData.total || 0) + ')'"></span>
                    </button>
                    <button @click="selectedFilter = 'present'" 
                        :class="selectedFilter === 'present' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'" 
                        class="px-4 py-2 ml-1 rounded-lg text-sm font-medium transition-colors">
                        <span>Present</span>
                        <span class="ml-1" x-text="'(' + (statsData.present || 0) + ')'"></span>
                    </button>
                    <button @click="selectedFilter = 'late'" 
                        :class="selectedFilter === 'late' ? 'bg-yellow-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'" 
                        class="px-4 py-2 ml-1 rounded-lg text-sm font-medium transition-colors">
                        <span>Late</span>
                        <span class="ml-1" x-text="'(' + (statsData.late || 0) + ')'"></span>
                    </button>
                    <button @click="selectedFilter = 'absent'" 
                        :class="selectedFilter === 'absent' ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'" 
                        class="px-4 py-2 ml-1 rounded-lg text-sm font-medium transition-colors">
                        <span>Absent</span>
                        <span class="ml-1" x-text="'(' + (statsData.absent || 0) + ')'"></span>
                    </button>
                    <button @click="selectedFilter = 'break'" 
                        :class="selectedFilter === 'break' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'" 
                        class="px-4 py-2 ml-1 rounded-lg text-sm font-medium transition-colors">
                        <span>On Break</span>
                        <span class="ml-1" x-text="'(' + (statsData.break || 0) + ')'"></span>
                    </button>
                </div>
                <div class="flex space-x-2">
                    <select class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <option>All Departments</option>
                        <option>IT</option>
                        <option>Marketing</option>
                        <option>Finance</option>
                        <option>HR</option>
                        <option>Logistics</option>
                    </select>
                    <input type="search" placeholder="Search employees..." class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                </div>
            </div>
        </div>

        <!-- Employee Table -->
        <div class="bg-white rounded-lg shadow-lg">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Employee Status</h3>
                    <div class="text-sm text-gray-500">
                        <span x-text="employeeData ? employeeData.length : 0"></span> employees 
                        <span x-show="selectedFilter !== 'all'" x-text="'(' + selectedFilter + ')'"></span>
                    </div>
                </div>
            </div>
            
            <!-- Loading State -->
            <div x-show="loading" class="p-6 text-center">
                <div class="animate-spin rounded-full h-8 w-8 border-2 border-blue-500 border-t-transparent mx-auto mb-4"></div>
                <p class="text-gray-500">Loading employee data...</p>
            </div>
            
            <!-- Table -->
            <div x-show="!loading" class="overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check In</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hours</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <!-- Direct iteration over employeeData -->
                            <template x-for="(employee, index) in employeeData" :key="employee.id || index">
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <!-- Employee Info -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center text-white font-medium text-sm" x-text="employee.avatar || 'N/A'"></div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900" x-text="employee.name || 'Unknown'"></div>
                                                <div class="text-sm text-gray-500" x-text="employee.position || 'No Position'"></div>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <!-- Department -->
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="employee.department || 'No Department'"></td>
                                    
                                    <!-- Status -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full" 
                                              :class="{
                                                  'bg-green-100 text-green-800': employee.status === 'present',
                                                  'bg-yellow-100 text-yellow-800': employee.status === 'late', 
                                                  'bg-red-100 text-red-800': employee.status === 'absent',
                                                  'bg-blue-100 text-blue-800': employee.status === 'break',
                                                  'bg-orange-100 text-orange-800': employee.status === 'on_leave',
                                                  'bg-gray-100 text-gray-800': employee.status === 'no_schedule' || employee.status === 'scheduled' || !employee.status
                                              }" 
                                              x-text="
                                                employee.status === 'break' ? 'On Break' :
                                                employee.status === 'no_schedule' ? 'No Schedule' :
                                                employee.status === 'scheduled' ? 'Scheduled' :
                                                employee.status === 'on_leave' ? 'On Leave' :
                                                (employee.status || 'Unknown')
                                              "></span>
                                    </td>
                                    
                                    <!-- Check In -->
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="employee.checkIn || 'Not clocked in'"></td>
                                    
                                    <!-- Hours -->
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="employee.hours ? employee.hours + ' hrs' : 'N/A'"></td>
                                    
                                    <!-- Actions -->
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <button
                                                @click="openAttendanceView(employee)"
                                                :disabled="!employee.attendance_id"
                                                :class="employee.attendance_id ? 'text-blue-600 hover:text-blue-900' : 'text-gray-400 cursor-not-allowed'"
                                                class="transition-colors">
                                                View
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                            
                            <!-- No data available message -->
                            <tr x-show="!employeeData || employeeData.length === 0">
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="text-gray-500">
                                        <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        <p class="text-lg font-medium mb-1">No Employee Data</p>
                                        <p class="text-sm">Employee data is not loading. Check the console for errors.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance View Modal -->
    <div x-cloak x-show="showAttendanceViewModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="closeAttendanceView()">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl mx-4">
            <div class="flex items-center justify-between border-b px-6 py-4">
                <h3 class="text-lg font-semibold text-gray-900">Attendance Details</h3>
                <button class="text-gray-500 hover:text-gray-700" @click="closeAttendanceView()">
                    <span class="sr-only">Close</span>
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="px-6 py-4">
                <template x-if="viewLoading">
                    <div class="text-center py-6 text-gray-600">Loading attendance details...</div>
                </template>
                <template x-if="viewError">
                    <div class="text-center py-6 text-red-600" x-text="viewError"></div>
                </template>
                <template x-if="!viewLoading && !viewError && viewRecord">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="text-gray-500">Employee</p>
                            <p class="font-medium text-gray-900" x-text="viewRecord.employee_name || 'Unknown'"></p>
                        </div>
                        <div>
                            <p class="text-gray-500">Employee ID</p>
                            <p class="font-medium text-gray-900" x-text="viewRecord.employee_id || 'N/A'"></p>
                        </div>
                        <div>
                            <p class="text-gray-500">Department</p>
                            <p class="font-medium text-gray-900" x-text="viewRecord.department || 'N/A'"></p>
                        </div>
                        <div>
                            <p class="text-gray-500">Position</p>
                            <p class="font-medium text-gray-900" x-text="viewRecord.position || 'N/A'"></p>
                        </div>
                        <div>
                            <p class="text-gray-500">Date</p>
                            <p class="font-medium text-gray-900" x-text="viewRecord.date || 'N/A'"></p>
                        </div>
                        <div>
                            <p class="text-gray-500">Status</p>
                            <p class="font-medium text-gray-900" x-text="viewRecord.status || 'N/A'"></p>
                        </div>
                        <div>
                            <p class="text-gray-500">Time Start</p>
                            <p class="font-medium text-gray-900" x-text="viewRecord.clock_in_time || 'N/A'"></p>
                        </div>
                        <div>
                            <p class="text-gray-500">Time End</p>
                            <p class="font-medium text-gray-900" x-text="viewRecord.clock_out_time || 'N/A'"></p>
                        </div>
                        <div>
                            <p class="text-gray-500">Break Start</p>
                            <p class="font-medium text-gray-900" x-text="viewRecord.break_start || 'N/A'"></p>
                        </div>
                        <div>
                            <p class="text-gray-500">Break End</p>
                            <p class="font-medium text-gray-900" x-text="viewRecord.break_end || 'N/A'"></p>
                        </div>
                        <div>
                            <p class="text-gray-500">Total Hours</p>
                            <p class="font-medium text-gray-900" x-text="viewRecord.hours_worked ?? 'N/A'"></p>
                        </div>
                        <div>
                            <p class="text-gray-500">Overtime Hours</p>
                            <p class="font-medium text-gray-900" x-text="viewRecord.overtime_hours ?? 'N/A'"></p>
                        </div>
                        <div class="md:col-span-2">
                            <p class="text-gray-500">Notes</p>
                            <p class="font-medium text-gray-900" x-text="viewRecord.notes || 'N/A'"></p>
                        </div>
                    </div>
                </template>
            </div>
            <div class="flex justify-end border-t px-6 py-4">
                <button class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200" @click="closeAttendanceView()">Close</button>
            </div>
        </div>
    </div>

    <!-- Clock In/Out Tab -->
    <div x-show="activeTab === 'clockinout'" class="space-y-6" x-init="
        if (!this.searchQuery) this.searchQuery = '';
        if (!this.selectedEmployees) this.selectedEmployees = [];
    ">
        <!-- Header Section -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-xl font-semibold text-gray-900">Employee Clock In/Out Management</h3>
                    <p class="text-gray-600 mt-1">Manage individual employee clock actions and monitor real-time activity</p>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-3 h-3 bg-green-400 rounded-full animate-pulse"></div>
                    <span class="text-sm text-gray-600">Live Updates</span>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg p-4 text-white text-center">
                    <div class="text-2xl font-bold" x-text="attendanceData.clockedIn || 0"></div>
                    <div class="text-sm text-green-200">Currently Clocked In</div>
                </div>
                <div class="bg-gradient-to-r from-red-500 to-red-600 rounded-lg p-4 text-white text-center">
                    <div class="text-2xl font-bold" x-text="attendanceData.clockedOut || 0"></div>
                    <div class="text-sm text-red-200">Clocked Out Today</div>
                </div>
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg p-4 text-white text-center">
                    <div class="text-2xl font-bold" x-text="attendanceData.onBreak || 0"></div>
                    <div class="text-sm text-blue-200">On Break</div>
                </div>
                <div class="bg-yellow-500 to-yellow-600 rounded-lg p-4 text-white text-center">
                    <div class="text-2xl font-bold" x-text="attendanceData.totalEmployees || 0"></div>
                    <div class="text-sm text-yellow-200">Total Employees</div>
                </div>
            </div>

            <!-- Search and Bulk Actions -->
            <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
                <div class="flex-1 max-w-md">
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <input 
                            x-model="searchQuery" 
                            type="search" 
                            placeholder="Search employees by name, department, or position..." 
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                </div>
                <div class="flex flex-wrap gap-3">
                    <button @click="openQrScanner()" class="flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-purple-600 to-indigo-600 text-white font-semibold rounded-lg transition-all shadow-md">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                        </svg>
                        <span class="whitespace-nowrap">Scan QR Code</span>
                    </button>
                    <a href="{{ route('attendance.manual-entry') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 active:bg-blue-800 transition-colors shadow-md border border-blue-600 hover:border-blue-700" style="display: inline-flex !important; visibility: visible !important; opacity: 1 !important; background-color: #2563eb !important; color: #ffffff !important; position: relative !important; z-index: 10 !important;">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #ffffff !important;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <span class="whitespace-nowrap" style="color: #ffffff !important;">Manual Entry</span>
                    </a>
                </div>
            </div>

            <!-- Bulk Actions Panel -->
            <div x-show="showBulkActions" x-transition class="mt-4 p-4 bg-gray-50 rounded-lg border">
                <div class="flex flex-wrap gap-3 items-center">
                    <span class="text-sm font-medium text-gray-700">Bulk Actions:</span>
                    <button @click="bulkClockIn()" class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed" :disabled="bulkActionProcessing">
                        <span x-show="!bulkActionProcessing">Clock In Selected</span>
                        <span x-show="bulkActionProcessing">Processing...</span>
                    </button>
                    <button @click="bulkClockOut()" class="bg-red-600 text-white px-3 py-1 rounded text-sm hover:bg-red-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed" :disabled="bulkActionProcessing">
                        <span x-show="!bulkActionProcessing">Clock Out Selected</span>
                        <span x-show="bulkActionProcessing">Processing...</span>
                    </button>
                    <button @click="bulkStartBreak()" class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed" :disabled="bulkActionProcessing">
                        <span x-show="!bulkActionProcessing">Start Break</span>
                        <span x-show="bulkActionProcessing">Processing...</span>
                    </button>
                    <button @click="bulkEndBreak()" class="bg-yellow-600 text-white px-3 py-1 rounded text-sm hover:bg-yellow-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed" :disabled="bulkActionProcessing">
                        <span x-show="!bulkActionProcessing">End Break</span>
                        <span x-show="bulkActionProcessing">Processing...</span>
                    </button>
                    <span class="text-sm text-gray-500 ml-auto" x-text="selectedEmployees.length + ' employee(s) selected'"></span>
                </div>
                
                <!-- Bulk Action Feedback Messages -->
                <div x-show="bulkActionMessage" x-transition class="mt-3 p-3 rounded-lg" :class="{
                    'bg-green-100 text-green-800': bulkActionSuccess,
                    'bg-red-100 text-red-800': !bulkActionSuccess
                }">
                    <p class="text-sm font-medium" x-text="bulkActionMessage"></p>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 gap-6">
            <!-- Employee Directory Table -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Employee Directory</h3>
                        <div class="text-sm text-gray-600">
                            <span class="font-medium" x-text="clockInOutFilteredEmployees.length"></span> 
                            <span class="text-gray-500">of</span> 
                            <span class="font-medium" x-text="employeeData.length"></span> 
                            <span class="text-gray-500">employees</span>
                        </div>
                    </div>
                </div>
                
                <!-- Table Container -->
                <div class="overflow-x-auto">
                    <div class="max-h-[600px] overflow-y-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-100 sticky top-0 z-10">
                                <tr>
                                    <th scope="col" class="w-12 px-4 py-3">
                                        <input 
                                            type="checkbox" 
                                            @change="selectedEmployees = $event.target.checked ? clockInOutFilteredEmployees.map(e => e.id) : []" 
                                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                            title="Select all">
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                        Name
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                        Department
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                        Hours
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <template x-for="employee in clockInOutFilteredEmployees" :key="employee.id">
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <!-- Checkbox Column -->
                                        <td class="px-4 py-4">
                                            <input 
                                                type="checkbox" 
                                                :value="employee.id" 
                                                x-model="selectedEmployees" 
                                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                        </td>
                                        
                                        <!-- Name Column -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center space-x-3">
                                                <div :class="'w-10 h-10 bg-' + employee.color + '-500 rounded-full flex items-center justify-center text-white font-semibold text-sm shadow-sm'" x-text="employee.avatar"></div>
                                                <div>
                                                    <div class="text-sm font-semibold text-gray-900" x-text="employee.name"></div>
                                                    <div class="text-xs text-gray-500" x-text="employee.position"></div>
                                                </div>
                                            </div>
                                        </td>
                                        
                                        <!-- Department Column -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900" x-text="employee.department"></div>
                                        </td>
                                        
                                        <!-- Status Column -->
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <span :class="{
                                                'bg-green-100 text-green-800 ring-1 ring-green-600/20': employee.status === 'present',
                                                'bg-yellow-100 text-yellow-800 ring-1 ring-yellow-600/20': employee.status === 'late',
                                                'bg-red-100 text-red-800 ring-1 ring-red-600/20': employee.status === 'absent',
                                                'bg-blue-100 text-blue-800 ring-1 ring-blue-600/20': employee.status === 'break',
                                                'bg-orange-100 text-orange-800 ring-1 ring-orange-600/20': employee.status === 'on_leave',
                                                'bg-gray-100 text-gray-800 ring-1 ring-gray-600/20': employee.status === 'no_schedule' || employee.status === 'scheduled'
                                            }" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize">
                                                <span class="w-1.5 h-1.5 mr-1.5 rounded-full" :class="{
                                                    'bg-green-600': employee.status === 'present',
                                                    'bg-yellow-600': employee.status === 'late',
                                                    'bg-red-600': employee.status === 'absent',
                                                    'bg-blue-600': employee.status === 'break',
                                                    'bg-orange-600': employee.status === 'on_leave',
                                                    'bg-gray-600': employee.status === 'no_schedule' || employee.status === 'scheduled'
                                                }"></span>
                                                <span x-text="
                                                    employee.status === 'break' ? 'On Break' :
                                                    employee.status === 'no_schedule' ? 'No Schedule' :
                                                    employee.status === 'scheduled' ? 'Scheduled' :
                                                    employee.status === 'on_leave' ? 'On Leave' :
                                                    employee.status
                                                "></span>
                                            </span>
                                        </td>
                                        
                                        <!-- Hours Column -->
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <div class="space-y-1">
                                                <div class="text-xs text-gray-500">
                                                    <span class="font-medium">In:</span> 
                                                    <span class="text-gray-900" x-text="employee.checkIn || '--:--'"></span>
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    <span class="font-medium">Total:</span> 
                                                    <span class="text-gray-900 font-semibold" x-text="employee.hours ? employee.hours + 'h' : '--'"></span>
                                                </div>
                                            </div>
                                        </td>
                                        
                                        <!-- Actions Column -->
                                        <td class="px-6 py-4 whitespace-nowrap text-right">
                                            <div class="flex justify-end space-x-2">
                                                <!-- Clock In Button (Absent) -->
                                                <template x-if="employee.status === 'absent'">
                                                    <button @click="singleClockIn(employee.id)" class="inline-flex items-center px-3 py-1.5 bg-green-600 text-white text-xs font-medium rounded-md hover:bg-green-700 transition-colors shadow-sm disabled:opacity-50 disabled:cursor-not-allowed" :disabled="employee.processing || false">
                                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                                                        </svg>
                                                        <span x-show="!(employee.processing || false)">Clock In</span>
                                                        <span x-show="employee.processing || false" x-cloak>...</span>
                                                    </button>
                                                </template>
                                                
                                                <!-- Break & Clock Out Buttons (Present/Late) -->
                                                <template x-if="employee.status === 'present' || employee.status === 'late'">
                                                    <div class="flex space-x-2">
                                                        <button @click="singleStartBreak(employee.id)" class="inline-flex items-center px-2.5 py-1.5 bg-blue-600 text-white text-xs font-medium rounded-md hover:bg-blue-700 transition-colors shadow-sm disabled:opacity-50 disabled:cursor-not-allowed" :disabled="employee.processing || false">
                                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                            </svg>
                                                            <span x-show="!(employee.processing || false)">Break</span>
                                                            <span x-show="employee.processing || false" x-cloak>...</span>
                                                        </button>
                                                        <button @click="singleClockOut(employee.id)" class="inline-flex items-center px-2.5 py-1.5 bg-red-600 text-white text-xs font-medium rounded-md hover:bg-red-700 transition-colors shadow-sm disabled:opacity-50 disabled:cursor-not-allowed" :disabled="employee.processing || false">
                                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                                                            </svg>
                                                            <span x-show="!(employee.processing || false)">Clock Out</span>
                                                            <span x-show="employee.processing || false" x-cloak>...</span>
                                                        </button>
                                                    </div>
                                                </template>
                                                
                                                <!-- End Break Button (On Break) -->
                                                <template x-if="employee.status === 'break'">
                                                    <button @click="singleEndBreak(employee.id)" class="inline-flex items-center px-3 py-1.5 bg-green-600 text-white text-xs font-medium rounded-md hover:bg-green-700 transition-colors shadow-sm disabled:opacity-50 disabled:cursor-not-allowed" :disabled="employee.processing || false">
                                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                        </svg>
                                                        <span x-show="!(employee.processing || false)">End Break</span>
                                                        <span x-show="employee.processing || false" x-cloak>...</span>
                                                    </button>
                                                </template>
                                                
                                                <!-- More Options Button -->
                                                <button class="inline-flex items-center px-2 py-1.5 bg-gray-100 text-gray-700 text-xs font-medium rounded-md hover:bg-gray-200 transition-colors" title="More options">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                        
                        <!-- No Data Available -->
                        <div x-show="clockInOutFilteredEmployees.length === 0" class="p-12 text-center">
                            <svg class="mx-auto h-16 w-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            <p class="text-lg font-medium text-gray-900 mb-1">No Employees Found</p>
                            <p class="text-sm text-gray-500">No employee data matches your current search. Try adjusting your filters.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reports & Analytics Tab -->
    <div x-show="activeTab === 'reports'" class="space-y-6" x-data="{
        reportView: 'daily',
        selectedDepartment: 'all',
        selectedEmployee: 'all',
        currentDate: new Date(),
        analyticsLoading: false,
        dailyData: {
            date: 'Loading...',
            totalEmployees: 0,
            present: 0,
            late: 0,
            absent: 0,
            onBreak: 0,
            avgCheckIn: '00:00',
            overtime: 0,
            undertime: 0
        },
        weeklyData: {
            weekOf: 'Loading...',
            totalHours: 0,
            avgDaily: { present: 0, late: 0, absent: 0 },
            bestDay: 'Loading...',
            worstDay: 'Loading...',
            overtimeHours: 0,
            undertimeHours: 0
        },
        monthlyData: {
            month: 'Loading...',
            workingDays: 0,
            totalHours: 0,
            avgAttendance: '0%',
            perfectAttendance: 0,
            lateInstances: 0,
            absentDays: 0,
            overtimeHours: 0
        },
        yearlyData: {
            year: '2024',
            workingDays: 0,
            totalHours: 0,
            avgAttendance: '0%',
            bestMonth: 'Loading...',
            worstMonth: 'Loading...',
            totalOvertime: 0,
            holidaysPaid: 0
        },
        
        // Function to load analytics data
        async loadAnalyticsData(period = null) {
            if (!period) period = this.reportView;
            this.analyticsLoading = true;
            
            try {
                const response = await fetch(`${getApiBaseUrl()}/attendance/analytics-data?period=${period}&department=${this.selectedDepartment}&employee=${this.selectedEmployee}`);
                const data = await response.json();
                
                if (data.success) {
                    // Update data based on period
                    if (period === 'daily') {
                        this.dailyData = {
                            date: data.data.date || 'Today',
                            totalEmployees: data.data.totalEmployees || 0,
                            present: data.data.present || 0,
                            late: data.data.late || 0,
                            absent: data.data.absent || 0,
                            onBreak: data.data.onBreak || 0,
                            avgCheckIn: data.data.avgCheckIn || '00:00',
                            overtime: data.data.overtime || 0,
                            undertime: data.data.undertime || 0
                        };
                    } else if (period === 'weekly') {
                        this.weeklyData = {
                            weekOf: data.data.weekOf || 'This Week',
                            totalHours: data.data.totalHours || 0,
                            avgDaily: data.data.avgDaily || { present: 0, late: 0, absent: 0 },
                            bestDay: data.data.bestDay || 'N/A',
                            worstDay: data.data.worstDay || 'N/A',
                            overtimeHours: data.data.overtimeHours || 0,
                            undertimeHours: data.data.undertimeHours || 0
                        };
                    } else if (period === 'monthly') {
                        this.monthlyData = {
                            month: data.data.month || 'This Month',
                            workingDays: data.data.workingDays || 0,
                            totalHours: data.data.totalHours || 0,
                            avgAttendance: data.data.avgAttendance || '0%',
                            perfectAttendance: data.data.perfectAttendance || 0,
                            lateInstances: data.data.lateInstances || 0,
                            absentDays: data.data.absentDays || 0,
                            overtimeHours: data.data.overtimeHours || 0
                        };
                    } else if (period === 'yearly') {
                        this.yearlyData = {
                            year: data.data.year || '2024',
                            workingDays: data.data.workingDays || 0,
                            totalHours: data.data.totalHours || 0,
                            avgAttendance: data.data.avgAttendance || '0%',
                            bestMonth: data.data.bestMonth || 'N/A',
                            worstMonth: data.data.worstMonth || 'N/A',
                            totalOvertime: data.data.totalOvertime || 0,
                            holidaysPaid: data.data.holidaysPaid || 0
                        };
                    }
                } else {
                    console.error('Failed to load analytics data:', data.message);
                }
            } catch (error) {
                console.error('Error loading analytics data:', error);
            } finally {
                this.analyticsLoading = false;
            }
        },
        
        // PDF Download Methods
        async downloadDailyPDF() {
            try {
                const response = await fetch(getApiBaseUrl() + '/attendance/export-daily-pdf?date=' + new Date().toISOString().split('T')[0]);
                if (response.ok) {
                    const contentType = response.headers.get('content-type');
                    if (contentType && contentType.includes('application/pdf')) {
                        const blob = await response.blob();
                        const url = window.URL.createObjectURL(blob);
                        const link = document.createElement('a');
                        link.href = url;
                        link.download = 'daily-attendance-report-' + new Date().toISOString().split('T')[0] + '.pdf';
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                        window.URL.revokeObjectURL(url);
                    } else {
                        // Response is likely JSON error message
                        const errorData = await response.json();
                        console.error('PDF Error Response:', errorData);
                        alert('Failed to generate PDF: ' + (errorData.error || 'Unknown error'));
                    }
                } else {
                    const errorText = await response.text();
                    console.error('HTTP Error:', response.status, errorText);
                    alert('Failed to generate PDF report. HTTP ' + response.status + ': ' + errorText);
                }
            } catch (error) {
                console.error('Error downloading PDF:', error);
                alert('Error downloading PDF: ' + error.message);
            }
        },
        
        async downloadWeeklyPDF() {
            try {
                const response = await fetch(getApiBaseUrl() + '/attendance/export-weekly-pdf?date=' + new Date().toISOString().split('T')[0]);
                if (response.ok) {
                    const contentType = response.headers.get('content-type');
                    if (contentType && contentType.includes('application/pdf')) {
                        const blob = await response.blob();
                        const url = window.URL.createObjectURL(blob);
                        const link = document.createElement('a');
                        link.href = url;
                        link.download = 'weekly-attendance-report-' + new Date().toISOString().split('T')[0] + '.pdf';
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                        window.URL.revokeObjectURL(url);
                    } else {
                        const errorData = await response.json();
                        console.error('PDF Error Response:', errorData);
                        alert('Failed to generate PDF: ' + (errorData.error || 'Unknown error'));
                    }
                } else {
                    const errorText = await response.text();
                    console.error('HTTP Error:', response.status, errorText);
                    alert('Failed to generate PDF report. HTTP ' + response.status + ': ' + errorText);
                }
            } catch (error) {
                console.error('Error downloading PDF:', error);
                alert('Error downloading PDF: ' + error.message);
            }
        },
        
        async downloadMonthlyPDF() {
            try {
                const response = await fetch(getApiBaseUrl() + '/attendance/export-monthly-pdf?date=' + new Date().toISOString().split('T')[0]);
                if (response.ok) {
                    const contentType = response.headers.get('content-type');
                    if (contentType && contentType.includes('application/pdf')) {
                        const blob = await response.blob();
                        const url = window.URL.createObjectURL(blob);
                        const link = document.createElement('a');
                        link.href = url;
                        link.download = 'monthly-attendance-report-' + new Date().toISOString().split('T')[0] + '.pdf';
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                        window.URL.revokeObjectURL(url);
                    } else {
                        const errorData = await response.json();
                        console.error('PDF Error Response:', errorData);
                        alert('Failed to generate PDF: ' + (errorData.error || 'Unknown error'));
                    }
                } else {
                    const errorText = await response.text();
                    console.error('HTTP Error:', response.status, errorText);
                    alert('Failed to generate PDF report. HTTP ' + response.status + ': ' + errorText);
                }
            } catch (error) {
                console.error('Error downloading PDF:', error);
                alert('Error downloading PDF: ' + error.message);
            }
        },
        
        async downloadYearlyPDF() {
            try {
                const response = await fetch(getApiBaseUrl() + '/attendance/export-yearly-pdf?date=' + new Date().toISOString().split('T')[0]);
                if (response.ok) {
                    const contentType = response.headers.get('content-type');
                    if (contentType && contentType.includes('application/pdf')) {
                        const blob = await response.blob();
                        const url = window.URL.createObjectURL(blob);
                        const link = document.createElement('a');
                        link.href = url;
                        link.download = 'yearly-attendance-report-' + new Date().getFullYear() + '.pdf';
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                        window.URL.revokeObjectURL(url);
                    } else {
                        const errorData = await response.json();
                        console.error('PDF Error Response:', errorData);
                        alert('Failed to generate PDF: ' + (errorData.error || 'Unknown error'));
                    }
                } else {
                    const errorText = await response.text();
                    console.error('HTTP Error:', response.status, errorText);
                    alert('Failed to generate PDF report. HTTP ' + response.status + ': ' + errorText);
                }
            } catch (error) {
                console.error('Error downloading PDF:', error);
                alert('Error downloading PDF: ' + error.message);
            }
        }
    }" x-init="
        // Load initial data
        loadAnalyticsData();
        
        // Watch for report view changes
        $watch('reportView', (newValue) => {
            loadAnalyticsData(newValue);
        });
        
        // Watch for filter changes
        $watch('selectedDepartment', () => {
            loadAnalyticsData();
        });
        $watch('selectedEmployee', () => {
            loadAnalyticsData();
        });
    ">
        <!-- Report Period Navigation -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-semibold text-gray-900">Attendance Reports & Analytics</h3>
                <!-- Loading Indicator -->
                <div x-show="analyticsLoading" class="flex items-center space-x-2">
                    <div class="animate-spin rounded-full h-4 w-4 border-2 border-blue-500 border-t-transparent"></div>
                    <span class="text-sm text-gray-600">Loading...</span>
                </div>
            </div>
            
            <!-- Period Selector -->
            <div class="flex flex-wrap gap-2 mb-6">
                <button @click="reportView = 'daily'" 
                    :class="reportView === 'daily' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'" 
                    :disabled="analyticsLoading"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition-colors disabled:opacity-50">
                    Daily Report
                </button>
                <button @click="reportView = 'weekly'" 
                    :class="reportView === 'weekly' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'" 
                    :disabled="analyticsLoading"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition-colors disabled:opacity-50">
                    Weekly Report
                </button>
                <button @click="reportView = 'monthly'" 
                    :class="reportView === 'monthly' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'" 
                    :disabled="analyticsLoading"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition-colors disabled:opacity-50">
                    Monthly Report
                </button>
                <button @click="reportView = 'yearly'" 
                    :class="reportView === 'yearly' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'" 
                    :disabled="analyticsLoading"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition-colors disabled:opacity-50">
                    Yearly Report
                </button>
            </div>

            <!-- Filters -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                    <select x-model="selectedDepartment" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="all">Core Transaction</option>
                        <option value="it">Admin</option>
                        <option value="marketing">Financials</option>
                        <option value="finance">Human Resources</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Employee</label>
                    <select x-model="selectedEmployee" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="all">All Employees</option>
                        <option value="john-smith">John Smith</option>
                        <option value="sarah-johnson">Sarah Johnson</option>
                        <option value="mike-davis">Mike Davis</option>
                        <option value="emily-brown">Emily Brown</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button class="w-full bg-gray-600 text-white py-2 px-4 rounded-lg hover:bg-gray-700 transition-colors">
                        Apply Filters
                    </button>
                </div>
            </div>
        </div>

        <!-- Daily Report -->
        <div x-show="reportView === 'daily'" class="space-y-6">
            <!-- Daily Summary Cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg shadow-md p-4 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-100 text-lg">Present Today</p>
                            <p class="text-2xl font-bold" x-text="dailyData.present"></p>
                            <p class="text-green-200 text-xs" x-text="Math.round((dailyData.present/dailyData.totalEmployees)*100) + '% attendance'"></p>
                        </div>
                        <div class="p-2 bg-green-400 rounded-full">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="bg-yellow-500 to-yellow-600 rounded-lg shadow-md p-4 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-yellow-100 text-xs">Late Arrivals</p>
                            <p class="text-2xl font-bold" x-text="dailyData.late"></p>
                            <p class="text-yellow-200 text-xs" x-text="Math.round((dailyData.late/dailyData.present)*100) + '% of present'"></p>
                        </div>
                        <div class="p-2 bg-yellow-400 rounded-full">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="bg-gradient-to-r from-red-500 to-red-600 rounded-lg shadow-md p-4 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-red-100 text-xs">Absent Today</p>
                            <p class="text-2xl font-bold" x-text="dailyData.absent"></p>
                            <p class="text-red-200 text-xs" x-text="Math.round((dailyData.absent/dailyData.totalEmployees)*100) + '% of workforce'"></p>
                        </div>
                        <div class="p-2 bg-red-400 rounded-full">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg shadow-md p-4 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-purple-100 text-xs">Overtime Hours</p>
                            <p class="text-2xl font-bold" x-text="dailyData.overtime"></p>
                            <p class="text-purple-200 text-xs">Extra hours logged</p>
                        </div>
                        <div class="p-2 bg-purple-400 rounded-full">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Daily Detailed Report -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex items-center justify-between mb-6">
                    <h4 class="text-lg font-semibold text-gray-900" x-text="'Daily Report - ' + dailyData.date"></h4>
                    <div class="flex space-x-2" x-data="pdfExporter()">
                        <button @click="downloadPDF('daily')" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors flex items-center space-x-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <span>Export PDF</span>
                        </button>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <h5 class="font-medium text-gray-900">Attendance Summary</h5>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                <span class="text-sm text-gray-700">Total Employees</span>
                                <span class="font-semibold" x-text="dailyData.totalEmployees"></span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-green-50 rounded-lg">
                                <span class="text-sm text-gray-700">Present</span>
                                <span class="font-semibold text-green-600" x-text="dailyData.present"></span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-yellow-50 rounded-lg">
                                <span class="text-sm text-gray-700">Late Arrivals</span>
                                <span class="font-semibold text-yellow-600" x-text="dailyData.late"></span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-red-50 rounded-lg">
                                <span class="text-sm text-gray-700">Absent</span>
                                <span class="font-semibold text-red-600" x-text="dailyData.absent"></span>
                            </div>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <h5 class="font-medium text-gray-900">Performance Metrics</h5>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                <span class="text-sm text-gray-700">Average Check-in Time</span>
                                <span class="font-semibold" x-text="dailyData.avgCheckIn + ' AM'"></span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-blue-50 rounded-lg">
                                <span class="text-sm text-gray-700">On Break</span>
                                <span class="font-semibold text-blue-600" x-text="dailyData.onBreak"></span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-purple-50 rounded-lg">
                                <span class="text-sm text-gray-700">Overtime Hours</span>
                                <span class="font-semibold text-purple-600" x-text="dailyData.overtime"></span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-orange-50 rounded-lg">
                                <span class="text-sm text-gray-700">Undertime Cases</span>
                                <span class="font-semibold text-orange-600" x-text="dailyData.undertime"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Weekly Report -->
        <div x-show="reportView === 'weekly'" class="space-y-6">
            <!-- Weekly Summary Cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-indigo-500 to-indigo-600 rounded-lg shadow-md p-4 text-white">
                    <div class="text-center">
                        <p class="text-indigo-100 text-xs">Total Hours</p>
                        <p class="text-2xl font-bold" x-text="weeklyData.totalHours.toLocaleString()"></p>
                        <p class="text-indigo-200 text-xs">This week</p>
                    </div>
                </div>
                <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg shadow-md p-4 text-white">
                    <div class="text-center">
                        <p class="text-green-100 text-xs">Avg Daily Present</p>
                        <p class="text-2xl font-bold" x-text="weeklyData.avgDaily.present"></p>
                        <p class="text-green-200 text-xs">Employees/day</p>
                    </div>
                </div>
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg shadow-md p-4 text-white">
                    <div class="text-center">
                        <p class="text-blue-100 text-xs">Best Day</p>
                        <p class="text-lg font-bold" x-text="weeklyData.bestDay"></p>
                        <p class="text-blue-200 text-xs">Attendance</p>
                    </div>
                </div>
                <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg shadow-md p-4 text-white">
                    <div class="text-center">
                        <p class="text-purple-100 text-xs">Overtime</p>
                        <p class="text-2xl font-bold" x-text="weeklyData.overtimeHours"></p>
                        <p class="text-purple-200 text-xs">Hours</p>
                    </div>
                </div>
            </div>

            <!-- Weekly Chart Placeholder -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex items-center justify-between mb-6">
                    <h4 class="text-lg font-semibold text-gray-900 mb-6" x-text="'Weekly Report - ' + weeklyData.weekOf"></h4>
                    <div class="flex space-x-2" x-data="pdfExporter()">
                        <button @click="downloadPDF('weekly')" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors flex items-center space-x-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <span>Export PDF</span>
                        </button>
                    </div>
                </div>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div>
                        <h5 class="font-medium text-gray-900 mb-4">Daily Breakdown</h5>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <span class="text-sm font-medium">Monday</span>
                                <div class="flex items-center space-x-2">
                                    <div class="text-sm text-green-600">138 present</div>
                                    <div class="text-sm text-red-600">12 absent</div>
                                </div>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <span class="text-sm font-medium">Tuesday</span>
                                <div class="flex items-center space-x-2">
                                    <div class="text-sm text-green-600">142 present</div>
                                    <div class="text-sm text-red-600">8 absent</div>
                                </div>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <span class="text-sm font-medium">Wednesday</span>
                                <div class="flex items-center space-x-2">
                                    <div class="text-sm text-green-600">145 present</div>
                                    <div class="text-sm text-red-600">5 absent</div>
                                </div>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <span class="text-sm font-medium">Thursday</span>
                                <div class="flex items-center space-x-2">
                                    <div class="text-sm text-green-600">140 present</div>
                                    <div class="text-sm text-red-600">10 absent</div>
                                </div>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <span class="text-sm font-medium">Friday</span>
                                <div class="flex items-center space-x-2">
                                    <div class="text-sm text-green-600">135 present</div>
                                    <div class="text-sm text-red-600">15 absent</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <h5 class="font-medium text-gray-900 mb-4">Weekly Insights</h5>
                        <div class="space-y-4">
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <div class="flex items-center space-x-2 mb-2">
                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span class="text-sm font-medium text-blue-900">Best Performance</span>
                                </div>
                                <p class="text-sm text-blue-800" x-text="weeklyData.bestDay + ' had the highest attendance rate'"></p>
                            </div>
                            <div class="bg-yellow-50 p-4 rounded-lg">
                                <div class="flex items-center space-x-2 mb-2">
                                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                    </svg>
                                    <span class="text-sm font-medium text-yellow-900">Needs Attention</span>
                                </div>
                                <p class="text-sm text-yellow-800" x-text="weeklyData.worstDay + ' had the lowest attendance'"></p>
                            </div>
                            <div class="bg-green-50 p-4 rounded-lg">
                                <div class="flex items-center space-x-2 mb-2">
                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span class="text-sm font-medium text-green-900">Overtime Stats</span>
                                </div>
                                <p class="text-sm text-green-800" x-text="weeklyData.overtimeHours + ' total overtime hours recorded'"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Report -->
        <div x-show="reportView === 'monthly'" class="space-y-6">
            <!-- Monthly Summary Cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-gradient-to-r from-cyan-500 to-cyan-600 rounded-lg shadow-md p-4 text-white">
                    <div class="text-center">
                        <p class="text-cyan-100 text-xs">Working Days</p>
                        <p class="text-2xl font-bold" x-text="monthlyData.workingDays"></p>
                        <p class="text-cyan-200 text-xs">This month</p>
                    </div>
                </div>
                <div class="bg-gradient-to-r from-emerald-500 to-emerald-600 rounded-lg shadow-md p-4 text-white">
                    <div class="text-center">
                        <p class="text-emerald-100 text-xs">Avg Attendance</p>
                        <p class="text-2xl font-bold" x-text="monthlyData.avgAttendance"></p>
                        <p class="text-emerald-200 text-xs">Monthly average</p>
                    </div>
                </div>
                <div class="bg-violet-500 to-violet-600 rounded-lg shadow-md p-4 text-white">
                    <div class="text-center">
                        <p class="text-violet-100 text-xs">Perfect Attendance</p>
                        <p class="text-2xl font-bold" x-text="monthlyData.perfectAttendance"></p>
                        <p class="text-violet-200 text-xs">Employees</p>
                    </div>
                </div>
                <div class="bg-orange-500 to-orange-600 rounded-lg shadow-md p-4 text-white">
                    <div class="text-center">
                        <p class="text-orange-100 text-xs">Total Hours</p>
                        <p class="text-lg font-bold" x-text="monthlyData.totalHours.toLocaleString()"></p>
                        <p class="text-orange-200 text-xs">Logged hours</p>
                    </div>
                </div>
            </div>

            <!-- Monthly Detailed Report -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex items-center justify-between mb-6">
                    <h4 class="text-lg font-semibold text-gray-900 mb-6" x-text="'Monthly Report - ' + monthlyData.month"></h4>
                    <div class="flex space-x-2" x-data="pdfExporter()">
                        <button @click="downloadPDF('monthly')" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors flex items-center space-x-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <span>Export PDF</span>
                        </button>
                    </div>
                </div>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div>
                        <h5 class="font-medium text-gray-900 mb-4">Attendance Statistics</h5>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                <span class="text-sm text-gray-700">Working Days</span>
                                <span class="font-semibold" x-text="monthlyData.workingDays"></span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-green-50 rounded-lg">
                                <span class="text-sm text-gray-700">Average Attendance</span>
                                <span class="font-semibold text-green-600" x-text="monthlyData.avgAttendance"></span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-blue-50 rounded-lg">
                                <span class="text-sm text-gray-700">Perfect Attendance</span>
                                <span class="font-semibold text-blue-600" x-text="monthlyData.perfectAttendance + ' employees'"></span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <h5 class="font-medium text-gray-900 mb-4">Issue Tracking</h5>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center p-3 bg-yellow-50 rounded-lg">
                                <span class="text-sm text-gray-700">Late Instances</span>
                                <span class="font-semibold text-yellow-600" x-text="monthlyData.lateInstances"></span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-red-50 rounded-lg">
                                <span class="text-sm text-gray-700">Absent Days</span>
                                <span class="font-semibold text-red-600" x-text="monthlyData.absentDays"></span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-orange-50 rounded-lg">
                                <span class="text-sm text-gray-700">Improvement Needed</span>
                                <span class="font-semibold text-orange-600">12 employees</span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <h5 class="font-medium text-gray-900 mb-4">Productivity Metrics</h5>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center p-3 bg-purple-50 rounded-lg">
                                <span class="text-sm text-gray-700">Total Hours</span>
                                <span class="font-semibold text-purple-600" x-text="monthlyData.totalHours.toLocaleString()"></span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-indigo-50 rounded-lg">
                                <span class="text-sm text-gray-700">Overtime Hours</span>
                                <span class="font-semibold text-indigo-600" x-text="monthlyData.overtimeHours"></span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-green-50 rounded-lg">
                                <span class="text-sm text-gray-700">Efficiency Rate</span>
                                <span class="font-semibold text-green-600">97.2%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Yearly Report -->
        <div x-show="reportView === 'yearly'" class="space-y-6">
            <!-- Yearly Summary Cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-slate-500 to-slate-600 rounded-lg shadow-md p-4 text-white">
                    <div class="text-center">
                        <p class="text-slate-100 text-xs">Working Days</p>
                        <p class="text-2xl font-bold" x-text="yearlyData.workingDays"></p>
                        <p class="text-slate-200 text-xs">Total this year</p>
                    </div>
                </div>
                <div class="bg-gradient-to-r from-teal-500 to-teal-600 rounded-lg shadow-md p-4 text-white">
                    <div class="text-center">
                        <p class="text-teal-100 text-xs">Avg Attendance</p>
                        <p class="text-2xl font-bold" x-text="yearlyData.avgAttendance"></p>
                        <p class="text-teal-200 text-xs">Annual average</p>
                    </div>
                </div>
                <div class="bg-rose-500 to-rose-600 rounded-lg shadow-md p-4 text-white">
                    <div class="text-center">
                        <p class="text-rose-100 text-xs">Best Month</p>
                        <p class="text-lg font-bold" x-text="yearlyData.bestMonth"></p>
                        <p class="text-rose-200 text-xs">Performance</p>
                    </div>
                </div>
                <div class="bg-amber-500 to-amber-600 rounded-lg shadow-md p-4 text-white">
                    <div class="text-center">
                        <p class="text-amber-100 text-xs">Total Overtime</p>
                        <p class="text-lg font-bold" x-text="yearlyData.totalOvertime.toLocaleString()"></p>
                        <p class="text-amber-200 text-xs">Hours</p>
                    </div>
                </div>
            </div>

            <!-- Yearly Comprehensive Report -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex items-center justify-between mb-6">
                    <h4 class="text-lg font-semibold text-gray-900 mb-6" x-text="'Annual Report - ' + yearlyData.year"></h4>
                    <div class="flex space-x-2" x-data="pdfExporter()">
                        <button @click="downloadPDF('yearly')" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors flex items-center space-x-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <span>Export PDF</span>
                        </button>
                    </div>
                </div>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <div class="space-y-6">
                        <div>
                            <h5 class="font-medium text-gray-900 mb-4">Annual Overview</h5>
                            <div class="space-y-3">
                                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                    <span class="text-sm text-gray-700">Total Working Days</span>
                                    <span class="font-semibold" x-text="yearlyData.workingDays"></span>
                                </div>
                                <div class="flex justify-between items-center p-3 bg-teal-50 rounded-lg">
                                    <span class="text-sm text-gray-700">Average Attendance</span>
                                    <span class="font-semibold text-teal-600" x-text="yearlyData.avgAttendance"></span>
                                </div>
                                <div class="flex justify-between items-center p-3 bg-slate-50 rounded-lg">
                                    <span class="text-sm text-gray-700">Total Hours Logged</span>
                                    <span class="font-semibold text-slate-600" x-text="yearlyData.totalHours.toLocaleString()"></span>
                                </div>
                                <div class="flex justify-between items-center p-3 bg-amber-50 rounded-lg">
                                    <span class="text-sm text-gray-700">Total Overtime</span>
                                    <span class="font-semibold text-amber-600" x-text="yearlyData.totalOvertime.toLocaleString() + ' hours'"></span>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <h5 class="font-medium text-gray-900 mb-4">Monthly Performance</h5>
                            <div class="space-y-2">
                                <div class="flex justify-between items-center p-2 bg-green-50 rounded">
                                    <span class="text-sm text-gray-700">Best Month</span>
                                    <span class="font-semibold text-green-600" x-text="yearlyData.bestMonth"></span>
                                </div>
                                <div class="flex justify-between items-center p-2 bg-red-50 rounded">
                                    <span class="text-sm text-gray-700">Lowest Month</span>
                                    <span class="font-semibold text-red-600" x-text="yearlyData.worstMonth"></span>
                                </div>
                                <div class="flex justify-between items-center p-2 bg-blue-50 rounded">
                                    <span class="text-sm text-gray-700">Paid Holidays</span>
                                    <span class="font-semibold text-blue-600" x-text="yearlyData.holidaysPaid + ' days'"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="space-y-6">
                        <div>
                            <h5 class="font-medium text-gray-900 mb-4">Annual Insights</h5>
                            <div class="space-y-4">
                                <div class="bg-green-50 p-4 rounded-lg">
                                    <div class="flex items-center space-x-2 mb-2">
                                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                        </svg>
                                        <span class="text-sm font-medium text-green-900">Positive Trends</span>
                                    </div>
                                    <p class="text-sm text-green-800">Attendance improved by 2.3% compared to last year</p>
                                </div>
                                
                                <div class="bg-blue-50 p-4 rounded-lg">
                                    <div class="flex items-center space-x-2 mb-2">
                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                        </svg>
                                        <span class="text-sm font-medium text-blue-900">Peak Performance</span>
                                    </div>
                                    <p class="text-sm text-blue-800">Q2 showed the strongest attendance rates overall</p>
                                </div>
                                
                                <div class="bg-yellow-50 p-4 rounded-lg">
                                    <div class="flex items-center space-x-2 mb-2">
                                        <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                        </svg>
                                        <span class="text-sm font-medium text-yellow-900">Areas for Improvement</span>
                                    </div>
                                    <p class="text-sm text-yellow-800">Monday mornings show 15% higher late arrival rates</p>
                                </div>
                                
                                <div class="bg-purple-50 p-4 rounded-lg">
                                    <div class="flex items-center space-x-2 mb-2">
                                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span class="text-sm font-medium text-purple-900">Overtime Analysis</span>
                                    </div>
                                    <p class="text-sm text-purple-800">Project deadlines drove 68% of overtime hours</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- QR Scanner Modal -->
<div id="qrScannerModal"
     x-show="showQrScanner" 
     x-data="qrScannerModal()"
     @keydown.escape.window="closeScanner()"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 9999; overflow-y: auto; display: none;"
     :style="showQrScanner ? 'display: block !important;' : 'display: none;'">
    <!-- Background Overlay -->
    <div @click="handleOverlayClick($event)" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.75); transition: opacity 0.3s; z-index: 9998;"></div>
    
    <!-- Modal Container - Constrained Height -->
    <div style="display: flex; min-height: 100vh; align-items: center; justify-content: center; padding: 1rem; position: relative; z-index: 9999;">
        <div style="position: relative; background-color: white; border-radius: 0.75rem; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); max-width: 48rem; width: 100%; max-height: 90vh; display: flex; flex-direction: column; overflow: hidden;" @click.stop>
            <!-- Header -->
            <div style="background: linear-gradient(to right, #9333ea, #4f46e5); padding: 1.5rem; border-top-left-radius: 0.75rem; border-top-right-radius: 0.75rem;">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <h3 style="font-size: 1.25rem; font-weight: 700; color: white; margin: 0;">QR Code Scanner</h3>
                        <p style="color: #e9d5ff; font-size: 0.875rem; margin-top: 0.25rem;">Scan employee QR code for attendance</p>
                    </div>
                    <button @click="closeScanner()" style="color: white; background: none; border: none; cursor: pointer; font-size: 1.5rem; padding: 0.5rem; transition: all 0.2s;" title="Close (ESC)" onmouseover="this.style.color='#d1d5db'" onmouseout="this.style.color='white'">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            
            <!-- Content - Scrollable -->
            <div style="padding: 1.5rem; overflow-y: auto; flex: 1 1 auto;">
                <!-- Camera Selection -->
                <div style="margin-bottom: 1rem;" x-show="!isScanning">
                    <label for="qrCameraSelect" class="block text-sm font-medium text-gray-700 mb-2">
                        <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Select Camera
                    </label>
                    <select id="qrCameraSelect" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                            @change="onCameraChange()">
                        <option value="">Loading cameras...</option>
                    </select>
                </div>
                
                <!-- Scanner Container - Full View -->
                <div id="qr-reader" class="rounded-lg border-4 border-gray-200 overflow-hidden bg-black" style="width: 100%; height: 400px; position: relative;"></div>
                
                <!-- Add CSS to ensure QR box visibility -->
                <style>
                    /* Ensure QR scanning box is visible */
                    #qr-reader video {
                        width: 100% !important;
                        height: 100% !important;
                        object-fit: cover !important;
                    }
                    
                    /* Make sure the scanning region outline is visible */
                    #qr-reader canvas {
                        position: absolute !important;
                        top: 0 !important;
                        left: 0 !important;
                    }
                    
                    /* Highlight the QR shaded region */
                    #qr-reader__dashboard_section_csr {
                        opacity: 0.5 !important;
                    }
                    
                    /* Ensure the scan region box is visible */
                    #qr-shaded-region {
                        border: 2px solid rgba(0, 255, 0, 0.5) !important;
                    }
                </style>
                
                <!-- Controls -->
                <div style="margin-top: 1rem; display: flex; gap: 0.75rem; justify-content: center;">
                    <button 
                        x-show="!isScanning" 
                        @click="startScanning()" 
                        style="padding: 0.5rem 1.5rem; background-color: #16a34a; color: white; font-weight: 600; border-radius: 0.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: none; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; transition: all 0.2s;"
                        onmouseover="this.style.backgroundColor='#15803d'" 
                        onmouseout="this.style.backgroundColor='#16a34a'"
                        :disabled="scanProcessing">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Start Scanner
                    </button>
                    <button 
                        x-show="isScanning" 
                        @click="stopScanning()" 
                        style="padding: 0.5rem 1.5rem; background-color: #dc2626; color: white; font-weight: 600; border-radius: 0.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: none; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; transition: all 0.2s;"
                        onmouseover="this.style.backgroundColor='#b91c1c'" 
                        onmouseout="this.style.backgroundColor='#dc2626'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"></path>
                        </svg>
                        Stop Scanner
                    </button>
                </div>
                
                <!-- Status Messages -->
                <div x-show="statusMessage" x-transition class="mt-4 p-4 rounded-lg" :class="{
                    'bg-green-50 border border-green-200 text-green-800': statusType === 'success',
                    'bg-red-50 border border-red-200 text-red-800': statusType === 'error',
                    'bg-blue-50 border border-blue-200 text-blue-800': statusType === 'info',
                    'bg-yellow-50 border border-yellow-200 text-yellow-800': statusType === 'warning'
                }">
                    <p class="text-sm font-medium" x-text="statusMessage"></p>
                </div>
                
                <!-- HTTPS Warning -->
                <div x-show="!isHttps && !isLocalhost" class="mt-4 bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded">
                    <div class="flex">
                        <svg class="h-5 w-5 text-yellow-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700"><strong>HTTPS Required:</strong> Camera access requires a secure connection. Please use HTTPS or access from localhost.</p>
                        </div>
                    </div>
                </div>
                
                <!-- Instructions -->
                <div class="mt-4 bg-blue-50 border-l-4 border-blue-400 p-4 rounded">
                    <div class="flex">
                        <svg class="h-5 w-5 text-blue-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700">
                                <strong>How to use:</strong> Click "Start Scanner" and allow camera access. Position the employee's QR code within the frame. The system will automatically detect and process it.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include html5-qrcode library -->
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<script>
// API Base URL - uses Laravel's url() helper to work in any environment
const ATTENDANCE_API_BASE_URL = '{{ url("") }}';

function attendanceTracker() {
    return {
        // Dynamic API base URL detection
        getApiBaseUrl() {
            // Use the global base URL constant
            return ATTENDANCE_API_BASE_URL;
        },
        
        // Main properties
        activeTab: '{{ request()->get('tab', 'overview') }}',
        currentTime: new Date(),
        attendanceData: {},
        overviewData: {},
        recentActivities: [],
        departmentData: [],
        selectedFilter: 'all',
        employeeData: [],
        statsData: {},
        loading: false,
        overviewLoading: false,
        activitiesLoading: false,
        departmentLoading: false,
        searchQuery: '',
        selectedEmployees: [],
        showBulkActions: false,
        bulkActionProcessing: false,
        bulkActionMessage: '',
        bulkActionSuccess: false,
        showQrScanner: false,
        showAttendanceViewModal: false,
        viewLoading: false,
        viewError: '',
        viewRecord: null,
        
        // QR Scanner methods
        openQrScanner() {
            console.log('[Attendance] Opening QR Scanner modal');
            this.showQrScanner = true;
            
            // FORCE modal visible with direct DOM manipulation
            this.$nextTick(() => {
                const modalEl = document.getElementById('qrScannerModal');
                if (modalEl) {
                    console.log('[Attendance] Force showing modal with inline styles');
                    modalEl.style.display = 'block';
                    modalEl.style.visibility = 'visible';
                    modalEl.style.opacity = '1';
                    modalEl.style.position = 'fixed';
                    modalEl.style.top = '0';
                    modalEl.style.left = '0';
                    modalEl.style.right = '0';
                    modalEl.style.bottom = '0';
                    modalEl.style.zIndex = '9999';
                }
                
                setTimeout(() => {
                    console.log('[Attendance] Triggering camera initialization');
                    // Find the QR scanner component and initialize cameras
                    const scanner = document.querySelector('[x-data*="qrScannerModal"]');
                    if (scanner && scanner._x_dataStack && scanner._x_dataStack[0]) {
                        scanner._x_dataStack[0].initializeCameras();
                    }
                }, 300);
            });
        },
        
        closeQrScanner() {
            console.log('[Attendance] Closing QR Scanner modal');
            this.showQrScanner = false;
            
            // Stop camera when closing modal
            const modalEl = document.querySelector('[x-data*="qrScannerModal"]');
            if (modalEl && modalEl._x_dataStack && modalEl._x_dataStack[0]) {
                const scanner = modalEl._x_dataStack[0];
                if (scanner.isScanning) {
                    scanner.stopScanning();
                }
            }
        },

        async openAttendanceView(employee) {
            this.viewError = '';
            this.viewRecord = null;
            this.showAttendanceViewModal = true;

            if (!employee || !employee.attendance_id) {
                this.viewError = 'No attendance record found for today.';
                return;
            }

            this.viewLoading = true;
            try {
                const response = await fetch(this.getApiBaseUrl() + '/attendance/' + employee.attendance_id, {
                    headers: { 'Accept': 'application/json' }
                });
                if (!response.ok) {
                    const errorText = await response.text();
                    throw new Error(errorText || 'Failed to load attendance record.');
                }
                const result = await response.json();
                if (result.success) {
                    this.viewRecord = result.data;
                } else {
                    this.viewError = result.message || 'Failed to load attendance record.';
                }
            } catch (error) {
                console.error('Error loading attendance details:', error);
                this.viewError = 'Failed to load attendance details. Please try again.';
            } finally {
                this.viewLoading = false;
            }
        },

        closeAttendanceView() {
            this.showAttendanceViewModal = false;
            this.viewLoading = false;
            this.viewError = '';
            this.viewRecord = null;
        },
        
        // Computed properties
        get filteredEmployees() {
            return this.employeeData || [];
        },
        
        get clockInOutFilteredEmployees() {
            if (!this.searchQuery) return this.employeeData || [];
            if (!this.employeeData) return [];
            return this.employeeData.filter(emp => 
                emp.name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                emp.department.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                emp.position.toLowerCase().includes(this.searchQuery.toLowerCase())
            );
        },
        
        // Initialize the component
        init() {
            console.log('Attendance Tracker initialized');
            
            // Start clock
            setInterval(() => { this.currentTime = new Date() }, 1000);
            
            // Load initial data based on active tab
            console.log('Initial activeTab:', this.activeTab);
            if (this.activeTab === 'clockinout') {
                console.log('Initial load: calling loadSimpleCounts for clockinout tab');
                this.loadSimpleCounts();
                // Don't load real-time data for clockinout tab to avoid 404 errors
            } else {
                console.log('Initial load: loading overview data');
                this.loadOverviewData();
                this.loadRecentActivities();
                this.loadDepartmentPerformance();
                this.loadRealTimeData();
            }
            
            // Set up watchers
            this.$watch('selectedFilter', () => {
                this.loadRealTimeData();
            });
            
            this.$watch('activeTab', () => {
                console.log('Tab switched to:', this.activeTab);
                if (this.activeTab === 'overview') {
                    console.log('Loading overview data');
                    this.loadOverviewData();
                    this.loadRecentActivities();
                    this.loadDepartmentPerformance();
                } else if (this.activeTab === 'clockinout') {
                    console.log('Loading clockinout data - calling loadSimpleCounts');
                    // For clock in/out tab, load simple counts only
                    this.loadSimpleCounts();
                    // Don't load real-time data to avoid 404 errors
                } else {
                    console.log('Loading realtime data');
                    this.loadRealTimeData();
                }
            });
            
            this.$watch('selectedEmployees', () => {
                this.showBulkActions = this.selectedEmployees.length > 0;
            });
            
            // Refresh data every 30 seconds
            setInterval(() => {
                if (this.activeTab === 'overview') {
                    this.loadOverviewData();
                    this.loadRecentActivities();
                    this.loadDepartmentPerformance();
                } else if (this.activeTab === 'clockinout') {
                    this.loadSimpleCounts();
                } else {
                    this.loadRealTimeData();
                }
            }, 30000);
        },
        
        // Data loading functions
        async loadOverviewData() {
            this.overviewLoading = true;
            try {
                const response = await fetch(this.getApiBaseUrl() + '/attendance/overview-data');
                const data = await response.json();
                if (data.success) {
                    this.overviewData = data.data;
                    this.attendanceData = {
                        todayPresent: data.data.present,
                        todayLate: data.data.late,
                        onBreak: data.data.onBreak,
                        todayAbsent: data.data.absent,
                        overtimeToday: data.data.weeklyOvertime,
                        clockedIn: data.data.clockedInToday || 0,
                        clockedOut: data.data.clockedOutToday || 0,
                        totalEmployees: data.data.totalEmployees,
                        avgCheckIn: data.data.avgCheckIn,
                        lateThreshold: 15
                    };
                }
            } catch (error) {
                console.error('Error loading overview data:', error);
            } finally {
                this.overviewLoading = false;
            }
        },
        
        // Simple function to load just attendance counts for clockinout tab
        async loadSimpleCounts() {
            try {
                const response = await fetch(this.getApiBaseUrl() + '/attendance/simple-counts');
                const data = await response.json();
                console.log('Simple counts loaded:', data);
                
                this.attendanceData = {
                    clockedIn: data.clockedIn || 0,
                    clockedOut: data.clockedOut || 0,
                    onBreak: data.onBreak || 0,
                    totalEmployees: data.totalEmployees || 0
                };
                
                console.log('attendanceData updated:', this.attendanceData);
                
                // Also load employee data for the Clock In/Out tab employee list
                await this.loadClockInOutEmployeeData();
                
            } catch (error) {
                console.error('Error loading simple counts:', error);
                // Fallback to zeros if there's an error
                this.attendanceData = {
                    clockedIn: 0,
                    clockedOut: 0,
                    onBreak: 0,
                    totalEmployees: 0
                };
            }
        },
        
        // Load employee data specifically for Clock In/Out tab
        async loadClockInOutEmployeeData() {
            try {
                const response = await fetch(this.getApiBaseUrl() + '/attendance/real-time-data');
                const data = await response.json();
                console.log('Clock In/Out employee data loaded:', data);
                
                if (data.employees) {
                    this.employeeData = data.employees;
                    console.log('employeeData updated for Clock In/Out tab:', this.employeeData);
                }
            } catch (error) {
                console.error('Error loading employee data for Clock In/Out tab:', error);
                this.employeeData = [];
            }
        },
        
        async loadRecentActivities() {
            this.activitiesLoading = true;
            try {
                const response = await fetch(this.getApiBaseUrl() + '/attendance/recent-activities?limit=10');
                const data = await response.json();
                if (data.success) {
                    this.recentActivities = data.activities;
                }
            } catch (error) {
                console.error('Error loading recent activities:', error);
            } finally {
                this.activitiesLoading = false;
            }
        },
        
        async loadDepartmentPerformance() {
            this.departmentLoading = true;
            try {
                const response = await fetch(this.getApiBaseUrl() + '/attendance/department-performance');
                const data = await response.json();
                if (data.success) {
                    this.departmentData = data.departments;
                }
            } catch (error) {
                console.error('Error loading department performance:', error);
            } finally {
                this.departmentLoading = false;
            }
        },
        
        async loadRealTimeData() {
            this.loading = true;
            try {
                const response = await fetch(this.getApiBaseUrl() + '/attendance/real-time-data?status=' + this.selectedFilter);
                const data = await response.json();
                console.log('API Response:', data);
                this.employeeData = data.employees;
                this.statsData = data.stats;
                
                if (this.activeTab === 'clockinout' || this.activeTab === 'realtime') {
                    try {
                        const overviewResponse = await fetch(this.getApiBaseUrl() + '/attendance/overview-data');
                        const overviewData = await overviewResponse.json();
                        
                        console.log('loadRealTimeData - overviewData:', overviewData);
                        
                        this.attendanceData = {
                            todayPresent: data.stats.present,
                            todayLate: data.stats.late,
                            onBreak: data.stats.break,
                            todayAbsent: data.stats.absent,
                            overtimeToday: 24,
                            clockedIn: overviewData.success ? (overviewData.data.clockedInToday || 0) : 0,
                            clockedOut: overviewData.success ? (overviewData.data.clockedOutToday || 0) : 0,
                            totalEmployees: data.stats.total,
                            avgCheckIn: '08:24',
                            lateThreshold: 15
                        };
                        
                        console.log('loadRealTimeData - final attendanceData:', this.attendanceData);
                    } catch (error) {
                        console.error('Error loading overview data in loadRealTimeData:', error);
                    }
                }
            } catch (error) {
                console.error('Error loading real-time data:', error);
            } finally {
                this.loading = false;
            }
        },
        
        resetModalForm() {
            this.modalFormData = {
                user_id: '',
                date: new Date().toISOString().split('T')[0],
                status: 'present',
                clock_in_time: '',
                clock_out_time: '',
                break_start: '',
                break_end: '',
                notes: ''
            };
        },
        
        resetModalMessages() {
            this.modalErrorMessage = '';
            this.modalSuccessMessage = '';
        },
        
        async loadModalEmployees() {
            try {
                const response = await fetch(this.getApiBaseUrl() + '/attendance/real-time-data');
                const data = await response.json();
                if (data.employees) {
                    this.modalAvailableEmployees = data.employees;
                }
            } catch (error) {
                console.error('Error loading employees for modal:', error);
            }
        },
        
        async submitModalEntry() {
            this.modalSubmitting = true;
            this.resetModalMessages();

            try {
                const response = await fetch('/attendance/manual-entry', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(this.modalFormData)
                });

                const result = await response.json();

                if (response.ok) {
                    this.modalSuccessMessage = result.message || 'Attendance entry saved successfully!';
                    this.resetModalForm();
                    this.loadRealTimeData();
                    
                    setTimeout(() => {
                        this.showManualEntryModal = false;
                    }, 1500);
                } else {
                    if (result.errors) {
                        const errorMessages = Object.values(result.errors).flat();
                        this.modalErrorMessage = errorMessages.join(', ');
                    } else {
                        this.modalErrorMessage = result.message || 'An error occurred while saving the entry.';
                    }
                }
            } catch (error) {
                this.modalErrorMessage = 'Network error. Please try again.';
                console.error('Error submitting entry:', error);
            } finally {
                this.modalSubmitting = false;
            }
        },
        
        // Bulk action methods
        async bulkClockIn() {
            if (this.selectedEmployees.length === 0) {
                this.showBulkActionMessage('Please select at least one employee', false);
                return;
            }
            
            console.log('Starting bulk clock in for employees:', this.selectedEmployees);
            this.bulkActionProcessing = true;
            this.bulkActionMessage = '';
            
            try {
                const url = this.getApiBaseUrl() + '/attendance/bulk-clock-in';
                console.log('Bulk clock in URL:', url);
                
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                console.log('CSRF Token found:', !!csrfToken);
                
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken || ''
                    },
                    body: JSON.stringify({
                        employee_ids: this.selectedEmployees
                    })
                });
                
                console.log('Response status:', response.status);
                console.log('Response ok:', response.ok);
                
                const result = await response.json();
                console.log('Response data:', result);
                
                if (result.success) {
                    this.showBulkActionMessage(result.message, true);
                    // Update employee statuses locally
                    this.updateEmployeeStatuses(this.selectedEmployees, 'present', result.timestamp);
                    // Reload data immediately
                    await this.loadSimpleCounts();
                    await this.loadClockInOutEmployeeData();
                    // Clear selection after a delay
                    setTimeout(() => {
                        this.selectedEmployees = [];
                        this.bulkActionMessage = '';
                    }, 3000);
                } else {
                    this.showBulkActionMessage('Failed to clock in employees', false);
                }
            } catch (error) {
                console.error('Error during bulk clock in:', error);
                this.showBulkActionMessage('Network error: ' + error.message, false);
            } finally {
                this.bulkActionProcessing = false;
            }
        },
        
        async bulkClockOut() {
            if (this.selectedEmployees.length === 0) {
                this.showBulkActionMessage('Please select at least one employee', false);
                return;
            }
            
            console.log('Starting bulk clock out for employees:', this.selectedEmployees);
            this.bulkActionProcessing = true;
            this.bulkActionMessage = '';
            
            try {
                const url = this.getApiBaseUrl() + '/attendance/bulk-clock-out';
                console.log('Bulk clock out URL:', url);
                
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                console.log('CSRF Token found:', !!csrfToken);
                
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken || ''
                    },
                    body: JSON.stringify({
                        employee_ids: this.selectedEmployees
                    })
                });
                
                console.log('Response status:', response.status);
                console.log('Response ok:', response.ok);
                
                // Check if response is ok before trying to parse JSON
                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('Server error response:', errorText);
                    throw new Error(`Server returned ${response.status}: ${errorText.substring(0, 200)}`);
                }
                
                const result = await response.json();
                console.log('Response data:', result);
                
                if (result.success) {
                    this.showBulkActionMessage(result.message, true);
                    this.updateEmployeeStatuses(this.selectedEmployees, 'absent', result.timestamp);
                    await this.loadSimpleCounts();
                    await this.loadClockInOutEmployeeData();
                    setTimeout(() => {
                        this.selectedEmployees = [];
                        this.bulkActionMessage = '';
                    }, 3000);
                } else {
                    this.showBulkActionMessage('Failed to clock out employees', false);
                }
            } catch (error) {
                console.error('Error during bulk clock out:', error);
                this.showBulkActionMessage('Network error: ' + error.message, false);
            } finally {
                this.bulkActionProcessing = false;
            }
        },
        
        async bulkStartBreak() {
            if (this.selectedEmployees.length === 0) {
                this.showBulkActionMessage('Please select at least one employee', false);
                return;
            }
            
            this.bulkActionProcessing = true;
            this.bulkActionMessage = '';
            
            try {
                const response = await fetch(this.getApiBaseUrl() + '/attendance/bulk-start-break', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({
                        employee_ids: this.selectedEmployees
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    this.showBulkActionMessage(result.message, true);
                    this.updateEmployeeStatuses(this.selectedEmployees, 'break', result.timestamp);
                    await this.loadSimpleCounts();
                    await this.loadClockInOutEmployeeData();
                    setTimeout(() => {
                        this.selectedEmployees = [];
                        this.bulkActionMessage = '';
                    }, 3000);
                } else{
                    this.showBulkActionMessage('Failed to start break for employees', false);
                }
            } catch (error) {
                console.error('Error during bulk start break:', error);
                this.showBulkActionMessage('Network error. Please try again.', false);
            } finally {
                this.bulkActionProcessing = false;
            }
        },
        
        async bulkEndBreak() {
            if (this.selectedEmployees.length === 0) {
                this.showBulkActionMessage('Please select at least one employee', false);
                return;
            }
            
            this.bulkActionProcessing = true;
            this.bulkActionMessage = '';
            
            try {
                const response = await fetch(this.getApiBaseUrl() + '/attendance/bulk-end-break', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({
                        employee_ids: this.selectedEmployees
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    this.showBulkActionMessage(result.message, true);
                    this.updateEmployeeStatuses(this.selectedEmployees, 'present', result.timestamp);
                    await this.loadSimpleCounts();
                    await this.loadClockInOutEmployeeData();
                    setTimeout(() => {
                        this.selectedEmployees = [];
                        this.bulkActionMessage = '';
                    }, 3000);
                } else {
                    this.showBulkActionMessage('Failed to end break for employees', false);
                }
            } catch (error) {
                console.error('Error during bulk end break:', error);
                this.showBulkActionMessage('Network error. Please try again.', false);
            } finally {
                this.bulkActionProcessing = false;
            }
        },
        
        showBulkActionMessage(message, success) {
            this.bulkActionMessage = message;
            this.bulkActionSuccess = success;
            
            // Auto-hide message after 5 seconds
            setTimeout(() => {
                this.bulkActionMessage = '';
            }, 5000);
        },
        
        updateEmployeeStatuses(employeeIds, newStatus, timestamp) {
            // Update employee data locally for instant UI feedback
            if (this.employeeData && Array.isArray(this.employeeData)) {
                this.employeeData = this.employeeData.map(emp => {
                    if (employeeIds.includes(emp.id)) {
                        return {
                            ...emp,
                            status: newStatus,
                            checkIn: newStatus !== 'absent' ? (emp.checkIn || timestamp) : null
                        };
                    }
                    return emp;
                });
            }
        },
        
        // Single employee action methods
        setEmployeeProcessing(employeeId, processing) {
            if (this.employeeData && Array.isArray(this.employeeData)) {
                this.employeeData = this.employeeData.map(emp => {
                    if (emp.id === employeeId) {
                        return { ...emp, processing: processing };
                    }
                    return emp;
                });
            }
        },
        
        async singleClockIn(employeeId) {
            console.log('Single clock in for employee:', employeeId);
            this.setEmployeeProcessing(employeeId, true);
            
            try {
                const url = this.getApiBaseUrl() + '/attendance/bulk-clock-in';
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken || ''
                    },
                    body: JSON.stringify({
                        employee_ids: [employeeId]
                    })
                });
                
                if (!response.ok) {
                    const errorText = await response.text();
                    throw new Error(`Server returned ${response.status}`);
                }
                
                const result = await response.json();
                console.log('Single clock in response:', result);
                
                if (result.success) {
                    // Update employee status locally
                    this.updateEmployeeStatuses([employeeId], 'present', result.timestamp);
                    // Reload data
                    await this.loadSimpleCounts();
                    await this.loadClockInOutEmployeeData();
                }
            } catch (error) {
                console.error('Error during single clock in:', error);
                alert('Failed to clock in employee: ' + error.message);
            } finally {
                this.setEmployeeProcessing(employeeId, false);
            }
        },
        
        async singleClockOut(employeeId) {
            console.log('Single clock out for employee:', employeeId);
            this.setEmployeeProcessing(employeeId, true);
            
            try {
                const url = this.getApiBaseUrl() + '/attendance/bulk-clock-out';
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken || ''
                    },
                    body: JSON.stringify({
                        employee_ids: [employeeId]
                    })
                });
                
                if (!response.ok) {
                    const errorText = await response.text();
                    throw new Error(`Server returned ${response.status}`);
                }
                
                const result = await response.json();
                console.log('Single clock out response:', result);
                
                if (result.success) {
                    // Keep the status as is (present/late) when clocking out
                    await this.loadSimpleCounts();
                    await this.loadClockInOutEmployeeData();
                }
            } catch (error) {
                console.error('Error during single clock out:', error);
                alert('Failed to clock out employee: ' + error.message);
            } finally {
                this.setEmployeeProcessing(employeeId, false);
            }
        },
        
        async singleStartBreak(employeeId) {
            console.log('Single start break for employee:', employeeId);
            this.setEmployeeProcessing(employeeId, true);
            
            try {
                const url = this.getApiBaseUrl() + '/attendance/bulk-start-break';
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken || ''
                    },
                    body: JSON.stringify({
                        employee_ids: [employeeId]
                    })
                });
                
                if (!response.ok) {
                    const errorText = await response.text();
                    throw new Error(`Server returned ${response.status}`);
                }
                
                const result = await response.json();
                console.log('Single start break response:', result);
                
                if (result.success) {
                    this.updateEmployeeStatuses([employeeId], 'break', result.timestamp);
                    await this.loadSimpleCounts();
                    await this.loadClockInOutEmployeeData();
                }
            } catch (error) {
                console.error('Error during single start break:', error);
                alert('Failed to start break: ' + error.message);
            } finally {
                this.setEmployeeProcessing(employeeId, false);
            }
        },
        
        async singleEndBreak(employeeId) {
            console.log('Single end break for employee:', employeeId);
            this.setEmployeeProcessing(employeeId, true);
            
            try {
                const url = this.getApiBaseUrl() + '/attendance/bulk-end-break';
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken || ''
                    },
                    body: JSON.stringify({
                        employee_ids: [employeeId]
                    })
                });
                
                if (!response.ok) {
                    const errorText = await response.text();
                    throw new Error(`Server returned ${response.status}`);
                }
                
                const result = await response.json();
                console.log('Single end break response:', result);
                
                if (result.success) {
                    this.updateEmployeeStatuses([employeeId], 'present', result.timestamp);
                    await this.loadSimpleCounts();
                    await this.loadClockInOutEmployeeData();
                }
            } catch (error) {
                console.error('Error during single end break:', error);
                alert('Failed to end break: ' + error.message);
            } finally {
                this.setEmployeeProcessing(employeeId, false);
            }
        }
    }
}

    function pdfExporter() {
        return {
            downloadPDF(type) {
                let url = null;

                switch (type) {
                    case 'daily':
                        url = @json(route('attendance.export-daily-pdf'));
                        break;
                    case 'weekly':
                        url = @json(route('attendance.export-weekly-pdf'));
                        break;
                    case 'monthly':
                        url = @json(route('attendance.export-monthly-pdf'));
                        break;
                    case 'yearly':
                        url = @json(route('attendance.export-yearly-pdf'));
                        break;
                    default:
                        console.error("Invalid PDF type:", type);
                        return;
                }

                fetch(url)
                    .then(res => res.blob())
                    .then(blob => {
                        const link = document.createElement('a');
                        link.href = window.URL.createObjectURL(blob);
                        link.download = `${type}-report.pdf`; // dynamic filename
                        document.body.appendChild(link);
                        link.click();
                        link.remove();
                    })
                    .catch(err => console.error("PDF export failed:", err));
            }
        }
    }

    // QR Scanner Modal Component
    function qrScannerModal() {
        return {
            isScanning: false,
            scanProcessing: false,
            statusMessage: '',
            statusType: 'info', // info, success, error, warning
            html5QrCode: null,
            lastScanTime: 0,
            scanCooldown: 3000, // 3 seconds debounce
            isHttps: window.location.protocol === 'https:',
            isLocalhost: window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1',
            camerasInitialized: false,
            selectedCameraId: null,
            availableCameras: [],
            
            init() {
                console.log('[QR Scanner] ========================================');
                console.log('[QR Scanner] Modal component initialized');
                console.log('[QR Scanner] Protocol:', window.location.protocol);
                console.log('[QR Scanner] Hostname:', window.location.hostname);
                console.log('[QR Scanner] Html5Qrcode available:', typeof Html5Qrcode !== 'undefined');
                console.log('[QR Scanner] ========================================');
                // DO NOT call initializeCameras() here - wait until modal is actually opened
            },
            
            async initializeCameras() {
                console.log('[QR Scanner] ======== initializeCameras() called ========');
                
                // If cameras already initialized, just update the status and return
                if (this.camerasInitialized) {
                    console.log('[QR Scanner] Cameras already initialized, updating UI...');
                    
                    // Update status message
                    if (this.availableCameras && this.availableCameras.length > 0) {
                        this.showStatus(`✓ Found ${this.availableCameras.length} camera(s). Click "Start Scanner" to begin`, 'success');
                    }
                    
                    // Make sure dropdown is populated
                    const select = document.getElementById('qrCameraSelect');
                    if (select && select.options.length <= 1) {
                        console.log('[QR Scanner] Repopulating camera dropdown...');
                        select.innerHTML = '';
                        this.availableCameras.forEach((device, index) => {
                            const option = document.createElement('option');
                            option.value = device.id;
                            option.text = device.label || `Camera ${index + 1}`;
                            select.appendChild(option);
                        });
                        if (this.selectedCameraId) {
                            select.value = this.selectedCameraId;
                        }
                    }
                    
                    return;
                }
                
                try {
                    // Check HTTPS requirement
                    if (!this.isHttps && !this.isLocalhost) {
                        console.error('[QR Scanner] ✗ HTTPS required (current protocol:', window.location.protocol + ')');
                        this.showStatus('⚠️ HTTPS is required for camera access. Please use https:// or localhost', 'error');
                        const select = document.getElementById('qrCameraSelect');
                        if (select) select.innerHTML = '<option value="">HTTPS Required</option>';
                        return;
                    }
                    
                    // Check if Html5Qrcode is available
                    if (typeof Html5Qrcode === 'undefined') {
                        console.error('[QR Scanner] ✗ Html5Qrcode library not loaded');
                        this.showStatus('QR library not loaded. Please refresh the page.', 'error');
                        return;
                    }
                    console.log('[QR Scanner] ✓ Html5Qrcode library loaded');
                    
                    // Request camera permission and get devices
                    console.log('[QR Scanner] Requesting camera devices...');
                    const devices = await Html5Qrcode.getCameras();
                    console.log('[QR Scanner] ✓ Found', devices.length, 'camera device(s):', devices);
                    
                    this.availableCameras = devices;
                    
                    const select = document.getElementById('qrCameraSelect');
                    
                    if (!select) {
                        console.error('[QR Scanner] ✗ Camera select element #qrCameraSelect not found in DOM');
                        return;
                    }
                    
                    select.innerHTML = '';
                    
                    if (devices && devices.length > 0) {
                        devices.forEach((device, index) => {
                            const option = document.createElement('option');
                            option.value = device.id;
                            option.text = device.label || `Camera ${index + 1}`;
                            select.appendChild(option);
                        });
                        
                        // Auto-select first camera
                        this.selectedCameraId = devices[0].id;
                        select.value = this.selectedCameraId;
                        
                        console.log(`[QR Scanner] ✓ Successfully loaded ${devices.length} camera(s)`);
                        console.log('[QR Scanner] ✓ Auto-selected camera:', this.selectedCameraId);
                        this.showStatus(`✓ Found ${devices.length} camera(s). Click "Start Scanner" to begin`, 'success');
                        this.camerasInitialized = true;
                    } else {
                        select.innerHTML = '<option value="">No cameras found</option>';
                        console.warn('[QR Scanner] ⚠️ No cameras detected');
                        this.showStatus('⚠️ No cameras detected. Please check device permissions.', 'warning');
                    }
                } catch (error) {
                    console.error('[QR Scanner] ✗ Error during camera initialization:', error);
                    console.error('[QR Scanner] Error name:', error.name);
                    console.error('[QR Scanner] Error message:', error.message);
                    
                    if (error.name === 'NotAllowedError' || error.message.includes('Permission denied')) {
                        this.showStatus('❌ Camera permission denied. Please allow camera access in your browser settings.', 'error');
                    } else if (error.name === 'NotFoundError') {
                        this.showStatus('❌ No camera found. Please connect a camera device.', 'error');
                    } else if (error.name === 'NotReadableError') {
                        this.showStatus('❌ Camera is in use by another application.', 'error');
                    } else {
                        this.showStatus('❌ Camera error: ' + error.message, 'error');
                    }
                }
            },
            
            onCameraChange() {
                const select = document.getElementById('qrCameraSelect');
                if (select) {
                    this.selectedCameraId = select.value;
                    console.log('[QR Scanner] Camera selection changed to:', this.selectedCameraId);
                }
            },
            
            async startScanning() {
                console.log('[QR Scanner] ======== START SCANNING clicked ========');
                
                const cameraId = this.selectedCameraId || document.getElementById('qrCameraSelect')?.value;
                console.log('[QR Scanner] Selected camera ID:', cameraId);
                
                if (!cameraId) {
                    console.warn('[QR Scanner] ⚠️ No camera selected');
                    this.showStatus('⚠️ Please select a camera first', 'warning');
                    return;
                }
                
                if (!this.isHttps && !this.isLocalhost) {
                    console.error('[QR Scanner] ✗ HTTPS required (not localhost)');
                    this.showStatus('❌ HTTPS is required for camera access (except on localhost)', 'error');
                    return;
                }
                
                // Prevent starting if already scanning
                if (this.isScanning) {
                    console.warn('[QR Scanner] ⚠️ Already scanning');
                    return;
                }
                
                try {
                    console.log('[QR Scanner] Creating Html5Qrcode instance for element #qr-reader...');
                    
                    // Stop any existing scanner first
                    if (this.html5QrCode) {
                        console.log('[QR Scanner] Cleaning up existing scanner instance...');
                        try {
                            await this.html5QrCode.stop();
                            this.html5QrCode.clear();
                        } catch (e) {
                            console.log('[QR Scanner] Cleanup error (expected):', e.message);
                        }
                    }
                    
                    // Create new scanner instance - STORE IN WINDOW to prevent garbage collection
                    this.html5QrCode = new Html5Qrcode('qr-reader');
                    window.__qrScannerInstance = this.html5QrCode; // Prevent garbage collection
                    console.log('[QR Scanner] ✓ Html5Qrcode instance created');
                    
                    console.log('[QR Scanner] Starting camera stream with config:', {
                        cameraId,
                        fps: 10,
                        qrbox: { width: 250, height: 250 }
                    });
                    
                    await this.html5QrCode.start(
                        cameraId,
                        {
                            fps: 10,
                            qrbox: function(viewfinderWidth, viewfinderHeight) {
                                // Calculate QR box size - 70% of the smaller dimension
                                let minEdgePercentage = 0.7;
                                let minEdgeSize = Math.min(viewfinderWidth, viewfinderHeight);
                                let qrboxSize = Math.floor(minEdgeSize * minEdgePercentage);
                                return {
                                    width: qrboxSize,
                                    height: qrboxSize
                                };
                            },
                            aspectRatio: 1.0,
                            // Force showing the scan region
                            showTorchButtonIfSupported: true,
                            formatsToSupport: [ Html5QrcodeSupportedFormats.QR_CODE ]
                        },
                        (decodedText, decodedResult) => {
                            console.log('[QR Scanner] 📷 QR Code detected!');
                            this.onScanSuccess(decodedText, decodedResult);
                        },
                        (errorMessage) => {
                            // Ignore scan errors - they're normal when no QR is visible
                            // console.log('[QR Scanner] Scan frame error (normal):', errorMessage);
                        }
                    );
                    
                    this.isScanning = true;
                    console.log('[QR Scanner] ✓✓✓ Camera started successfully! Scanning active.');
                    this.showStatus('✓ Scanner active - Ready to scan QR codes', 'success');
                } catch (error) {
                    console.error('[QR Scanner] ✗✗✗ Error starting scanner:', error);
                    console.error('[QR Scanner] Error name:', error.name);
                    console.error('[QR Scanner] Error message:', error.message);
                    console.error('[QR Scanner] Error stack:', error.stack);
                    
                    this.isScanning = false;
                    
                    if (error.name === 'NotAllowedError' || error.message.includes('Permission denied')) {
                        this.showStatus('❌ Camera permission denied. Click the camera icon in your browser address bar to allow access.', 'error');
                    } else if (error.name === 'NotReadableError' || error.message.includes('already in use')) {
                        this.showStatus('❌ Camera is in use by another application. Please close other apps using the camera.', 'error');
                    } else if (error.name === 'NotFoundError' || error.message.includes('not found')) {
                        this.showStatus('❌ Camera not found. Please connect a camera and refresh.', 'error');
                    } else if (error.message.includes('Could not start video source')) {
                        this.showStatus('❌ Camera access failed. Try refreshing the page or restarting your browser.', 'error');
                    } else {
                        this.showStatus('❌ Failed to start scanner: ' + error.message, 'error');
                    }
                    
                    // Clean up failed instance
                    if (this.html5QrCode) {
                        try {
                            this.html5QrCode.clear();
                        } catch (e) {}
                        this.html5QrCode = null;
                        window.__qrScannerInstance = null;
                    }
                }
            },
            
            async stopScanning() {
                console.log('[QR Scanner] ======== STOP SCANNING called ========');
                
                if (this.html5QrCode) {
                    try {
                        console.log('[QR Scanner] Stopping camera stream...');
                        await this.html5QrCode.stop();
                        console.log('[QR Scanner] ✓ Camera stream stopped');
                        
                        console.log('[QR Scanner] Clearing scanner instance...');
                        this.html5QrCode.clear();
                        this.html5QrCode = null;
                        window.__qrScannerInstance = null;
                        console.log('[QR Scanner] ✓ Scanner instance cleared');
                        
                        this.isScanning = false;
                        
                        console.log('[QR Scanner] ✓ Scanner stopped successfully');
                        this.showStatus('Scanner stopped', 'info');
                    } catch (error) {
                        console.error('[QR Scanner] ✗ Error stopping scanner:', error);
                        // Force cleanup even on error
                        this.isScanning = false;
                        if (this.html5QrCode) {
                            try { this.html5QrCode.clear(); } catch (e) {}
                            this.html5QrCode = null;
                            window.__qrScannerInstance = null;
                        }
                    }
                } else {
                    console.log('[QR Scanner] No scanner instance to stop');
                    this.isScanning = false;
                }
            },
            
            async onScanSuccess(decodedText, decodedResult) {
                const now = Date.now();
                
                console.log('[QR Scanner] 📷 QR Code scanned successfully!');
                console.log('[QR Scanner] Decoded text:', decodedText);
                
                // Debounce - prevent rapid scanning
                if (now - this.lastScanTime < this.scanCooldown) {
                    console.log('[QR Scanner] ⏱️ Scan cooldown active, ignoring scan');
                    return;
                }
                this.lastScanTime = now;
                
                // Pause scanning during processing
                if (this.scanProcessing) {
                    console.log('[QR Scanner] ⏱️ Already processing a scan, ignoring');
                    return;
                }
                
                // Parse QR code data
                try {
                    const qrData = JSON.parse(decodedText);
                    console.log('[QR Scanner] ✓ QR data parsed:', qrData);
                    
                    // Brief visual feedback
                    this.showStatus('📷 QR Code detected! Processing...', 'info');
                    
                    await this.processAttendance(qrData);
                } catch (error) {
                    console.error('[QR Scanner] ✗ Invalid QR code format:', error);
                    this.showStatus('❌ Invalid QR code format. Please use a valid attendance QR code.', 'error');
                }
            },
            
            async processAttendance(qrData) {
                this.scanProcessing = true;
                console.log('[QR Scanner] 📤 Sending attendance data to server:', qrData);
                this.showStatus('⏳ Processing attendance...', 'info');
                
                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
                    
                    if (!csrfToken) {
                        console.error('[QR Scanner] ✗ CSRF token not found');
                        this.showStatus('❌ Security token missing. Please refresh the page.', 'error');
                        this.scanProcessing = false;
                        return;
                    }
                    
                    console.log('[QR Scanner] Posting to:', '{{ route("attendance.qr-scan") }}');
                    const response = await fetch('{{ route("attendance.qr-scan") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(qrData)
                    });
                    
                    console.log('[QR Scanner] Server response status:', response.status);
                    const result = await response.json();
                    console.log('[QR Scanner] Server response data:', result);
                    
                    if (response.ok && result.success) {
                        console.log('[QR Scanner] ✓✓✓ Attendance logged successfully!');
                        this.showStatus(`✅ SUCCESS: ${result.employee.name} - ${result.type} at ${result.time}`, 'success');
                        
                        // Play success sound if available
                        try {
                            const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBjGH0fPTgjMGHm7A7+OZUQ4PVK3n77BfHAc9ktjyz4A1Bjh+zPLaizsIGGS56+SdUhENTqfk8bllHgY7k9fzzYQ5CDiEzvPajj0HHnHD8OKcUg8NVq/o8bBfHAc/ltjyz4I1BjiBzfLajj0HH3HE8OSZUQ8PVK7o8bJiHQZAl9nz0II3Bjh+zPLajj0HHnDD8OScUhANVa/o8bFfHAc/ltjyz4I1BjiBzPLajj0HHnHE8OWbURAPVK7n8bBfHAc/ltjyz4I1BjiBzPLajj0HHnDD8OWbURAPVK7o8bFfHQdAl9jzz4M1BjiBzPLajj0HHnDD8OWbURAPVK7n8bBfHAc/ltjzz4I1BjiBzPLajj0HHnDD8OWbURAPVK7o8bBfHQdAl9jzz4M1BjiBzPLajj0HHnDD8OWbURAPVK7o8bBfHQc/ltjyz4I1BjiBzPLajj0HHnHE8OWbURAPVK7o8bBfHQdAl9jzz4M1BjiBzPLajj0HHnDD8OWbURAPVK7n8bBfHAc/ltjzz4I1BjiBzPLajj0HHnDD8OWbURAPVK7n8bBfHAc/ltjzz4I1BjiBzPLajj0HHnDD8OWbURAPVK7o8bBfHQdAl9jzz4I1BjiBzPLajj0HHnDD8OWbURAPVK7o8bBfHQdAl9jzz4I1BjiBzPLajj0HHnDD8OWbURAPVK7o8bBfHQc/ltjyz4I1BjiBzPLajj0HHnDD8OWbURAPVK7o8bBfHQdAl9jzz4I1BjiBzPLajj0HHnDD8OWbURAPVK7n8bBfHAc/ltjzz4I1BjiBzPLajj0HHnDD8OWbURAPVK7n8bBfHAc/ltjzz4I1BjiBzPLajj0HHnDD8OWbURAPVK7o8bBfHQdAl9jzz4I1BjiBzPLajj0HHnDD8OWbURAPVK7o8bBfHQdAl9jzz4M1BjiBzPLajj0HHnDD8OWbURAPVK7o8bBfHQdAl9jzz4I1BjiBzPLajj0HHnDD8OWbURAPVK7o8bBfHQdAl9jzz4I1BjiBzPLajj0HHnDD8OWbURAPVK7o8bBfHQdAl9jzz4I1BjiBzPLajj0HHnDD8OWbURAPVK7o8bBfHQdAl9jzz4I1BjiBzPLajj0HHnDD8OWbURAPVK7o8bBfHQdAl9jzz4I1BjiBzPLajj0HHnDD8OWbURAPVK7o8bBfHQdAl9jzz4I1BjiBzPLajj0HHnDD8OWbURAPVK7o8bBfHQc=');
                            audio.play().catch(e => console.log('[QR Scanner] Audio play failed (expected):', e.message));
                        } catch (e) {}
                        
                        // Reload attendance data in parent component
                        setTimeout(() => {
                            const parentEl = document.querySelector('[x-data*="attendanceTracker"]');
                            if (parentEl && parentEl._x_dataStack && parentEl._x_dataStack[0]) {
                                console.log('[QR Scanner] Triggering parent data reload...');
                                const tracker = parentEl._x_dataStack[0];
                                if (tracker.activeTab === 'clockinout') {
                                    tracker.loadSimpleCounts();
                                    tracker.loadClockInOutEmployeeData();
                                } else {
                                    tracker.loadOverviewData();
                                    tracker.loadRecentActivities();
                                }
                            }
                        }, 1000);
                    } else {
                        console.error('[QR Scanner] ✗ Scan failed:', result.message);
                        this.showStatus('❌ ' + (result.message || 'Scan failed'), 'error');
                    }
                } catch (error) {
                    console.error('[QR Scanner] ✗ Network/processing error:', error);
                    this.showStatus('❌ Network error - please check connection and try again', 'error');
                } finally {
                    this.scanProcessing = false;
                }
            },
            
            showStatus(message, type) {
                console.log(`[QR Scanner] Status [${type.toUpperCase()}]:`, message);
                this.statusMessage = message;
                this.statusType = type;
                
                // Auto-hide success/info messages after 7 seconds
                if (type === 'success') {
                    setTimeout(() => {
                        if (this.statusMessage === message) {
                            this.statusMessage = '';
                        }
                    }, 7000);
                } else if (type === 'info') {
                    setTimeout(() => {
                        if (this.statusMessage === message) {
                            this.statusMessage = '';
                        }
                    }, 5000);
                }
                // Errors and warnings stay until dismissed
            },
            
            handleOverlayClick(event) {
                // Close only if clicking directly on the overlay (not the modal content)
                console.log('[QR Scanner] Overlay click detected on:', event.target);
                this.closeScanner();
            },
            
            closeScanner() {
                console.log('[QR Scanner] ======== closeScanner() called ========');
                
                // Stop scanner and release camera FIRST
                if (this.isScanning || this.html5QrCode) {
                    console.log('[QR Scanner] Stopping scanner before close...');
                    this.stopScanning();
                }
                
                // Reset all state
                this.statusMessage = '';
                this.scanProcessing = false;
                this.isScanning = false;
                
                // Force hide modal immediately
                const modalEl = document.getElementById('qrScannerModal');
                if (modalEl) {
                    modalEl.style.display = 'none';
                }
                
                // Find parent component and close modal
                const parentEl = document.querySelector('[x-data*="attendanceTracker"]');
                if (parentEl && parentEl._x_dataStack && parentEl._x_dataStack[0]) {
                    console.log('[QR Scanner] Closing modal via parent...');
                    parentEl._x_dataStack[0].closeQrScanner();
                } else {
                    console.error('[QR Scanner] ✗ Could not find parent attendanceTracker component');
                }
            }
        }
    }

</script>

@endsection
