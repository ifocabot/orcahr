<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    protected $fillable = [
        'employee_code',
        'full_name',
        'email',
        'nik',
        'nik_hash',
        'npwp',
        'phone',
        'bank_name',
        'bank_account_number',
        'bank_account_name',
        'department_id',
        'position_id',
        'job_level_id',
        'user_id',
        'join_date',
        'resign_date',
        'employment_status',
        'gender',
        'manager_id',
    ];

    protected function casts(): array
    {
        return [
            'email' => 'encrypted',
            'nik' => 'encrypted',
            'npwp' => 'encrypted',
            'phone' => 'encrypted',
            'bank_account_number' => 'encrypted',
            'bank_account_name' => 'encrypted',
            'join_date' => 'date',
            'resign_date' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Employee $employee) {
            if ($employee->nik) {
                $employee->nik_hash = hash('sha256', $employee->nik);
            }
        });
    }

    // Core relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    public function subordinates(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Employee::class, 'manager_id');
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function jobLevel(): BelongsTo
    {
        return $this->belongsTo(JobLevel::class);
    }

    // Leave
    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function leaveBalances(): HasMany
    {
        return $this->hasMany(LeaveBalance::class);
    }

    // Payroll
    public function payrollConfigs(): HasMany
    {
        return $this->hasMany(EmployeePayrollConfig::class);
    }

    public function payrollDetails(): HasMany
    {
        return $this->hasMany(PayrollDetail::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(EmployeeHistory::class)->orderByDesc('effective_date')->orderByDesc('created_at');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('employment_status', 'active');
    }

    public function scopeSearch($query, ?string $search)
    {
        if (!$search) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('full_name', 'like', "%{$search}%")
                ->orWhere('employee_code', 'like', "%{$search}%");
        });
    }
}
