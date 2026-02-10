@extends('dashboard')

@section('title', 'Pending Leave Requests')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Success Message -->
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                    <span class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.style.display='none'">
                        <svg class="fill-current h-6 w-6 text-green-500 cursor-pointer" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                            <title>Close</title>
                            <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
                        </svg>
                    </span>
                </div>
            @endif

            <!-- Error Message -->
            @if(session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                    <span class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.style.display='none'">
                        <svg class="fill-current h-6 w-6 text-red-500 cursor-pointer" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                            <title>Close</title>
                            <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
                        </svg>
                    </span>
                </div>
            @endif

            <!-- Validation Errors -->
            @if($errors->any())
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <strong class="font-bold">Please fix the following errors:</strong>
                    <ul class="mt-2 list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <span class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.style.display='none'">
                        <svg class="fill-current h-6 w-6 text-red-500 cursor-pointer" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                            <title>Close</title>
                            <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
                        </svg>
                    </span>
                </div>
            @endif

            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-3xl font-bold text-gray-900">Pending Leave Requests</h3>
                        <p class="text-gray-600 mt-1">Review and approve/reject leave requests</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="bg-white rounded-lg shadow px-4 py-3 text-center">
                            <p class="text-sm text-gray-600">Total Pending</p>
                            <p class="text-2xl font-bold text-yellow-600">{{ $pendingRequests->count() }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pending Requests Table -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="flex px-6 py-4 border-b border-gray-200 items-center">
                    <h3 class="text-lg font-medium text-gray-900">
                        Requests Awaiting Action
                    </h3>

                    <button
                        onclick="showAddLeaveModal()"
                        class="ml-auto px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 flex items-center">
                        
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Add Leave Request
                    </button>
                </div>

                
                
                @if($pendingRequests->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-green-950">
                                <tr>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Employee</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Leave Type</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Duration</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Dates</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Applied</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">ML Suggestion</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($pendingRequests as $request)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center text-white font-medium">
                                                {{ substr($request->user->name, 0, 1) }}{{ substr($request->user->lastname ?? '', 0, 1) }}
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $request->user->name }} {{ $request->user->lastname }}</div>
                                                <div class="text-sm text-gray-500">{{ $request->user->employee->department ?? 'N/A' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                                            {{ $request->leave_type }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $request->days_requested }} {{ $request->days_requested == 1 ? 'day' : 'days' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $request->start_date->format('M j, Y') }} - {{ $request->end_date->format('M j, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $request->created_at->format('M j, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        @if($request->approvalPrediction)
                                            <div class="flex flex-col">
                                                <span class="text-sm font-medium">{{ $request->approvalPrediction->predicted_label }}</span>
                                                <span class="text-xs text-gray-500">{{ number_format($request->approvalPrediction->predicted_probability * 100, 1) }}%</span>
                                            </div>
                                        @else
                                            <span class="text-xs text-gray-500">Pending prediction</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center space-x-2">
                                            <button onclick="showRequestDetails({{ $request->id }})" class="action-btn action-btn--view">
                                                View
                                            </button>
                                            <form action="{{ route('leave-management.requests.approve', $request) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="action-btn action-btn--approve ml-2" onclick="return confirm('Are you sure you want to approve this request?')">
                                                    Approve
                                                </button>
                                            </form>
                                            <button onclick="showRejectModal({{ $request->id }})" class="action-btn action-btn--reject">
                                                Reject
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $pendingRequests->links() }}
                    </div>
                @else
                    <div class="text-center py-12">
                        <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No Pending Requests</h3>
                        <p class="text-gray-600">All leave requests have been processed.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Add Leave Request Modal -->
<div id="addLeaveModal" class="absolute inset-0 hidden items-center flex justify-center z-50" style="background: rgba(0, 0, 0, 0.4);">
    <div class="bg-white rounded-lg shadow-xl max-w-3xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Add Leave Request</h3>
        </div>
        <form id="addLeaveForm" method="POST" action="{{ route('leave-management.requests.create') }}">
            @csrf
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Employee Selection -->
                    <div class="md:col-span-2">
                        <label for="user_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Employee <span class="text-red-500">*</span>
                        </label>
                        <select id="user_id" name="user_id" required 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select an employee</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}">
                                    {{ $employee->name }} {{ $employee->lastname }} 
                                    @if($employee->employee && $employee->employee->department)
                                        ({{ $employee->employee->department }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Leave Type -->
                    <div>
                        <label for="leave_type" class="block text-sm font-medium text-gray-700 mb-2">
                            Leave Type <span class="text-red-500">*</span>
                        </label>
                        <select id="leave_type" name="leave_type" required 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select leave type</option>
                            <option value="sick">Sick Leave</option>
                            <option value="vacation">Vacation Leave</option>
                            <option value="personal">Personal Leave</option>
                            <option value="maternity">Maternity Leave</option>
                            <option value="paternity">Paternity Leave</option>
                            <option value="emergency">Emergency Leave</option>
                            <option value="bereavement">Bereavement Leave</option>
                            <option value="annual">Annual Leave</option>
                            <option value="unpaid">Unpaid Leave</option>
                        </select>
                    </div>

                    <!-- Initial Status -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                            Initial Status <span class="text-red-500">*</span>
                        </label>
                        <select id="status" name="status" required 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>

                    <!-- Start Date -->
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">
                            Start Date <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="start_date" name="start_date" required 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <!-- End Date -->
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">
                            End Date <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="end_date" name="end_date" required 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <!-- Reason -->
                    <div class="md:col-span-2">
                        <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">
                            Reason <span class="text-red-500">*</span>
                        </label>
                        <textarea id="reason" name="reason" rows="4" required 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" 
                            placeholder="Reason for leave request..."></textarea>
                    </div>

                    <!-- Manager Comments (optional) -->
                    <div class="md:col-span-2">
                        <label for="manager_comments_add" class="block text-sm font-medium text-gray-700 mb-2">
                            Manager Comments (Optional)
                        </label>
                        <textarea id="manager_comments_add" name="manager_comments" rows="3" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" 
                            placeholder="Additional comments from manager..."></textarea>
                    </div>
                </div>
            </div>
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
                <button type="button" onclick="closeAddLeaveModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Create Request
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="fixed inset-0 hidden items-center justify-center z-50" style="background: rgba(0, 0, 0, 0.4);">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Reject Leave Request</h3>
        </div>
        <form id="rejectForm" method="POST">
            @csrf
            <div class="p-6">
                <label for="manager_comments" class="block text-sm font-medium text-gray-700 mb-2">
                    Reason for Rejection <span class="text-red-500">*</span>
                </label>
                <textarea id="manager_comments" name="manager_comments" rows="4" required 
                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500" 
                    placeholder="Please provide a reason for rejecting this request..."></textarea>
            </div>
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
                <button type="button" onclick="closeRejectModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                    Reject Request
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function showAddLeaveModal() {
    const modal = document.getElementById('addLeaveModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeAddLeaveModal() {
    const modal = document.getElementById('addLeaveModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    
    // Clear the form
    document.getElementById('addLeaveForm').reset();
}

function showRejectModal(requestId) {
    const modal = document.getElementById('rejectModal');
    const form = document.getElementById('rejectForm');
    form.action = '{{ url("/leave-management/requests") }}/' + requestId + '/reject';
    
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeRejectModal() {
    const modal = document.getElementById('rejectModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    
    // Clear the form
    document.getElementById('manager_comments').value = '';
}

function showRequestDetails(requestId) {
    window.location.href = '{{ url("/leave-management/requests") }}/' + requestId;
}

// Auto-calculate minimum end date based on start date
document.getElementById('start_date').addEventListener('change', function() {
    const startDate = this.value;
    const endDateInput = document.getElementById('end_date');
    endDateInput.min = startDate;
    
    // Clear end date if it's before the new start date
    if (endDateInput.value && endDateInput.value < startDate) {
        endDateInput.value = '';
    }
});

// Close modals when clicking outside
document.getElementById('addLeaveModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeAddLeaveModal();
    }
});

document.getElementById('rejectModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeRejectModal();
    }
});
</script>
@endsection
