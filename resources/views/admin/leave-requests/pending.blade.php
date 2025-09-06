@extends('layouts.app')

@section('title', 'Pending Leave Requests')

@section('content')
<div class="min-h-screen bg-gray-100">
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Pending Leave Requests</h1>
                        <p class="text-gray-600 mt-1">Review and manage employee leave requests</p>
                    </div>
                    <a href="{{ route('admin.dashboard') }}" class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">
                        Back to Dashboard
                    </a>
                </div>
            </div>

            <!-- Pending Requests List -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">
                        Pending Requests ({{ $leaveRequests->total() }})
                    </h2>
                </div>
                
                <div class="divide-y divide-gray-200">
                    @forelse($leaveRequests as $request)
                    <div class="p-6">
                        <div class="flex items-start justify-between">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center space-x-3 mb-2">
                                    <div class="flex-shrink-0">
                                        @if($request->user->photo)
                                            <img class="h-10 w-10 rounded-full" src="{{ asset('storage/' . $request->user->photo) }}" alt="{{ $request->user->name }}">
                                        @else
                                            <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                                <span class="text-sm font-medium text-gray-700">{{ substr($request->user->name, 0, 1) }}{{ substr($request->user->lastname, 0, 1) }}</span>
                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-medium text-gray-900">
                                            {{ $request->user->name }} {{ $request->user->lastname }}
                                        </h3>
                                        <p class="text-sm text-gray-600">{{ $request->user->email }}</p>
                                    </div>
                                    <span class="px-2 py-1 text-xs rounded-full {{ $request->status_badge_color }}">
                                        {{ ucfirst($request->status) }}
                                    </span>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">Leave Type</p>
                                        <p class="text-sm text-gray-600">{{ ucfirst(str_replace('_', ' ', $request->leave_type)) }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">Duration</p>
                                        <p class="text-sm text-gray-600">{{ $request->days_requested }} day{{ $request->days_requested > 1 ? 's' : '' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">Dates</p>
                                        <p class="text-sm text-gray-600">{{ $request->start_date->format('M j, Y') }} - {{ $request->end_date->format('M j, Y') }}</p>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <p class="text-sm font-medium text-gray-900 mb-1">Reason</p>
                                    <p class="text-sm text-gray-600">{{ $request->reason }}</p>
                                </div>

                                @if($request->attachment_path)
                                <div class="mb-4">
                                    <p class="text-sm font-medium text-gray-900 mb-1">Attachment</p>
                                    <a href="{{ asset('storage/' . $request->attachment_path) }}" target="_blank" 
                                       class="inline-flex items-center px-3 py-1 text-sm bg-blue-100 text-blue-800 rounded hover:bg-blue-200">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                        </svg>
                                        View Attachment
                                    </a>
                                </div>
                                @endif
                                
                                <div class="text-sm text-gray-500">
                                    Submitted on {{ $request->created_at->format('M j, Y g:i A') }}
                                </div>
                            </div>
                            
                            <div class="flex items-center space-x-2 ml-4">
                                <button type="button" 
                                        onclick="showApprovalModal({{ $request->id }}, '{{ $request->user->name }} {{ $request->user->lastname }}', 'approve')"
                                        class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 focus:ring-2 focus:ring-green-500 focus:ring-offset-2 text-sm">
                                    Approve
                                </button>
                                <button type="button" 
                                        onclick="showApprovalModal({{ $request->id }}, '{{ $request->user->name }} {{ $request->user->lastname }}', 'reject')"
                                        class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 focus:ring-2 focus:ring-red-500 focus:ring-offset-2 text-sm">
                                    Reject
                                </button>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="p-6 text-center text-gray-500">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No pending requests</h3>
                        <p class="mt-1 text-sm text-gray-500">All leave requests have been processed.</p>
                    </div>
                    @endforelse
                </div>
                
                <!-- Pagination -->
                @if($leaveRequests->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $leaveRequests->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Approval/Rejection Modal -->
<div id="approvalModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full" id="modalIcon">
                <!-- Icon will be set by JavaScript -->
            </div>
            <div class="mt-3 text-center">
                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modalTitle">Confirm Action</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500" id="modalMessage">
                        <!-- Message will be set by JavaScript -->
                    </p>
                </div>
                <div class="mt-4">
                    <label for="managerComments" class="block text-sm font-medium text-gray-700 text-left">Comments (Optional)</label>
                    <textarea id="managerComments" rows="3" 
                              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                              placeholder="Add any comments about this decision..."></textarea>
                </div>
                <div class="items-center px-4 py-3 space-x-2 flex justify-end">
                    <button id="cancelButton" 
                            class="px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-md shadow-sm hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-300">
                        Cancel
                    </button>
                    <button id="confirmButton" 
                            class="px-4 py-2 text-white text-base font-medium rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2">
                        <!-- Text will be set by JavaScript -->
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let currentRequestId = null;
let currentAction = null;

function showApprovalModal(requestId, employeeName, action) {
    currentRequestId = requestId;
    currentAction = action;
    
    const modal = document.getElementById('approvalModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalMessage = document.getElementById('modalMessage');
    const modalIcon = document.getElementById('modalIcon');
    const confirmButton = document.getElementById('confirmButton');
    
    if (action === 'approve') {
        modalTitle.textContent = 'Approve Leave Request';
        modalMessage.textContent = `Are you sure you want to approve ${employeeName}'s leave request?`;
        modalIcon.innerHTML = '<svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';
        modalIcon.className = 'mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100';
        confirmButton.textContent = 'Approve';
        confirmButton.className = 'px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-base font-medium rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2';
    } else {
        modalTitle.textContent = 'Reject Leave Request';
        modalMessage.textContent = `Are you sure you want to reject ${employeeName}'s leave request?`;
        modalIcon.innerHTML = '<svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>';
        modalIcon.className = 'mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100';
        confirmButton.textContent = 'Reject';
        confirmButton.className = 'px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-base font-medium rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2';
    }
    
    modal.classList.remove('hidden');
}

function hideApprovalModal() {
    const modal = document.getElementById('approvalModal');
    modal.classList.add('hidden');
    document.getElementById('managerComments').value = '';
    currentRequestId = null;
    currentAction = null;
}

document.getElementById('cancelButton').addEventListener('click', hideApprovalModal);

document.getElementById('confirmButton').addEventListener('click', function() {
    if (!currentRequestId || !currentAction) return;
    
    const comments = document.getElementById('managerComments').value;
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/leave-management/requests/${currentRequestId}/${currentAction}`;
    
    // Add CSRF token
    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = '{{ csrf_token() }}';
    form.appendChild(csrfToken);
    
    // Add comments if provided
    if (comments) {
        const commentsInput = document.createElement('input');
        commentsInput.type = 'hidden';
        commentsInput.name = 'manager_comments';
        commentsInput.value = comments;
        form.appendChild(commentsInput);
    }
    
    document.body.appendChild(form);
    form.submit();
});

// Close modal when clicking outside
document.getElementById('approvalModal').addEventListener('click', function(e) {
    if (e.target.id === 'approvalModal') {
        hideApprovalModal();
    }
});
</script>
@endsection
