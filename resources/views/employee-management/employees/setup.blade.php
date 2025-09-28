@extends('dashboard')

@section('title', 'Employee Profile Setup')

@section('content')
<div class="min-h-screen bg-gray-100">
    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">
                            {{ $user->employee ? 'Edit' : 'Setup' }} Employee Profile
                        </h1>
                        <p class="text-gray-600 mt-1">{{ $user->name }} {{ $user->lastname }}</p>
                    </div>
                    <a href="{{ route('employee-management.employees') }}" class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">
                        Back to Employees
                    </a>
                </div>
            </div>

            <!-- Success Message -->
            @if(session('success'))
                <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Profile Form -->
            <div class="bg-white shadow rounded-lg">
                <form method="POST" action="{{ route('employee-management.employees.store-profile', $user) }}">
                    @csrf
                    
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-medium text-gray-900">Employee Information</h2>
                        <p class="text-sm text-gray-600">Set up the employee's basic information and job details.</p>
                    </div>

                    <div class="px-6 py-6 space-y-6">
                        <!-- Basic Information -->
                        <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
                            <div>
                                <label for="employee_id" class="block text-sm font-medium text-gray-700">Employee ID *</label>
                                <input type="text" name="employee_id" id="employee_id" 
                                       value="{{ old('employee_id', $user->employee->employee_id ?? '') }}"
                                       class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md @error('employee_id') border-red-300 @enderror" 
                                       required>
                                @error('employee_id')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                                <input type="text" name="phone" id="phone" 
                                       value="{{ old('phone', $user->phone) }}"
                                       class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md @error('phone') border-red-300 @enderror">
                                @error('phone')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Job Information -->
                        <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
                            <div>
                                <label for="department" class="block text-sm font-medium text-gray-700">Department *</label>
                                <select name="department" id="department" 
                                        class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('department') border-red-300 @enderror" 
                                        required>
                                    <option value="">Select Department</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->department_name }}" 
                                                {{ old('department', $user->employee->department ?? '') == $department->department_name ? 'selected' : '' }}>
                                            {{ $department->department_name }} ({{ $department->department_code }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('department')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="position" class="block text-sm font-medium text-gray-700">Position *</label>
                                <input type="text" name="position" id="position" 
                                       value="{{ old('position', $user->employee->position ?? $user->position) }}"
                                       class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md @error('position') border-red-300 @enderror" 
                                       required>
                                @error('position')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
                            <div>
                                <label for="manager_name" class="block text-sm font-medium text-gray-700">Manager Name</label>
                                <input type="text" name="manager_name" id="manager_name" 
                                       value="{{ old('manager_name', $user->employee->manager_name ?? '') }}"
                                       class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md @error('manager_name') border-red-300 @enderror">
                                @error('manager_name')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="hire_date" class="block text-sm font-medium text-gray-700">Hire Date *</label>
                                <input type="date" name="hire_date" id="hire_date" 
                                       value="{{ old('hire_date', $user->employee->hire_date?->format('Y-m-d') ?? '') }}"
                                       class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md @error('hire_date') border-red-300 @enderror" 
                                       required>
                                @error('hire_date')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Employment Details -->
                        <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-3">
                            <div>
                                <label for="employment_type" class="block text-sm font-medium text-gray-700">Employment Type *</label>
                                <select name="employment_type" id="employment_type" 
                                        class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('employment_type') border-red-300 @enderror" 
                                        required>
                                    <option value="">Select Type</option>
                                    <option value="full_time" {{ old('employment_type', $user->employee->employment_type ?? '') == 'full_time' ? 'selected' : '' }}>Full Time</option>
                                    <option value="part_time" {{ old('employment_type', $user->employee->employment_type ?? '') == 'part_time' ? 'selected' : '' }}>Part Time</option>
                                    <option value="contract" {{ old('employment_type', $user->employee->employment_type ?? '') == 'contract' ? 'selected' : '' }}>Contract</option>
                                    <option value="internship" {{ old('employment_type', $user->employee->employment_type ?? '') == 'internship' ? 'selected' : '' }}>Internship</option>
                                </select>
                                @error('employment_type')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="salary" class="block text-sm font-medium text-gray-700">Salary (Optional)</label>
                                <input type="number" name="salary" id="salary" step="0.01" min="0"
                                       value="{{ old('salary', $user->employee->salary ?? '') }}"
                                       class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md @error('salary') border-red-300 @enderror">
                                @error('salary')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700">Employment Status *</label>
                                <select name="status" id="status" 
                                        class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('status') border-red-300 @enderror" 
                                        required>
                                    <option value="">Select Status</option>
                                    <option value="active" {{ old('status', $user->employee->status ?? 'active') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status', $user->employee->status ?? '') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    <option value="terminated" {{ old('status', $user->employee->status ?? '') == 'terminated' ? 'selected' : '' }}>Terminated</option>
                                </select>
                                @error('status')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Work Location -->
                        <div>
                            <label for="work_location" class="block text-sm font-medium text-gray-700">Work Location *</label>
                            <input type="text" name="work_location" id="work_location" 
                                   value="{{ old('work_location', $user->employee->work_location ?? '') }}"
                                   placeholder="e.g., Main Office, Remote, Building A - Floor 2"
                                   class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md @error('work_location') border-red-300 @enderror" 
                                   required>
                            @error('work_location')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Emergency Contact -->
                        <div class="border-t border-gray-200 pt-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Emergency Contact</h3>
                            <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
                                <div>
                                    <label for="emergency_contact_name" class="block text-sm font-medium text-gray-700">Emergency Contact Name</label>
                                    <input type="text" name="emergency_contact_name" id="emergency_contact_name" 
                                           value="{{ old('emergency_contact_name', $user->employee->emergency_contact_name ?? '') }}"
                                           class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md @error('emergency_contact_name') border-red-300 @enderror">
                                    @error('emergency_contact_name')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="emergency_contact_phone" class="block text-sm font-medium text-gray-700">Emergency Contact Phone</label>
                                    <input type="text" name="emergency_contact_phone" id="emergency_contact_phone" 
                                           value="{{ old('emergency_contact_phone', $user->employee->emergency_contact_phone ?? '') }}"
                                           class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md @error('emergency_contact_phone') border-red-300 @enderror">
                                    @error('emergency_contact_phone')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Address -->
                        <div>
                            <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                            <textarea name="address" id="address" rows="3" 
                                      class="shadow-sm focus:ring-blue-500 focus:border-blue-500 mt-1 block w-full sm:text-sm border border-gray-300 rounded-md @error('address') border-red-300 @enderror"
                                      placeholder="Full address including street, city, state, and postal code">{{ old('address', $user->employee->address ?? '') }}</textarea>
                            @error('address')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-between">
                        <a href="{{ route('employee-management.employees') }}" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Cancel
                        </a>
                        <button type="submit" class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            {{ $user->employee ? 'Update Profile' : 'Create Profile' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
