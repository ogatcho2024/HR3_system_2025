@extends('dashboard')

@section('title', 'Leave Request Details')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-3xl font-bold text-gray-900">Leave Request Details</h3>
                        <p class="text-gray-600 mt-1">Request #{{ $leaveRequest->id }}</p>
                    </div>
                    <a href="{{ route('leave-management.pending-requests') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                        Back to List
                    </a>
                </div>
            </div>

            <!-- Request Details Card -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <!-- Status Banner -->
                <div class="px-6 py-4 border-b border-gray-200 {{ $leaveRequest->status === 'pending' ? 'bg-yellow-50' : ($leaveRequest->status === 'approved' ? 'bg-green-50' : 'bg-red-50') }}">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold {{ $leaveRequest->status === 'pending' ? 'text-yellow-800' : ($leaveRequest->status === 'approved' ? 'text-green-800' : 'text-red-800') }}">
                                Status: {{ ucfirst($leaveRequest->status) }}
                            </h3>
                        </div>
                        <span class="px-4 py-2 text-sm font-medium rounded-full {{ $leaveRequest->status === 'pending' ? 'bg-yellow-200 text-yellow-800' : ($leaveRequest->status === 'approved' ? 'bg-green-200 text-green-800' : 'bg-red-200 text-red-800') }}">
                            {{ ucfirst($leaveRequest->status) }}
                        </span>
                    </div>
                </div>

                <!-- Employee Information -->
                <div class="px-6 py-6 border-b border-gray-200">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4">Employee Information</h4>
                    <div class="flex items-center">
                        <div class="w-16 h-16 bg-blue-500 rounded-full flex items-center justify-center text-white text-xl font-medium">
                            {{ substr($leaveRequest->user->name, 0, 1) }}{{ substr($leaveRequest->user->lastname ?? '', 0, 1) }}
                        </div>
                        <div class="ml-4">
                            <div class="text-lg font-medium text-gray-900">{{ $leaveRequest->user->name }} {{ $leaveRequest->user->lastname }}</div>
                            <div class="text-sm text-gray-500">{{ $leaveRequest->user->email }}</div>
                            <div class="text-sm text-gray-500">Department: {{ $leaveRequest->user->employee->department ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>

                <!-- Leave Details -->
                <div class="px-6 py-6 border-b border-gray-200">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4">Leave Details</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Leave Type</label>
                            <p class="text-base text-gray-900">
                                <span class="px-3 py-1 text-sm font-medium rounded-full bg-blue-100 text-blue-800">
                                    {{ ucfirst($leaveRequest->leave_type) }}
                                </span>
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Duration</label>
                            <p class="text-base text-gray-900">{{ $leaveRequest->days_requested }} {{ $leaveRequest->days_requested == 1 ? 'day' : 'days' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Start Date</label>
                            <p class="text-base text-gray-900">{{ $leaveRequest->start_date->format('F j, Y') }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">End Date</label>
                            <p class="text-base text-gray-900">{{ $leaveRequest->end_date->format('F j, Y') }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Date Applied</label>
                            <p class="text-base text-gray-900">{{ $leaveRequest->created_at->format('F j, Y g:i A') }}</p>
                        </div>
                        @if($leaveRequest->approved_at)
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Date Processed</label>
                            <p class="text-base text-gray-900">{{ $leaveRequest->approved_at->format('F j, Y g:i A') }}</p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Reason -->
                <div class="px-6 py-6 border-b border-gray-200">
                    <h4 class="text-lg font-semibold text-gray-900 mb-2">Reason for Leave</h4>
                    <p class="text-base text-gray-700 whitespace-pre-wrap">{{ $leaveRequest->reason }}</p>
                </div>

                <!-- Manager Comments -->
                @if($leaveRequest->manager_comments)
                <div class="px-6 py-6 border-b border-gray-200">
                    <h4 class="text-lg font-semibold text-gray-900 mb-2">Manager Comments</h4>
                    <p class="text-base text-gray-700 whitespace-pre-wrap">{{ $leaveRequest->manager_comments }}</p>
                </div>
                @endif

                <!-- Approval Information -->
                @if($leaveRequest->approvedBy)
                <div class="px-6 py-6 border-b border-gray-200">
                    <h4 class="text-lg font-semibold text-gray-900 mb-2">Processed By</h4>
                    <p class="text-base text-gray-700">{{ $leaveRequest->approvedBy->name }} {{ $leaveRequest->approvedBy->lastname }}</p>
                    <p class="text-sm text-gray-500">{{ $leaveRequest->approvedBy->email }}</p>
                </div>
                @endif

                <!-- Actions (if pending) -->
                @if($leaveRequest->status === 'pending')
                <div class="px-6 py-6 bg-gray-50">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4">Actions</h4>
                    <div class="flex space-x-3">
                        <form action="{{ route('leave-management.requests.approve', $leaveRequest) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500" onclick="return confirm('Are you sure you want to approve this request?')">
                                Approve Request
                            </button>
                        </form>
                        <button onclick="showRejectModal()" class="px-6 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                            Reject Request
                        </button>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Reject Leave Request</h3>
        </div>
        <form action="{{ route('leave-management.requests.reject', $leaveRequest) }}" method="POST">
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
function showRejectModal() {
    const modal = document.getElementById('rejectModal');
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

// Close modal when clicking outside
document.getElementById('rejectModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeRejectModal();
    }
});
</script>
@endsection
