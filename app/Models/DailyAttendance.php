<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyAttendance extends Model
{
    use HasUlids, Auditable;

    protected $fillable = [
        'employee_id',
        'date',
        'schedule_id',
        'clock_in',
        'clock_out',
        'status',
        'late_minutes',
        'early_leave_minutes',
        'overtime_minutes',
        'work_hours',
        'source_log_ids',
        'calculated_at',
        'calculation_trigger',
    ];

    protected $casts = [
        'date' => 'date',
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
        'source_log_ids' => 'array',
        'calculated_at' => 'datetime',
    ];

    public static array $statusLabels = [
        'present' => 'Hadir',
        'absent' => 'Tidak Hadir',
        'late' => 'Terlambat',
        'early_leave' => 'Pulang Awal',
        'leave' => 'Cuti',
        'holiday' => 'Hari Libur',
        'weekend' => 'Akhir Pekan',
    ];

    public static array $statusColors = [
        'present' => 'badge-green',
        'absent' => 'badge-red',
        'late' => 'badge-yellow',
        'early_leave' => 'badge-orange',
        'leave' => 'badge-blue',
        'holiday' => 'badge-gray',
        'weekend' => 'badge-gray',
    ];

    public function statusLabel(): string
    {
        return self::$statusLabels[$this->status] ?? ucfirst($this->status);
    }

    public function statusColor(): string
    {
        return self::$statusColors[$this->status] ?? 'badge-gray';
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(ScheduleAssignment::class, 'schedule_id');
    }
}
