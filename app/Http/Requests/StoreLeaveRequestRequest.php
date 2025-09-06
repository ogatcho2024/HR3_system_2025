<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeaveRequestRequest extends FormRequest
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
            'leave_type' => 'required|string|in:sick,vacation,personal,maternity,paternity,emergency,bereavement',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:1000',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     */
    public function attributes(): array
    {
        return [
            'leave_type' => 'leave type',
            'start_date' => 'start date',
            'end_date' => 'end date',
            'reason' => 'reason',
            'attachment' => 'attachment',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'start_date.after_or_equal' => 'The start date must be today or a future date.',
            'end_date.after_or_equal' => 'The end date must be the same as or after the start date.',
            'leave_type.in' => 'Please select a valid leave type.',
            'attachment.max' => 'The attachment may not be greater than 2MB.',
        ];
    }
}
