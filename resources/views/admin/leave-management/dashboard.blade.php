@extends('dashboard')

@section('title', 'Leave Management Admin Dashboard')

@push('styles')
<style>
    .tab-button.active {
        border-color: #3B82F6;
        color: #3B82F6;
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-3xl font-bold text-gray-900">Leave Management</h3>
                    </div>
                </div>
            </div>

            <!-- Leave Management Overview Content -->
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

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                    <!-- Today's Leave Requests -->
                    <div class="bg-white rounded-lg shadow-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Employees on Leave Today</h3>
                        </div>
                        <div class="p-6">
                            @if($todayLeaveRequests->count() > 0)
                                <div class="space-y-4">
                                    @foreach($todayLeaveRequests->take(5) as $request)
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center text-white font-medium">
                                                {{ substr($request->user->name, 0, 1) }}{{ substr($request->user->lastname, 0, 1) }}
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-gray-900">{{ $request->user->name }} {{ $request->user->lastname }}</p>
                                                <p class="text-xs text-gray-500">{{ $request->user->employee->department ?? 'N/A' }}</p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-sm font-medium text-gray-900">{{ $request->leave_type }}</p>
                                            <p class="text-xs text-gray-500">{{ $request->start_date->format('M j') }} - {{ $request->end_date->format('M j') }}</p>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                @if($todayLeaveRequests->count() > 5)
                                <p class="text-sm text-gray-500 mt-4 text-center">And {{ $todayLeaveRequests->count() - 5 }} more employees...</p>
                                @endif
                            @else
                                <div class="text-center py-8">
                                    <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                    </svg>
                                    <p class="text-gray-500">No employees on leave today</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Department Coverage -->
                    <div class="bg-white rounded-lg shadow-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Critical Department Coverage</h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                @foreach($departmentCoverage as $dept => $coverage)
                                <div class="p-4 border border-gray-200 rounded-lg">
                                    <div class="flex justify-between items-center mb-2">
                                        <h4 class="font-medium text-gray-900">{{ $dept }}</h4>
                                        <span class="px-2 py-1 text-xs font-medium rounded-full 
                                            {{ $coverage['coverage_percentage'] >= 80 ? 'bg-green-100 text-green-800' : 
                                               ($coverage['coverage_percentage'] >= 60 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                            {{ round($coverage['coverage_percentage']) }}% Coverage
                                        </span>
                                    </div>
                                    <div class="flex items-center text-sm text-gray-600">
                                        <span>{{ $coverage['available'] }}/{{ $coverage['total'] }} Available</span>
                                        @if($coverage['on_leave'] > 0)
                                        <span class="ml-4 text-orange-600">{{ $coverage['on_leave'] }} on leave</span>
                                        @endif
                                    </div>
                                    <div class="mt-2 w-full bg-gray-200 rounded-full h-2">
                                        <div class="h-2 rounded-full 
                                            {{ $coverage['coverage_percentage'] >= 80 ? 'bg-green-500' : 
                                               ($coverage['coverage_percentage'] >= 60 ? 'bg-yellow-500' : 'bg-red-500') }}" 
                                            style="width: {{ $coverage['coverage_percentage'] }}%"></div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Leave Utilization Chart -->
                    <div class="bg-white rounded-lg shadow-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Leave Utilization by Type</h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                @foreach($leaveUtilization as $utilization)
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <div class="flex justify-between mb-1">
                                            <span class="text-sm font-medium text-gray-700">{{ $utilization->leave_type }}</span>
                                            <span class="text-sm text-gray-500">{{ $utilization->total_days }} days</span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2">
                                            @php
                                                $maxDays = $leaveUtilization->max('total_days') ?: 1;
                                            @endphp
                                            <div class="bg-blue-500 h-2 rounded-full" style="width: {{ ($utilization->total_days / $maxDays) * 100 }}%"></div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Department Statistics -->
                    <div class="bg-white rounded-lg shadow-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Department Statistics</h3>
                        </div>
                        <div class="p-6">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Department</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Approved</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pending</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($departmentStats as $stat)
                                        <tr>
                                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $stat->department }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-500">{{ $stat->total_requests }}</td>
                                            <td class="px-4 py-3 text-sm text-green-600">{{ $stat->approved }}</td>
                                            <td class="px-4 py-3 text-sm text-yellow-600">{{ $stat->pending }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

        </div>
    </div>
</div>

<script>
// Time display function
function updateDashboardTime() {
    const now = new Date();
    const timeOptions = {
        timeZone: 'Asia/Manila',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: true
    };
    
    const dateOptions = {
        timeZone: 'Asia/Manila',
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    };
    
    const timeString = now.toLocaleString('en-PH', timeOptions);
    const dateString = now.toLocaleString('en-PH', dateOptions);
    
    const timeElement = document.getElementById('current-time');
    const dateElement = document.getElementById('current-date');
    
    if (timeElement) {
        timeElement.textContent = timeString;
    }
    
    if (dateElement) {
        dateElement.textContent = dateString;
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    updateDashboardTime();
    setInterval(updateDashboardTime, 1000);
});
</script>
@endsection
