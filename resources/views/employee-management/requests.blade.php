@extends('dashboard')

@section('title', 'Employee Requests Management')

@section('content')
<div class="min-h-screen bg-gray-300" x-data="requestsManagement()">
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            

            <!-- Main Tabs Navigation -->
            <div class="mb-6">
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                        <button 
                            @click="activeTab = 'leave'" 
                            :class="activeTab === 'leave' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm"
                            data-requests-tab
                        >
                            Leave Requests
                            <span class="ml-2 bg-yellow-100 text-yellow-800 py-1 px-2 rounded-full text-xs">
                                {{ $pendingLeaveRequests->count() + $approvedLeaveRequests->count() + $rejectedLeaveRequests->count() }}
                            </span>
                        </button>
                        <button 
                            @click="activeTab = 'shift'"
                            :class="activeTab === 'shift' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm"
                            data-requests-tab
                        >
                            Shift Requests
                            <span class="ml-2 bg-blue-100 text-blue-800 py-1 px-2 rounded-full text-xs">
                                {{ $pendingShiftRequests->count() + $approvedShiftRequests->count() + $rejectedShiftRequests->count() }}
                            </span>
                        </button>
                    </nav>
                </div>
                
                <!-- Status Sub-tabs -->
                <div class="mt-4" x-show="activeTab !== null">
                    <div class="flex space-x-4">
                        <button 
                            @click="statusTab = 'pending'" 
                            :class="statusTab === 'pending' ? 'bg-yellow-100 text-yellow-800 border-yellow-300' : 'bg-gray-100 text-gray-600 border-gray-300 hover:bg-gray-200'"
                            class="px-4 py-2 text-sm font-medium rounded-md border transition-colors"
                            data-requests-status
                        >
                            Pending
                            <span class="ml-2 px-2 py-1 bg-yellow-200 text-yellow-800 rounded-full text-xs" x-show="activeTab === 'leave'">
                                {{ $pendingLeaveRequests->count() }}
                            </span>
                            <span class="ml-2 px-2 py-1 bg-yellow-200 text-yellow-800 rounded-full text-xs" x-show="activeTab === 'shift'">
                                {{ $pendingShiftRequests->count() }}
                            </span>
                        </button>
                        <button 
                            @click="statusTab = 'approved'" 
                            :class="statusTab === 'approved' ? 'bg-green-100 text-green-800 border-green-300' : 'bg-gray-100 text-gray-600 border-gray-300 hover:bg-gray-200'"
                            class="px-4 py-2 text-sm font-medium rounded-md border transition-colors"
                            data-requests-status
                        >
                            Approved
                            <span class="ml-2 px-2 py-1 bg-green-200 text-green-800 rounded-full text-xs" x-show="activeTab === 'leave'">
                                {{ $approvedLeaveRequests->count() }}
                            </span>
                            <span class="ml-2 px-2 py-1 bg-green-200 text-green-800 rounded-full text-xs" x-show="activeTab === 'shift'">
                                {{ $approvedShiftRequests->count() }}
                            </span>
                        </button>
                        <button 
                            @click="statusTab = 'rejected'" 
                            :class="statusTab === 'rejected' ? 'bg-red-100 text-red-800 border-red-300' : 'bg-gray-100 text-gray-600 border-gray-300 hover:bg-gray-200'"
                            class="px-4 py-2 text-sm font-medium rounded-md border transition-colors"
                            data-requests-status
                        >
                            Rejected
                            <span class="ml-2 px-2 py-1 bg-red-200 text-red-800 rounded-full text-xs" x-show="activeTab === 'leave'">
                                {{ $rejectedLeaveRequests->count() }}
                            </span>
                            <span class="ml-2 px-2 py-1 bg-red-200 text-red-800 rounded-full text-xs" x-show="activeTab === 'shift'">
                                {{ $rejectedShiftRequests->count() }}
                            </span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Leave Requests Tab -->
            <div x-show="activeTab === 'leave'" class="space-y-6">
                
                <!-- Pending Leave Requests -->
                <div x-show="statusTab === 'pending'" class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <h2 class="text-lg font-medium text-gray-900">
                            Pending Leave Requests ({{ $pendingLeaveRequests->count() }})
                        </h2>
                        <div class="w-full sm:w-72">
                            <input type="text" placeholder="Search..."
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                   data-requests-search data-table-key="leave-pending">
                        </div>
                    </div>
                    
                    @if($pendingLeaveRequests->count() > 0)
                    <div class="overflow-x-auto js-requests-table" data-table-key="leave-pending">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-green-950">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Employee</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Leave Type</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Duration</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($pendingLeaveRequests as $request)
                                <tr data-search="{{ e(strtolower(
                                    ($request->user->name ?? '') . ' ' .
                                    ($request->user->lastname ?? '') . ' ' .
                                    ($request->user->email ?? '') . ' ' .
                                    ($request->leave_type ?? '') . ' ' .
                                    'pending' . ' ' .
                                    ($request->start_date ? $request->start_date->format('Y-m-d') : '') . ' ' .
                                    ($request->end_date ? $request->end_date->format('Y-m-d') : '')
                                )) }}">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                @if($request->user->photo)
                                                    <img class="h-10 w-10 rounded-full" src="{{ asset('storage/' . $request->user->photo) }}" alt="">
                                                @else
                                                    <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                                        <span class="text-sm font-medium text-gray-700">{{ e(substr($request->user->name, 0, 1)) }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ e($request->user->name) }} {{ e($request->user->lastname) }}</div>
                                                <div class="text-sm text-gray-500">{{ e($request->user->email) }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ e(ucfirst(str_replace('_', ' ', $request->leave_type))) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $request->days_requested }} day{{ $request->days_requested > 1 ? 's' : '' }}</div>
                                        <div class="text-sm text-gray-500">{{ $request->start_date->format('M j') }} - {{ $request->end_date->format('M j, Y') }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            Pending
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button @click="openLeaveModal({{ $request->id }})" class="inline-flex items-center justify-center w-9 h-9 rounded-md border border-gray-200 bg-white text-blue-600 hover:bg-blue-50 hover:text-blue-700 shadow-sm" aria-label="View details"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zm6 0c-1.273 4.057-5.064 7-9.542 7C7.064 19 3.273 16.057 2 12c1.273-4.057 5.064-7 9.458-7 4.478 0 8.269 2.943 9.542 7z"/></svg></button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="px-6 py-3 border-t border-gray-200 flex items-center justify-between text-sm text-gray-600" data-pagination data-table-key="leave-pending"></div>
                    @else
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No pending leave requests</h3>
                        <p class="mt-1 text-sm text-gray-500">All leave requests have been processed.</p>
                    </div>
                    @endif
                </div>

                <!-- Approved Leave Requests -->
                <div x-show="statusTab === 'approved'" class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <h2 class="text-lg font-medium text-gray-900">
                            Approved Leave Requests ({{ $approvedLeaveRequests->count() }})
                        </h2>
                        <div class="w-full sm:w-72">
                            <input type="text" placeholder="Search..."
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                   data-requests-search data-table-key="leave-approved">
                        </div>
                    </div>
                    
                    @if($approvedLeaveRequests->count() > 0)
                    <div class="overflow-x-auto js-requests-table" data-table-key="leave-approved">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-green-950">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Employee</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Leave Type</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Duration</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Approved Date</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($approvedLeaveRequests as $request)
                                <tr data-search="{{ e(strtolower(
                                    ($request->user->name ?? '') . ' ' .
                                    ($request->user->lastname ?? '') . ' ' .
                                    ($request->user->email ?? '') . ' ' .
                                    ($request->leave_type ?? '') . ' ' .
                                    'approved' . ' ' .
                                    ($request->start_date ? $request->start_date->format('Y-m-d') : '') . ' ' .
                                    ($request->end_date ? $request->end_date->format('Y-m-d') : '') . ' ' .
                                    ($request->approved_at ? $request->approved_at->format('Y-m-d') : '')
                                )) }}">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                @if($request->user->photo)
                                                    <img class="h-10 w-10 rounded-full" src="{{ asset('storage/' . $request->user->photo) }}" alt="">
                                                @else
                                                    <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                                        <span class="text-sm font-medium text-gray-700">{{ e(substr($request->user->name, 0, 1)) }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ e($request->user->name) }} {{ e($request->user->lastname) }}</div>
                                                <div class="text-sm text-gray-500">{{ $request->user->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ ucfirst(str_replace('_', ' ', $request->leave_type)) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $request->days_requested }} day{{ $request->days_requested > 1 ? 's' : '' }}</div>
                                        <div class="text-sm text-gray-500">{{ $request->start_date->format('M j') }} - {{ $request->end_date->format('M j, Y') }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Approved
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $request->approved_at ? $request->approved_at->format('M j, Y') : 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button @click="openLeaveModal({{ $request->id }})" class="inline-flex items-center justify-center w-9 h-9 rounded-md border border-gray-200 bg-white text-blue-600 hover:bg-blue-50 hover:text-blue-700 shadow-sm" aria-label="View details"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zm6 0c-1.273 4.057-5.064 7-9.542 7C7.064 19 3.273 16.057 2 12c1.273-4.057 5.064-7 9.458-7 4.478 0 8.269 2.943 9.542 7z"/></svg></button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="px-6 py-3 border-t border-gray-200 flex items-center justify-between text-sm text-gray-600" data-pagination data-table-key="leave-approved"></div>
                    @else
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No approved leave requests</h3>
                        <p class="mt-1 text-sm text-gray-500">No leave requests have been approved yet.</p>
                    </div>
                    @endif
                </div>

                <!-- Rejected Leave Requests -->
                <div x-show="statusTab === 'rejected'" class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <h2 class="text-lg font-medium text-gray-900">
                            Rejected Leave Requests ({{ $rejectedLeaveRequests->count() }})
                        </h2>
                        <div class="w-full sm:w-72">
                            <input type="text" placeholder="Search..."
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                   data-requests-search data-table-key="leave-rejected">
                        </div>
                    </div>
                    
                    @if($rejectedLeaveRequests->count() > 0)
                    <div class="overflow-x-auto js-requests-table" data-table-key="leave-rejected">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-green-950">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Employee</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Leave Type</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Duration</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Rejected Date</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($rejectedLeaveRequests as $request)
                                <tr data-search="{{ e(strtolower(
                                    ($request->user->name ?? '') . ' ' .
                                    ($request->user->lastname ?? '') . ' ' .
                                    ($request->user->email ?? '') . ' ' .
                                    ($request->leave_type ?? '') . ' ' .
                                    'rejected' . ' ' .
                                    ($request->start_date ? $request->start_date->format('Y-m-d') : '') . ' ' .
                                    ($request->end_date ? $request->end_date->format('Y-m-d') : '') . ' ' .
                                    ($request->approved_at ? $request->approved_at->format('Y-m-d') : '')
                                )) }}">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                @if($request->user->photo)
                                                    <img class="h-10 w-10 rounded-full" src="{{ asset('storage/' . $request->user->photo) }}" alt="">
                                                @else
                                                    <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                                        <span class="text-sm font-medium text-gray-700">{{ substr($request->user->name, 0, 1) }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $request->user->name }} {{ $request->user->lastname }}</div>
                                                <div class="text-sm text-gray-500">{{ $request->user->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ ucfirst(str_replace('_', ' ', $request->leave_type)) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $request->days_requested }} day{{ $request->days_requested > 1 ? 's' : '' }}</div>
                                        <div class="text-sm text-gray-500">{{ $request->start_date->format('M j') }} - {{ $request->end_date->format('M j, Y') }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            Rejected
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $request->approved_at ? $request->approved_at->format('M j, Y') : 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button @click="openLeaveModal({{ $request->id }})" class="inline-flex items-center justify-center w-9 h-9 rounded-md border border-gray-200 bg-white text-blue-600 hover:bg-blue-50 hover:text-blue-700 shadow-sm" aria-label="View details"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zm6 0c-1.273 4.057-5.064 7-9.542 7C7.064 19 3.273 16.057 2 12c1.273-4.057 5.064-7 9.458-7 4.478 0 8.269 2.943 9.542 7z"/></svg></button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="px-6 py-3 border-t border-gray-200 flex items-center justify-between text-sm text-gray-600" data-pagination data-table-key="leave-rejected"></div>
                    @else
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No rejected leave requests</h3>
                        <p class="mt-1 text-sm text-gray-500">No leave requests have been rejected.</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Shift Requests Tab -->
            <div x-show="activeTab === 'shift'" class="space-y-6">
                
                <!-- Pending Shift Requests -->
                <div x-show="statusTab === 'pending'" class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <h2 class="text-lg font-medium text-gray-900">
                            Pending Shift Requests ({{ $pendingShiftRequests->count() }})
                        </h2>
                        <div class="w-full sm:w-72">
                            <input type="text" placeholder="Search..."
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                   data-requests-search data-table-key="shift-pending">
                        </div>
                    </div>
                    
                    @if($pendingShiftRequests->count() > 0)
                    <div class="overflow-x-auto js-requests-table" data-table-key="shift-pending">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-green-950">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Employee</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Request Type</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Date</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($pendingShiftRequests as $request)
                                <tr data-search="{{ e(strtolower(
                                    ($request->user->name ?? '') . ' ' .
                                    ($request->user->lastname ?? '') . ' ' .
                                    ($request->user->email ?? '') . ' ' .
                                    ($request->request_type ?? '') . ' ' .
                                    'pending' . ' ' .
                                    ($request->request_date ? $request->request_date->format('Y-m-d') : '')
                                )) }}">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                @if($request->user->photo)
                                                    <img class="h-10 w-10 rounded-full" src="{{ asset('storage/' . $request->user->photo) }}" alt="">
                                                @else
                                                    <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                                        <span class="text-sm font-medium text-gray-700">{{ substr($request->user->name, 0, 1) }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $request->user->name }} {{ $request->user->lastname }}</div>
                                                <div class="text-sm text-gray-500">{{ $request->user->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ ucfirst(str_replace('_', ' ', $request->request_type)) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $request->request_date ? $request->request_date->format('M j, Y') : 'N/A' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            Pending
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button @click="openShiftModal({{ $request->id }})" class="inline-flex items-center justify-center w-9 h-9 rounded-md border border-gray-200 bg-white text-blue-600 hover:bg-blue-50 hover:text-blue-700 shadow-sm" aria-label="View details"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zm6 0c-1.273 4.057-5.064 7-9.542 7C7.064 19 3.273 16.057 2 12c1.273-4.057 5.064-7 9.458-7 4.478 0 8.269 2.943 9.542 7z"/></svg></button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="px-6 py-3 border-t border-gray-200 flex items-center justify-between text-sm text-gray-600" data-pagination data-table-key="shift-pending"></div>
                    @else
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No pending shift requests</h3>
                        <p class="mt-1 text-sm text-gray-500">All shift requests have been processed.</p>
                    </div>
                    @endif
                </div>

                <!-- Approved Shift Requests -->
                <div x-show="statusTab === 'approved'" class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <h2 class="text-lg font-medium text-gray-900">
                            Approved Shift Requests ({{ $approvedShiftRequests->count() }})
                        </h2>
                        <div class="w-full sm:w-72">
                            <input type="text" placeholder="Search..."
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                   data-requests-search data-table-key="shift-approved">
                        </div>
                    </div>
                    
                    @if($approvedShiftRequests->count() > 0)
                    <div class="overflow-x-auto js-requests-table" data-table-key="shift-approved">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-green-950">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Employee</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Request Type</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Date</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Approved Date</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($approvedShiftRequests as $request)
                                <tr data-search="{{ e(strtolower(
                                    ($request->user->name ?? '') . ' ' .
                                    ($request->user->lastname ?? '') . ' ' .
                                    ($request->user->email ?? '') . ' ' .
                                    ($request->request_type ?? '') . ' ' .
                                    'approved' . ' ' .
                                    ($request->request_date ? $request->request_date->format('Y-m-d') : '') . ' ' .
                                    ($request->approved_at ? $request->approved_at->format('Y-m-d') : '')
                                )) }}">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                @if($request->user->photo)
                                                    <img class="h-10 w-10 rounded-full" src="{{ asset('storage/' . $request->user->photo) }}" alt="">
                                                @else
                                                    <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                                        <span class="text-sm font-medium text-gray-700">{{ substr($request->user->name, 0, 1) }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $request->user->name }} {{ $request->user->lastname }}</div>
                                                <div class="text-sm text-gray-500">{{ $request->user->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ ucfirst(str_replace('_', ' ', $request->request_type)) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $request->request_date ? $request->request_date->format('M j, Y') : 'N/A' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Approved
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $request->approved_at ? $request->approved_at->format('M j, Y') : 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button @click="openShiftModal({{ $request->id }})" class="inline-flex items-center justify-center w-9 h-9 rounded-md border border-gray-200 bg-white text-blue-600 hover:bg-blue-50 hover:text-blue-700 shadow-sm" aria-label="View details"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zm6 0c-1.273 4.057-5.064 7-9.542 7C7.064 19 3.273 16.057 2 12c1.273-4.057 5.064-7 9.458-7 4.478 0 8.269 2.943 9.542 7z"/></svg></button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="px-6 py-3 border-t border-gray-200 flex items-center justify-between text-sm text-gray-600" data-pagination data-table-key="shift-approved"></div>
                    @else
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No approved shift requests</h3>
                        <p class="mt-1 text-sm text-gray-500">No shift requests have been approved yet.</p>
                    </div>
                    @endif
                </div>

                <!-- Rejected Shift Requests -->
                <div x-show="statusTab === 'rejected'" class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <h2 class="text-lg font-medium text-gray-900">
                            Rejected Shift Requests ({{ $rejectedShiftRequests->count() }})
                        </h2>
                        <div class="w-full sm:w-72">
                            <input type="text" placeholder="Search..."
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                   data-requests-search data-table-key="shift-rejected">
                        </div>
                    </div>
                    
                    @if($rejectedShiftRequests->count() > 0)
                    <div class="overflow-x-auto js-requests-table" data-table-key="shift-rejected">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-green-950">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Employee</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Request Type</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Date</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Rejected Date</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($rejectedShiftRequests as $request)
                                <tr data-search="{{ e(strtolower(
                                    ($request->user->name ?? '') . ' ' .
                                    ($request->user->lastname ?? '') . ' ' .
                                    ($request->user->email ?? '') . ' ' .
                                    ($request->request_type ?? '') . ' ' .
                                    'rejected' . ' ' .
                                    ($request->request_date ? $request->request_date->format('Y-m-d') : '') . ' ' .
                                    ($request->approved_at ? $request->approved_at->format('Y-m-d') : '')
                                )) }}">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                @if($request->user->photo)
                                                    <img class="h-10 w-10 rounded-full" src="{{ asset('storage/' . $request->user->photo) }}" alt="">
                                                @else
                                                    <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                                        <span class="text-sm font-medium text-gray-700">{{ substr($request->user->name, 0, 1) }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $request->user->name }} {{ $request->user->lastname }}</div>
                                                <div class="text-sm text-gray-500">{{ $request->user->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ ucfirst(str_replace('_', ' ', $request->request_type)) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $request->request_date ? $request->request_date->format('M j, Y') : 'N/A' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            Rejected
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $request->approved_at ? $request->approved_at->format('M j, Y') : 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button @click="openShiftModal({{ $request->id }})" class="inline-flex items-center justify-center w-9 h-9 rounded-md border border-gray-200 bg-white text-blue-600 hover:bg-blue-50 hover:text-blue-700 shadow-sm" aria-label="View details"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zm6 0c-1.273 4.057-5.064 7-9.542 7C7.064 19 3.273 16.057 2 12c1.273-4.057 5.064-7 9.458-7 4.478 0 8.269 2.943 9.542 7z"/></svg></button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="px-6 py-3 border-t border-gray-200 flex items-center justify-between text-sm text-gray-600" data-pagination data-table-key="shift-rejected"></div>
                    @else
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No rejected shift requests</h3>
                        <p class="mt-1 text-sm text-gray-500">No shift requests have been rejected.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Leave Request Modal -->
    <div x-show="showLeaveModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showLeaveModal" @click="showLeaveModal = false" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div x-show="showLeaveModal" @click.stop class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Leave Request Details</h3>
                            <div class="mt-4" x-show="selectedLeaveRequest">
                                <div class="space-y-4">
                                    <div class="flex items-center space-x-3">
                                        <div class="flex-shrink-0">
                                            <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                                <span class="text-sm font-medium text-gray-700" x-text="selectedLeaveRequest?.user?.name?.charAt(0)"></span>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900" x-text="selectedLeaveRequest?.user?.name + ' ' + (selectedLeaveRequest?.user?.lastname || '')"></div>
                                            <div class="text-sm text-gray-500" x-text="selectedLeaveRequest?.user?.email"></div>
                                        </div>
                                    </div>
                                    <div class="border-t pt-4">
                                        <dl class="space-y-3">
                                            <div><dt class="text-sm font-medium text-gray-500">Leave Type</dt><dd class="text-sm text-gray-900" x-text="selectedLeaveRequest?.leave_type_formatted"></dd></div>
                                            <div><dt class="text-sm font-medium text-gray-500">Duration</dt><dd class="text-sm text-gray-900" x-text="selectedLeaveRequest?.days_requested + ' day' + (selectedLeaveRequest?.days_requested > 1 ? 's' : '')"></dd></div>
                                            <div><dt class="text-sm font-medium text-gray-500">Dates</dt><dd class="text-sm text-gray-900" x-text="selectedLeaveRequest?.start_date + ' - ' + selectedLeaveRequest?.end_date"></dd></div>
                                            <div><dt class="text-sm font-medium text-gray-500">Reason</dt><dd class="text-sm text-gray-900" x-text="selectedLeaveRequest?.reason"></dd></div>
                                            <div><dt class="text-sm font-medium text-gray-500">Submitted</dt><dd class="text-sm text-gray-900" x-text="selectedLeaveRequest?.created_at"></dd></div>
                                        </dl>
                                    </div>
                                    <div x-show="showRejectionReason" class="border-t pt-4">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Rejection Reason</label>
                                        <textarea x-model="rejectionReason" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-red-500 focus:border-red-500 text-sm" placeholder="Please provide a reason for rejecting this request..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 flex flex-col sm:flex-row sm:justify-end space-y-2 sm:space-y-0 sm:space-x-3">
                    <button @click="showLeaveModal = false; showRejectionReason = false; rejectionReason = ''" 
                            class="inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                        <span x-text="selectedLeaveRequest?.status === 'pending' ? 'Cancel' : 'Close'"></span>
                    </button>
                    <!-- Only show approve/reject buttons for pending requests -->
                    <template x-if="selectedLeaveRequest?.status === 'pending'">
                        <div class="flex space-x-3">
                            <button @click="showRejectionReason ? updateRequestStatus('leave', selectedLeaveRequest.id, 'rejected') : (showRejectionReason = true)" 
                                    :disabled="processing" 
                                    class="inline-flex justify-center ml-2 rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-sm font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                                <span x-text="processing ? 'Processing...' : (showRejectionReason ? 'Confirm Reject' : 'Reject')"></span>
                            </button>
                            <button @click="updateRequestStatus('leave', selectedLeaveRequest.id, 'approved')" 
                                    :disabled="processing" 
                                    class="inline-flex justify-center ml-2 rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-sm font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                                <span x-text="processing ? 'Processing...' : 'Approve'"></span>
                            </button>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <!-- Shift Request Modal -->
    <div x-show="showShiftModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showShiftModal" @click="showShiftModal = false" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div x-show="showShiftModal" @click.stop class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-purple-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Shift Request Details</h3>
                            <div class="mt-4" x-show="selectedShiftRequest">
                                <div class="space-y-4">
                                    <div class="flex items-center space-x-3">
                                        <div class="flex-shrink-0">
                                            <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                                <span class="text-sm font-medium text-gray-700" x-text="selectedShiftRequest?.user?.name?.charAt(0)"></span>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900" x-text="selectedShiftRequest?.user?.name + ' ' + (selectedShiftRequest?.user?.lastname || '')"></div>
                                            <div class="text-sm text-gray-500" x-text="selectedShiftRequest?.user?.email"></div>
                                        </div>
                                    </div>
                                    <div class="border-t pt-4">
                                        <dl class="space-y-3">
                                            <div><dt class="text-sm font-medium text-gray-500">Request Type</dt><dd class="text-sm text-gray-900" x-text="selectedShiftRequest?.request_type_formatted"></dd></div>
                                            <div><dt class="text-sm font-medium text-gray-500">Date</dt><dd class="text-sm text-gray-900" x-text="selectedShiftRequest?.request_date"></dd></div>
                                            <div x-show="selectedShiftRequest?.request_type === 'swap' && selectedShiftRequest?.other_user"><dt class="text-sm font-medium text-gray-500">Swap With</dt><dd class="text-sm text-gray-900" x-text="selectedShiftRequest?.other_user?.name + ' ' + (selectedShiftRequest?.other_user?.lastname || '')"></dd></div>
                                            <div x-show="selectedShiftRequest?.reason"><dt class="text-sm font-medium text-gray-500">Reason</dt><dd class="text-sm text-gray-900" x-text="selectedShiftRequest?.reason"></dd></div>
                                            <div><dt class="text-sm font-medium text-gray-500">Submitted</dt><dd class="text-sm text-gray-900" x-text="selectedShiftRequest?.created_at"></dd></div>
                                        </dl>
                                    </div>
                                    <div x-show="showRejectionReason" class="border-t pt-4">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Rejection Reason</label>
                                        <textarea x-model="rejectionReason" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-red-500 focus:border-red-500 text-sm" placeholder="Please provide a reason for rejecting this request..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 flex flex-col sm:flex-row sm:justify-end space-y-2 sm:space-y-0 sm:space-x-3">
                    <button @click="showShiftModal = false; showRejectionReason = false; rejectionReason = ''" 
                            class="inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                        <span x-text="selectedShiftRequest?.status === 'pending' ? 'Cancel' : 'Close'"></span>
                    </button>
                    <!-- Only show approve/reject buttons for pending requests -->
                    <template x-if="selectedShiftRequest?.status === 'pending'">
                        <div class="flex space-x-3">
                            <button @click="showRejectionReason ? updateRequestStatus('shift', selectedShiftRequest.id, 'rejected') : (showRejectionReason = true)" 
                                    :disabled="processing" 
                                    class="inline-flex justify-center ml-2 rounded-lg border border-transparent shadow-sm px-4 py-2 bg-red-600 text-sm font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                                <span x-text="processing ? 'Processing...' : (showRejectionReason ? 'Confirm Reject' : 'Reject')"></span>
                            </button>
                            <button @click="updateRequestStatus('shift', selectedShiftRequest.id, 'approved')" 
                                    :disabled="processing" 
                                    class="inline-flex justify-center ml-2 rounded-lg border border-transparent shadow-sm px-4 py-2 bg-green-600 text-sm font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                                <span x-text="processing ? 'Processing...' : 'Approve'"></span>
                            </button>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
</style>

<script>
function requestsManagement() {
    return {
        activeTab: 'leave',
        statusTab: 'pending',
        showLeaveModal: false,
        showShiftModal: false,
        selectedLeaveRequest: null,
        selectedShiftRequest: null,
        showRejectionReason: false,
        rejectionReason: '',
        processing: false,
        
        // Leave requests by status
        pendingLeaveRequests: @json($pendingLeaveRequests->toArray()),
        approvedLeaveRequests: @json($approvedLeaveRequests->toArray()),
        rejectedLeaveRequests: @json($rejectedLeaveRequests->toArray()),
        
        // Shift requests by status
        pendingShiftRequests: @json($pendingShiftRequests->toArray()),
        approvedShiftRequests: @json($approvedShiftRequests->toArray()),
        rejectedShiftRequests: @json($rejectedShiftRequests->toArray()),

        async openLeaveModal(requestId) {
            // Search across all leave request arrays
            const allLeaveRequests = [...this.pendingLeaveRequests, ...this.approvedLeaveRequests, ...this.rejectedLeaveRequests];
            const request = allLeaveRequests.find(r => r.id === requestId);
            
            if (request) {
                this.selectedLeaveRequest = {
                    ...request,
                    leave_type_formatted: this.formatLeaveType(request.leave_type),
                    start_date: new Date(request.start_date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }),
                    end_date: new Date(request.end_date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }),
                    created_at: new Date(request.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })
                };
                this.showLeaveModal = true;
                this.showRejectionReason = false;
                this.rejectionReason = '';
            }
        },

        async openShiftModal(requestId) {
            // Search across all shift request arrays
            const allShiftRequests = [...this.pendingShiftRequests, ...this.approvedShiftRequests, ...this.rejectedShiftRequests];
            const request = allShiftRequests.find(r => r.id === requestId);
            
            if (request) {
                this.selectedShiftRequest = {
                    ...request,
                    request_type_formatted: this.formatRequestType(request.request_type),
                    request_date: new Date(request.request_date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }),
                    created_at: new Date(request.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })
                };
                this.showShiftModal = true;
                this.showRejectionReason = false;
                this.rejectionReason = '';
            }
        },

        async updateRequestStatus(type, requestId, status) {
            if (status === 'rejected' && !this.rejectionReason.trim()) {
                alert('Please provide a rejection reason.');
                return;
            }

            this.processing = true;
            const endpoint = type === 'leave' 
                ? `/employee-management/leave-requests/${requestId}/status`
                : `/employee-management/shift-requests/${requestId}/status`;

            // Prepare request data
            const requestData = { status: status };
            if (status === 'rejected') {
                requestData.rejection_reason = this.rejectionReason;
            }

            try {
                const response = await fetch(endpoint, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(requestData)
                });

                const result = await response.json();
                if (result.success) {
                    if (type === 'leave') {
                        // Find and remove request from pending array
                        const pendingIndex = this.pendingLeaveRequests.findIndex(r => r.id === requestId);
                        if (pendingIndex !== -1) {
                            const request = this.pendingLeaveRequests.splice(pendingIndex, 1)[0];
                            request.status = status;
                            request.approved_at = new Date().toISOString();
                            
                            // Move to appropriate array
                            if (status === 'approved') {
                                this.approvedLeaveRequests.unshift(request);
                            } else if (status === 'rejected') {
                                request.rejection_reason = this.rejectionReason;
                                this.rejectedLeaveRequests.unshift(request);
                            }
                        }
                        this.showLeaveModal = false;
                    } else {
                        // Find and remove request from pending array
                        const pendingIndex = this.pendingShiftRequests.findIndex(r => r.id === requestId);
                        if (pendingIndex !== -1) {
                            const request = this.pendingShiftRequests.splice(pendingIndex, 1)[0];
                            request.status = status;
                            request.approved_at = new Date().toISOString();
                            
                            // Move to appropriate array
                            if (status === 'approved') {
                                this.approvedShiftRequests.unshift(request);
                            } else if (status === 'rejected') {
                                request.rejection_reason = this.rejectionReason;
                                this.rejectedShiftRequests.unshift(request);
                            }
                        }
                        this.showShiftModal = false;
                    }
                    alert(result.message);
                    
                    // No need to reload page, data is updated in real-time
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred while processing the request.');
            } finally {
                this.processing = false;
                this.showRejectionReason = false;
                this.rejectionReason = '';
            }
        },

        formatLeaveType(leaveType) {
            return leaveType.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
        },

        formatRequestType(requestType) {
            return requestType.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const searchInputs = Array.from(document.querySelectorAll('[data-requests-search]'));
    const tables = Array.from(document.querySelectorAll('.js-requests-table'));
    const pageSize = 10;
    const state = new Map();

    tables.forEach((table) => {
        const key = table.dataset.tableKey;
        state.set(key, { page: 1 });
    });

    const normalize = (value) => (value || '').toString().toLowerCase();

    const renderPagination = (key, totalItems, totalPages, currentPage, startIndex, endIndex) => {
        const pagination = document.querySelector(`[data-pagination][data-table-key="${key}"]`);
        if (!pagination) return;

        if (totalItems === 0) {
            pagination.innerHTML = '<span class="text-gray-500">No matching records</span>';
            return;
        }

        const prevDisabled = currentPage <= 1 ? 'opacity-50 cursor-not-allowed' : '';
        const nextDisabled = currentPage >= totalPages ? 'opacity-50 cursor-not-allowed' : '';

        pagination.innerHTML = `
            <div class="text-gray-500">Showing ${startIndex}–${endIndex} of ${totalItems}</div>
            <div class="flex items-center space-x-2">
                <button type="button" class="px-3 py-1 border border-gray-300 rounded-md text-sm ${prevDisabled}" data-page-action="prev" data-table-key="${key}">Previous</button>
                <span class="text-sm text-gray-600">Page ${currentPage} of ${totalPages}</span>
                <button type="button" class="px-3 py-1 border border-gray-300 rounded-md text-sm ${nextDisabled}" data-page-action="next" data-table-key="${key}">Next</button>
            </div>
        `;
    };

    const applyTable = (table) => {
        const key = table.dataset.tableKey;
        const rows = Array.from(table.querySelectorAll('tbody tr'));
        const input = document.querySelector(`[data-requests-search][data-table-key="${key}"]`);
        const query = normalize(input ? input.value.trim() : '');
        const filtered = rows.filter((row) => normalize(row.dataset.search).includes(query));

        const tableState = state.get(key) || { page: 1 };
        const totalPages = Math.max(1, Math.ceil(filtered.length / pageSize));
        const currentPage = Math.min(tableState.page, totalPages);
        tableState.page = currentPage;
        state.set(key, tableState);

        const start = (currentPage - 1) * pageSize;
        const end = start + pageSize;
        const visibleRows = new Set(filtered.slice(start, end));

        rows.forEach((row) => {
            row.style.display = visibleRows.has(row) ? '' : 'none';
        });

        const startIndex = filtered.length === 0 ? 0 : start + 1;
        const endIndex = filtered.length === 0 ? 0 : Math.min(end, filtered.length);
        renderPagination(key, filtered.length, totalPages, currentPage, startIndex, endIndex);
    };

    const applyAllTables = () => {
        tables.forEach(applyTable);
    };

    searchInputs.forEach((input) => {
        input.addEventListener('input', () => {
            const key = input.dataset.tableKey;
            if (!key) return;
            const tableState = state.get(key) || { page: 1 };
            tableState.page = 1;
            state.set(key, tableState);
            const table = tables.find((t) => t.dataset.tableKey === key);
            if (table) applyTable(table);
        });
    });

    document.addEventListener('click', (event) => {
        const target = event.target;
        if (!(target instanceof HTMLElement)) return;

        const action = target.dataset.pageAction;
        const key = target.dataset.tableKey;
        if (!action || !key) return;

        const tableState = state.get(key) || { page: 1 };
        if (action === 'prev') {
            tableState.page = Math.max(1, tableState.page - 1);
        } else if (action === 'next') {
            tableState.page = tableState.page + 1;
        }
        state.set(key, tableState);
        const table = tables.find((t) => t.dataset.tableKey === key);
        if (table) applyTable(table);
    });

    document.querySelectorAll('[data-requests-tab],[data-requests-status]').forEach((button) => {
        button.addEventListener('click', () => {
            setTimeout(() => applyAllTables(), 0);
        });
    });

    applyAllTables();
});
</script>
@endsection
