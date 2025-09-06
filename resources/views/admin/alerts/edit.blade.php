@extends('layouts.app')

@section('title', 'Edit Alert')

@section('content')
<div class="min-h-screen bg-gray-100">
    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Edit Alert</h1>
                        <p class="text-gray-600 mt-1">Update the alert details and settings</p>
                    </div>
                    <a href="{{ route('admin.alerts') }}" class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">
                        Back to Alerts
                    </a>
                </div>
            </div>

            <!-- Edit Alert Form -->
            <div class="bg-white rounded-lg shadow">
                <form method="POST" action="{{ route('admin.alerts.update', $alert) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-medium text-gray-900">Alert Details</h2>
                        <p class="text-sm text-gray-600">Update the alert message and settings.</p>
                    </div>

                    <div class="px-6 py-6 space-y-6">
                        <!-- Title -->
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700">Alert Title</label>
                            <input type="text" name="title" id="title" 
                                   value="{{ old('title', $alert->title) }}"
                                   placeholder="Enter a brief, descriptive title"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('title') border-red-300 @enderror" 
                                   required>
                            @error('title')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Message -->
                        <div>
                            <label for="message" class="block text-sm font-medium text-gray-700">Alert Message</label>
                            <textarea name="message" id="message" rows="4" 
                                      placeholder="Enter the detailed alert message"
                                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('message') border-red-300 @enderror" 
                                      required>{{ old('message', $alert->message) }}</textarea>
                            @error('message')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Type and Priority -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="type" class="block text-sm font-medium text-gray-700">Alert Type</label>
                                <select name="type" id="type" 
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('type') border-red-300 @enderror" 
                                        required>
                                    <option value="">Select Type</option>
                                    <option value="info" {{ old('type', $alert->type) == 'info' ? 'selected' : '' }}>Information</option>
                                    <option value="warning" {{ old('type', $alert->type) == 'warning' ? 'selected' : '' }}>Warning</option>
                                    <option value="error" {{ old('type', $alert->type) == 'error' ? 'selected' : '' }}>Error</option>
                                    <option value="success" {{ old('type', $alert->type) == 'success' ? 'selected' : '' }}>Success</option>
                                </select>
                                @error('type')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="priority" class="block text-sm font-medium text-gray-700">Priority Level</label>
                                <select name="priority" id="priority" 
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('priority') border-red-300 @enderror" 
                                        required>
                                    <option value="">Select Priority</option>
                                    <option value="low" {{ old('priority', $alert->priority) == 'low' ? 'selected' : '' }}>Low</option>
                                    <option value="medium" {{ old('priority', $alert->priority) == 'medium' ? 'selected' : '' }}>Medium</option>
                                    <option value="high" {{ old('priority', $alert->priority) == 'high' ? 'selected' : '' }}>High</option>
                                    <option value="urgent" {{ old('priority', $alert->priority) == 'urgent' ? 'selected' : '' }}>Urgent</option>
                                </select>
                                @error('priority')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Active Status -->
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="is_active" value="1" 
                                       {{ old('is_active', $alert->is_active) ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-700">Alert is active</span>
                            </label>
                            <p class="mt-1 text-xs text-gray-500">Uncheck to disable this alert without deleting it</p>
                        </div>

                        <!-- Date Range -->
                        <div class="border-t border-gray-200 pt-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Schedule (Optional)</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date & Time</label>
                                    <input type="datetime-local" name="start_date" id="start_date" 
                                           value="{{ old('start_date', $alert->start_date?->format('Y-m-d\TH:i')) }}"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('start_date') border-red-300 @enderror">
                                    <p class="mt-1 text-xs text-gray-500">Leave empty to show immediately</p>
                                    @error('start_date')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="end_date" class="block text-sm font-medium text-gray-700">End Date & Time</label>
                                    <input type="datetime-local" name="end_date" id="end_date" 
                                           value="{{ old('end_date', $alert->end_date?->format('Y-m-d\TH:i')) }}"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('end_date') border-red-300 @enderror">
                                    <p class="mt-1 text-xs text-gray-500">Leave empty to show indefinitely</p>
                                    @error('end_date')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Target Roles -->
                        <div class="border-t border-gray-200 pt-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Target Audience (Optional)</h3>
                            <div class="space-y-2">
                                <p class="text-sm text-gray-600 mb-3">Select which user roles should see this alert. Leave unchecked to show to all users.</p>
                                
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                    <label class="flex items-center">
                                        <input type="checkbox" name="target_roles[]" value="employee" 
                                               {{ in_array('employee', old('target_roles', $alert->target_roles ?? [])) ? 'checked' : '' }}
                                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                        <span class="ml-2 text-sm text-gray-700">Employee</span>
                                    </label>
                                    
                                    <label class="flex items-center">
                                        <input type="checkbox" name="target_roles[]" value="manager" 
                                               {{ in_array('manager', old('target_roles', $alert->target_roles ?? [])) ? 'checked' : '' }}
                                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                        <span class="ml-2 text-sm text-gray-700">Manager</span>
                                    </label>
                                    
                                    <label class="flex items-center">
                                        <input type="checkbox" name="target_roles[]" value="hr" 
                                               {{ in_array('hr', old('target_roles', $alert->target_roles ?? [])) ? 'checked' : '' }}
                                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                        <span class="ml-2 text-sm text-gray-700">HR</span>
                                    </label>
                                    
                                    <label class="flex items-center">
                                        <input type="checkbox" name="target_roles[]" value="admin" 
                                               {{ in_array('admin', old('target_roles', $alert->target_roles ?? [])) ? 'checked' : '' }}
                                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                        <span class="ml-2 text-sm text-gray-700">Admin</span>
                                    </label>
                                </div>
                            </div>
                            @error('target_roles')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-between">
                        <a href="{{ route('admin.alerts') }}" class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            Cancel
                        </a>
                        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            Update Alert
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
