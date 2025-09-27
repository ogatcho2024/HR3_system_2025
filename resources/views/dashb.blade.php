@extends('dashboard')

@section('title', 'HR Dashboard')

@section('content')
<div class="min-h-screen bg-gray-300">
    <div class="py-6">
        <div style="width: 100%; padding: 0 1rem;">
            <div class="flex items-center justify-between space-x-4">
                <!-- Breadcrumbs -->
                @include('partials.breadcrumbs', ['breadcrumbs' => [
                    ['label' => 'Dashboard', 'url' => route('dashb')]
                ]])
            </div>
            
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                   
                </div>
            </div>

            <!-- Key Metrics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <!-- Total Employees -->
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-2xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm">Total Employees</p>
                    <p class="text-3xl font-bold">{{ $totalEmployees ?? '150' }}</p>
                    <p class="text-blue-200 text-xs mt-1">+5 this month</p>
                </div>
                <div class="p-3 bg-blue-400 rounded-full">
                    <svg fill="#000000" width="40px" height="40px" viewBox="0 0 36 36" xmlns="http://www.w3.org/2000/svg">
                        <title>employee_solid</title>
                        <g id="aad88ad3-6d51-4184-9840-f392d18dd002" data-name="Layer 3">
                        <circle cx="16.86" cy="9.73" r="6.46"/>
                        <rect x="21" y="28" width="7" height="1.4"/>
                        <path d="M15,30v3a1,1,0,0,0,1,1H33a1,1,0,0,0,1-1V23a1,1,0,0,0-1-1H26V20.53a1,1,0,0,0-2,0V22H22V18.42A32.12,32.12,0,0,0,16.86,18a26,26,0,0,0-11,2.39,3.28,3.28,0,0,0-1.88,3V30Zm17,2H17V24h7v.42a1,1,0,0,0,2,0V24h6Z"/>
                        </g>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Active Leave Requests -->
        <div class="bg-yellow-500 rounded-2xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-white-100 text-sm">Pending Requests</p>
                    <p class="text-3xl font-bold">{{ $pendingRequests ?? '24' }}</p>
                    <p class="text-white-200 text-xs mt-1">Needs approval</p>
                </div>
                <div class="p-3 bg-yellow-400 rounded-full">
                    <svg width="40px" height="40px" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M15.6666 8L17.75 10.5L15.6666 8Z" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M15.6666 13L17.75 10.5L15.6666 13Z" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M16.5 10.5L10 10.5" stroke="#000000" stroke-width="2" stroke-linecap="round"/>
                        <line x1="4" y1="3.5" x2="13" y2="3.5" stroke="#000000" stroke-width="2" stroke-linecap="round"/>
                        <line x1="4" y1="17.5" x2="13" y2="17.5" stroke="#000000" stroke-width="2" stroke-linecap="round"/>
                        <path d="M13 3.5V7.5" stroke="#000000" stroke-width="2" stroke-linecap="round"/>
                        <path d="M13 13.5V17.5" stroke="#000000" stroke-width="2" stroke-linecap="round"/>
                        <path d="M4 3.5L4 17.5" stroke="#000000" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Today's Attendance -->
        <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-2xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm">Attendance</p>
                    <p class="text-3xl font-bold">{{ $todayAttendance }}</p>
                    <p class="text-green-200 text-xs mt-1">{{ $attendancePercentage }}% present</p>
                </div>
                <div class="p-3 bg-green-400 rounded-full">
                    <svg width="40px" height="40px" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" mirror-in-rtl="true">
                        <path fill="#494c4e" d="M7 11c-1.1 0-2-.9-2-2V8c0-1.1.9-2 2-2s2 .9 2 2v1c0 1.1-.9 2-2 2zm-2 6.993L9 18c.55 0 1-.45 1-1v-2c0-1.65-1.35-3-3-3s-3 1.35-3 3v2c0 .552.448.993 1 .993zM19 18h-6c-.553 0-1-.447-1-1s.447-1 1-1h6c.553 0 1 .447 1 1s-.447 1-1 1zm0-4h-6c-.553 0-1-.448-1-1s.447-1 1-1h6c.553 0 1 .448 1 1s-.447 1-1 1zm0-4h-6c-.553 0-1-.448-1-1s.447-1 1-1h6c.553 0 1 .448 1 1s-.447 1-1 1z"/>
                        <path fill="#494c4e" d="M22 2H2C.9 2 0 2.9 0 4v16c0 1.1.9 2 2 2h20c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 17.5c0 .28-.22.5-.5.5h-19c-.28 0-.5-.22-.5.5v-15c0-.28.22-.5.5-.5h19c.28 0 .5.22.5.5v15z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

            <!-- Today's Attendance Breakdown -->
            @if(isset($attendanceStats))
            <div class="bg-white rounded-2xl shadow-lg p-6 mb-8">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900">Today's Attendance Overview</h3>
            <div class="text-sm text-gray-500">
                {{ now()->format('l, F j, Y') }}
            </div>
        </div>
        
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <!-- Present -->
            <div class="text-center p-4 bg-green-50 rounded-lg border border-green-200">
                <div class="text-2xl font-bold text-green-600">{{ $attendanceStats['present'] }}</div>
                <div class="text-sm font-medium text-green-700">Present</div>
                <div class="text-xs text-gray-500 mt-1">
                    {{ $totalEmployees > 0 ? round(($attendanceStats['present'] / $totalEmployees) * 100, 1) : 0 }}%
                </div>
            </div>
            
            <!-- Late -->
            <div class="text-center p-4 bg-orange-50 rounded-lg border border-orange-200">
                <div class="text-2xl font-bold text-orange-600">{{ $attendanceStats['late'] }}</div>
                <div class="text-sm font-medium text-orange-700">Late</div>
                <div class="text-xs text-gray-500 mt-1">
                    {{ $totalEmployees > 0 ? round(($attendanceStats['late'] / $totalEmployees) * 100, 1) : 0 }}%
                </div>
            </div>
            
            <!-- On Break -->
            <div class="text-center p-4 bg-blue-50 rounded-lg border border-blue-200">
                <div class="text-2xl font-bold text-blue-600">{{ $attendanceStats['on_break'] }}</div>
                <div class="text-sm font-medium text-blue-700">On Break</div>
                <div class="text-xs text-gray-500 mt-1">
                    {{ $totalEmployees > 0 ? round(($attendanceStats['on_break'] / $totalEmployees) * 100, 1) : 0 }}%
                </div>
            </div>
            
            <!-- Absent -->
            <div class="text-center p-4 bg-red-50 rounded-lg border border-red-200">
                <div class="text-2xl font-bold text-red-600">{{ $attendanceStats['absent'] }}</div>
                <div class="text-sm font-medium text-red-700">Absent</div>
                <div class="text-xs text-gray-500 mt-1">
                    {{ $totalEmployees > 0 ? round(($attendanceStats['absent'] / $totalEmployees) * 100, 1) : 0 }}%
                </div>
            </div>
        </div>
        
        <div class="mt-6 flex justify-between items-center">
            <div class="text-sm text-gray-600">
                <span class="font-medium">Overall Attendance Rate:</span> 
                <span class="text-lg font-bold text-{{ $attendancePercentage >= 90 ? 'green' : ($attendancePercentage >= 80 ? 'yellow' : 'red') }}-600">
                    {{ $attendancePercentage }}%
                </span>
            </div>
            <a href="{{ route('attendanceTimeTracking') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
                View Real-Time Tracking
            </a>
        </div>
    </div>
            @endif

            <!-- Quick Actions -->
            <div class="bg-gray-100 rounded-2xl shadow-lg p-6 mb-8">
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            <a href="{{ route('reports.index') }}" class="flex flex-col items-center border border-gray-400 p-4 rounded-lg group">
                <div class="p-3 bg-green-600 rounded-full mb-3 group-hover:bg-green-700">
                    <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 32 32" xml:space="preserve">
                        <path d="M24 7V5H4v18a4 4 0 0 0 4 4h16a4 4 0 0 0 4-4V7zm2 16a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V7h16v16h2V9h2zM14 9H8v6h6zm-2 4h-2v-2h2zm4-4h4v2h-4zm0 4h4v2h-4zm-8 4h12v2H8zm0 4h12v2H8z" style="fill:#111918"/>
                    </svg>
                </div>
                <span class="text-sm font-medium text-black">View Reports</span>
            </a>

            <a href="{{ route('leave-management.admin-dashboard') }}" class="flex flex-col items-center border border-gray-400 p-4 group">
                <div class="p-3 bg-yellow-600 rounded-full mb-3 group-hover:bg-yellow-700">
                    <svg width="30" height="30" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M15.6666 8L17.75 10.5L15.6666 8Z" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M15.6666 13L17.75 10.5L15.6666 13Z" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M16.5 10.5L10 10.5" stroke="#000000" stroke-width="2" stroke-linecap="round"/>
                        <line x1="4" y1="3.5" x2="13" y2="3.5" stroke="#000000" stroke-width="2" stroke-linecap="round"/>
                        <line x1="4" y1="17.5" x2="13" y2="17.5" stroke="#000000" stroke-width="2" stroke-linecap="round"/>
                        <path d="M13 3.5V7.5" stroke="#000000" stroke-width="2" stroke-linecap="round"/>
                        <path d="M13 13.5V17.5" stroke="#000000" stroke-width="2" stroke-linecap="round"/>
                        <path d="M4 3.5L4 17.5" stroke="#000000" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </div>
                <span class="text-sm font-medium text-black">Leave Requests</span>
            </a>

            <a href="{{ route('workScheduleShiftManagement') }}" class="flex flex-col items-center p-4 border border-gray-400 rounded-lg">
                <div class="p-3 bg-indigo-600 rounded-full mb-3 group-hover:bg-indigo-800">
                    <svg width="30px" height="30px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9 20H6C3.79086 20 2 18.2091 2 16V7C2 4.79086 3.79086 3 6 3H17C19.2091 3 21 4.79086 21 7V10" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M8 2V4" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M15 2V4" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M2 8H21" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M18.5 15.6429L17 17.1429" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <circle cx="17" cy="17" r="5" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <span class="text-sm font-medium text-black">Shift Requests</span>
            </a>
                </div>
            </div>

            <!-- Dashboard Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        <!-- Recent Activities -->
        <div class="lg:col-span-2 rounded-2xl bg-gray-100 shadow-lg">
            <div class="px-6 py-4 border-b rounded-tr-2xl rounded-tl-2xl bg-gray-200 border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Recent Activities</h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm text-gray-900">New employee <span class="font-medium">John Smith</span> added to Marketing department</p>
                            <p class="text-xs text-gray-500">2 hours ago</p>
                        </div>
                    </div>

                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm text-gray-900">Leave request from <span class="font-medium">Sarah Johnson</span> needs approval</p>
                            <p class="text-xs text-gray-500">4 hours ago</p>
                        </div>
                    </div>

                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm text-gray-900">Monthly payroll processed successfully</p>
                            <p class="text-xs text-gray-500">1 day ago</p>
                        </div>
                    </div>

                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm text-gray-900">IT department budget updated</p>
                            <p class="text-xs text-gray-500">2 days ago</p>
                        </div>
                    </div>
                </div>
                <div class="mt-6">
                    <a href="{{ route('activities') }}" class="text-blue-600 hover:text-blue-700 text-sm font-medium">View all activities →</a>
                </div>
            </div>
        </div>

        <!-- Upcoming Events -->
        <div class="bg-gray-100 rounded-2xl shadow-lg">
            <div class="px-6 py-4 border-b rounded-tr-2xl rounded-tl-2xl bg-gray-200 border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Upcoming Events</h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0 w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                            <span class="text-sm font-semibold text-red-700">18</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Team Building Event</p>
                            <p class="text-xs text-gray-500">Aug 18, 2025 • 2:00 PM</p>
                        </div>
                    </div>

                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0 w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <span class="text-sm font-semibold text-blue-700">22</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">HR Policy Review</p>
                            <p class="text-xs text-gray-500">Aug 22, 2025 • 10:00 AM</p>
                        </div>
                    </div>

                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0 w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                            <span class="text-sm font-semibold text-green-700">25</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Monthly All-Hands</p>
                            <p class="text-xs text-gray-500">Aug 25, 2025 • 3:00 PM</p>
                        </div>
                    </div>
                </div>
                <div class="mt-6">
                    <a href="{{ route('leave-management.calendar') }}" class="text-blue-600 hover:text-blue-700 text-sm font-medium">View calendar →</a>
                </div>
            </div>
            </div>
        </div>
    </div>
</div>
@endsection

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
