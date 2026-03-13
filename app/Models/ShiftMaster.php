<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShiftMaster extends Model
{
    protected $fillable = [
        'name',
        'start_time',
        'end_time',
        'is_overnight',
        'break_minutes',
        'overtime_threshold_minutes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_overnight' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(EmployeeSchedule::class, 'shift_id');
    }

    public function summaries(): HasMany
    {
        return $this->hasMany(AttendanceSummary::class, 'shift_id');
    }

    /** Total shift hours minus break */
    public function getTotalHoursAttribute(): float
    {
        [$startH, $startM] = explode(':', $this->start_time);
        [$endH, $endM] = explode(':', $this->end_time);

        $startMinutes = (int) $startH * 60 + (int) $startM;
        $endMinutes = (int) $endH * 60 + (int) $endM;

        if ($this->is_overnight && $endMinutes <= $startMinutes) {
            $endMinutes += 1440;
        }

        return ($endMinutes - $startMinutes - $this->break_minutes) / 60;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
