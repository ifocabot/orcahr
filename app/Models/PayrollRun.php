<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollRun extends Model
{
    protected $fillable = [
        'period_month',
        'period_year',
        'status',
        'total_gross',
        'total_deductions',
        'total_net',
        'calculated_by',
        'calculated_at',
        'approved_by',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'total_gross' => 'decimal:2',
            'total_deductions' => 'decimal:2',
            'total_net' => 'decimal:2',
            'calculated_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    public function details(): HasMany
    {
        return $this->hasMany(PayrollDetail::class, 'payroll_run_id');
    }

    public function calculatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'calculated_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /** Human-readable period label e.g. "Maret 2026" */
    public function getPeriodLabelAttribute(): string
    {
        $months = [
            '',
            'Januari',
            'Februari',
            'Maret',
            'April',
            'Mei',
            'Juni',
            'Juli',
            'Agustus',
            'September',
            'Oktober',
            'November',
            'Desember'
        ];
        return $months[$this->period_month] . ' ' . $this->period_year;
    }
}
