<?php

namespace App\Services;

use App\Models\ClockLog;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class ClockService
{
    public function __construct(private AttendanceEngine $engine)
    {
    }

    /**
     * Clock in untuk karyawan.
     */
    public function clockIn(Employee $employee, array $options = []): ClockLog
    {
        if ($this->hasClockInToday($employee)) {
            throw ValidationException::withMessages([
                'clock' => 'Anda sudah melakukan clock in hari ini.',
            ]);
        }

        return ClockLog::create([
            'employee_id' => $employee->id,
            'timestamp' => now(),
            'type' => 'clock_in',
            'source' => $options['source'] ?? 'web',
            'ip_address' => $options['ip_address'] ?? null,
            'location' => $options['location'] ?? null,
            'is_manual' => false,
        ]);
    }

    /**
     * Clock out untuk karyawan — auto-trigger attendance calculation.
     */
    public function clockOut(Employee $employee, array $options = []): ClockLog
    {
        if (!$this->hasClockInToday($employee)) {
            throw ValidationException::withMessages([
                'clock' => 'Anda belum melakukan clock in hari ini.',
            ]);
        }

        if ($this->hasClockOutToday($employee)) {
            throw ValidationException::withMessages([
                'clock' => 'Anda sudah melakukan clock out hari ini.',
            ]);
        }

        $log = ClockLog::create([
            'employee_id' => $employee->id,
            'timestamp' => now(),
            'type' => 'clock_out',
            'source' => $options['source'] ?? 'web',
            'ip_address' => $options['ip_address'] ?? null,
            'location' => $options['location'] ?? null,
            'is_manual' => false,
        ]);

        // Auto-kalkulasi attendance setelah clock out
        $this->engine->calculate($employee, Carbon::today(), 'clock_out');

        return $log;
    }

    /**
     * Cek apakah karyawan sudah clock in hari ini.
     */
    public function hasClockInToday(Employee $employee): bool
    {
        return ClockLog::where('employee_id', $employee->id)
            ->where('type', 'clock_in')
            ->whereDate('timestamp', today())
            ->exists();
    }

    /**
     * Cek apakah karyawan sudah clock out hari ini.
     */
    public function hasClockOutToday(Employee $employee): bool
    {
        return ClockLog::where('employee_id', $employee->id)
            ->where('type', 'clock_out')
            ->whereDate('timestamp', today())
            ->exists();
    }

    /**
     * Ambil clock in hari ini.
     */
    public function getClockInToday(Employee $employee): ?ClockLog
    {
        return ClockLog::where('employee_id', $employee->id)
            ->where('type', 'clock_in')
            ->whereDate('timestamp', today())
            ->first();
    }

    /**
     * Ambil clock out hari ini.
     */
    public function getClockOutToday(Employee $employee): ?ClockLog
    {
        return ClockLog::where('employee_id', $employee->id)
            ->where('type', 'clock_out')
            ->whereDate('timestamp', today())
            ->first();
    }

    /**
     * Ambil status kehadiran karyawan hari ini.
     * Returns: 'not_started' | 'clocked_in' | 'clocked_out'
     */
    public function getTodayStatus(Employee $employee): string
    {
        if ($this->hasClockOutToday($employee))
            return 'clocked_out';
        if ($this->hasClockInToday($employee))
            return 'clocked_in';
        return 'not_started';
    }
}
