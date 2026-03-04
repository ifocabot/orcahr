<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OvertimeRequest extends Model
{
    use HasUlids, Auditable;

    protected $fillable = [
        'employee_id',
        'date',
        'planned_start',
        'planned_end',
        'actual_minutes',
        'reason',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    protected $casts = [
        'date' => 'date',
        'approved_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function plannedDurationMinutes(): int
    {
        $start = \Carbon\Carbon::parse($this->planned_start);
        $end = \Carbon\Carbon::parse($this->planned_end);
        return (int) $start->diffInMinutes($end);
    }
}
