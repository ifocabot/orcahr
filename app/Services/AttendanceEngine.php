<?php

namespace App\Services;

use App\Models\DailyAttendance;
use App\Models\Employee;
use App\Models\Holiday;
use App\Models\ScheduleAssignment;
use Carbon\Carbon;

class AttendanceEngine
{
    /**
     * Kalkulasi / recalculate attendance harian untuk satu karyawan.
     */
    public function calculate(Employee $employee, Carbon $date, string $trigger = 'manual'): DailyAttendance
    {
        // Cek akhir pekan
        if ($date->isWeekend()) {
            return $this->upsert($employee, $date, [
                'status' => 'weekend',
                'calculated_at' => now(),
                'calculation_trigger' => $trigger,
            ]);
        }

        // Cek hari libur
        if (Holiday::isHoliday($date)) {
            return $this->upsert($employee, $date, [
                'status' => 'holiday',
                'calculated_at' => now(),
                'calculation_trigger' => $trigger,
            ]);
        }

        // Ambil schedule aktif untuk tanggal ini
        $schedule = ScheduleAssignment::where('employee_id', $employee->id)
            ->where('effective_from', '<=', $date->toDateString())
            ->where(fn($q) => $q->whereNull('effective_to')->orWhere('effective_to', '>=', $date->toDateString()))
            ->with('shift')
            ->latest('effective_from')
            ->first();

        // Ambil clock logs hari itu
        $logs = $employee->clockLogs()
            ->whereDate('timestamp', $date)
            ->orderBy('timestamp')
            ->get();

        $clockIn = $logs->where('type', 'clock_in')->first();
        $clockOut = $logs->where('type', 'clock_out')->last();

        if (!$clockIn) {
            // Tidak ada clock in = absent
            return $this->upsert($employee, $date, [
                'schedule_id' => $schedule?->id,
                'status' => 'absent',
                'calculated_at' => now(),
                'calculation_trigger' => $trigger,
            ]);
        }

        $shift = $schedule?->shift;
        $clockInTime = Carbon::parse($clockIn->timestamp);
        $clockOutTime = $clockOut ? Carbon::parse($clockOut->timestamp) : null;

        $lateMinutes = 0;
        $earlyLeaveMinutes = 0;
        $workHours = 0;
        $overtimeMinutes = 0;

        if ($shift && !$shift->is_flexible) {
            $shiftStart = Carbon::parse($date->toDateString() . ' ' . $shift->clock_in);
            $shiftEnd = Carbon::parse($date->toDateString() . ' ' . $shift->clock_out);

            // Kalkulasi keterlambatan
            if ($clockInTime->gt($shiftStart)) {
                $lateMinutes = max(0, (int) $shiftStart->diffInMinutes($clockInTime) - $shift->late_tolerance_minutes);
            }

            // Kalkulasi pulang awal
            if ($clockOutTime && $clockOutTime->lt($shiftEnd)) {
                $earlyLeaveMinutes = max(0, (int) $clockOutTime->diffInMinutes($shiftEnd) - $shift->early_leave_tolerance_minutes);
            }

            // Overtime: clock_out > shift_end
            if ($clockOutTime && $clockOutTime->gt($shiftEnd)) {
                $overtimeMinutes = (int) $shiftEnd->diffInMinutes($clockOutTime);
            }
        }

        // Work hours
        if ($clockOutTime) {
            $workMinutes = (int) $clockInTime->diffInMinutes($clockOutTime);
            // Kurangi break time jika ada
            if ($shift?->break_start && $shift?->break_end) {
                $breakStart = Carbon::parse($date->toDateString() . ' ' . $shift->break_start);
                $breakEnd = Carbon::parse($date->toDateString() . ' ' . $shift->break_end);
                $workMinutes -= (int) $breakStart->diffInMinutes($breakEnd);
            }
            $workHours = round(max(0, $workMinutes) / 60, 2);
        }

        // Tentukan status
        $status = 'present';
        if ($lateMinutes > 0 && $earlyLeaveMinutes > 0) {
            $status = 'late'; // prioritaskan late jika keduanya ada
        } elseif ($lateMinutes > 0) {
            $status = 'late';
        } elseif ($earlyLeaveMinutes > 0) {
            $status = 'early_leave';
        }

        return $this->upsert($employee, $date, [
            'schedule_id' => $schedule?->id,
            'clock_in' => $clockIn->timestamp,
            'clock_out' => $clockOut?->timestamp,
            'status' => $status,
            'late_minutes' => $lateMinutes,
            'early_leave_minutes' => $earlyLeaveMinutes,
            'overtime_minutes' => $overtimeMinutes,
            'work_hours' => $workHours,
            'source_log_ids' => $logs->pluck('id')->all(),
            'calculated_at' => now(),
            'calculation_trigger' => $trigger,
        ]);
    }

    private function upsert(Employee $employee, Carbon $date, array $data): DailyAttendance
    {
        return DailyAttendance::updateOrCreate(
            ['employee_id' => $employee->id, 'date' => $date->toDateString()],
            $data
        );
    }
}
