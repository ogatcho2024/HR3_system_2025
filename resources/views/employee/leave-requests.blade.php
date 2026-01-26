@extends('dashboard-user')

@section('title', 'Leave Requests')

@section('content')
<div class="min-h-screen bg-gray-100">
    <!-- Header -->
    <div class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Leave Requests</h1>
                    <p class="text-gray-600">Submit and track your leave applications</p>
                </div>
                <div class="flex space-x-4">
                    <a href="{{ route('employee.dashboard') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        ← Back to Dashboard
                    </a>
                    <button onclick="document.getElementById('leave-form').scrollIntoView({behavior: 'smooth'})" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        + New Leave Request
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Success/Error Messages -->
        @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-md">
            {{ session('success') }}
        </div>
        @endif
        
        @if(session('error'))
        <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-md">
            {{ session('error') }}
        </div>
        @endif
        
        @if($errors->any())
        <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-md">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Leave Balance Card -->
            <div class="lg:col-span-1">
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Leave Balance</h3>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Total Entitlement</span>
                                <span class="font-semibold text-gray-900">{{ $leaveBalance['total'] }} days</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Used</span>
                                <span class="font-semibold text-red-600">{{ $leaveBalance['used'] }} days</span>
                            </div>
                            <div class="border-t pt-4">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium text-gray-900">Remaining</span>
                                    <span class="text-xl font-bold text-green-600">{{ $leaveBalance['remaining'] }} days</span>
                                </div>
                            </div>
                            
                            <!-- Progress Bar -->
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-green-600 h-2 rounded-full" style="width: {{ ($leaveBalance['remaining'] / $leaveBalance['total']) * 100 }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Leave Request Form -->
                <div id="leave-form" class="bg-white overflow-hidden shadow rounded-lg mt-6">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">New Leave Request</h3>
                        <form action="{{ route('employee.leave-requests.store') }}" method="POST" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Leave Type</label>
                                <select name="leave_type" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <option value="annual_leave">Annual Leave</option>
                                    <option value="sick_leave">Sick Leave</option>
                                    <option value="emergency_leave">Emergency Leave</option>
                                    <option value="maternity_paternity_leave">Maternity/Paternity Leave</option>
                                    <option value="personal_leave">Personal Leave</option>
                                </select>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Start Date</label>
                                    <input type="date" name="start_date" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" min="{{ date('Y-m-d') }}">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">End Date</label>
                                    <input type="date" name="end_date" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" min="{{ date('Y-m-d') }}">
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Reason</label>
                                <textarea name="reason" required rows="4" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Please provide a brief reason for your leave request..."></textarea>
                            </div>
                            
                            <div>
                                <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Submit Leave Request
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Leave Requests History -->
            <div class="lg:col-span-2">
                <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <div class="px-6 py-4 border-b bg-gray-200 border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Leave Request History</h3>
                    </div>
                    
                    @if($leaveRequests->count() > 0)
                    <ul class="divide-y divide-gray-200">
                        @foreach($leaveRequests as $request)
                        <li class="px-6 py-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-start space-x-4">
                                    <div class="flex-shrink-0">
                                        @if($request->status == 'approved')
                                        <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        </div>
                                        @elseif($request->status == 'rejected')
                                        <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                                            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </div>
                                        @else
                                        <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                                            <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </div>
                                        @endif
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center justify-between">
                                            <p class="text-lg font-medium text-gray-900">{{ ucfirst(str_replace('_', ' ', $request->leave_type)) }}</p>
                                            <span class="px-3 py-1 text-sm rounded-full {{ $request->status == 'approved' ? 'bg-green-100 text-green-800' : ($request->status == 'rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                                {{ ucfirst($request->status) }}
                                            </span>
                                        </div>
                                        <div class="mt-2 text-sm text-gray-600">
                                            <p><strong>Duration:</strong> {{ $request->days_requested }} day{{ $request->days_requested > 1 ? 's' : '' }}</p>
                                            <p><strong>Dates:</strong> {{ $request->start_date->format('M j, Y') }} - {{ $request->end_date->format('M j, Y') }}</p>
                                            <p><strong>Reason:</strong> {{ $request->reason }}</p>
                                            @if($request->status == 'rejected' && $request->manager_comments)
                                            <p class="mt-2"><strong class="text-red-600">Manager Comments:</strong> <span class="text-red-600">{{ $request->manager_comments }}</span></p>
                                            @endif
                                        </div>
                                        <p class="mt-2 text-xs text-gray-500">
                                            Submitted on {{ $request->created_at->format('M j, Y g:i A') }}
                                            @if($request->approved_at)
                                            • {{ ucfirst($request->status) }} on {{ $request->approved_at->format('M j, Y g:i A') }}
                                            @endif
                                        </p>
                                    </div>
                                    @if($request->status == 'pending')
                                    <div class="ml-4">
                                        <form action="{{ route('employee.leave-requests.destroy', $request) }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel this leave request?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900 text-sm font-medium">
                                                Cancel Request
                                            </button>
                                        </form>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                    
                    <!-- Pagination -->
                    @if($leaveRequests->hasPages())
                    <div class="bg-white px-6 py-3 border-t border-gray-200">
                        {{ $leaveRequests->links() }}
                    </div>
                    @endif
                    @else
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4m-4 8a4 4 0 01-4-4V7a4 4 0 118 0v4a4 4 0 01-4 4z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No leave requests</h3>
                        <p class="mt-1 text-sm text-gray-500">You haven't submitted any leave requests yet.</p>
                        <div class="mt-6">
                            <button onclick="document.getElementById('leave-form').scrollIntoView({behavior: 'smooth'})" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Submit Your First Leave Request
                            </button>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
