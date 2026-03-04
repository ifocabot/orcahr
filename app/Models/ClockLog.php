<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClockLog extends Model
{
    use HasUlids, Auditable;

    protected $fillable = [
        'employee_id',
        'timestamp',
        'type',
        'source',
        'ip_address',
        'location',
        'photo',
        'is_manual',
        'manual_reason',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'location' => 'array',
        'is_manual' => 'boolean',
    ];

    // ─── Relations ────────────────────────────────────────────────────────────

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public function isClockIn(): bool
    {
        return $this->type === 'clock_in';
    }

    public function isClockOut(): bool
    {
        return $this->type === 'clock_out';
    }
}
