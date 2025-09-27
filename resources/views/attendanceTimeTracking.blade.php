@extends('dashboard')

@section('title', 'Attendance & Time Tracking')

@section('content')
<div class="py-2 px-3 md:p-6 max-w-full bg-gray-300" x-data="{
        // Dynamic API base URL detection
        getApiBaseUrl() {
            // Check if we're running on Vite dev server (port 5173)
            if (window.location.port === '5173') {
                // Use configured app URL for Vite dev server
                return '{{ config("app.url") }}';
            }
            // Use current origin for php artisan serve or other environments
            return window.location.origin;
        },
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
    selectedEmployees: []
}" x-init="
    setInterval(() => { currentTime = new Date() }, 1000);
    
    // Function to load overview data
    async function loadOverviewData() {
        overviewLoading = true;
        try {
            const response = await fetch(getApiBaseUrl() + '/attendance/overview-data');
            const data = await response.json();
            if (data.success) {
                overviewData = data.data;
                // Update attendance data for compatibility with existing code
                attendanceData = {
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
            overviewLoading = false;
        }
    }
    
    // Function to load recent activities
    async function loadRecentActivities() {
        activitiesLoading = true;
        try {
            const response = await fetch(getApiBaseUrl() + '/attendance/recent-activities?limit=10');
            const data = await response.json();
            if (data.success) {
                recentActivities = data.activities;
            }
        } catch (error) {
            console.error('Error loading recent activities:', error);
        } finally {
            activitiesLoading = false;
        }
    }
    
    // Function to load department performance
    async function loadDepartmentPerformance() {
        departmentLoading = true;
        try {
            const response = await fetch(getApiBaseUrl() + '/attendance/department-performance');
            const data = await response.json();
            if (data.success) {
                departmentData = data.departments;
            }
        } catch (error) {
            console.error('Error loading department performance:', error);
        } finally {
            departmentLoading = false;
        }
    }
    
    // Function to load real-time data
    async function loadRealTimeData() {
        loading = true;
        try {
            const response = await fetch(getApiBaseUrl() + '/attendance/real-time-data?status=' + selectedFilter);
            const data = await response.json();
            console.log('API Response:', data);
            console.log('Employees received:', data.employees);
            console.log('Stats received:', data.stats);
            employeeData = data.employees;
            statsData = data.stats;
            console.log('employeeData set to:', employeeData);
            console.log('statsData set to:', statsData);
            
            // Update attendance data with real stats for clock in/out tab
            if (activeTab === 'clockinout' || activeTab === 'realtime') {
                // For consistency, also load overview data to get real clock in/out counts
                const overviewResponse = await fetch(getApiBaseUrl() + '/attendance/overview-data');
                const overviewData = await overviewResponse.json();
                
                attendanceData = {
                    todayPresent: data.stats.present,
                    todayLate: data.stats.late,
                    onBreak: data.stats.break,
                    todayAbsent: data.stats.absent,
                    overtimeToday: 24, // Keep this as sample for now
                    clockedIn: overviewData.success ? (overviewData.data.clockedInToday || 0) : 0,
                    clockedOut: overviewData.success ? (overviewData.data.clockedOutToday || 0) : 0,
                    totalEmployees: data.stats.total,
                    avgCheckIn: '08:24', // Keep this as sample for now
                    lateThreshold: 15
                };
            }
        } catch (error) {
            console.error('Error loading real-time data:', error);
        } finally {
            loading = false;
        }
    }
    
    // Load initial data
    loadOverviewData();
    loadRecentActivities();
    loadDepartmentPerformance();
    loadRealTimeData();
    
    // Refresh data every 30 seconds
    setInterval(() => {
        if (activeTab === 'overview') {
            loadOverviewData();
            loadRecentActivities();
            loadDepartmentPerformance();
        } else {
            loadRealTimeData();
        }
    }, 30000);
    
    // Watch for filter changes
    $watch('selectedFilter', () => {
        loadRealTimeData();
    });
    
    // Watch for tab changes
    $watch('activeTab', () => {
        if (activeTab === 'overview') {
            loadOverviewData();
            loadRecentActivities();
            loadDepartmentPerformance();
        } else {
            loadRealTimeData();
        }
    });
" x-computed="{
    filteredEmployees() {
        // Since the backend already filters the data based on selectedFilter,
        // we just return the employeeData as-is for the real-time tab
        return this.employeeData || [];
    },
    clockInOutFilteredEmployees() {
        // For the Clock In/Out tab, filter by search query
        if (!this.searchQuery) return this.employeeData || [];
        if (!this.employeeData) return [];
        return this.employeeData.filter(emp => 
            emp.name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
            emp.department.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
            emp.position.toLowerCase().includes(this.searchQuery.toLowerCase())
        );
    }
}"
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
            <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg shadow-md p-4 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-100 text-lg">Present Today</p>
                        <p class="text-2xl font-bold" x-text="attendanceData.todayPresent || 0"></p>
                        <p class="text-green-200 text-xs mt-0.5">Present employees</p>
                    </div>
                    <div class="p-2 bg-green-400 rounded-full">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Late Arrivals -->
            <div class="bg-yellow-500 rounded-lg shadow-md p-4 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-white-100 text-lg">Late Arrivals</p>
                        <p class="text-2xl font-bold" x-text="attendanceData.todayLate || 0"></p>
                        <p class="text-white-200 text-xs mt-0.5">Late employees</p>
                    </div>
                    <div class="p-2 bg-yellow-400 rounded-full">
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
            <div class="bg-gradient-to-r from-red-500 to-red-600 rounded-lg shadow-md p-4 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-red-100 text-lg">Absent Today</p>
                        <p class="text-2xl font-bold" x-text="attendanceData.todayAbsent || 0"></p>
                        <p class="text-red-200 text-xs mt-0.5">Absent employees</p>
                    </div>
                    <div class="p-2 bg-red-400 rounded-full">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Overtime Today -->
            <div class="bg-orange-500 to-orange-600 rounded-lg shadow-md p-4 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-orange-100 text-lg">Overtime Today</p>
                        <p class="text-2xl font-bold" x-text="attendanceData.overtimeToday || 0"></p>
                        <p class="text-orange-200 text-xs mt-0.5">Overtime hours</p>
                    </div>
                    <div class="p-2 bg-orange-400 rounded-full">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Employees -->
            <div class="bg-purple-500 to-purple-600 rounded-lg shadow-md p-4 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-purple-100 text-lg">Total Employees</p>
                        <p class="text-2xl font-bold" x-text="attendanceData.totalEmployees || 0"></p>
                        <p class="text-purple-200 text-xs mt-0.5">Company workforce</p>
                    </div>
                    <div class="p-2 bg-purple-400 rounded-full">
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
                        <div class="grid grid-cols-2 gap-4">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-green-400">+2.3%</div>
                                <div class="text-xs text-gray-300">Attendance vs last week</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-blue-400">-5 min</div>
                                <div class="text-xs text-gray-300">Avg arrival improvement</div>
                            </div>
                        </div>
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
                                                  'bg-gray-100 text-gray-800': !employee.status
                                              }" 
                                              x-text="employee.status === 'break' ? 'On Break' : (employee.status || 'Unknown')"></span>
                                    </td>
                                    
                                    <!-- Check In -->
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="employee.checkIn || 'Not clocked in'"></td>
                                    
                                    <!-- Hours -->
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="employee.hours ? employee.hours + ' hrs' : 'N/A'"></td>
                                    
                                    <!-- Actions -->
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <button class="text-blue-600 hover:text-blue-900 transition-colors">View</button>
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
                <div class="flex space-x-3">
                    <a href="{{ route('attendance.manual-entry') }}" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors flex items-center space-x-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <span>Manual Entry</span>
                    </a>
                </div>
            </div>

            <!-- Bulk Actions Panel -->
            <div x-show="showBulkActions" x-transition class="mt-4 p-4 bg-gray-50 rounded-lg border">
                <div class="flex flex-wrap gap-3 items-center">
                    <span class="text-sm font-medium text-gray-700">Bulk Actions:</span>
                    <button class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700 transition-colors">Clock In Selected</button>
                    <button class="bg-red-600 text-white px-3 py-1 rounded text-sm hover:bg-red-700 transition-colors">Clock Out Selected</button>
                    <button class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700 transition-colors">Start Break</button>
                    <button class="bg-yellow-600 text-white px-3 py-1 rounded text-sm hover:bg-yellow-700 transition-colors">End Break</button>
                    <span class="text-sm text-gray-500 ml-auto" x-text="selectedEmployees.length + ' employee(s) selected'"></span>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Employee List -->
            <div class="lg:col-span-2 bg-white rounded-lg shadow-lg">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Employee Directory</h3>
                        <div class="text-sm text-gray-500">
                            <span x-text="clockInOutFilteredEmployees.length"></span> of <span x-text="employeeData.length"></span> employees
                        </div>
                    </div>
                </div>
                
                <div class="max-h-96 overflow-y-auto">
                    <template x-for="employee in clockInOutFilteredEmployees" :key="employee.id">
                        <div class="p-4 border-b border-gray-100 hover:bg-gray-50 transition-colors">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <div class="flex items-center space-x-3">
                                        <input 
                                            type="checkbox" 
                                            :value="employee.id" 
                                            x-model="selectedEmployees" 
                                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                        <div :class="'w-12 h-12 bg-' + employee.color + '-500 rounded-full flex items-center justify-center text-white font-medium'" x-text="employee.avatar"></div>
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900" x-text="employee.name"></div>
                                        <div class="text-sm text-gray-500" x-text="employee.position"></div>
                                        <div class="text-xs text-gray-400" x-text="employee.department"></div>
                                    </div>
                                </div>
                                
                                <div class="flex items-center space-x-3">
                                    <!-- Status Badge -->
                                    <span :class="{
                                        'bg-green-100 text-green-800': employee.status === 'present',
                                        'bg-yellow-100 text-yellow-800': employee.status === 'late',
                                        'bg-red-100 text-red-800': employee.status === 'absent',
                                        'bg-blue-100 text-blue-800': employee.status === 'break'
                                    }" class="px-2 py-1 text-xs font-medium rounded-full capitalize" x-text="employee.status === 'break' ? 'On Break' : employee.status"></span>
                                    
                                    <!-- Check-in Time -->
                                    <div class="text-right">
                                        <div class="text-sm font-medium" x-text="employee.checkIn ? employee.checkIn : 'Not checked in'"></div>
                                        <div class="text-xs text-gray-500" x-text="employee.hours ? 'Hours: ' + employee.hours : ''"></div>
                                    </div>
                                    
                                    <!-- Action Buttons -->
                                    <div class="flex space-x-1">
                                        <template x-if="employee.status === 'absent'">
                                            <button class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700 transition-colors flex items-center space-x-1">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                                                </svg>
                                                <span>Clock In</span>
                                            </button>
                                        </template>
                                        
                                        <template x-if="employee.status === 'present' || employee.status === 'late'">
                                            <div class="flex space-x-1">
                                                <button class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700 transition-colors flex items-center space-x-1">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    <span>Break</span>
                                                </button>
                                                <button class="bg-red-600 text-white px-3 py-1 rounded text-sm hover:bg-red-700 transition-colors flex items-center space-x-1">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                                                    </svg>
                                                    <span>Clock Out</span>
                                                </button>
                                            </div>
                                        </template>
                                        
                                        <template x-if="employee.status === 'break'">
                                            <button class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700 transition-colors flex items-center space-x-1">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                <span>End Break</span>
                                            </button>
                                        </template>
                                        
                                        <button class="bg-gray-500 text-white px-2 py-1 rounded text-sm hover:bg-gray-600 transition-colors" title="More options">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                    
                    <!-- No Data Available -->
                    <div x-show="clockInOutFilteredEmployees.length === 0" class="p-8 text-center text-gray-500">
                        <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <p class="text-lg font-medium mb-1">No Data Available</p>
                        <p class="text-sm">No employee data found for the current search. Try adjusting your search terms or check back later.</p>
                    </div>
                </div>
            </div>
            
            <!-- Live Activity Feed -->
            <div class="bg-white rounded-lg shadow-lg">
                <div class="p-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        Live Activity
                    </h3>
                    <p class="text-sm text-gray-600">Real-time clock in/out activities</p>
                </div>
                
                <div class="max-h-96 overflow-y-auto">
                    <template x-for="activity in recentActivities" :key="activity.id">
                        <div class="p-4 border-b border-gray-100 hover:bg-gray-50 transition-colors">
                            <div class="flex items-center space-x-3">
                                <!-- Activity Icon -->
                                <div :class="{
                                    'bg-green-100': activity.type === 'in',
                                    'bg-red-100': activity.type === 'out',
                                    'bg-blue-100': activity.type === 'break' || activity.type === 'break_end',
                                    'bg-yellow-100': activity.type === 'manual'
                                }" class="w-10 h-10 rounded-full flex items-center justify-center">
                                    <template x-if="activity.type === 'in'">
                                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                                        </svg>
                                    </template>
                                    <template x-if="activity.type === 'out'">
                                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                                        </svg>
                                    </template>
                                    <template x-if="activity.type === 'break' || activity.type === 'break_end'">
                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </template>
                                    <template x-if="activity.type === 'manual'">
                                        <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                        </svg>
                                    </template>
                                </div>
                                
                                <!-- Activity Details -->
                                <div class="flex-1">
                                    <div class="font-medium text-gray-900" x-text="activity.employee + ' - ' + activity.action"></div>
                                    <div class="text-sm text-gray-500" x-text="activity.time + ' • ' + activity.department"></div>
                                </div>
                                
                                <!-- Status Indicator -->
                                <div class="w-3 h-3 rounded-full animate-pulse" :class="{
                                    'bg-green-400': activity.type === 'in',
                                    'bg-red-400': activity.type === 'out',
                                    'bg-blue-400': activity.type === 'break' || activity.type === 'break_end',
                                    'bg-yellow-400': activity.type === 'manual'
                                }"></div>
                            </div>
                        </div>
                    </template>
                </div>
                
                <!-- View All Activities Button -->
                <div class="p-4 border-t border-gray-200">
                    <a href="{{ route('attendance.all-activities') }}" class="block w-full text-center text-blue-600 hover:text-blue-800 text-sm font-medium transition-colors">
                        View All Activities →
                    </a>
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

<script>
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
</script>

@endsection
