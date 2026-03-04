<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends Model
{
    use HasUlids, Auditable;

    protected $fillable = [
        'name',
        'code',
        'clock_in',
        'clock_out',
        'break_start',
        'break_end',
        'is_flexible',
        'late_tolerance_minutes',
        'early_leave_tolerance_minutes',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_flexible' => 'boolean',
        'is_active' => 'boolean',
    ];

    // ─── Relations ────────────────────────────────────────────────────────────

    public function schedules(): HasMany
    {
        return $this->hasMany(ScheduleAssignment::class);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    /** Jumlah jam kerja efektif (exclude break) */
    public function workingHours(): float
    {
        $clockIn = \Carbon\Carbon::parse($this->clock_in);
        $clockOut = \Carbon\Carbon::parse($this->clock_out);
        $total = $clockIn->diffInMinutes($clockOut);

        if ($this->break_start && $this->break_end) {
            $breakStart = \Carbon\Carbon::parse($this->break_start);
            $breakEnd = \Carbon\Carbon::parse($this->break_end);
            $total -= $breakStart->diffInMinutes($breakEnd);
        }

        return round($total / 60, 2);
    }

    public function formattedHours(): string
    {
        return substr($this->clock_in, 0, 5) . ' – ' . substr($this->clock_out, 0, 5);
    }
}
