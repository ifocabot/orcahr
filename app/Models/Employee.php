<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\Encryptable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasUlids, SoftDeletes, Auditable, Encryptable;

    protected $fillable = [
        'user_id',
        'employee_number',
        'full_name',
        'email',
        'birth_date',
        'gender',
        'marital_status',
        'blood_type',
        'religion',
        'photo',
    ];

    /** Fields yang akan di-encrypt otomatis oleh Encryptable trait */
    protected array $encryptable = [
        'personal_email',
        'phone',
        'nik',
        'npwp',
        'birth_place',
        'address',
    ];

    /** Audit: jangan log nilai encrypted (sudah di-handle Auditable) */
    protected array $auditMasked = [
        'nik_encrypted',
        'npwp_encrypted',
        'phone_encrypted',
        'address_encrypted',
        'personal_email_encrypted',
        'birth_place_encrypted',
    ];

    protected $casts = [
        'birth_date' => 'date',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function employments(): HasMany
    {
        return $this->hasMany(Employment::class);
    }

    /** Employment yang aktif saat ini (effective_to = null) */
    public function currentEmployment(): HasOne
    {
        return $this->hasOne(Employment::class)->whereNull('effective_to')->latest('effective_from');
    }

    public function bankAccounts(): HasMany
    {
        return $this->hasMany(BankAccount::class);
    }

    public function primaryBank(): HasOne
    {
        return $this->hasOne(BankAccount::class)->where('is_primary', true);
    }

    public function bpjs(): HasOne
    {
        return $this->hasOne(EmployeeBpjs::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class)->latest();
    }

    public function currentSchedule(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ScheduleAssignment::class)
            ->whereNull('effective_to')
            ->latest('effective_from');
    }

    public function scheduleHistory(): HasMany
    {
        return $this->hasMany(ScheduleAssignment::class)->latest('effective_from');
    }

    public function clockLogs(): HasMany
    {
        return $this->hasMany(ClockLog::class)->latest('timestamp');
    }

    public function dailyAttendances(): HasMany
    {
        return $this->hasMany(DailyAttendance::class)->latest('date');
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class)->latest();
    }
}

