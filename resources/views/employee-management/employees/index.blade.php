@extends('dashboard')

@section('noFooter', true)


@section('title', 'User Profile Management')

@section('content')
<div class="bg-gray-300 min-h-full flex flex-col">
    <div class="flex-1 py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-full flex flex-col">
        
        <!-- Breadcrumbs -->
            @include('partials.breadcrumbs', ['breadcrumbs' => [
                ['label' => 'Employee Self Service Management', 'url' => route('employee-management.dashboard')],
                ['label' => 'Profile Management', 'url' => route('employee-management.employees')]
            ]])
    
            
            <!-- Success/Error Messages -->
            @if(session('success'))
                <div class="bg-green-50 border border-green-200 rounded-md p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif
            
            @if(session('error'))
                <div class="bg-red-50 border border-red-200 rounded-md p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
            @endif
            
            <!-- Search and Filters -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <form method="GET" action="{{ route('employee-management.employees') }}" class="space-y-4 sm:space-y-0 sm:flex sm:items-end sm:space-x-4">
                    <div class="flex-1 rounded-lg">
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
                    
                    <div>
                        <label for="department" class="block text-sm font-medium text-gray-700">Department</label>
                        <select name="department" id="department" 
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Departments</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->department_name }}" {{ request('department') == $department->department_name ? 'selected' : '' }}>
                                    {{ $department->department_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="flex space-x-2">
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                            Search
                        </button>
                        <a href="{{ route('employee-management.employees') }}" class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">
                            Clear
                        </a>
                    </div>
                </form>
            </div>

            <!-- User Profiles Management Table -->
            <div class="bg-white shadow overflow-hidden sm:rounded-lg flex-1">
                <div class="px-6 py-4 border-b bg-gray-50 border-gray-200">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <h2 class="text-lg font-medium text-gray-900">
                            User Profiles Management ({{ $employees->total() }})
                        </h2>
                    </div>
                </div>
                
                @if($employees->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 table-fixed">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="w-1/4 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    User
                                </th>
                                <th scope="col" class="w-1/6 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Department
                                </th>
                                <th scope="col" class="w-1/8 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Profile Status
                                </th>
                                <th scope="col" class="w-1/8 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Date Created
                                </th>
                                <th scope="col" class="w-1/8 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    User Role
                                </th>
                                <th scope="col" class="w-1/6 px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ Auth::user()->isStaff() ? 'Access' : 'Actions' }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($employees as $employee)
                            <tr class="hover:bg-gray-50">
                                <!-- User Column -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            @if($employee->user && $employee->user->photo)
                                                <img class="h-10 w-10 rounded-full object-cover" src="{{ asset('storage/' . $employee->user->photo) }}" alt="{{ $employee->user->name ?? 'User' }}">
                                            @else
                                                <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                                    <span class="text-sm font-medium text-gray-700">
                                                        @if($employee->user)
                                                            {{ substr($employee->user->name ?? '', 0, 1) }}{{ substr($employee->user->lastname ?? '', 0, 1) }}
                                                        @else
                                                            ??
                                                        @endif
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                @if($employee->user)
                                                    {{ $employee->user->name ?? '' }} {{ $employee->user->lastname ?? '' }}
                                                @else
                                                    <span class="text-gray-400 italic">No User Account</span>
                                                @endif
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ $employee->user->email ?? 'N/A' }}
                                            </div>
                                            <div class="text-xs text-gray-400">
                                                ID: {{ $employee->employee_id ?? 'Not Assigned' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                
                                <!-- Department Column -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    @if($employee->department)
                                        @php
                                            $colors = [
                                                'Information Technology' => 'bg-blue-100 text-blue-800',
                                                'IT' => 'bg-blue-100 text-blue-800',
                                                'Marketing' => 'bg-blue-100 text-blue-800',
                                                'Finance' => 'bg-blue-100 text-blue-800',
                                                'Human Resources' => 'bg-blue-100 text-blue-800',
                                                'HR' => 'bg-blue-100 text-blue-800',
                                                'Logistics' => 'bg-blue-100 text-blue-800',
                                                'Maintenance' => 'bg-blue-100 text-blue-800',
                                            ];
                                            $colorClass = $colors[$employee->department] ?? 'bg-gray-100 text-gray-800';
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $colorClass }}">
                                            {{ $employee->department }}
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-400 italic">Not Assigned</span>
                                    @endif
                                </td>
                                
                                <!-- Profile Status Column -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        @if($employee->user) {{-- User exists in database --}}
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                </svg>
                                                Complete
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                                </svg>
                                                Incomplete
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                
                                <!-- Date Created Column -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <div class="flex flex-col">
                                        @if($employee->created_at)
                                            <span class="font-medium text-sm">{{ $employee->created_at->format('M j, Y') }}</span>
                                            <span class="text-xs text-gray-500">{{ $employee->created_at->format('g:i A') }}</span>
                                        @else
                                            <span class="font-medium text-sm text-gray-400">N/A</span>
                                        @endif
                                    </div>
                                </td>
                                
                                <!-- User Role Column -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    @if($employee->user)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                            @if($employee->user->account_type == 'Super admin') bg-blue-100 text-blue-800
                                            @elseif($employee->user->account_type == 'Admin') bg-blue-100 text-blue-800
                                            @elseif($employee->user->account_type == 'Staff') bg-blue-100 text-blue-800
                                            @else bg-blue-100 text-blue-800
                                            @endif">
                                            @if($employee->user->account_type == 'Super admin' || $employee->user->account_type == 'Admin')
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd" />
                                                </svg>
                                            @endif
                                            {{ $employee->user->account_type }}
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-400 italic">No Account</span>
                                    @endif
                                </td>
                                
                                <!-- Actions Column -->
                                <td class="px-6 py-4 text-right text-sm font-medium">
                                    <div class="flex items-center justify-end space-x-1 min-w-max">
                                        @if(Auth::user()->isStaff())
                                            <span class="text-xs text-gray-500">View only</span>
                                        @else
                                            @if($employee->user) {{-- User exists in database --}}
                                                <!-- Edit and Delete for existing users -->
                                                <button onclick="openEditModal({{ $employee->user->id }}, '{{ $employee->user->name }}', '{{ $employee->user->lastname }}', '{{ $employee->user->email }}', '{{ $employee->user->phone }}', '{{ $employee->user->position }}', '{{ $employee->user->account_type }}', '{{ $employee->user->photo }}')" 
                                                   class="inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                                                   title="Edit User">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                    <span class="ml-1 hidden sm:inline">Edit</span>
                                                </button>
                                                <button onclick="deleteUser({{ $employee->user->id }}, '{{ $employee->user->name }} {{ $employee->user->lastname }}')" 
                                                        class="inline-flex items-center px-2 py-1 border ml-2 border-transparent text-xs font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors"
                                                        title="Delete User">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                    <span class="ml-1 hidden sm:inline">Delete</span>
                                                </button>
                                            @else
                                                <!-- Create User Account for employees without users -->
                                                <button onclick="openCreateUserModal({{ $employee->id }})" 
                                                   class="inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors"
                                                   title="Create User Account">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                                    </svg>
                                                    <span class="ml-1">Create User</span>
                                                </button>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
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

<!-- Edit User Modal -->
<div id="editModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <!-- Backdrop with blur effect -->
    <div class="absolute inset-0 backdrop-blur-sm transition-opacity" aria-hidden="true"></div>
        
        <!-- Modal Content -->
        <div class="relative bg-white rounded-xl shadow-2xl w-1/2 max-h-[90vh] overflow-y-auto transform transition-all">
            <div class="bg-white rounded-xl">
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Edit User Account</h3>
                    <button type="button" onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <!-- Modal Body with Registration Form -->
                <div class="p-6">
                    <form id="editUserForm" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <!-- Profile Photo -->
                        <div class="w-full justify-center items-center flex mb-6">
                            <div class="w-25 h-25 rounded-full relative">
                                <img id="editProfilePreview" class="w-full h-full object-cover rounded-full shadow-lg" src="{{ asset('images/uploadprof.png') }}" alt="Profile Preview">
                                <input class="hidden" id="editInputFile" name="photo" type="file" accept="image/*">
                                <label for="editInputFile" class="bg-blue-950 w-8 h-8 absolute bottom-[-8px] right-1 z-50 cursor-pointer rounded-full shadow-lg hover:scale-110 flex justify-center items-center">
                                    <svg class="w-5 h-5" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M3 3H0V14H16V3H13L11 1H5L3 3ZM8 11C9.65685 11 11 9.65685 11 8C11 6.34315 9.65685 5 8 5C6.34315 5 5 6.34315 5 8C5 9.65685 6.34315 11 8 11Z" fill="#ffffff" />
                                    </svg>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Form Fields -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div class="relative z-0 w-full mb-5 group">
                                <input type="text" name="name" id="editName" class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-blue-600 peer" placeholder=" " required />
                                <label for="editName" class="peer-focus:font-medium absolute text-sm text-gray-500 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:text-blue-600 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">First Name</label>
                            </div>
                            
                            <div class="relative z-0 w-full mb-5 group">
                                <input type="text" name="lastname" id="editLastname" class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-blue-600 peer" placeholder=" " required />
                                <label for="editLastname" class="peer-focus:font-medium absolute text-sm text-gray-500 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:text-blue-600 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">Last Name</label>
                            </div>
                            
                            <div class="relative z-0 w-full mb-5 group">
                                <input type="email" name="email" id="editEmail" class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-blue-600 peer" placeholder=" " required />
                                <label for="editEmail" class="peer-focus:font-medium absolute text-sm text-gray-500 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:text-blue-600 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">Email Address</label>
                            </div>
                            
                            <div class="relative z-0 w-full mb-5 group">
                                <input type="tel" name="phone" id="editPhone" class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-blue-600 peer" placeholder=" " required />
                                <label for="editPhone" class="peer-focus:font-medium absolute text-sm text-gray-500 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:text-blue-600 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">Phone Number</label>
                            </div>
                            
                            <div class="relative z-0 w-full mb-5 group">
                                <input type="text" name="position" id="editPosition" class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-blue-600 peer" placeholder=" " required />
                                <label for="editPosition" class="peer-focus:font-medium absolute text-sm text-gray-500 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:text-blue-600 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">Position</label>
                            </div>
                            
                            <div class="relative z-0 w-full mb-5 group">
                                <select name="account_type" id="editAccountType" class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-blue-600 peer" required>
                                    <option value="" disabled></option>
                                    <option value="Super admin">Super Admin</option>
                                    <option value="Admin">Admin</option>
                                    <option value="Staff">Staff</option>
                                    <option value="Employee">Employee</option>
                                </select>
                                <label for="editAccountType" class="peer-focus:font-medium absolute text-sm text-gray-500 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:text-blue-600 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">Account Type</label>
                            </div>
                        </div>
                        
                        <!-- Modal Footer -->
                        <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                            <button type="button" onclick="closeEditModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Cancel
                            </button>
                            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Update User
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <!-- Backdrop with blur effect -->
    <div class="absolute inset-0 backdrop-blur-sm transition-opacity" aria-hidden="true"></div>
    
    <!-- Modal Content -->
    <div class="relative bg-white rounded-xl shadow-2xl max-w-md w-full p-6 transform transition-all">
        <!-- Icon -->
        <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 bg-red-100 rounded-full">
            <svg class="w-8 h-8 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
            </svg>
        </div>
        
        <!-- Content -->
        <div class="text-center">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">
                Delete User Account
            </h3>
            <p class="text-sm text-gray-600 mb-6">
                Are you sure you want to delete the user account for <strong id="userName" class="text-gray-900"></strong>? This action cannot be undone and will permanently remove the user from the system.
            </p>
        </div>
        
        <!-- Buttons -->
        <div class="flex gap-3">
            <button type="button" 
                    onclick="closeDeleteModal()" 
                    class="flex-1 px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors">
                Cancel
            </button>
            <button type="button" 
                    onclick="confirmDelete()" 
                    class="flex-1 px-4 py-2.5 text-sm font-medium text-white bg-red-600 border border-transparent rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                Delete
            </button>
        </div>
    </div>
</div>

<script>
    let userToDelete = null;
    let currentEditUserId = null;
    
    // Edit Modal Functions
    function openEditModal(userId, name, lastname, email, phone, position, accountType, photo) {
        currentEditUserId = userId;
        
        // Fill form fields
        document.getElementById('editName').value = name || '';
        document.getElementById('editLastname').value = lastname || '';
        document.getElementById('editEmail').value = email || '';
        document.getElementById('editPhone').value = phone || '';
        document.getElementById('editPosition').value = position || '';
        document.getElementById('editAccountType').value = accountType || '';
        
        // Set profile photo
        const profilePreview = document.getElementById('editProfilePreview');
        if (photo && photo !== 'null') {
            profilePreview.src = `{{ url('storage') }}/${photo}`;
        } else {
            profilePreview.src = '{{ asset('images/uploadprof.png') }}';
        }
        
        // Set form action
        document.getElementById('editUserForm').action = `{{ url('employee-management/users') }}/${userId}`;
        
        // Show modal
        document.getElementById('editModal').classList.remove('hidden');
    }
    
    function closeEditModal() {
        currentEditUserId = null;
        document.getElementById('editModal').classList.add('hidden');
        document.getElementById('editUserForm').reset();
        document.getElementById('editProfilePreview').src = '{{ asset('images/uploadprof.png') }}';
    }
    
    function openSetupModal() {
        // For setup, we'll redirect to a registration page or open a create modal
        // For now, let's redirect to the register page
        window.location.href = '{{ route('register') }}';
    }
    
    // Delete Modal Functions
    function deleteUser(userId, userName) {
        userToDelete = userId;
        document.getElementById('userName').textContent = userName;
        document.getElementById('deleteModal').classList.remove('hidden');
    }
    
    function closeDeleteModal() {
        userToDelete = null;
        document.getElementById('deleteModal').classList.add('hidden');
    }
    
    function confirmDelete() {
        if (userToDelete) {
            // Create a form to submit the DELETE request
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `{{ url('employee-management/users') }}/${userToDelete}`;
            
            // Add CSRF token
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);
            
            // Add method override for DELETE
            const methodField = document.createElement('input');
            methodField.type = 'hidden';
            methodField.name = '_method';
            methodField.value = 'DELETE';
            form.appendChild(methodField);
            
            // Submit the form
            document.body.appendChild(form);
            form.submit();
        }
    }
    
    // Profile photo preview for edit modal
    document.addEventListener('DOMContentLoaded', function() {
        const editInputFile = document.getElementById('editInputFile');
        const editProfilePreview = document.getElementById('editProfilePreview');
        
        editInputFile.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const file = this.files[0];
                if (!file.type.match('image.*')) {
                    alert('Please select an image file');
                    return;
                }
                if (file.size > 5 * 1024 * 1024) {
                    alert('Image size should be less than 5MB');
                    return;
                }
                const reader = new FileReader();
                reader.onload = function(e) {
                    editProfilePreview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
        
        // Form submission handling
        document.getElementById('editUserForm').addEventListener('submit', function(e) {
            e.preventDefault();
            this.submit();
        });
    });
    
    // Close modals when clicking outside or with Escape key
    document.addEventListener('click', function(e) {
        if (e.target.id === 'editModal') {
            closeEditModal();
        }
        if (e.target.id === 'deleteModal') {
            closeDeleteModal();
        }
    });
    
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeEditModal();
            closeDeleteModal();
        }
    });
</script>


<style>
    /* Ensure backdrop blur works across browsers */
    .backdrop-blur-sm {
        backdrop-filter: blur(4px);
        -webkit-backdrop-filter: blur(4px);
    }
    
    /* Modal animation */
    #deleteModal, #editModal {
        transition: opacity 0.3s ease-in-out;
    }
    
    #deleteModal.hidden, #editModal.hidden {
        opacity: 0;
        pointer-events: none;
    }
    
    #deleteModal:not(.hidden), #editModal:not(.hidden) {
        opacity: 1;
        pointer-events: auto;
    }
    
    /* Modal content animation */
    #deleteModal .relative, #editModal .relative {
        transition: transform 0.3s ease-in-out;
    }
    
    #deleteModal.hidden .relative, #editModal.hidden .relative {
        transform: scale(0.95);
    }
    
    #deleteModal:not(.hidden) .relative, #editModal:not(.hidden) .relative {
        transform: scale(1);
    }
</style>



@endsection
