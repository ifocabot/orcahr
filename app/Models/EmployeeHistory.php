<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeHistory extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'employee_id',
        'change_type',
        'old_value',
        'new_value',
        'effective_date',
        'notes',
        'changed_by',
    ];

    protected function casts(): array
    {
        return [
            'effective_date' => 'date',
            'created_at' => 'datetime',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'changed_by');
    }
}
