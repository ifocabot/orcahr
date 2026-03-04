<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\ScheduleAssignment;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class ScheduleService
{
    /**
     * Assign shift ke karyawan.
     * Jika sudah ada schedule aktif, tutup (set effective_to = effective_from - 1 hari).
     */
    public function assign(Employee $employee, Shift $shift, string $effectiveFrom, ?string $notes = null): ScheduleAssignment
    {
        $from = Carbon::parse($effectiveFrom);

        // Tutup schedule aktif yang ada
        $this->closeActiveSchedule($employee, $from->copy()->subDay());

        return ScheduleAssignment::create([
            'employee_id' => $employee->id,
            'shift_id' => $shift->id,
            'effective_from' => $from,
            'effective_to' => null,
            'type' => 'individual',
            'notes' => $notes,
        ]);
    }

    /**
     * Tutup schedule aktif karyawan (set effective_to).
     */
    public function closeActiveSchedule(Employee $employee, Carbon $closedAt): void
    {
        ScheduleAssignment::where('employee_id', $employee->id)
            ->whereNull('effective_to')
            ->update(['effective_to' => $closedAt]);
    }

    /**
     * Hapus assignment (hanya yang belum aktif / future).
     */
    public function delete(ScheduleAssignment $assignment): void
    {
        if ($assignment->isActive()) {
            throw ValidationException::withMessages([
                'schedule' => 'Schedule aktif tidak bisa langsung dihapus. Assign shift baru untuk menggantikannya.',
            ]);
        }

        $assignment->delete();
    }

    /**
     * Ambil shift yang berlaku untuk karyawan pada tanggal tertentu.
     */
    public function getShiftOnDate(Employee $employee, Carbon $date): ?Shift
    {
        $schedule = ScheduleAssignment::where('employee_id', $employee->id)
            ->where('effective_from', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('effective_to')
                    ->orWhere('effective_to', '>=', $date);
            })
            ->with('shift')
            ->latest('effective_from')
            ->first();

        return $schedule?->shift;
    }
}
