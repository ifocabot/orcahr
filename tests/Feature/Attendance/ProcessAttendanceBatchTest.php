<?php

use App\Jobs\ProcessAttendanceBatch;
use App\Models\AttendanceSummary;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeSchedule;
use App\Models\RawTimeEvent;
use App\Models\ShiftMaster;
use Carbon\Carbon;

function mkFix(string $start, string $end, string $workDate, bool $overnight = false): array
{
    $s = bin2hex(random_bytes(2));
    $shift = ShiftMaster::create(['name' => "S{$s}", 'start_time' => $start, 'end_time' => $end, 'is_overnight' => $overnight, 'break_minutes' => 60, 'overtime_threshold_minutes' => 30, 'is_active' => true]);
    $dept = Department::create(['name' => "D{$s}", 'code' => "D{$s}", 'is_active' => true]);
    $emp = Employee::create(['employee_code' => "E{$s}", 'full_name' => "Emp", 'email' => "{$s}@t.com", 'join_date' => '2024-01-01', 'employment_status' => 'active', 'department_id' => $dept->id]);
    EmployeeSchedule::create(['employee_id' => $emp->id, 'shift_id' => $shift->id, 'start_date' => $workDate, 'end_date' => null, 'status' => 'active']);
    return [$emp, $shift];
}

function mkEv(Employee $emp, string $dt, string $type): RawTimeEvent
{
    return RawTimeEvent::create(['employee_id' => $emp->id, 'event_time' => Carbon::parse($dt), 'event_type' => $type, 'source' => 'web', 'processed_flag' => false, 'selfie_path' => null, 'latitude' => -6.2, 'longitude' => 106.8]);
}

function getSummary(int $employeeId, string $workDate): AttendanceSummary
{
    return AttendanceSummary::where('employee_id', $employeeId)
        ->whereDate('work_date', $workDate)
        ->firstOrFail();
}

test('calculates late minutes correctly for a day shift', function () {
    $workDate = '2026-01-10';
    [$emp] = mkFix('08:00:00', '17:00:00', $workDate);
    mkEv($emp, '2026-01-10 08:10:00', 'IN');
    mkEv($emp, '2026-01-10 17:00:00', 'OUT');
    ProcessAttendanceBatch::dispatchSync($emp->id, $workDate);
    $s = getSummary($emp->id, $workDate);
    expect($s->late_minutes)->toBe(10)->and($s->status)->toBe('late')->and($s->dirty_flag)->toBeFalse();
});

test('calculates zero late minutes when employee arrives early', function () {
    $workDate = '2026-01-11';
    [$emp] = mkFix('09:00:00', '18:00:00', $workDate);
    mkEv($emp, '2026-01-11 08:55:00', 'IN');
    mkEv($emp, '2026-01-11 18:00:00', 'OUT');
    ProcessAttendanceBatch::dispatchSync($emp->id, $workDate);
    $s = getSummary($emp->id, $workDate);
    expect($s->late_minutes)->toBe(0)->and($s->status)->toBe('present');
});

test('handles overnight shift: OUT event on next calendar day', function () {
    $workDate = '2026-01-12';
    [$emp, $shift] = mkFix('22:00:00', '06:00:00', $workDate, overnight: true);

    $inEv = mkEv($emp, '2026-01-12 22:05:00', 'IN');
    $outEv = mkEv($emp, '2026-01-13 06:00:00', 'OUT');

    // Manually verify what the job would fetch for overnight
    $nextDay = Carbon::parse($workDate)->addDay()->toDateString(); // '2026-01-13'
    $events = RawTimeEvent::where('employee_id', $emp->id)
        ->where(function ($q) use ($workDate, $shift, $nextDay) {
            $q->whereDate('event_time', $workDate);
            if ($shift->is_overnight) {
                $q->orWhere(function ($q2) use ($nextDay) {
                    $q2->whereDate('event_time', $nextDay)->where('event_type', 'OUT');
                });
            }
        })
        ->orderBy('event_time')
        ->get();

    file_put_contents(
        storage_path('logs/overnight.txt'),
        "event_count=" . $events->count() .
        ", in_id={$inEv->id}, out_id={$outEv->id}" .
        ", fetched_ids=" . $events->pluck('id')->implode(',') .
        ", out_type=" . ($events->where('event_type', 'OUT')->first()?->event_type ?? 'NULL')
    );

    ProcessAttendanceBatch::dispatchSync($emp->id, $workDate);
    $s = getSummary($emp->id, $workDate);
    expect($s->late_minutes)->toBe(5);
    expect($s->work_duration_minutes)->toBe(415);
});

test('marks raw time events as processed after batch run', function () {
    $workDate = '2026-01-13';
    [$emp] = mkFix('08:00:00', '17:00:00', $workDate);
    $inEvent = mkEv($emp, '2026-01-13 08:00:00', 'IN');
    $outEvent = mkEv($emp, '2026-01-13 17:00:00', 'OUT');
    ProcessAttendanceBatch::dispatchSync($emp->id, $workDate);
    expect($inEvent->fresh()->processed_flag)->toBeTrue();
    expect($outEvent->fresh()->processed_flag)->toBeTrue();
});
