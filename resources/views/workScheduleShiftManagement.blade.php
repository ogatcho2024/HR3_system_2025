@extends('dashboard')

@section('title', 'Work Schedule & Shift Management')

@section('content')
<div class="py-2 px-3 md:p-6 max-w-full bg-gray-300" x-data="shiftManagement()" x-init="init()"
    x-cloak>
    <!-- Header -->
    <div class="mb-6 md:mb-8">
        
    </div>

    <!-- Overview Tab -->
    <div x-show="activeTab === 'overview'" class="space-y-6">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Total Shifts</p>
                        <p class="text-3xl font-bold text-blue-600">{{ $stats['total_shifts'] }}</p>
                        <p class="text-xs text-gray-500 mt-1">Active templates</p>
                    </div>
                    <div class="p-3 bg-blue-100 rounded-full">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Assigned Employees</p>
                        <p class="text-3xl font-bold text-green-600">{{ $stats['total_assigned_employees'] }}</p>
                        <p class="text-xs text-gray-500 mt-1">Across all shifts</p>
                    </div>
                    <div class="p-3 bg-green-100 rounded-full">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Pending Requests</p>
                        <p class="text-3xl font-bold text-yellow-600">{{ $stats['pending_requests'] }}</p>
                        <p class="text-xs text-gray-500 mt-1">Need approval</p>
                    </div>
                    <div class="p-3 bg-yellow-100 rounded-full">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Coverage Rate</p>
                        <p class="text-3xl font-bold text-purple-600">{{ $stats['coverage_rate'] }}%</p>
                        <p class="text-xs text-gray-500 mt-1">
                            @if($stats['coverage_rate'] >= 90)
                                Excellent coverage
                            @elseif($stats['coverage_rate'] >= 70)
                                Good coverage
                            @elseif($stats['coverage_rate'] >= 50)
                                Needs improvement
                            @else
                                Critical gaps
                            @endif
                        </p>
                    </div>
                    <div class="p-3 bg-purple-100 rounded-full">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <button @click="activeTab = 'shifts'; showShiftModal = true" class="flex items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                    <svg class="w-8 h-8 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <div class="text-left">
                        <div class="font-medium text-gray-900">Create Shift</div>
                        <div class="text-sm text-gray-600">New shift template</div>
                    </div>
                </button>

                <button @click="activeTab = 'assignments'" class="flex items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                    <svg class="w-8 h-8 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    <div class="text-left">
                        <div class="font-medium text-gray-900">Assign Employees</div>
                        <div class="text-sm text-gray-600">Manage assignments</div>
                    </div>
                </button>

                <button @click="activeTab = 'requests'" class="flex items-center p-4 bg-yellow-50 rounded-lg hover:bg-yellow-100 transition-colors">
                    <svg class="w-8 h-8 text-yellow-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <div class="text-left">
                        <div class="font-medium text-gray-900">Review Requests</div>
                        <div class="text-sm text-gray-600">{{ $stats['pending_requests'] }} pending</div>
                    </div>
                </button>

                <button @click="activeTab = 'schedule'" class="flex items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors">
                    <svg class="w-8 h-8 text-purple-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4m-4 8a4 4 0 01-4-4V7a4 4 0 118 0v4a4 4 0 01-4 4z"></path>
                    </svg>
                    <div class="text-left">
                        <div class="font-medium text-gray-900">View Schedule</div>
                        <div class="text-sm text-gray-600">Weekly calendar</div>
                    </div>
                </button>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Activity</h3>
            <div class="space-y-4">
                @forelse($recentActivities as $activity)
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0 w-8 h-8 bg-{{ $activity['color'] }}-100 rounded-full flex items-center justify-center">
                            @if($activity['icon'] === 'check')
                                <svg class="w-4 h-4 text-{{ $activity['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            @elseif($activity['icon'] === 'plus')
                                <svg class="w-4 h-4 text-{{ $activity['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                            @elseif($activity['icon'] === 'exclamation')
                                <svg class="w-4 h-4 text-{{ $activity['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                            @endif
                        </div>
                        <div class="flex-1">
                            <p class="text-sm text-gray-900">{!! $activity['message'] !!}</p>
                            <p class="text-xs text-gray-500">{{ $activity['time'] }}</p>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No recent activity</h3>
                        <p class="mt-1 text-sm text-gray-500">Activities will appear here as they happen.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Shift Templates Tab -->
    <div x-show="activeTab === 'shifts'" class="space-y-6">
        <div class="bg-white rounded-lg shadow-lg">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center">
                <h3 class="text-lg font-semibold text-gray-900">Shift Templates</h3>
                <button @click="activeTab = 'shifts'; showShiftModal = true" 
                    class="flex items-center p-2 bg-blue ml-auto rounded-lg hover:bg-blue-100 transition-colors">
                    + Shift Template
                </button>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Dynamic Shift Templates -->
                    <template x-for="shift in shiftTemplates" :key="shift.id">
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="font-medium text-gray-900" x-text="shift.name"></h4>
                                <span class="px-2 py-1 text-xs font-medium rounded-full"
                                      :class="shift.status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'"
                                      x-text="shift.status === 'active' ? 'Active' : 'Inactive'"></span>
                            </div>
                            <div class="space-y-2 text-sm text-gray-600">
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span x-text="shift.time_range || (shift.start_time + ' - ' + shift.end_time)"></span>
                                </div>
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4m-4 8a4 4 0 01-4-4V7a4 4 0 118 0v4a4 4 0 01-4 4z"></path>
                                    </svg>
                                    <span x-text="shift.formatted_days"></span>
                                </div>
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    <span x-text="(shift.assigned_employees_count || 0) + ' employees assigned'"></span>
                                </div>
                                <div x-show="shift.department" class="flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h1a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                    <span x-text="shift.department"></span>
                                </div>
                            </div>
                            <div class="mt-4 flex space-x-2">
                                <button @click="loadShiftForEdit(shift.id)" 
                                        class="flex-1 px-3 py-1 text-sm bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors">Edit</button>
                                <button @click="deleteShift(shift.id)" 
                                        class="ml-2 px-3 py-1 text-sm bg-red-600 text-white rounded hover:bg-red-700 transition-colors">Delete</button>
                            </div>
                        </div>
                    </template>
                    
                    <!-- Empty State -->
                    <div x-show="shiftTemplates.length === 0" class="col-span-full">
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No shift templates</h3>
                            <p class="mt-1 text-sm text-gray-500">Get started by creating your first shift template.</p>
                            <div class="mt-6">
                                <button @click="showShiftModal = true" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    Create Shift Template
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Shift Calendar Tab -->
    <div x-show="activeTab === 'schedule'" class="space-y-6">
        <div class="bg-white rounded-lg shadow-lg">
            <div class="px-4 md:px-6 py-4 border-b border-gray-200">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-3 md:space-y-0">
                    <h3 class="text-lg font-semibold text-gray-900">Shift Calendar - Employee Assignments</h3>
                    <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:items-center sm:space-x-4">
                        <div class="flex items-center space-x-2">
                            <button class="p-2 text-gray-400 hover:text-gray-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                </svg>
                            </button>
                            <span class="text-sm font-medium text-gray-900">Aug 12 - Aug 18, 2025</span>
                            <button class="p-2 text-gray-400 hover:text-gray-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </button>
                        </div>
                        <div class="flex space-x-2">
                            <button @click="openAssignModal()" class="px-2 py-1.5 bg-green-600 text-white text-xs sm:text-sm rounded-lg hover:bg-green-700 whitespace-nowrap">Assign Employee</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="p-4 md:p-6">
                <!-- Filters -->
                <div class="mb-4 md:mb-6">
                    <div class="flex flex-col sm:flex-row gap-2 md:gap-4">
                        <select class="flex-1 sm:flex-none px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                            <option>All Shifts</option>
                            <option>Morning Shift</option>
                            <option>Evening Shift</option>
                            <option>Night Shift</option>
                            <option>Weekend Shift</option>
                        </select>
                        <select class="flex-1 sm:flex-none px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                            <option>All Departments</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->department_code }}">{{ $department->department_name }}</option>
                            @endforeach
                        </select>
                        <button class="px-3 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm">Reset Filters</button>
                    </div>
                </div>

<!-- Calendar Grid -->
                <div class="overflow-x-auto rounded-lg shadow border border-gray-200">
                    <div class="min-w-[800px]">
                        <table class="w-full border-collapse divide-y divide-gray-200">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-2 py-3 text-left text-xs md:text-sm font-medium text-gray-500 border-b sticky left-0 bg-gray-50 z-10 w-28 md:w-32">Shift</th>
                                @php
                                    $currentWeek = \Carbon\Carbon::now()->startOfWeek();
                                    $weekDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                                @endphp
                                @foreach($weekDays as $index => $day)
                                    @php
                                        $dayDate = $currentWeek->copy()->addDays($index);
                                        $dayShort = substr($day, 0, 3);
                                    @endphp
                                    <th class="px-1 py-3 text-center text-xs md:text-sm font-medium text-gray-500 border-b min-w-[90px]">
                                        {{ $dayShort }}<br><span class="text-xs font-normal">{{ $dayDate->day }}</span>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($shiftCalendarData as $shift)
                                <tr>
                                    <td class="px-2 py-3 text-xs md:text-sm font-medium text-gray-900 border-b bg-{{ $shift['color'] }}-50 sticky left-0 z-10">
                                        <div class="flex items-center space-x-1 md:space-x-2">
                                            <div class="w-2 h-2 md:w-3 md:h-3 bg-{{ $shift['color'] }}-500 rounded-full flex-shrink-0"></div>
                                            <div class="min-w-0">
                                                <div class="font-medium truncate">{{ $shift['name'] }}</div>
                                                <div class="text-xs text-gray-500 truncate">{{ $shift['time_range'] }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    @foreach($weekDays as $day)
                                        @php
                                            $dayData = $shift['days'][$day] ?? ['total_count' => 0, 'departments' => []];
                                        @endphp
                                        <td class="px-1 py-3 text-center border-b">
                                            <div class="space-y-1">
                                                <div class="text-xs font-medium text-gray-900 mb-1">{{ $dayData['total_count'] }}</div>
                                                @if($dayData['total_count'] > 0)
                                                    <div class="space-y-1">
                                                        @foreach($dayData['departments'] as $deptName => $deptInfo)
                                                            @php
                                                                $employeeNames = collect($deptInfo['employees'])->pluck('name')->implode(', ');
                                                                $tooltipText = $employeeNames;
                                                                if(count($deptInfo['employees']) > 3) {
                                                                    $displayNames = collect($deptInfo['employees'])->take(3)->pluck('initials')->implode(', ');
                                                                    $remaining = count($deptInfo['employees']) - 3;
                                                                    $tooltipText = $displayNames . ", +{$remaining} more";
                                                                }
                                                            @endphp
                                                            <div class="bg-{{ $deptInfo['color'] }}-100 text-{{ $deptInfo['color'] }}-800 text-xs px-1 py-0.5 rounded cursor-pointer hover:bg-{{ $deptInfo['color'] }}-200" 
                                                                 title="{{ $tooltipText }}">
                                                                {{ strtoupper(substr($deptName, 0, 3)) }} ({{ $deptInfo['count'] }})
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <div class="text-xs text-gray-400">No staff</div>
                                                @endif
                                            </div>
                                        </td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center">
                                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <h3 class="mt-2 text-sm font-medium text-gray-900">No shift assignments</h3>
                                            <p class="mt-1 text-sm text-gray-500">Create some shift templates and assign employees to see the schedule.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Legend -->
                <div class="mt-6 border-t pt-4">
                    <h4 class="text-sm font-medium text-gray-900 mb-3">Department Legend</h4>
                    <div class="flex flex-wrap gap-2 md:gap-4">
                        @php
                            $colors = ['blue', 'green', 'purple', 'orange', 'red', 'yellow', 'indigo', 'pink'];
                        @endphp
                        @foreach($departments as $index => $department)
                            @php
                                $color = $colors[$index % count($colors)];
                            @endphp
                            <div class="flex items-center space-x-2">
                                <div class="w-3 h-3 bg-{{ $color }}-500 rounded"></div>
                                <span class="text-xs text-gray-600">{{ $department->department_name }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="mt-6 grid grid-cols-2 md:grid-cols-4 gap-2 md:gap-4">
                    <div class="bg-blue-50 rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold text-blue-600">85</div>
                        <div class="text-xs text-gray-600">Total Assigned</div>
                    </div>
                    <div class="bg-green-50 rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold text-green-600">94%</div>
                        <div class="text-xs text-gray-600">Coverage Rate</div>
                    </div>
                    <div class="bg-yellow-50 rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold text-yellow-600">3</div>
                        <div class="text-xs text-gray-600">Understaffed</div>
                    </div>
                    <div class="bg-red-50 rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold text-red-600">2</div>
                        <div class="text-xs text-gray-600">Critical Gaps</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Employee Assignments Tab -->
    <div x-show="activeTab === 'assignments'" class="space-y-6">
        <div class="bg-white rounded-lg shadow-lg">
            <div class="px-4 md:px-6 py-4 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-3 sm:space-y-0">
                    <h3 class="text-lg font-semibold text-gray-900">Employee Shift Assignments</h3>
                    <button @click="openAssignModal()" class="px-3 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 whitespace-nowrap">Assign Employee</button>
                </div>
            </div>
            <div class="p-4 md:p-6">
                <div class="mb-4 md:mb-6">
                    <div class="flex flex-col sm:flex-row gap-2 md:gap-4">
                        <div class="flex-1">
                            <input x-model="assignmentFilters.search" 
                                   @input="filterAssignments()"
                                   type="search" 
                                   placeholder="Search employees..." 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                        </div>
                        <select x-model="assignmentFilters.shift" 
                                @change="filterAssignments()"
                                class="flex-1 sm:flex-none px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                            <option value="">All Shifts</option>
                            <template x-for="template in shiftTemplates" :key="template.id">
                                <option :value="template.name" x-text="template.name"></option>
                            </template>
                        </select>
                        <select x-model="assignmentFilters.department" 
                                @change="filterAssignments()"
                                class="flex-1 sm:flex-none px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                            <option value="">All Departments</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->department_code }}" x-text="'{{ $department->department_name }}'"></option>
                            @endforeach
                        </select>
                        <button @click="resetFilters()" 
                                class="px-3 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm whitespace-nowrap">Reset</button>
                    </div>
                </div>
                <div class="overflow-x-auto rounded-lg shadow border border-gray-200">
                    <div class="min-w-[700px]">
                        <table class="w-full border-collapse divide-y divide-gray-200">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                                    <th class="px-2 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                                    <th class="px-2 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Shift</th>
                                    <th class="px-2 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">Schedule</th>
                                    <th class="px-2 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Started</th>
                                    <th class="px-2 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <template x-for="assignment in filteredAssignments" :key="assignment.id">
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-3 md:px-6 py-3 md:py-4">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-8 w-8 md:h-10 md:w-10">
                                                    <div x-show="assignment.employee.avatar">
                                                        <img class="h-8 w-8 md:h-10 md:w-10 rounded-full object-cover" 
                                                             :src="assignment.employee.avatar" 
                                                             :alt="assignment.employee.name">
                                                    </div>
                                                    <div x-show="!assignment.employee.avatar" 
                                                         class="h-8 w-8 md:h-10 md:w-10 rounded-full flex items-center justify-center text-white font-medium text-xs md:text-sm"
                                                         :class="'bg-' + (assignment.employee.avatar_color || 'blue') + '-500'"
                                                         x-text="assignment.employee.initials">
                                                    </div>
                                                </div>
                                                <div class="ml-2 md:ml-4 min-w-0">
                                                    <div class="text-xs md:text-sm font-medium text-gray-900 truncate" x-text="assignment.employee.name"></div>
                                                    <div class="text-xs text-gray-500 truncate" x-text="assignment.employee.email"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-2 md:px-6 py-3 md:py-4 text-xs md:text-sm text-gray-900" x-text="assignment.employee.department"></td>
                                        <td class="px-2 md:px-6 py-3 md:py-4">
                                            <span class="px-1.5 md:px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                                  :class="'bg-' + (assignment.shift.color || 'blue') + '-100 text-' + (assignment.shift.color || 'blue') + '-800'"
                                                  x-text="assignment.shift.name">
                                            </span>
                                            <div class="text-xs text-gray-500 mt-1 lg:hidden" x-text="assignment.shift.time_range"></div>
                                        </td>
                                        <td class="px-2 md:px-6 py-3 md:py-4 text-xs md:text-sm text-gray-900 hidden lg:table-cell" x-text="assignment.schedule"></td>
                                        <td class="px-2 md:px-6 py-3 md:py-4 text-xs md:text-sm text-gray-900" x-text="assignment.start_date"></td>
                                        <td class="px-2 md:px-6 py-3 md:py-4 text-xs md:text-sm font-medium">
                                            <div class="flex flex-col sm:flex-row space-y-1 sm:space-y-0 sm:space-x-2">
                                                <button @click="editAssignment(assignment.id)" 
                                                        class="text-blue-600 hover:text-blue-900 text-xs md:text-sm">Edit</button>
                                                <button @click="removeAssignment(assignment.id)" 
                                                        class="text-red-600 hover:text-red-900 text-xs ml-2 md:text-sm">Remove</button>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="filteredAssignments.length === 0">
                                    <td colspan="6" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center">
                                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                            <h3 class="mt-2 text-sm font-medium text-gray-900">No employee assignments found</h3>
                                            <p class="mt-1 text-sm text-gray-500" x-text="employeeAssignments.length === 0 ? 'Get started by assigning employees to shifts.' : 'Try adjusting your filters or search terms.'"></p>
                                            <div class="mt-6" x-show="employeeAssignments.length === 0">
                                                <button @click="openAssignModal()" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                    </svg>
                                                    Assign First Employee
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Shift Requests Tab -->
    <div x-show="activeTab === 'requests'" class="space-y-6">
        <div class="bg-white rounded-lg shadow-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Pending Shift Requests</h3>
            </div>
            
            <!-- Success/Error Messages -->
            @if(session('success'))
                <div class="mx-6 mt-4 p-4 bg-green-100 border border-green-300 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif
            
            @if(session('error'))
                <div class="mx-6 mt-4 p-4 bg-red-100 border border-red-300 text-red-700 rounded">
                    {{ session('error') }}
                </div>
            @endif
            <div class="p-6">
                @if(count($pendingShiftRequests) > 0)
                    <div class="space-y-4">
                        @foreach($pendingShiftRequests as $request)
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex items-start justify-between">
                                    <div class="flex items-start space-x-3">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-{{ $request['user']['avatar_color'] }}-500 flex items-center justify-center text-white font-medium">{{ $request['user']['initials'] }}</div>
                                        </div>
                                        <div class="flex-1">
                                            <h4 class="text-sm font-medium text-gray-900">{{ $request['user']['name'] }}</h4>
                                            <p class="text-sm text-gray-600">{{ $request['user']['department'] }}</p>
                                            <div class="mt-2">
                                                <p class="text-xs text-gray-500">Requested: {{ $request['created_at'] }}</p>
                                                <p class="text-sm text-gray-900 mt-1">{!! $request['readable_request'] !!}</p>
                                                @if($request['requested_date'])
                                                    <p class="text-xs text-gray-600 mt-1">Date: {{ $request['requested_date'] }}</p>
                                                @endif
                                                <p class="text-xs text-gray-600 mt-1">Reason: {{ $request['reason'] }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex flex-col space-y-2">
                                        <span class="px-2 py-1 text-xs font-medium rounded-full {{ $request['status_badge_color'] }}">{{ ucfirst($request['status']) }}</span>
                                        @if($request['status'] === 'pending')
                                            <div class="flex space-x-2">
                                                <form method="POST" action="{{ route('shift-management.api.shift-requests.approve', $request['id']) }}" style="display: inline;">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="comments" value="Approved">
                                                    <button type="submit" class="px-3 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700" onclick="return confirm('Are you sure you want to approve this request?')">Approve</button>
                                                </form>
                                                <form method="POST" action="{{ route('shift-management.api.shift-requests.reject', $request['id']) }}" style="display: inline;">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="comments" value="Rejected">
                                                    <button type="submit" class="px-3 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700" onclick="return confirm('Are you sure you want to reject this request?')">Reject</button>
                                                </form>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No pending shift requests</h3>
                        <p class="mt-1 text-sm text-gray-500">All shift requests have been processed or no requests have been submitted yet.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Create Shift Modal -->
    <div x-show="showShiftModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div x-show="showShiftModal" 
                 x-transition:enter="ease-out duration-300" 
                 x-transition:enter-start="opacity-0" 
                 x-transition:enter-end="opacity-100" 
                 x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100" 
                 x-transition:leave-end="opacity-0" 
                 @click="showShiftModal = false"
                 class="fixed inset-0 bg-opacity-3 backdrop-blur-sm transition-opacity" 
                 aria-hidden="true"></div>
            
            <!-- This element is to trick the browser into centering the modal contents. -->
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            
            <!-- Modal panel -->
            <div x-show="showShiftModal" 
                 x-transition:enter="ease-out duration-300" 
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 @click.stop
                 class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                
                <!-- Modal Header -->
                <div class="bg-white px-4 pt-6 pb-4 sm:px-6 border-b border-gray-200">
                    <div class="text-center sm:text-left">
                        <h3 class="text-xl leading-6 font-semibold text-gray-900" id="modal-title"
                            x-text="isEditMode ? 'Edit Shift Template' : 'Create New Shift Template'">
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-600"
                               x-text="isEditMode ? 'Update the shift template with new times and working days.' : 'Define a new shift template with specific times and working days.'">
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Modal Body -->
                <div class="bg-white px-4 pb-4 sm:px-6">
                    <form @submit.prevent="createShift()" class="space-y-4">
                        <!-- Shift Name -->
                        <div>
                            <label for="shift-name" class="block text-sm font-medium text-gray-700 mb-1">Shift Name <span class="text-red-500">*</span></label>
                            <input type="text" 
                                   id="shift-name"
                                   x-model="shiftForm.name"
                                   placeholder="e.g. Morning Shift, Weekend Support" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                   required>
                        </div>
                        
                        <!-- Time Range -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="start-time" class="block text-sm font-medium text-gray-700 mb-1">Start Time <span class="text-red-500">*</span></label>
                                <input type="time" 
                                       id="start-time"
                                       x-model="shiftForm.startTime"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                       required>
                            </div>
                            <div>
                                <label for="end-time" class="block text-sm font-medium text-gray-700 mb-1">End Time <span class="text-red-500">*</span></label>
                                <input type="time" 
                                       id="end-time"
                                       x-model="shiftForm.endTime"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                       required>
                            </div>
                        </div>
                        
                        <!-- Department -->
                        <div>
                            <label for="department" class="block text-sm font-medium text-gray-700 mb-1">Primary Department</label>
                            <select id="department" 
                                    x-model="shiftForm.department"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                                <option value="">Select Department (Optional)</option>
                                <option value="IT">IT Department</option>
                                <option value="Marketing">Marketing</option>
                                <option value="Finance">Finance</option>
                                <option value="HR">Human Resources</option>
                                <option value="Operations">Operations</option>
                                <option value="Security">Security</option>
                                <option value="Maintenance">Maintenance</option>
                            </select>
                        </div>
                        
                        <!-- Schedule Options -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Schedule Type <span class="text-red-500">*</span></label>
                            <div class="space-y-3">
                                <!-- Option 1: Recurring Weekly -->
                                <label class="flex items-start space-x-3 p-3 border rounded-lg cursor-pointer hover:bg-gray-50"
                                       :class="shiftForm.scheduleType === 'weekly' ? 'bg-blue-50 border-blue-300' : 'border-gray-300'">
                                    <input type="radio" 
                                           value="weekly"
                                           x-model="shiftForm.scheduleType"
                                           class="mt-0.5">
                                    <div>
                                        <div class="font-medium text-sm text-gray-900">Recurring Weekly Schedule</div>
                                        <div class="text-xs text-gray-600">Select specific days of the week that repeat every week</div>
                                    </div>
                                </label>
                                
                                <!-- Option 2: Specific Dates -->
                                <label class="flex items-start space-x-3 p-3 border rounded-lg cursor-pointer hover:bg-gray-50"
                                       :class="shiftForm.scheduleType === 'dates' ? 'bg-blue-50 border-blue-300' : 'border-gray-300'">
                                    <input type="radio" 
                                           value="dates"
                                           x-model="shiftForm.scheduleType"
                                           class="mt-0.5">
                                    <div>
                                        <div class="font-medium text-sm text-gray-900">Specific Dates</div>
                                        <div class="text-xs text-gray-600">Select specific dates from a calendar view</div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Weekly Days Selection -->
                        <div x-show="shiftForm.scheduleType === 'weekly'" x-transition>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Working Days</label>
                            <div class="grid grid-cols-7 gap-2">
                                <template x-for="(day, index) in ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']" :key="day">
                                    <label class="flex flex-col items-center p-2 border rounded cursor-pointer hover:bg-gray-50"
                                           :class="shiftForm.days.includes(day) ? 'bg-blue-50 border-blue-300' : 'border-gray-300'">
                                        <input type="checkbox" 
                                               :value="day"
                                               x-model="shiftForm.days"
                                               class="sr-only">
                                        <span class="text-xs font-medium"
                                              :class="shiftForm.days.includes(day) ? 'text-blue-700' : 'text-gray-600'"
                                              x-text="day.substring(0, 3)"></span>
                                        <div class="w-2 h-2 rounded-full mt-1"
                                             :class="shiftForm.days.includes(day) ? 'bg-blue-500' : 'bg-gray-300'"></div>
                                    </label>
                                </template>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Select at least one working day</p>
                        </div>

                        <!-- Calendar Date Selection -->
                        <div x-show="shiftForm.scheduleType === 'dates'" x-transition>
                            <div class="space-y-3">
                                <!-- Calendar Navigation -->
                                <div class="flex items-center justify-between">
                                    <button type="button" @click="previousMonth()" class="p-1 rounded hover:bg-gray-100">
                                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                        </svg>
                                    </button>
                                    <h3 class="font-medium text-gray-900" x-text="currentMonthYear"></h3>
                                    <button type="button" @click="nextMonth()" class="p-1 rounded hover:bg-gray-100">
                                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </button>
                                </div>
                                
                                <!-- Calendar Grid -->
                                <div class="border rounded-lg p-3 bg-gray-50">
                                    <!-- Day Headers -->
                                    <div class="grid grid-cols-7 gap-1 mb-2">
                                        <template x-for="day in ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']">
                                            <div class="text-center text-xs font-medium text-gray-500 py-1" x-text="day"></div>
                                        </template>
                                    </div>
                                    
                                    <!-- Calendar Days -->
                                    <div class="grid grid-cols-7 gap-1">
                                        <template x-for="day in calendarDays" :key="day.date">
                                            <button type="button" 
                                                    @click="toggleDate(day.dateString)"
                                                    :disabled="day.isPast"
                                                    class="relative h-8 text-xs rounded transition-colors"
                                                    :class="{
                                                        'text-gray-300': day.isOtherMonth,
                                                        'text-gray-400 cursor-not-allowed': day.isPast,
                                                        'text-gray-900 hover:bg-blue-100': day.isCurrentMonth && !day.isPast && !shiftForm.selectedDates.includes(day.dateString),
                                                        'bg-blue-500 text-white': shiftForm.selectedDates.includes(day.dateString),
                                                        'bg-red-100 text-red-800': day.isToday && !shiftForm.selectedDates.includes(day.dateString),
                                                        'ring-2 ring-red-300': day.isToday
                                                    }">
                                                <span x-text="day.day"></span>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                                
                                <div class="flex items-center justify-between text-xs text-gray-500">
                                    <span x-text="shiftForm.selectedDates.length + ' dates selected'"></span>
                                    <button type="button" @click="clearSelectedDates()" class="text-blue-600 hover:text-blue-800">Clear All</button>
                                </div>
                                
                                <p class="text-xs text-gray-500">Select specific dates for this shift schedule. Past dates are disabled.</p>
                            </div>
                        </div>
                        
                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description (Optional)</label>
                            <textarea id="description"
                                      x-model="shiftForm.description"
                                      placeholder="Brief description of this shift template..." 
                                      rows="3" 
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm resize-none"></textarea>
                        </div>
                    </form>
                </div>
                
                <!-- Modal Footer -->
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-3">
                    <button type="button" 
                            @click="createShift()"
                            class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 hover:shadow-md">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <span x-text="isEditMode ? 'Update Shift' : 'Create Shift'"></span>
                    </button>
                    <button type="button" 
                            @click="showShiftModal = false; resetShiftForm()"
                            class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-2.5 bg-white text-gray-700 text-sm font-medium border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Assign Employee Modal -->
    <div x-show="showAssignModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="assign-modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div x-show="showAssignModal" 
                 x-transition:enter="ease-out duration-300" 
                 x-transition:enter-start="opacity-0" 
                 x-transition:enter-end="opacity-100" 
                 x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100" 
                 x-transition:leave-end="opacity-0" 
                 @click="showAssignModal = false"
                 class="fixed inset-0 bg-opacity-3 backdrop-blur-sm transition-opacity" 
                 aria-hidden="true"></div>
            
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            
            <!-- Modal panel -->
            <div x-show="showAssignModal" 
                 x-transition:enter="ease-out duration-300" 
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 @click.stop
                 class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                
                <!-- Modal content for Assign Employee -->
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="assign-modal-title"
                                x-text="editingAssignmentId ? 'Edit Employee Assignment' : 'Assign Employee to Shift'">
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500"
                                   x-text="editingAssignmentId ? 'Update the employee assignment details.' : 'Select an employee and assign them to a specific shift schedule.'">
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white px-4 pb-4 sm:px-6">
                    <form class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Select Employee 
                                @if(count($availableEmployees) > 0)
                                    <span class="text-xs text-gray-500 font-normal">({{ count($availableEmployees) }} available)</span>
                                @endif
                            </label>
                            <select x-model="assignForm.employee_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                                <option value="">Choose an employee...</option>
                                @foreach($availableEmployees as $employee)
                                    <option value="{{ $employee['id'] }}">{{ $employee['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Shift Template</label>
                            <select x-model="assignForm.shift_template_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                                <option value="">Select shift template...</option>
                                <template x-for="template in shiftTemplates" :key="template.id">
                                    <option :value="template.id" x-text="template.name + ' (' + template.start_time + ' - ' + template.end_time + ')'"></option>
                                </template>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                            <input x-model="assignForm.start_date" type="date" :min="new Date().toISOString().split('T')[0]" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Notes (Optional)</label>
                            <textarea x-model="assignForm.notes" placeholder="Any special notes or instructions..." rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm resize-none"></textarea>
                        </div>
                    </form>
                </div>
                
                <!-- Modal Footer -->
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <!-- Primary Button -->
                    <button type="button" 
                            @click="assignEmployee()"
                            :disabled="assignmentLoading"
                            class="assign-modal-button w-full ml-2 mt-3 sm:w-auto inline-flex items-center justify-center gap-2 rounded-lg border border-transparent shadow-sm 
                                bg-green-600 text-white 
                                hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500
                                transition-colors duration-200
                                disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg x-show="!assignmentLoading" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <svg x-show="assignmentLoading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-text="assignmentLoading ? (editingAssignmentId ? 'Updating...' : 'Assigning...') : (editingAssignmentId ? 'Update Assignment' : 'Assign Employee')"></span>
                    </button>

                    <!-- Secondary Button -->
                    <button type="button" 
                            @click="showAssignModal = false"
                            class="assign-modal-cancel-button mt-3 w-full sm:mt-0 sm:w-auto sm:ml-3 inline-flex items-center justify-center rounded-lg border border-gray-300 shadow-sm 
                                bg-white text-gray-700 
                                hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500
                                transition-colors duration-200">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
  [x-cloak] { display: none !important; }
  
  /* Fix for assign employee modal button sizing */
  .assign-modal-button {
    min-height: 36px !important;
    max-height: 40px !important;
    padding: 8px 16px !important;
    font-size: 14px !important;
    font-weight: 500 !important;
  }
  
  .assign-modal-cancel-button {
    min-height: 36px !important;
    max-height: 40px !important;
    padding: 8px 16px !important;
    font-size: 14px !important;
    font-weight: 500 !important;
  }
</style>

<script>
// API Base URL - uses Laravel's url() helper to work in any environment
const API_BASE_URL = '{{ url("/shift-management/api") }}';

function shiftManagement() {
    return {
        activeTab: '{{ $activeTab }}',
        selectedShift: null,
        showShiftModal: false,
        showEditModal: false,
        showAssignModal: false,
        currentWeek: new Date(),
        isEditMode: false,
        shiftTemplates: @json($shiftTemplates ?? []),
        loading: false,
        
        // Assignment form data (now using server-side data, no need for JavaScript array)
        // availableEmployees populated server-side
        employeeAssignments: @json($employeeAssignments ?? []),
        filteredAssignments: [],
        assignmentFilters: {
            search: '',
            shift: '',
            department: ''
        },
        assignForm: {
            employee_id: '',
            shift_template_id: '',
            start_date: '',
            notes: ''
        },
        assignmentLoading: false,
        loadingEmployees: false,
        editingAssignmentId: null,
        shiftForm: {
            id: null,
            name: '',
            startTime: '',
            endTime: '',
            days: [],
            description: '',
            department: '',
            scheduleType: 'weekly',
            selectedDates: []
        },
        
        // Calendar properties
        currentCalendarDate: new Date(),

        init() {
            console.log('🎬 Alpine.js shiftManagement component initialized!');
            console.log('📋 Initial availableEmployees:', this.availableEmployees);
            console.log('🔧 Initial loadingEmployees:', this.loadingEmployees);
            this.filterAssignments();
        },

        filterAssignments() {
            const search = this.assignmentFilters.search.toLowerCase();
            const shiftFilter = this.assignmentFilters.shift;
            const departmentFilter = this.assignmentFilters.department;
            
            this.filteredAssignments = this.employeeAssignments.filter(assignment => {
                // Search filter (name or email)
                const matchesSearch = !search || 
                    assignment.employee.name.toLowerCase().includes(search) ||
                    assignment.employee.email.toLowerCase().includes(search);
                
                // Shift filter
                const matchesShift = !shiftFilter || assignment.shift.name === shiftFilter;
                
                // Department filter
                const matchesDepartment = !departmentFilter || assignment.employee.department === departmentFilter;
                
                return matchesSearch && matchesShift && matchesDepartment;
            });
        },
        
        resetFilters() {
            this.assignmentFilters = {
                search: '',
                shift: '',
                department: ''
            };
            this.filterAssignments();
        },

        async editAssignment(assignmentId) {
            const assignment = this.employeeAssignments.find(a => a.id === assignmentId);
            if (!assignment) {
                alert('Assignment not found');
                return;
            }
            
            // Pre-populate the assignment form with current data
            this.assignForm = {
                employee_id: assignment.employee.id || assignment.employee.employee_id,
                shift_template_id: assignment.shift.id || assignment.shift.shift_template_id,
                start_date: assignment.start_date.includes('/') ? 
                    new Date(assignment.start_date).toISOString().split('T')[0] : 
                    assignment.start_date,
                notes: assignment.notes || ''
            };
            
            // Open the assign modal in edit mode
            this.editingAssignmentId = assignmentId;
            this.openAssignModal();
        },
        
        async removeAssignment(assignmentId) {
            if (!confirm('Are you sure you want to remove this employee assignment?')) {
                return;
            }
            
            try {
                const response = await fetch(`${API_BASE_URL}/assignments/${assignmentId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Remove from local array
                    this.employeeAssignments = this.employeeAssignments.filter(a => a.id !== assignmentId);
                    this.filterAssignments();
                    alert('Assignment removed successfully!');
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                console.error('Error removing assignment:', error);
                alert('An error occurred while removing the assignment.');
            }
        },

        resetShiftForm() {
            this.shiftForm = {
                id: null,
                name: '',
                startTime: '',
                endTime: '',
                days: [],
                description: '',
                department: '',
                scheduleType: 'weekly',
                selectedDates: []
            };
            this.isEditMode = false;
            this.currentCalendarDate = new Date();
        },

        loadShiftForEdit(shiftId) {
            const shift = this.shiftTemplates.find(s => s.id === shiftId);
            if (shift) {
                this.shiftForm = {
                    id: shift.id,
                    name: shift.name,
                    startTime: shift.start_time,
                    endTime: shift.end_time,
                    days: shift.days || [],
                    description: shift.description || '',
                    department: shift.department || '',
                    scheduleType: 'weekly',
                    selectedDates: []
                };
                this.isEditMode = true;
                this.showShiftModal = true;
            }
        },

        async createShift() {
            // Validation based on schedule type
            if (!this.shiftForm.name || !this.shiftForm.startTime || !this.shiftForm.endTime) {
                alert('Please fill in all required fields');
                return;
            }
            
            if (this.shiftForm.scheduleType === 'weekly' && this.shiftForm.days.length === 0) {
                alert('Please select at least one working day');
                return;
            }
            
            if (this.shiftForm.scheduleType === 'dates' && this.shiftForm.selectedDates.length === 0) {
                alert('Please select at least one specific date');
                return;
            }

            // Check for CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (!csrfToken) {
                alert('CSRF token not found. Please refresh the page and try again.');
                return;
            }

            this.loading = true;

            try {
                const url = this.isEditMode 
                    ? `${API_BASE_URL}/templates/${this.shiftForm.id}`
                    : `${API_BASE_URL}/templates`;
                const method = this.isEditMode ? 'PUT' : 'POST';

                const requestData = {
                    name: this.shiftForm.name,
                    start_time: this.shiftForm.startTime,
                    end_time: this.shiftForm.endTime,
                    days: this.shiftForm.scheduleType === 'weekly' ? this.shiftForm.days : [],
                    schedule_type: this.shiftForm.scheduleType,
                    selected_dates: this.shiftForm.scheduleType === 'dates' ? this.shiftForm.selectedDates : [],
                    department: this.shiftForm.department || null,
                    description: this.shiftForm.description || null
                };

                console.log('Sending request:', {
                    url: url,
                    method: method,
                    data: requestData
                });

                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(requestData)
                });

                console.log('Response status:', response.status);
                console.log('Response ok:', response.ok);
                
                const result = await response.json();
                console.log('Server response:', result);

                if (result.success) {
                    if (this.isEditMode) {
                        const index = this.shiftTemplates.findIndex(s => s.id === this.shiftForm.id);
                        if (index !== -1) {
                            this.shiftTemplates[index] = result.data;
                        }
                    } else {
                        this.shiftTemplates.push(result.data);
                    }

                    alert(result.message);
                    this.resetShiftForm();
                    this.showShiftModal = false;
                } else {
                    let errorMessage = 'Error: ' + (result.message || 'Unknown server error');
                    if (result.errors) {
                        console.error('Validation errors:', result.errors);
                        const errorList = [];
                        Object.keys(result.errors).forEach(field => {
                            if (result.errors[field] && Array.isArray(result.errors[field])) {
                                result.errors[field].forEach(error => {
                                    errorList.push(`${field}: ${error}`);
                                });
                            }
                        });
                        if (errorList.length > 0) {
                            errorMessage += '\n\nValidation Errors:\n' + errorList.join('\n');
                        }
                    }
                    alert(errorMessage);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred while saving the shift template.');
            } finally {
                this.loading = false;
            }
        },

        async deleteShift(shiftId) {
            if (!confirm('Are you sure you want to delete this shift template?')) {
                return;
            }

            try {
                const response = await fetch(`${API_BASE_URL}/templates/${shiftId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                const result = await response.json();

                if (result.success) {
                    this.shiftTemplates = this.shiftTemplates.filter(s => s.id !== shiftId);
                    alert(result.message);
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred while deleting the shift template.');
            }
        },
        
        // Calendar computed properties and methods
        get currentMonthYear() {
            const months = [
                'January', 'February', 'March', 'April', 'May', 'June',
                'July', 'August', 'September', 'October', 'November', 'December'
            ];
            return `${months[this.currentCalendarDate.getMonth()]} ${this.currentCalendarDate.getFullYear()}`;
        },
        
        get calendarDays() {
            const year = this.currentCalendarDate.getFullYear();
            const month = this.currentCalendarDate.getMonth();
            const today = new Date();
            
            // First day of the month
            const firstDay = new Date(year, month, 1);
            // Last day of the month
            const lastDay = new Date(year, month + 1, 0);
            // First day of the calendar grid (might be from previous month)
            const startDate = new Date(firstDay);
            startDate.setDate(firstDay.getDate() - firstDay.getDay());
            
            const days = [];
            const currentDate = new Date(startDate);
            
            // Generate 6 weeks (42 days) for the calendar grid
            for (let i = 0; i < 42; i++) {
                const isCurrentMonth = currentDate.getMonth() === month;
                const isToday = currentDate.toDateString() === today.toDateString();
                const isPast = currentDate < today && !isToday;
                const dateString = currentDate.toISOString().split('T')[0];
                
                days.push({
                    day: currentDate.getDate(),
                    date: new Date(currentDate),
                    dateString: dateString,
                    isCurrentMonth: isCurrentMonth,
                    isOtherMonth: !isCurrentMonth,
                    isToday: isToday,
                    isPast: isPast
                });
                
                currentDate.setDate(currentDate.getDate() + 1);
            }
            
            return days;
        },
        
        previousMonth() {
            this.currentCalendarDate = new Date(this.currentCalendarDate.getFullYear(), this.currentCalendarDate.getMonth() - 1, 1);
        },
        
        nextMonth() {
            this.currentCalendarDate = new Date(this.currentCalendarDate.getFullYear(), this.currentCalendarDate.getMonth() + 1, 1);
        },
        
        toggleDate(dateString) {
            const index = this.shiftForm.selectedDates.indexOf(dateString);
            if (index > -1) {
                this.shiftForm.selectedDates.splice(index, 1);
            } else {
                this.shiftForm.selectedDates.push(dateString);
            }
        },
        
        clearSelectedDates() {
            this.shiftForm.selectedDates = [];
        },
        
        // Assignment-related methods
        // Note: Employee loading is now handled server-side, no API calls needed
        
        async assignEmployee() {
            // Validation
            if (!this.assignForm.employee_id || !this.assignForm.shift_template_id || !this.assignForm.start_date) {
                alert('Please fill in all required fields');
                return;
            }
            
            this.assignmentLoading = true;
            
            try {
                const isEditing = this.editingAssignmentId !== null;
                const url = isEditing 
                    ? `${API_BASE_URL}/assignments/${this.editingAssignmentId}`
                    : `${API_BASE_URL}/assignments`;
                const method = isEditing ? 'PUT' : 'POST';
                
                const response = await fetch(url, {
                    method: method,
                    credentials: 'same-origin', // Include cookies for authentication
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest' // Identify as AJAX request
                    },
                    body: JSON.stringify({
                        employee_id: this.assignForm.employee_id,
                        shift_template_id: this.assignForm.shift_template_id,
                        start_date: this.assignForm.start_date,
                        notes: this.assignForm.notes
                    })
                });
                
                console.log('📊 Assignment API Response status:', response.status);
                console.log('📊 Assignment API Response ok:', response.ok);
                
                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('❌ Assignment API Error:', errorText);
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                const result = await response.json();
                console.log('📊 Assignment API Result:', result);
                
                if (result.success) {
                    if (isEditing) {
                        // Update existing assignment in local array
                        const index = this.employeeAssignments.findIndex(a => a.id === this.editingAssignmentId);
                        if (index !== -1) {
                            this.employeeAssignments[index] = result.data;
                        }
                        this.editingAssignmentId = null;
                    } else {
                        // Add new assignment to local array
                        this.employeeAssignments.push(result.data);
                    }
                    
                    // Update filtered results
                    this.filterAssignments();
                    
                    // Reset form
                    this.assignForm = {
                        employee_id: '',
                        shift_template_id: '',
                        start_date: '',
                        notes: ''
                    };
                    
                    // Close modal
                    this.showAssignModal = false;
                    
                    // Show success message
                    alert(isEditing ? 'Assignment updated successfully!' : 'Employee assigned successfully!');
                    
                    // Refresh calendar if on calendar tab
                    if (this.activeTab === 'calendar') {
                        // Calendar will automatically refresh on next load
                    }
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                console.error('Error assigning employee:', error);
                alert('An error occurred while assigning the employee.');
            } finally {
                this.assignmentLoading = false;
            }
        },
        
        // Initialize data when modal opens
        openAssignModal() {
            console.log('🚀 openAssignModal function called - using server-side employee data');
            this.showAssignModal = true;
            // No need to load employees via API - they're already in the HTML
            
            // If not in editing mode, reset the form
            if (this.editingAssignmentId === null) {
                this.assignForm = {
                    employee_id: '',
                    shift_template_id: '',
                    start_date: '',
                    notes: ''
                };
            }
        }
    }
}
</script>

@endsection
