@extends('dashboard')

@section('title', 'Pending Leave Requests')

@section('content')
<div class="min-h-screen bg-gray-100">
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-3xl font-bold text-gray-900">Pending Leave Requests</h3>
                        <p class="text-gray-600 mt-1">Review and manage employee leave requests</p>
                    </div>
                    <a href="{{ route('employee-management.dashboard') }}" class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">
                        Back to Dashboard
                    </a>
                </div>
            </div>

            <!-- Pending Requests List -->
            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">
                        Pending Requests ({{ $leaveRequests->total() }})
                    </h2>
                </div>
                
                @if($leaveRequests->count() > 0)
                <ul class="divide-y divide-gray-200">
                    @foreach($leaveRequests as $request)
                    <li class="px-6 py-4">
                        <div class="flex items-start justify-between">
                            <div class="flex items-start space-x-4">
                                <div class="flex-shrink-0">
                                    @if($request->user->photo)
                                        <img class="h-12 w-12 rounded-full" src="{{ asset('storage/' . $request->user->photo) }}" alt="{{ $request->user->name }}">
                                    @else
                                        <div class="h-12 w-12 rounded-full bg-gray-300 flex items-center justify-center">
                                            <span class="text-sm font-medium text-gray-700">{{ substr($request->user->name, 0, 1) }}</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $request->user->name }} {{ $request->user->lastname }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ $request->user->email }}
                                    </div>
                                    <div class="mt-2 text-sm text-gray-600">
                                        <p><strong>Type:</strong> {{ ucfirst(str_replace('_', ' ', $request->leave_type)) }}</p>
                                        <p><strong>Duration:</strong> {{ $request->days_requested }} day{{ $request->days_requested > 1 ? 's' : '' }}</p>
                                        <p><strong>Dates:</strong> {{ $request->start_date->format('M j, Y') }} - {{ $request->end_date->format('M j, Y') }}</p>
                                        <p><strong>Reason:</strong> {{ $request->reason }}</p>
                                    </div>
                                    <div class="mt-2 text-xs text-gray-500">
                                        Submitted on {{ $request->created_at->format('M j, Y g:i A') }}
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex items-center space-x-3">
                                <span class="px-3 py-1 text-sm rounded-full {{ $request->status_badge_color }}">
                                    {{ ucfirst($request->status) }}
                                </span>
                                <!-- Action buttons would go here if you want to add approve/reject functionality -->
                            </div>
                        </div>
                    </li>
                    @endforeach
                </ul>
                @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No pending leave requests</h3>
                    <p class="mt-1 text-sm text-gray-500">All leave requests have been processed.</p>
                </div>
                @endif
                
                <!-- Pagination -->
                @if($leaveRequests->hasPages())
                <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                    {{ $leaveRequests->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
