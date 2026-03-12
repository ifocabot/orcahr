<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
