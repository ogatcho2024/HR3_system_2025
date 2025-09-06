@extends('dashboard')

@section('title', 'Employee Reports')

@section('content')
<div class="w-full p-3 sm:px-4">
    <!-- Breadcrumbs -->
    @include('partials.breadcrumbs', ['breadcrumbs' => [
        ['label' => 'Reports', 'url' => route('reports.index')],
        ['label' => 'Employee Reports', 'url' => route('reports.employees')]
    ]])
    
    <!-- Header -->
    <div class="mb-4 sm:mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h3 class="text-xl sm:text-2xl font-bold text-gray-900">Employee Reports</h3>
                <p class="text-gray-600 text-sm mt-1">Comprehensive employee information and analytics</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-2 sm:gap-3">
                <button onclick="window.print()" class="px-3 py-2 mr-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm">
                    Print Report
                </button>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-lg p-3 sm:p-4 mb-4">
        <h4 class="text-lg font-semibold text-gray-900 mb-3 sm:mb-4">Filters</h4>
        <form method="GET" action="{{ route('reports.employees') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div>
                <label for="department" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Department</label>
                <select name="department" id="department" class="w-full px-2 py-1.5 sm:px-3 sm:py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                    <option value="">All Departments</option>
                    @foreach($departments ?? [] as $dept)
                        <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>
                            {{ $dept }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="status" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" id="status" class="w-full px-2 py-1.5 sm:px-3 sm:py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="terminated" {{ request('status') == 'terminated' ? 'selected' : '' }}>Terminated</option>
                </select>
            </div>

            <div>
                <label for="employment_type" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Employment Type</label>
                <select name="employment_type" id="employment_type" class="w-full px-2 py-1.5 sm:px-3 sm:py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                    <option value="">All Types</option>
                    @foreach($employmentTypes ?? [] as $type)
                        <option value="{{ $type }}" {{ request('employment_type') == $type ? 'selected' : '' }}>
                            {{ ucfirst(str_replace('_', ' ', $type)) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex flex-col sm:flex-row sm:col-span-2 lg:col-span-4 justify-end gap-2 sm:gap-3 mt-2">
                <a href="{{ route('reports.employees') }}" class="px-3 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors text-center text-sm">
                    Clear Filters
                </a>
                <button type="submit" class="px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm">
                    Apply Filters
                </button>
            </div>
        </form>
    </div>

    <!-- Employee Table -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="px-3 py-3 sm:px-4 sm:py-4 border-b border-gray-200 bg-gray-50">
            <h4 class="text-base sm:text-lg font-semibold text-gray-900">
                Employee List 
                <span class="text-xs sm:text-sm font-normal text-gray-600">
                    ({{ $employees->total() ?? 0 }} employees)
                </span>
            </h4>
        </div>

        @if(isset($employees) && $employees->count() > 0)
            <!-- Desktop Table View -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 sm:px-4 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Employee
                            </th>
                            <th class="px-3 py-2 sm:px-4 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Dept
                            </th>
                            <th class="px-3 py-2 sm:px-4 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Position
                            </th>
                            <th class="px-3 py-2 sm:px-4 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Type
                            </th>
                            <th class="px-3 py-2 sm:px-4 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Hired
                            </th>
                            <th class="px-3 py-2 sm:px-4 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-3 py-2 sm:px-4 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Salary
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($employees as $employee)
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-3 sm:px-4 sm:py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-8 w-8 sm:h-10 sm:w-10">
                                            <div class="h-8 w-8 sm:h-10 sm:w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                                <span class="text-xs sm:text-sm font-medium text-gray-700">
                                                    {{ strtoupper(substr($employee->user->name ?? '', 0, 1)) }}{{ strtoupper(substr($employee->user->lastname ?? '', 0, 1)) }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="ml-2 sm:ml-4">
                                            <div class="text-xs sm:text-sm font-medium text-gray-900">
                                                {{ $employee->user->name ?? 'N/A' }} {{ $employee->user->lastname ?? '' }}
                                            </div>
                                            <div class="text-xs text-gray-500 hidden sm:block">
                                                {{ $employee->user->email ?? 'N/A' }}
                                            </div>
                                            <div class="text-xs text-gray-400 sm:hidden">
                                                ID: {{ $employee->employee_id ?? 'N/A' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-3 sm:px-4 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-900">
                                    {{ $employee->department ?? 'N/A' }}
                                </td>
                                <td class="px-3 py-3 sm:px-4 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-900">
                                    {{ $employee->position ?? 'N/A' }}
                                </td>
                                <td class="px-3 py-3 sm:px-4 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-900">
                                    <span class="px-1.5 py-0.5 sm:px-2 sm:py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                                        @if($employee->employment_type == 'full_time') bg-blue-100 text-blue-800
                                        @elseif($employee->employment_type == 'part_time') bg-yellow-100 text-yellow-800
                                        @elseif($employee->employment_type == 'contract') bg-purple-100 text-purple-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                        {{ ucfirst(str_replace('_', ' ', $employee->employment_type ?? 'N/A')) }}
                                    </span>
                                </td>
                                <td class="px-3 py-3 sm:px-4 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-900">
                                    {{ $employee->hire_date ? \Carbon\Carbon::parse($employee->hire_date)->format('M d, Y') : 'N/A' }}
                                </td>
                                <td class="px-3 py-3 sm:px-4 sm:py-4 whitespace-nowrap">
                                    <span class="px-1.5 py-0.5 sm:px-2 sm:py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                                        @if($employee->status == 'active') bg-green-100 text-green-800
                                        @elseif($employee->status == 'inactive') bg-yellow-100 text-yellow-800
                                        @else bg-red-100 text-red-800 @endif">
                                        {{ ucfirst($employee->status ?? 'N/A') }}
                                    </span>
                                </td>
                                <td class="px-3 py-3 sm:px-4 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-900">
                                    @if($employee->salary)
                                        ₱{{ number_format($employee->salary, 2) }}
                                    @else
                                        N/A
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card View -->
            <div class="md:hidden">
                @foreach($employees as $employee)
                    <div class="p-3 border-b border-gray-200 last:border-b-0">
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0">
                                <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                    <span class="text-xs font-medium text-gray-700">
                                        {{ strtoupper(substr($employee->user->name ?? '', 0, 1)) }}{{ strtoupper(substr($employee->user->lastname ?? '', 0, 1)) }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-900 truncate">
                                            {{ $employee->user->name ?? 'N/A' }} {{ $employee->user->lastname ?? '' }}
                                        </h4>
                                        <p class="text-xs text-gray-500 truncate">
                                            {{ $employee->user->email ?? 'N/A' }}
                                        </p>
                                    </div>
                                    <span class="px-1.5 py-0.5 text-xs leading-5 font-semibold rounded-full
                                        @if($employee->status == 'active') bg-green-100 text-green-800
                                        @elseif($employee->status == 'inactive') bg-yellow-100 text-yellow-800
                                        @else bg-red-100 text-red-800 @endif">
                                        {{ ucfirst($employee->status ?? 'N/A') }}
                                    </span>
                                </div>
                                <div class="mt-2 grid grid-cols-2 gap-2 text-xs">
                                    <div>
                                        <span class="text-gray-500">ID:</span>
                                        <span class="text-gray-900">{{ $employee->employee_id ?? 'N/A' }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Dept:</span>
                                        <span class="text-gray-900">{{ $employee->department ?? 'N/A' }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Position:</span>
                                        <span class="text-gray-900">{{ $employee->position ?? 'N/A' }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Type:</span>
                                        <span class="px-1 py-0.5 inline-flex text-xs leading-4 font-medium rounded
                                            @if($employee->employment_type == 'full_time') bg-blue-100 text-blue-800
                                            @elseif($employee->employment_type == 'part_time') bg-yellow-100 text-yellow-800
                                            @elseif($employee->employment_type == 'contract') bg-purple-100 text-purple-800
                                            @else bg-gray-100 text-gray-800 @endif">
                                            {{ ucfirst(str_replace('_', ' ', $employee->employment_type ?? 'N/A')) }}
                                        </span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Hired:</span>
                                        <span class="text-gray-900">
                                            {{ $employee->hire_date ? \Carbon\Carbon::parse($employee->hire_date)->format('M d, Y') : 'N/A' }}
                                        </span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Salary:</span>
                                        <span class="text-gray-900 font-medium">
                                            @if($employee->salary)
                                                ₱{{ number_format($employee->salary, 2) }}
                                            @else
                                                N/A
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            @if(isset($employees) && $employees->hasPages())
                <div class="px-3 py-2 sm:px-4 sm:py-3 border-t border-gray-200 bg-gray-50">
                    {{ $employees->withQueryString()->links() }}
                </div>
            @endif
        @else
            <div class="px-4 py-8 sm:py-12 text-center">
                <svg class="mx-auto h-10 w-10 sm:h-12 sm:w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <h3 class="mt-3 sm:mt-4 text-base sm:text-lg font-medium text-gray-900">No employees found</h3>
                <p class="mt-1 sm:mt-2 text-xs sm:text-sm text-gray-500">
                    No employees match the current filter criteria.
                </p>
            </div>
        @endif
    </div>
</div>
@endsection
