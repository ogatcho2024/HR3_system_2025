<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTimesheetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'work_date' => 'required|date|before_or_equal:today',
            'clock_in_time' => 'nullable|date_format:H:i',
            'clock_out_time' => 'nullable|date_format:H:i|after:clock_in_time',
            'break_start' => 'nullable|date_format:H:i|after:clock_in_time',
            'break_end' => 'nullable|date_format:H:i|after:break_start|before:clock_out_time',
            'hours_worked' => 'nullable|numeric|min:0.25|max:24',
            'project_name' => 'nullable|string|max:255',
            'task_description' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|in:draft,submitted',
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     */
    public function attributes(): array
    {
        return [
            'work_date' => 'work date',
            'clock_in_time' => 'clock in time',
            'clock_out_time' => 'clock out time',
            'break_start' => 'break start time',
            'break_end' => 'break end time',
            'hours_worked' => 'hours worked',
            'project_name' => 'project name',
            'task_description' => 'task description',
            'description' => 'work description',
            'status' => 'status',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'work_date.before_or_equal' => 'The work date cannot be in the future.',
            'clock_out_time.after' => 'Clock out time must be after clock in time.',
            'break_start.after' => 'Break start time must be after clock in time.',
            'break_end.after' => 'Break end time must be after break start time.',
            'break_end.before' => 'Break end time must be before clock out time.',
        ];
    }
}
