@extends('dashboard')

@section('title', 'My Timesheets')

@section('content')
<div class="min-h-screen bg-gray-100">
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-3xl font-bold text-gray-900">My Timesheets</h3>
                        <p class="text-gray-600 mt-1">View and manage your work hour submissions</p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('employee.timesheets.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                            Add New Timesheet
                        </a>
                        <a href="{{ route('employee.dashboard') }}" class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">
                            Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>

            <!-- Success/Error Messages -->
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Filter Section -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <div class="flex flex-wrap items-center gap-4">
                    <div class="flex items-center space-x-2">
                        <label for="status-filter" class="text-sm font-medium text-gray-700">Filter by Status:</label>
                        <select id="status-filter" class="border border-gray-300 rounded-md px-3 py-1 text-sm">
                            <option value="">All Timesheets</option>
                            <option value="draft">Draft</option>
                            <option value="submitted">Submitted</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    
                    <div class="flex items-center space-x-2">
                        <label for="month-filter" class="text-sm font-medium text-gray-700">Month:</label>
                        <select id="month-filter" class="border border-gray-300 rounded-md px-3 py-1 text-sm">
                            <option value="">All Months</option>
                            @for($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" {{ now()->month == $i ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create()->month($i)->format('F') }}
                                </option>
                            @endfor
                        </select>
                    </div>

                    <button onclick="applyFilters()" class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm hover:bg-blue-700">
                        Apply Filters
                    </button>
                </div>
            </div>

            <!-- Timesheets List -->
            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <h2 class="text-lg font-medium text-gray-900">
                            Timesheet History ({{ $timesheets->total() }} entries)
                        </h2>
                        <div class="text-sm text-gray-500">
                            Total hours this month: 
                            <span class="font-semibold text-gray-900">
                                {{ $timesheets->where('work_date', '>=', now()->startOfMonth())->sum('hours_worked') }} hours
                            </span>
                        </div>
                    </div>
                </div>
                
                @if($timesheets->count() > 0)
                <ul class="divide-y divide-gray-200">
                    @foreach($timesheets as $timesheet)
                    <li class="px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center space-x-3">
                                        <p class="text-sm font-medium text-gray-900">
                                            {{ $timesheet->work_date->format('l, F j, Y') }}
                                        </p>
                                        <span class="px-2 py-1 text-xs font-medium rounded-full
                                            {{ $timesheet->status === 'draft' ? 'bg-gray-100 text-gray-800' : '' }}
                                            {{ $timesheet->status === 'submitted' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                            {{ $timesheet->status === 'approved' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $timesheet->status === 'rejected' ? 'bg-red-100 text-red-800' : '' }}
                                        ">
                                            {{ ucfirst($timesheet->status) }}
                                        </span>
                                    </div>
                                    <div class="mt-1 flex items-center text-sm text-gray-500 space-x-4">
                                        <span>
                                            <strong>Hours:</strong> {{ $timesheet->hours_worked ?? 0 }}
                                        </span>
                                        @if($timesheet->clock_in_time && $timesheet->clock_out_time)
                                            <span>
                                                <strong>Time:</strong> 
                                                {{ \Carbon\Carbon::createFromTimeString($timesheet->clock_in_time)->format('g:i A') }} - 
                                                {{ \Carbon\Carbon::createFromTimeString($timesheet->clock_out_time)->format('g:i A') }}
                                            </span>
                                        @endif
                                        @if($timesheet->project_name)
                                            <span><strong>Project:</strong> {{ $timesheet->project_name }}</span>
                                        @endif
                                    </div>
                                    @if($timesheet->description)
                                        <div class="mt-2 text-sm text-gray-600">
                                            <strong>Description:</strong> {{ Str::limit($timesheet->description, 100) }}
                                        </div>
                                    @endif
                                    @if($timesheet->submitted_at)
                                        <div class="mt-1 text-xs text-gray-500">
                                            Submitted: {{ $timesheet->submitted_at->format('M j, Y g:i A') }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="flex items-center space-x-2">
                                @if($timesheet->status === 'draft')
                                    <a href="{{ route('employee.timesheets.edit', $timesheet) }}" 
                                       class="action-btn action-btn--edit">
                                        Edit
                                    </a>
                                    <button onclick="submitTimesheet({{ $timesheet->id }})" 
                                            class="action-btn action-btn--approve">
                                        Submit
                                    </button>
                                @elseif($timesheet->status === 'submitted')
                                    <span class="text-yellow-600 text-sm">Pending Review</span>
                                @elseif($timesheet->status === 'approved')
                                    <span class="text-green-600 text-sm">Approved</span>
                                @elseif($timesheet->status === 'rejected')
                                    <span class="text-red-600 text-sm">Rejected</span>
                                    @if($timesheet->rejection_reason)
                                        <button onclick="showRejectionReason('{{ $timesheet->rejection_reason }}')" 
                                                class="action-btn action-btn--view">
                                            View Reason
                                        </button>
                                    @endif
                                @endif
                                
                                <button onclick="viewTimesheetDetails({{ $timesheet->id }})" 
                                        class="action-btn action-btn--view">
                                    View Details
                                </button>
                            </div>
                        </div>
                    </li>
                    @endforeach
                </ul>
                @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No timesheets found</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by creating your first timesheet entry.</p>
                    <div class="mt-6">
                        <a href="{{ route('employee.timesheets.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700">
                            <svg class="-ml-1 mr-2 h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                            </svg>
                            Add New Timesheet
                        </a>
                    </div>
                </div>
                @endif
                
                <!-- Pagination -->
                @if($timesheets->hasPages())
                <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                    {{ $timesheets->links() }}
                </div>
                @endif
            </div>

            <!-- Summary Cards -->
            <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-blue-100 rounded-full mr-4">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-2xl font-semibold text-gray-900">{{ $timesheets->where('status', 'draft')->count() }}</p>
                            <p class="text-sm text-gray-600">Draft Timesheets</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-yellow-100 rounded-full mr-4">
                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-2xl font-semibold text-gray-900">{{ $timesheets->where('status', 'submitted')->count() }}</p>
                            <p class="text-sm text-gray-600">Pending Review</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-green-100 rounded-full mr-4">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-2xl font-semibold text-gray-900">{{ $timesheets->where('status', 'approved')->count() }}</p>
                            <p class="text-sm text-gray-600">Approved</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Timesheet Details Modal -->
<div id="timesheetModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Timesheet Details</h3>
                <button onclick="closeModal('timesheetModal')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div id="timesheet-details-content" class="space-y-3 text-sm">
                <!-- Content will be populated by JavaScript -->
            </div>
        </div>
    </div>
</div>

<!-- Rejection Reason Modal -->
<div id="rejectionModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Rejection Reason</h3>
                <button onclick="closeModal('rejectionModal')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="border-l-4 border-red-400 bg-red-50 p-4">
                <p id="rejection-reason-text" class="text-sm text-red-700"></p>
            </div>
        </div>
    </div>
</div>

<script>
function submitTimesheet(timesheetId) {
    if (confirm('Are you sure you want to submit this timesheet? You won\'t be able to edit it after submission.')) {
        fetch(`/employee/timesheets/${timesheetId}/submit`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error submitting timesheet: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while submitting the timesheet.');
        });
    }
}

function viewTimesheetDetails(timesheetId) {
    // Find the timesheet data from the current page
    const timesheets = @json($timesheets->items());
    const timesheet = timesheets.find(t => t.id === timesheetId);
    
    if (timesheet) {
        const content = document.getElementById('timesheet-details-content');
        content.innerHTML = `
            <div><strong>Date:</strong> ${new Date(timesheet.work_date).toLocaleDateString()}</div>
            <div><strong>Hours Worked:</strong> ${timesheet.hours_worked || 0} hours</div>
            ${timesheet.clock_in_time ? `<div><strong>Clock In:</strong> ${timesheet.clock_in_time}</div>` : ''}
            ${timesheet.clock_out_time ? `<div><strong>Clock Out:</strong> ${timesheet.clock_out_time}</div>` : ''}
            ${timesheet.break_start && timesheet.break_end ? `<div><strong>Break:</strong> ${timesheet.break_start} - ${timesheet.break_end}</div>` : ''}
            ${timesheet.project_name ? `<div><strong>Project:</strong> ${timesheet.project_name}</div>` : ''}
            ${timesheet.task_description ? `<div><strong>Task:</strong> ${timesheet.task_description}</div>` : ''}
            ${timesheet.description ? `<div><strong>Description:</strong> ${timesheet.description}</div>` : ''}
            <div><strong>Status:</strong> ${timesheet.status.charAt(0).toUpperCase() + timesheet.status.slice(1)}</div>
            ${timesheet.submitted_at ? `<div><strong>Submitted:</strong> ${new Date(timesheet.submitted_at).toLocaleString()}</div>` : ''}
        `;
        
        document.getElementById('timesheetModal').classList.remove('hidden');
    }
}

function showRejectionReason(reason) {
    document.getElementById('rejection-reason-text').textContent = reason;
    document.getElementById('rejectionModal').classList.remove('hidden');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}

function applyFilters() {
    const status = document.getElementById('status-filter').value;
    const month = document.getElementById('month-filter').value;
    
    let url = new URL(window.location);
    
    if (status) {
        url.searchParams.set('status', status);
    } else {
        url.searchParams.delete('status');
    }
    
    if (month) {
        url.searchParams.set('month', month);
    } else {
        url.searchParams.delete('month');
    }
    
    window.location.href = url.toString();
}

// Close modals when clicking outside
window.onclick = function(event) {
    const modals = ['timesheetModal', 'rejectionModal'];
    modals.forEach(modalId => {
        const modal = document.getElementById(modalId);
        if (event.target === modal) {
            modal.classList.add('hidden');
        }
    });
}

// Set filter values from URL on page load
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    
    const status = urlParams.get('status');
    if (status) {
        document.getElementById('status-filter').value = status;
    }
    
    const month = urlParams.get('month');
    if (month) {
        document.getElementById('month-filter').value = month;
    }
});
</script>
@endsection
