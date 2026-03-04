<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Services\ClockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ClockController extends Controller
{
    public function __construct(private ClockService $service)
    {
    }

    /**
     * Halaman absensi hari ini.
     * Menampilkan status clock in/out + shift aktif.
     */
    public function index(): View
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Ambil employee yang terasosiasi dengan user yang login
        $employee = Employee::where('user_id', $user->id)->first();

        $clockIn = null;
        $clockOut = null;
        $status = 'no_employee';
        $todayShift = null;

        if ($employee) {
            $status = $this->service->getTodayStatus($employee);
            $clockIn = $this->service->getClockInToday($employee);
            $clockOut = $this->service->getClockOutToday($employee);

            // Ambil shift hari ini
            $scheduleService = app(\App\Services\ScheduleService::class);
            $todayShift = $scheduleService->getShiftOnDate($employee, today());
        }

        return view('attendance.clock', compact('employee', 'status', 'clockIn', 'clockOut', 'todayShift'));
    }

    /**
     * Action clock in atau clock out.
     */
    public function store(Request $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->firstOrFail();

        $action = $request->validate([
            'action' => 'required|in:clock_in,clock_out',
        ])['action'];

        $options = [
            'source' => 'web',
            'ip_address' => $request->ip(),
        ];

        if ($action === 'clock_in') {
            $this->service->clockIn($employee, $options);
            return redirect()->route('attendance.clock')->with('success', 'Clock In berhasil! Selamat bekerja 💪');
        }

        $this->service->clockOut($employee, $options);
        return redirect()->route('attendance.clock')->with('success', 'Clock Out berhasil! Jangan lupa istirahat 😊');
    }
}
