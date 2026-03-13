<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'balance_date',
        'opening_balance',
        'accrued',
        'used',
        'adjustment',
        'closing_balance',
        'entitlement_year',
        'expiry_date',
    ];

    protected $casts = [
        'balance_date' => 'date',
        'expiry_date' => 'date',
        'opening_balance' => 'decimal:2',
        'accrued' => 'decimal:2',
        'used' => 'decimal:2',
        'adjustment' => 'decimal:2',
        'closing_balance' => 'decimal:2',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function scopeForYear($query, int $year)
    {
        return $query->where('entitlement_year', $year);
    }

    public function scopeLatestPerType($query)
    {
        return $query->orderBy('balance_date', 'desc');
    }
}
