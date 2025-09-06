@extends('dashboard')

@section('title', 'Add Timesheet')

@section('content')
<div class="min-h-screen bg-gray-100">
    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-3xl font-bold text-gray-900">Add New Timesheet</h3>
                        <p class="text-gray-600 mt-1">Record your work hours and activities</p>
                    </div>
                    <a href="{{ route('employee.timesheets') }}" class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">
                        Back to Timesheets
                    </a>
                </div>
            </div>

            <!-- Timesheet Form -->
            <div class="bg-white shadow sm:rounded-md">
                <form action="{{ route('employee.timesheets.store') }}" method="POST">
                    @csrf
                    
                    <div class="px-6 py-6 space-y-6">
                        <!-- Work Date -->
                        <div>
                            <label for="work_date" class="block text-sm font-medium text-gray-700">Work Date *</label>
                            <input type="date" 
                                   id="work_date" 
                                   name="work_date" 
                                   value="{{ old('work_date', now()->format('Y-m-d')) }}"
                                   max="{{ now()->format('Y-m-d') }}"
                                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('work_date') border-red-300 @enderror"
                                   required>
                            @error('work_date')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Time Section -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="clock_in_time" class="block text-sm font-medium text-gray-700">Clock In Time</label>
                                <input type="time" 
                                       id="clock_in_time" 
                                       name="clock_in_time" 
                                       value="{{ old('clock_in_time') }}"
                                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('clock_in_time') border-red-300 @enderror">
                                @error('clock_in_time')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="clock_out_time" class="block text-sm font-medium text-gray-700">Clock Out Time</label>
                                <input type="time" 
                                       id="clock_out_time" 
                                       name="clock_out_time" 
                                       value="{{ old('clock_out_time') }}"
                                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('clock_out_time') border-red-300 @enderror">
                                @error('clock_out_time')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Break Time (Optional) -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="break_start" class="block text-sm font-medium text-gray-700">Break Start (Optional)</label>
                                <input type="time" 
                                       id="break_start" 
                                       name="break_start" 
                                       value="{{ old('break_start') }}"
                                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>

                            <div>
                                <label for="break_end" class="block text-sm font-medium text-gray-700">Break End (Optional)</label>
                                <input type="time" 
                                       id="break_end" 
                                       name="break_end" 
                                       value="{{ old('break_end') }}"
                                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>
                        </div>

                        <!-- Hours Worked (Manual Entry) -->
                        <div>
                            <label for="hours_worked" class="block text-sm font-medium text-gray-700">
                                Total Hours Worked
                                <span class="text-xs text-gray-500">(Leave blank if using clock times above)</span>
                            </label>
                            <input type="number" 
                                   id="hours_worked" 
                                   name="hours_worked" 
                                   value="{{ old('hours_worked') }}"
                                   step="0.25" 
                                   min="0" 
                                   max="24"
                                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('hours_worked') border-red-300 @enderror"
                                   placeholder="e.g., 8.5">
                            @error('hours_worked')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Project/Client Information -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="project_name" class="block text-sm font-medium text-gray-700">Project/Client Name</label>
                                <input type="text" 
                                       id="project_name" 
                                       name="project_name" 
                                       value="{{ old('project_name') }}"
                                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('project_name') border-red-300 @enderror"
                                       placeholder="Enter project or client name">
                                @error('project_name')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="task_description" class="block text-sm font-medium text-gray-700">Task/Activity</label>
                                <input type="text" 
                                       id="task_description" 
                                       name="task_description" 
                                       value="{{ old('task_description') }}"
                                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('task_description') border-red-300 @enderror"
                                       placeholder="Brief task description">
                                @error('task_description')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Work Description -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">
                                Detailed Work Description
                                <span class="text-xs text-gray-500">(Optional)</span>
                            </label>
                            <textarea id="description" 
                                      name="description" 
                                      rows="4" 
                                      class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('description') border-red-300 @enderror"
                                      placeholder="Describe the work accomplished, any challenges faced, or additional notes...">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                            <select id="status" 
                                    name="status" 
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('status') border-red-300 @enderror">
                                <option value="draft" {{ old('status', 'draft') === 'draft' ? 'selected' : '' }}>
                                    Save as Draft
                                </option>
                                <option value="submitted" {{ old('status') === 'submitted' ? 'selected' : '' }}>
                                    Submit for Approval
                                </option>
                            </select>
                            @error('status')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-2 text-xs text-gray-500">
                                You can save as draft to edit later, or submit directly for approval.
                            </p>
                        </div>

                        <!-- Auto-calculated Hours Display -->
                        <div id="calculated-hours" class="bg-blue-50 border border-blue-200 rounded-md p-4 hidden">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-blue-800">Calculated Hours</h3>
                                    <div class="mt-1 text-sm text-blue-700">
                                        <p id="hours-calculation">Total hours will be calculated automatically</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
                        <a href="{{ route('employee.timesheets') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400">
                            Cancel
                        </a>
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                            Save Timesheet
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tips Section -->
            <div class="mt-8 bg-blue-50 border border-blue-200 rounded-md p-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">Timesheet Tips</h3>
                        <div class="mt-2 text-sm text-blue-700">
                            <ul class="list-disc pl-5 space-y-1">
                                <li>Enter either clock times OR total hours worked - not both</li>
                                <li>If you enter clock times, total hours will be calculated automatically</li>
                                <li>Break times are optional but help with accurate hour calculations</li>
                                <li>Save as draft to edit later, or submit directly for approval</li>
                                <li>Be accurate and detailed in your work descriptions</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function calculateHours() {
    const clockIn = document.getElementById('clock_in_time').value;
    const clockOut = document.getElementById('clock_out_time').value;
    const breakStart = document.getElementById('break_start').value;
    const breakEnd = document.getElementById('break_end').value;
    
    if (clockIn && clockOut) {
        const clockInTime = new Date(`2000-01-01 ${clockIn}`);
        const clockOutTime = new Date(`2000-01-01 ${clockOut}`);
        
        if (clockOutTime > clockInTime) {
            let totalMinutes = (clockOutTime - clockInTime) / (1000 * 60);
            
            // Subtract break time if provided
            if (breakStart && breakEnd) {
                const breakStartTime = new Date(`2000-01-01 ${breakStart}`);
                const breakEndTime = new Date(`2000-01-01 ${breakEnd}`);
                
                if (breakEndTime > breakStartTime) {
                    const breakMinutes = (breakEndTime - breakStartTime) / (1000 * 60);
                    totalMinutes -= breakMinutes;
                }
            }
            
            const totalHours = (totalMinutes / 60).toFixed(2);
            
            // Show calculated hours
            const calculatedHoursDiv = document.getElementById('calculated-hours');
            const hoursCalculation = document.getElementById('hours-calculation');
            
            hoursCalculation.textContent = `Based on your clock times${breakStart && breakEnd ? ' and break' : ''}, you worked ${totalHours} hours.`;
            calculatedHoursDiv.classList.remove('hidden');
            
            // Clear manual hours input
            document.getElementById('hours_worked').value = '';
        }
    } else {
        // Hide calculated hours if times are incomplete
        document.getElementById('calculated-hours').classList.add('hidden');
    }
}

// Add event listeners for time inputs
document.addEventListener('DOMContentLoaded', function() {
    ['clock_in_time', 'clock_out_time', 'break_start', 'break_end'].forEach(id => {
        document.getElementById(id).addEventListener('change', calculateHours);
    });
    
    // Clear calculated hours when manual hours is entered
    document.getElementById('hours_worked').addEventListener('input', function() {
        if (this.value) {
            document.getElementById('calculated-hours').classList.add('hidden');
            // Clear time inputs
            ['clock_in_time', 'clock_out_time', 'break_start', 'break_end'].forEach(id => {
                document.getElementById(id).value = '';
            });
        }
    });
});
</script>
@endsection
