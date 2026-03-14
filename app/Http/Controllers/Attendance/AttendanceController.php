<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessAttendanceBatch;
use App\Models\AttendanceSummary;
use App\Models\Employee;
use App\Models\RawTimeEvent;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AttendanceController extends Controller
{
    public function clockInOut(): Response
    {
        $employee = Employee::where('user_id', auth()->id())->first();
        $todayData = null;

        if ($employee) {
            $todayData = $this->getTodayData($employee->id);
        }

        return Inertia::render('Attendance/ClockInOut', [
            'employee' => $employee,
            'today' => $todayData,
            'officeLat' => SystemSetting::getOption('attendance.office_latitude', -6.2),
            'officeLng' => SystemSetting::getOption('attendance.office_longitude', 106.8),
            'radiusMeters' => SystemSetting::getOption('attendance.radius_meters', 100),
        ]);
    }

    public function clock(Request $request)
    {
        $employee = Employee::where('user_id', auth()->id())->firstOrFail();

        $data = $request->validate([
            'event_type' => 'required|in:IN,OUT',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'selfie' => 'required|image|max:5120',
        ]);

        // TC-04: Prevent duplicate clock-in/out within 1 minute
        $recentDuplicate = RawTimeEvent::where('employee_id', $employee->id)
            ->where('event_type', $data['event_type'])
            ->where('event_time', '>=', now()->subMinute())
            ->exists();

        if ($recentDuplicate) {
            return back()->withErrors(['event_type' => 'Anda sudah melakukan clock ' . strtolower($data['event_type']) . ' baru saja. Tunggu 1 menit.']);
        }

        // Store selfie
        $selfiePath = $request->file('selfie')->store('selfies/' . now()->format('Y/m'), 'local');

        RawTimeEvent::create([
            'employee_id' => $employee->id,
            'event_time' => now(),
            'event_type' => $data['event_type'],
            'source' => 'web',
            'processed_flag' => false,
            'selfie_path' => $selfiePath,
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'metadata' => [
                'user_agent' => $request->userAgent(),
            ],
        ]);

        // Dispatch real-time calculation (don't wait for cron)
        ProcessAttendanceBatch::dispatch($employee->id, today()->toDateString());

        $label = $data['event_type'] === 'IN' ? 'masuk' : 'pulang';

        return back()->with('success', "Absensi {$label} berhasil dicatat pada " . now()->format('H:i') . '.');
    }

    public function today(): \Illuminate\Http\JsonResponse
    {
        $employee = Employee::where('user_id', auth()->id())->firstOrFail();
        return response()->json($this->getTodayData($employee->id));
    }

    private function getTodayData(int $employeeId): array
    {
        $events = RawTimeEvent::where('employee_id', $employeeId)
            ->whereDate('event_time', today())
            ->orderBy('event_time')
            ->get(['event_type', 'event_time']);

        $summary = AttendanceSummary::where('employee_id', $employeeId)
            ->where('work_date', today())
            ->first();

        return [
            'clock_in' => $events->where('event_type', 'IN')->first()?->event_time,
            'clock_out' => $events->where('event_type', 'OUT')->last()?->event_time,
            'summary' => $summary,
        ];
    }
}
