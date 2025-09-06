@extends('dashboard')

@section('title', 'Create Alert')

@section('content')
<div class="min-h-screen bg-gray-100">
    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Create New Alert</h1>
                        <p class="text-gray-600 mt-1">Send a system-wide notification to employees</p>
                    </div>
                    <a href="{{ route('employee-management.alerts') }}" class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">
                        Back to Alerts
                    </a>
                </div>
            </div>

            <!-- Create Alert Form -->
            <div class="bg-white shadow rounded-lg">
                <form method="POST" action="{{ route('employee-management.alerts.store') }}">
                    @csrf
                    
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-medium text-gray-900">Alert Details</h2>
                        <p class="text-sm text-gray-600">Configure the alert message and settings.</p>
                    </div>

                    <div class="px-6 py-6 space-y-6">
                        <!-- Title -->
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700">Alert Title *</label>
                            <input type="text" name="title" id="title" 
                                   value="{{ old('title') }}"
                                   placeholder="Enter a brief, descriptive title"
                                   class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md @error('title') border-red-300 @enderror" 
                                   required>
                            @error('title')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Message -->
                        <div>
                            <label for="message" class="block text-sm font-medium text-gray-700">Alert Message *</label>
                            <textarea name="message" id="message" rows="4" 
                                      placeholder="Enter the detailed alert message"
                                      class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md @error('message') border-red-300 @enderror" 
                                      required>{{ old('message') }}</textarea>
                            @error('message')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Type and Priority -->
                        <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
                            <div>
                                <label for="type" class="block text-sm font-medium text-gray-700">Alert Type *</label>
                                <select name="type" id="type" 
                                        class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('type') border-red-300 @enderror" 
                                        required>
                                    <option value="">Select Type</option>
                                    <option value="info" {{ old('type') == 'info' ? 'selected' : '' }}>Information</option>
                                    <option value="warning" {{ old('type') == 'warning' ? 'selected' : '' }}>Warning</option>
                                    <option value="error" {{ old('type') == 'error' ? 'selected' : '' }}>Error</option>
                                    <option value="success" {{ old('type') == 'success' ? 'selected' : '' }}>Success</option>
                                </select>
                                @error('type')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="priority" class="block text-sm font-medium text-gray-700">Priority Level *</label>
                                <select name="priority" id="priority" 
                                        class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('priority') border-red-300 @enderror" 
                                        required>
                                    <option value="">Select Priority</option>
                                    <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
                                    <option value="medium" {{ old('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                                    <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
                                    <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                                </select>
                                @error('priority')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Date Range -->
                        <div class="border-t border-gray-200 pt-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Schedule (Optional)</h3>
                            <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
                                <div>
                                    <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date & Time</label>
                                    <input type="datetime-local" name="start_date" id="start_date" 
                                           value="{{ old('start_date') }}"
                                           class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md @error('start_date') border-red-300 @enderror">
                                    <p class="mt-1 text-xs text-gray-500">Leave empty to show immediately</p>
                                    @error('start_date')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="end_date" class="block text-sm font-medium text-gray-700">End Date & Time</label>
                                    <input type="datetime-local" name="end_date" id="end_date" 
                                           value="{{ old('end_date') }}"
                                           class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md @error('end_date') border-red-300 @enderror">
                                    <p class="mt-1 text-xs text-gray-500">Leave empty to show indefinitely</p>
                                    @error('end_date')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Target Roles -->
                        <div class="border-t border-gray-200 pt-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Target Audience (Optional)</h3>
                            <div class="space-y-2">
                                <p class="text-sm text-gray-600 mb-3">Select which user roles should see this alert. Leave unchecked to show to all users.</p>
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <label class="relative flex items-start">
                                        <div class="flex items-center h-5">
                                            <input id="role_employee" name="target_roles[]" value="employee" type="checkbox" 
                                                   {{ in_array('employee', old('target_roles', [])) ? 'checked' : '' }}
                                                   class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                                        </div>
                                        <div class="ml-3 text-sm">
                                            <label for="role_employee" class="font-medium text-gray-700">Employee</label>
                                        </div>
                                    </label>
                                    
                                    <label class="relative flex items-start">
                                        <div class="flex items-center h-5">
                                            <input id="role_manager" name="target_roles[]" value="manager" type="checkbox" 
                                                   {{ in_array('manager', old('target_roles', [])) ? 'checked' : '' }}
                                                   class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                                        </div>
                                        <div class="ml-3 text-sm">
                                            <label for="role_manager" class="font-medium text-gray-700">Manager</label>
                                        </div>
                                    </label>
                                    
                                    <label class="relative flex items-start">
                                        <div class="flex items-center h-5">
                                            <input id="role_hr" name="target_roles[]" value="hr" type="checkbox" 
                                                   {{ in_array('hr', old('target_roles', [])) ? 'checked' : '' }}
                                                   class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                                        </div>
                                        <div class="ml-3 text-sm">
                                            <label for="role_hr" class="font-medium text-gray-700">HR</label>
                                        </div>
                                    </label>
                                    
                                    <label class="relative flex items-start">
                                        <div class="flex items-center h-5">
                                            <input id="role_admin" name="target_roles[]" value="admin" type="checkbox" 
                                                   {{ in_array('admin', old('target_roles', [])) ? 'checked' : '' }}
                                                   class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                                        </div>
                                        <div class="ml-3 text-sm">
                                            <label for="role_admin" class="font-medium text-gray-700">Admin</label>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            @error('target_roles')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-between">
                        <a href="{{ route('employee-management.alerts') }}" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Cancel
                        </a>
                        <button type="submit" class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Create Alert
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
