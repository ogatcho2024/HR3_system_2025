@extends('dashboard')

@section('title', 'Manage Alerts')

@section('content')
<div class="min-h-screen bg-gray-100">
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8 flex justify-between items-center">
                <div>
                    <h3 class="text-3xl font-bold text-gray-900">Manage Alerts</h3>
                    <p class="text-gray-600 mt-1">Create and manage system-wide notifications</p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('employee-management.alerts.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                        Create New Alert
                    </a>
                    <a href="{{ route('employee-management.dashboard') }}" class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">
                        Back to Dashboard
                    </a>
                </div>
            </div>

            <!-- Success Message -->
            @if(session('success'))
                <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Alerts List -->
            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">
                        System Alerts ({{ $alerts->total() }})
                    </h2>
                </div>
                
                @if($alerts->count() > 0)
                <ul class="divide-y divide-gray-200">
                    @foreach($alerts as $alert)
                    <li class="px-6 py-4">
                        <div class="flex items-start justify-between">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center space-x-3 mb-2">
                                    <h3 class="text-lg font-medium text-gray-900 truncate">
                                        {{ $alert->title }}
                                    </h3>
                                    <span class="px-2 py-1 text-xs rounded-full {{ $alert->type_badge_color }}">
                                        {{ ucfirst($alert->type) }}
                                    </span>
                                    <span class="px-2 py-1 text-xs rounded-full {{ $alert->priority_badge_color }}">
                                        {{ ucfirst($alert->priority) }}
                                    </span>
                                    @if($alert->is_active)
                                        <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">
                                            Active
                                        </span>
                                    @else
                                        <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">
                                            Inactive
                                        </span>
                                    @endif
                                </div>
                                
                                <p class="text-gray-600 mb-3">{{ $alert->message }}</p>
                                
                                <div class="flex items-center space-x-4 text-sm text-gray-500">
                                    <span>
                                        Created by: {{ $alert->creator->name }} {{ $alert->creator->lastname }}
                                    </span>
                                    <span>•</span>
                                    <span>{{ $alert->created_at->format('M j, Y g:i A') }}</span>
                                    
                                    @if($alert->start_date)
                                        <span>•</span>
                                        <span>Start: {{ $alert->start_date->format('M j, Y g:i A') }}</span>
                                    @endif
                                    
                                    @if($alert->end_date)
                                        <span>•</span>
                                        <span>End: {{ $alert->end_date->format('M j, Y g:i A') }}</span>
                                    @endif
                                </div>
                                
                                @if($alert->target_roles && count($alert->target_roles) > 0)
                                <div class="mt-2">
                                    <span class="text-sm text-gray-500">Target Roles:</span>
                                    @foreach($alert->target_roles as $role)
                                        <span class="ml-2 px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded">
                                            {{ ucfirst($role) }}
                                        </span>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                            
                            <div class="flex items-center space-x-2 ml-4">
                                <a href="{{ route('employee-management.alerts.edit', $alert) }}" class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                                    Edit
                                </a>
                                <form method="POST" action="{{ route('employee-management.alerts.delete', $alert) }}" class="inline"
                                      onsubmit="return confirm('Are you sure you want to delete this alert?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900 text-sm font-medium">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </li>
                    @endforeach
                </ul>
                @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5-5-5h5v-12a1 1 0 011-1h2a1 1 0 011 1v12z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No alerts</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by creating a new alert.</p>
                    <div class="mt-6">
                        <a href="{{ route('employee-management.alerts.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            Create New Alert
                        </a>
                    </div>
                </div>
                @endif
                
                <!-- Pagination -->
                @if($alerts->hasPages())
                <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                    {{ $alerts->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
