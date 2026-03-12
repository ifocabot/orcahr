<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_code' => 'required|string|max:20|unique:employees,employee_code',
            'full_name' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'nik' => 'nullable|string|max:20',
            'npwp' => 'nullable|string|max:30',
            'phone' => 'nullable|string|max:20',
            'bank_name' => 'nullable|string|max:50',
            'bank_account_number' => 'nullable|string|max:30',
            'bank_account_name' => 'nullable|string|max:100',
            'department_id' => 'nullable|exists:departments,id',
            'position_id' => 'nullable|exists:positions,id',
            'job_level_id' => 'nullable|exists:job_levels,id',
            'join_date' => 'required|date',
            'resign_date' => 'nullable|date|after:join_date',
            'employment_status' => 'required|in:active,probation,resigned,terminated',
            'gender' => 'nullable|in:male,female',
        ];
    }
}
