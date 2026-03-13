<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeSchedule extends Model
{
    protected $fillable = [
        'employee_id',
        'shift_id',
        'start_date',
        'end_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(ShiftMaster::class, 'shift_id');
    }

    /** Find active schedule for a given employee and date */
    public function scopeActiveOn($query, int $employeeId, string $date)
    {
        return $query
            ->where('employee_id', $employeeId)
            ->where('status', 'active')
            ->whereDate('start_date', '<=', $date)
            ->where(fn($q) => $q->whereNull('end_date')->orWhereDate('end_date', '>=', $date));
    }
}
