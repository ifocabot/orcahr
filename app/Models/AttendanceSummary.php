<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceSummary extends Model
{
    protected $fillable = [
        'employee_id',
        'work_date',
        'shift_id',
        'actual_in',
        'actual_out',
        'late_minutes',
        'early_leave_minutes',
        'overtime_minutes',
        'work_duration_minutes',
        'status',
        'dirty_flag',
        'calculated_at',
    ];

    protected function casts(): array
    {
        return [
            'work_date' => 'date',
            'actual_in' => 'datetime',
            'actual_out' => 'datetime',
            'dirty_flag' => 'boolean',
            'calculated_at' => 'datetime',
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

    public function scopeDirty($query)
    {
        return $query->where('dirty_flag', true);
    }
}
