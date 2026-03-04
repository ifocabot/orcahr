<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Employment extends Model
{
    use HasUlids, Auditable;

    protected $fillable = [
        'employee_id',
        'department_id',
        'position_id',
        'job_level_id',
        'employment_status',
        'join_date',
        'end_date',
        'effective_from',
        'effective_to',
    ];

    protected $casts = [
        'join_date' => 'date',
        'end_date' => 'date',
        'effective_from' => 'date',
        'effective_to' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function jobLevel(): BelongsTo
    {
        return $this->belongsTo(JobLevel::class);
    }
}
