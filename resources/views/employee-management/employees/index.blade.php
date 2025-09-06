@extends('dashboard')

@section('title', 'Employees')

@section('content')
<div class="min-h-screen bg-gray-300">
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Breadcrumbs -->
            @include('partials.breadcrumbs', ['breadcrumbs' => [
                ['label' => 'Employee Self Service Management', 'url' => route('employee-management.dashboard')],
                ['label' => 'Profile Management', 'url' => route('employee-management.employees')]
            ]])
        
        <!-- Header -->
            <div class="mb-8">
            </div>

            <!-- Search and Filters -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <form method="GET" action="{{ route('employee-management.employees') }}" class="space-y-4 sm:space-y-0 sm:flex sm:items-end sm:space-x-4">
                    <div class="flex-1">
                        <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                        <input type="text" name="search" id="search" 
                               value="{{ request('search') }}"
                               placeholder="Search by name or email..." 
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    
                    <div>
                        <label for="profile_status" class="block text-sm font-medium text-gray-700">Profile Status</label>
                        <select name="profile_status" id="profile_status" 
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Employees</option>
                            <option value="complete" {{ request('profile_status') == 'complete' ? 'selected' : '' }}>Complete Profiles</option>
                            <option value="incomplete" {{ request('profile_status') == 'incomplete' ? 'selected' : '' }}>Incomplete Profiles</option>
                        </select>
                    </div>
                    
                    <div class="flex space-x-2">
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                            Search
                        </button>
                        <a href="{{ route('employee-management.employees') }}" class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">
                            Clear
                        </a>
                    </div>
                </form>
            </div>

            <!-- Employees Grid -->
            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">
                        Employees ({{ $employees->total() }})
                    </h2>
                </div>
                
                @if($employees->count() > 0)
                <ul class="divide-y divide-gray-200">
                    @foreach($employees as $user)
                    <li>
                        <div class="px-6 py-4 flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-12 w-12">
                                    @if($user->photo)
                                        <img class="h-12 w-12 rounded-full" src="{{ asset('storage/' . $user->photo) }}" alt="{{ $user->name }}">
                                    @else
                                        <div class="h-12 w-12 rounded-full bg-gray-300 flex items-center justify-center">
                                            <span class="text-sm font-medium text-gray-700">{{ substr($user->name, 0, 1) }}{{ substr($user->lastname, 0, 1) }}</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $user->name }} {{ $user->lastname }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ $user->email }}
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex items-center space-x-4">
                                <div class="text-sm text-gray-900">
                                    <div class="font-medium">{{ $user->employee->employee_id ?? 'Not Set' }}</div>
                                    <div class="text-gray-500">{{ $user->employee->department ?? 'No Department' }}</div>
                                </div>
                                
                                <div>
                                    @if($user->employee)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Complete
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            Incomplete
                                        </span>
                                    @endif
                                </div>
                                
                                <div>
                                    <a href="{{ route('employee-management.employees.setup', $user) }}" 
                                       class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 text-sm">
                                        {{ $user->employee ? 'Edit Profile' : 'Setup Profile' }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </li>
                    @endforeach
                </ul>
                @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No employees found</h3>
                    <p class="mt-1 text-sm text-gray-500">Try adjusting your search criteria.</p>
                </div>
                @endif
                
                <!-- Pagination -->
                @if($employees->hasPages())
                <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                    {{ $employees->appends(request()->query())->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
