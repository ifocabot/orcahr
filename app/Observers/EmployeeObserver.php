<?php

namespace App\Observers;

use App\Models\Employee;
use App\Models\EmployeeHistory;

class EmployeeObserver
{
    private array $watchedFields = [
        'department_id' => 'department',
        'position_id' => 'position',
        'job_level_id' => 'job_level',
        'employment_status' => 'status',
        'manager_id' => 'manager',
    ];

    public function updating(Employee $employee): void
    {
        $changedBy = auth()->id();
        if (!$changedBy)
            return;

        foreach ($this->watchedFields as $field => $changeType) {
            if ($employee->isDirty($field)) {
                $old = $employee->getOriginal($field);
                $new = $employee->getAttribute($field);

                EmployeeHistory::create([
                    'employee_id' => $employee->id,
                    'change_type' => $changeType,
                    'old_value' => $old !== null ? (string) $old : null,
                    'new_value' => $new !== null ? (string) $new : null,
                    'effective_date' => now()->toDateString(),
                    'changed_by' => $changedBy,
                ]);
            }
        }
    }
}
