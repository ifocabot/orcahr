<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeePayrollConfig extends Model
{
    protected $fillable = [
        'employee_id',
        'component_id',
        'amount',
        'effective_date',
        'end_date',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'effective_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function component(): BelongsTo
    {
        return $this->belongsTo(PayrollComponent::class, 'component_id');
    }

    /** Active configs: effective_date <= today AND (end_date IS NULL OR end_date >= today) */
    public function scopeActive($query)
    {
        return $query->where('effective_date', '<=', now()->toDateString())
            ->where(fn($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', now()->toDateString()));
    }
}
