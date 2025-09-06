@extends('dashboard')

@section('title', 'My Profile')

@section('content')
<div class="p-6">
    <!-- Header -->
    <div class="mb-6">
        <h3 class="text-3xl font-bold text-gray-900">My Profile</h3>
        <p class="text-gray-600 mt-2">Manage your personal and contact information</p>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Profile Information Card -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-center">
                    <div class="w-24 h-24 mx-auto bg-gray-300 rounded-full flex items-center justify-center mb-4">
                        @if($user->photo)
                            <img src="{{ asset('storage/' . $user->photo) }}" alt="Profile Photo" class="w-24 h-24 rounded-full object-cover">
                        @else
                            <svg class="w-12 h-12 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        @endif
                    </div>
                    <h2 class="text-xl font-semibold text-gray-900">{{ $user->name }} {{ $user->lastname }}</h2>
                    <p class="text-gray-600">{{ $user->email }}</p>
                    @if($employee)
                        <p class="text-sm text-gray-500 mt-1">{{ $employee->employee_id ?? 'No Employee ID' }}</p>
                        <p class="text-sm text-gray-500">{{ $employee->department ?? 'No Department' }}</p>
                    @endif
                </div>
            </div>

            @if($employee)
            <!-- Employment Information Card -->
            <div class="bg-white rounded-lg shadow p-6 mt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Employment Information</h3>
                <div class="space-y-3">
                    <div>
                        <label class="text-sm font-medium text-gray-500">Position</label>
                        <p class="text-gray-900">{{ $employee->position ?? 'Not specified' }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Manager</label>
                        <p class="text-gray-900">{{ $employee->manager_name ?? 'Not specified' }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Hire Date</label>
                        <p class="text-gray-900">{{ $employee->hire_date ? $employee->hire_date->format('M d, Y') : 'Not specified' }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Employment Type</label>
                        <p class="text-gray-900">{{ ucfirst($employee->employment_type) }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Work Location</label>
                        <p class="text-gray-900">{{ $employee->work_location ?? 'Not specified' }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Status</label>
                        <span class="px-2 py-1 text-xs font-medium rounded-full {{ $employee->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ ucfirst($employee->status) }}
                        </span>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Editable Information -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Personal Information</h3>
                    <p class="text-sm text-gray-600">Update your contact information and emergency details</p>
                </div>

                <form method="POST" action="{{ route('employee.profile.update') }}" class="p-6">
                    @csrf
                    @method('PUT')

                    <!-- Basic Information (Read-only) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
                            <input type="text" value="{{ $user->name }}" disabled class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 text-gray-500">
                            <p class="text-xs text-gray-500 mt-1">Contact HR to update</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
                            <input type="text" value="{{ $user->lastname }}" disabled class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 text-gray-500">
                            <p class="text-xs text-gray-500 mt-1">Contact HR to update</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                            <input type="email" value="{{ $user->email }}" disabled class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 text-gray-500">
                            <p class="text-xs text-gray-500 mt-1">Contact HR to update</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                            <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('phone') border-red-500 @enderror">
                            @error('phone')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Emergency Contact Information -->
                    <div class="border-t border-gray-200 pt-6 mb-6">
                        <h4 class="text-md font-medium text-gray-900 mb-4">Emergency Contact</h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Emergency Contact Name</label>
                                <input type="text" name="emergency_contact_name" value="{{ old('emergency_contact_name', $employee->emergency_contact_name ?? '') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('emergency_contact_name') border-red-500 @enderror">
                                @error('emergency_contact_name')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Emergency Contact Phone</label>
                                <input type="tel" name="emergency_contact_phone" value="{{ old('emergency_contact_phone', $employee->emergency_contact_phone ?? '') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('emergency_contact_phone') border-red-500 @enderror">
                                @error('emergency_contact_phone')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Address Information -->
                    <div class="border-t border-gray-200 pt-6">
                        <h4 class="text-md font-medium text-gray-900 mb-4">Address Information</h4>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Home Address</label>
                            <textarea name="address" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('address') border-red-500 @enderror" placeholder="Enter your complete address">{{ old('address', $employee->address ?? '') }}</textarea>
                            @error('address')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Save Button -->
                    <div class="flex justify-end pt-6 border-t border-gray-200 mt-6">
                        <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Update Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
