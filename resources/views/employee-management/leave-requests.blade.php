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

            <!-- Pending Requests Table -->
            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">
                        Pending Requests ({{ $leaveRequests->total() }})
                    </h2>
                </div>

                @if($leaveRequests->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Leave Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dates</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Days</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Submitted</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($leaveRequests as $request)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            @if($request->user->photo)
                                                <img class="h-10 w-10 rounded-full" src="{{ asset('storage/' . $request->user->photo) }}" alt="{{ $request->user->name }}">
                                            @else
                                                <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                                    <span class="text-xs font-medium text-gray-700">{{ substr($request->user->name, 0, 1) }}</span>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $request->user->name }} {{ $request->user->lastname }}</div>
                                            <div class="text-sm text-gray-500">{{ $request->user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ ucfirst(str_replace('_', ' ', $request->leave_type)) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $request->start_date->format('M j, Y') }} - {{ $request->end_date->format('M j, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $request->days_requested }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700 max-w-xs truncate">
                                    {{ $request->reason }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $request->created_at->format('M j, Y g:i A') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-3 py-1 text-xs rounded-full {{ $request->status_badge_color }}">
                                        {{ ucfirst($request->status) }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
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
