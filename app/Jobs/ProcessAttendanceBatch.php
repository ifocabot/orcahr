<?php

namespace App\Jobs;

use App\Models\AttendanceException;
use App\Models\AttendanceSummary;
use App\Models\EmployeeSchedule;
use App\Models\RawTimeEvent;
use App\Models\SystemSetting;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessAttendanceBatch implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly ?int $employeeId = null,
        private readonly ?string $workDate = null,
    ) {
    }

    public function handle(): void
    {
        // When called for a specific employee+date (e.g. after clock-in),
        // process that precise day, considering overnight shifts.
        if ($this->employeeId && $this->workDate) {
            $this->processSingleDay($this->employeeId, $this->workDate);
            return;
        }

        // Batch mode: group all unprocessed IN events by employee+date
        $query = RawTimeEvent::unprocessed()
            ->where('event_type', 'IN')  // anchor by IN events
            ->orderBy('event_time');

        if ($this->employeeId) {
            $query->where('employee_id', $this->employeeId);
        }

        $grouped = $query->get()->groupBy([
            'employee_id',
            fn($e) => $e->event_time->toDateString(),
        ]);

        foreach ($grouped as $employeeId => $dateGroups) {
            foreach ($dateGroups as $workDate => $events) {
                $this->processSingleDay((int) $employeeId, $workDate);
            }
        }
    }

    private function processSingleDay(int $employeeId, string $workDate): void
    {
        $schedule = EmployeeSchedule::activeOn($employeeId, $workDate)->with('shift')->first();

        if (!$schedule) {
            return;
        }

        $shift = $schedule->shift;

        // For overnight shifts, OUT events appear on the next calendar day
        $nextDay = Carbon::parse($workDate)->addDay()->toDateString();

        $events = RawTimeEvent::where('employee_id', $employeeId)
            ->where(function ($q) use ($workDate, $shift, $nextDay) {
                // Events on the work date itself (includes both IN and OUT for normal shifts)
                $q->whereDate('event_time', $workDate);
                // For overnight shifts, also grab any OUT events from the next calendar day
                if ($shift->is_overnight) {
                    $q->orWhere(function ($q2) use ($nextDay) {
                        $q2->whereDate('event_time', $nextDay)
                            ->where('event_type', 'OUT');
                    });
                }
            })
            ->orderBy('event_time')
            ->get();

        $clockIn = $events->where('event_type', 'IN')->first();
        $clockOut = $events->where('event_type', 'OUT')->last();

        // Late minutes
        $lateMinutes = 0;
        if ($clockIn) {
            $shiftStart = Carbon::parse($workDate . ' ' . $shift->start_time);
            $diffMinutes = (int) $shiftStart->diffInMinutes($clockIn->event_time, false);
            $tolerance = SystemSetting::getOption('attendance.late_tolerance_minutes', 0);

            $lateMinutes = $diffMinutes > $tolerance ? $diffMinutes : 0;
        }

        // Overtime & shift end
        $overtimeMinutes = 0;
        if ($clockOut) {
            $shiftEnd = Carbon::parse($workDate . ' ' . $shift->end_time);
            if ($shift->is_overnight && $shiftEnd->lte(Carbon::parse($workDate . ' ' . $shift->start_time))) {
                $shiftEnd->addDay();
            }
            $rawOT = max(0, (int) $shiftEnd->diffInMinutes($clockOut->event_time, false));
            $overtimeMinutes = $rawOT >= $shift->overtime_threshold_minutes ? $rawOT : 0;
        }

        // Work duration
        $workDuration = ($clockIn && $clockOut)
            ? max(0, $clockIn->event_time->diffInMinutes($clockOut->event_time) - $shift->break_minutes)
            : 0;

        // Determine status
        $status = match (true) {
            !$clockIn && !$clockOut => 'absent',
            $lateMinutes > 0 => 'late',
            default => 'present',
        };

        // Override with approved exception if any
        $exception = AttendanceException::where('employee_id', $employeeId)
            ->where('work_date', $workDate)
            ->where('approval_status', 'approved')
            ->first();

        if ($exception) {
            $status = match ($exception->exception_type) {
                'half_day_permit' => 'half_permit',
                default => $exception->exception_type,
            };
        }

        AttendanceSummary::updateOrCreate(
            ['employee_id' => $employeeId, 'work_date' => $workDate],
            [
                'shift_id' => $shift->id,
                'actual_in' => $clockIn?->event_time,
                'actual_out' => $clockOut?->event_time,
                'late_minutes' => $lateMinutes,
                'overtime_minutes' => $overtimeMinutes,
                'work_duration_minutes' => $workDuration,
                'status' => $status,
                'dirty_flag' => false,
                'calculated_at' => now(),
            ],
        );

        RawTimeEvent::whereIn('id', $events->pluck('id'))->update(['processed_flag' => true]);
    }
}
