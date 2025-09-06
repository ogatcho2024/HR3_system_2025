@extends('dashboard')

@section('title', 'Pending Shift Requests')

@section('content')
<div class="min-h-screen bg-gray-100">
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-3xl font-bold text-gray-900">Pending Shift Requests</h3>
                        <p class="text-gray-600 mt-1">Review and manage employee shift requests</p>
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
                        Pending Requests ({{ $shiftRequests->total() }})
                    </h2>
                </div>
                
                @if($shiftRequests->count() > 0)
                <ul class="divide-y divide-gray-200">
                    @foreach($shiftRequests as $request)
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
                                        <p><strong>Type:</strong> {{ ucfirst(str_replace('_', ' ', $request->request_type)) }}</p>
                                        <p><strong>Date:</strong> {{ $request->request_date->format('M j, Y') }}</p>
                                        @if($request->request_type === 'swap' && $request->other_user)
                                            <p><strong>Swap With:</strong> {{ $request->otherUser->name }} {{ $request->otherUser->lastname }}</p>
                                        @endif
                                        @if($request->reason)
                                            <p><strong>Reason:</strong> {{ $request->reason }}</p>
                                        @endif
                                    </div>
                                    <div class="mt-2 text-xs text-gray-500">
                                        Submitted on {{ $request->created_at->format('M j, Y g:i A') }}
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex items-center space-x-3">
                                <span class="px-3 py-1 text-sm rounded-full 
                                    @if($request->status === 'pending')
                                        bg-yellow-100 text-yellow-800
                                    @elseif($request->status === 'approved')
                                        bg-green-100 text-green-800
                                    @elseif($request->status === 'rejected')
                                        bg-red-100 text-red-800
                                    @else
                                        bg-gray-100 text-gray-800
                                    @endif
                                ">
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No pending shift requests</h3>
                    <p class="mt-1 text-sm text-gray-500">All shift requests have been processed.</p>
                </div>
                @endif
                
                <!-- Pagination -->
                @if($shiftRequests->hasPages())
                <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                    {{ $shiftRequests->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
