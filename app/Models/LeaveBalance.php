<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveBalance extends Model
{
    use HasUlids, Auditable;

    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'year',
        'total_quota',
        'used',
        'pending',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function remaining(): int
    {
        return $this->total_quota - $this->used - $this->pending;
    }

    public function usedPercentage(): float
    {
        if ($this->total_quota === 0)
            return 0;
        return round(($this->used / $this->total_quota) * 100, 1);
    }
}
