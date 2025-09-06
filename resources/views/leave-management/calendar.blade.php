@extends('dashboard')

@section('title', 'Leave Calendar')

@section('content')
<div class="p-6">
    <!-- Breadcrumbs -->
    @include('partials.breadcrumbs', ['breadcrumbs' => [
        ['label' => 'Leave Management', 'url' => route('leave-management.dashboard')],
        ['label' => 'Calendar', 'url' => route('leave-management.calendar')]
    ]])
    
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-3xl font-bold text-gray-900">Leave Calendar</h3>
                <p class="text-gray-600 mt-2">View all approved leave requests in calendar format</p>
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

    <!-- Calendar Navigation -->
    <div class="bg-gray-100 rounded-lg shadow-lg p-6 mb-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('leave-management.calendar', ['month' => $month - 1, 'year' => $month == 1 ? $year - 1 : $year]) }}" 
                   class="p-2 rounded-md bg-white text-gray-700 hover:bg-gray-50">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>
                <h2 class="text-xl font-bold text-gray-900">
                    {{ date('F Y', mktime(0, 0, 0, $month, 1, $year)) }}
                </h2>
                <a href="{{ route('leave-management.calendar', ['month' => $month + 1, 'year' => $month == 12 ? $year + 1 : $year]) }}" 
                   class="p-2 rounded-md bg-white text-gray-700 hover:bg-gray-50">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>
            <div>
                <a href="{{ route('leave-management.calendar') }}" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                    Today
                </a>
            </div>
        </div>
    </div>

    <!-- Calendar Grid -->
    <div class="bg-gray-100 rounded-lg shadow-lg mb-8">
        <div class="grid grid-cols-7 border-b border-gray-200">
            <div class="p-4 text-center font-medium text-gray-900">Sunday</div>
            <div class="p-4 text-center font-medium text-gray-900">Monday</div>
            <div class="p-4 text-center font-medium text-gray-900">Tuesday</div>
            <div class="p-4 text-center font-medium text-gray-900">Wednesday</div>
            <div class="p-4 text-center font-medium text-gray-900">Thursday</div>
            <div class="p-4 text-center font-medium text-gray-900">Friday</div>
            <div class="p-4 text-center font-medium text-gray-900">Saturday</div>
        </div>
        
        <div class="grid grid-cols-7">
            @php
                $firstDayOfMonth = mktime(0, 0, 0, $month, 1, $year);
                $daysInMonth = date('t', $firstDayOfMonth);
                $firstDayOfWeek = date('w', $firstDayOfMonth);
                $dayCount = 1;
            @endphp
            
            @for ($i = 0; $i < 42; $i++)
                @if ($i < $firstDayOfWeek || $dayCount > $daysInMonth)
                    <div class="min-h-32 p-2 border border-gray-200 bg-gray-50"></div>
                @else
                    <div class="min-h-32 p-2 border border-gray-200">
                        <div class="text-right">
                            <span class="inline-flex items-center justify-center w-8 h-8 text-sm {{ 
                                date('Y-m-d') == date('Y-m-d', mktime(0, 0, 0, $month, $dayCount, $year)) ? 
                                'bg-blue-600 text-white rounded-full' : 
                                'text-gray-900' 
                            }}">
                                {{ $dayCount }}
                            </span>
                        </div>
                        
                        @php
                            $currentDate = date('Y-m-d', mktime(0, 0, 0, $month, $dayCount, $year));
                            $dayEvents = $leaveRequests->filter(function($event) use ($currentDate) {
                                return $currentDate >= $event->start_date->format('Y-m-d') && 
                                       $currentDate <= $event->end_date->format('Y-m-d');
                            });
                        @endphp
                        
                        @if ($dayEvents->count() > 0)
                            <div class="mt-1 space-y-1 max-h-20 overflow-y-auto">
                                @foreach ($dayEvents as $event)
                                    <div class="text-xs p-1 rounded cursor-pointer hover:bg-gray-100 bg-white border border-gray-200"
                                         onclick="showEventDetails('{{ $event->id }}', '{{ $event->user->name }}', '{{ $event->leave_type }}', '{{ $event->start_date->format('M d, Y') }}', '{{ $event->end_date->format('M d, Y') }}', '{{ $event->days_requested }}', '{{ $event->reason }}')">
                                        <div class="font-medium truncate">{{ $event->user->name }}</div>
                                        <div class="text-gray-600 truncate">{{ $event->leave_type }}</div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    @php $dayCount++; @endphp
                @endif
            @endfor
        </div>
    </div>
    
    <!-- Event Details Modal -->
    <div id="eventModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900">Leave Request Details</h3>
                    <button onclick="closeEventModal()" class="text-gray-400 hover:text-gray-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Employee</label>
                        <p id="modalEmployee" class="mt-1 text-sm text-gray-900"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Leave Type</label>
                        <p id="modalLeaveType" class="mt-1 text-sm text-gray-900"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Start Date</label>
                        <p id="modalStartDate" class="mt-1 text-sm text-gray-900"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">End Date</label>
                        <p id="modalEndDate" class="mt-1 text-sm text-gray-900"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Days Requested</label>
                        <p id="modalDays" class="mt-1 text-sm text-gray-900"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Reason</label>
                        <p id="modalReason" class="mt-1 text-sm text-gray-900"></p>
                    </div>
                </div>
            </div>
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 text-right">
                <button onclick="closeEventModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                    Close
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
    
    // Event details modal functions
    function showEventDetails(id, employee, leaveType, startDate, endDate, days, reason) {
        document.getElementById('modalEmployee').textContent = employee;
        document.getElementById('modalLeaveType').textContent = leaveType;
        document.getElementById('modalStartDate').textContent = startDate;
        document.getElementById('modalEndDate').textContent = endDate;
        document.getElementById('modalDays').textContent = days + ' days';
        document.getElementById('modalReason').textContent = reason || 'No reason provided';
        
        document.getElementById('eventModal').classList.remove('hidden');
        document.getElementById('eventModal').classList.add('flex');
    }
    
    function closeEventModal() {
        document.getElementById('eventModal').classList.add('hidden');
        document.getElementById('eventModal').classList.remove('flex');
    }
    
    // Close modal when clicking outside
    document.getElementById('eventModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeEventModal();
        }
    });
</script>
@endsection