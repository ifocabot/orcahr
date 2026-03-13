<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Jobs\RecalculateDirtySummaries;
use App\Models\AttendanceException;
use App\Models\AttendanceSummary;
use App\Models\Employee;
use App\Models\EmployeeSchedule;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ExceptionController extends Controller
{
    public function index(Request $request): Response
    {
        $exceptions = AttendanceException::with(['employee', 'approvedBy'])
            ->when($request->status, fn($q) => $q->where('approval_status', $request->status))
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Attendance/Exceptions/Index', [
            'exceptions' => $exceptions,
            'filters' => $request->only('status'),
        ]);
    }

    public function approve(AttendanceException $exception)
    {
        abort_if($exception->approval_status !== 'pending', 422, 'Exception sudah diproses.');

        $exception->update([
            'approval_status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        // Mark attendance summary as dirty so it gets recalculated
        AttendanceSummary::where('employee_id', $exception->employee_id)
            ->where('work_date', $exception->work_date)
            ->update(['dirty_flag' => true]);

        RecalculateDirtySummaries::dispatch();

        return back()->with('success', 'Exception disetujui. Absensi akan dihitung ulang.');
    }

    public function reject(Request $request, AttendanceException $exception)
    {
        abort_if($exception->approval_status !== 'pending', 422, 'Exception sudah diproses.');

        $request->validate(['reason' => 'required|string|max:255']);

        $exception->update([
            'approval_status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'reason' => $request->reason,
        ]);

        return back()->with('success', 'Exception ditolak.');
    }

    public function storeHalfDay(Request $request)
    {
        $employee = Employee::where('user_id', auth()->id())->firstOrFail();

        $validated = $request->validate([
            'work_date' => ['required', 'date', 'after_or_equal:today'],
            'session' => ['required', 'in:AM,PM'],
            'reason' => ['required', 'string', 'max:500'],
        ]);

        // Find active shift for the employee on this date
        $schedule = EmployeeSchedule::where('employee_id', $employee->id)
            ->where('start_date', '<=', $validated['work_date'])
            ->where(fn($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', $validated['work_date']))
            ->where('status', 'active')
            ->with('shift')
            ->first();

        abort_if(!$schedule, 422, 'Tidak ada jadwal aktif untuk tanggal tersebut.');

        $shift = $schedule->shift;
        $shiftHours = ($shift->total_hours ?? 8);
        $minDuration = $shiftHours / 2;

        AttendanceException::create([
            'employee_id' => $employee->id,
            'work_date' => $validated['work_date'],
            'exception_type' => 'half_day_permit',
            'duration_hours' => $minDuration,
            'reason' => $validated['reason'],
            'approval_status' => 'pending',
        ]);

        return redirect()->route('leave.requests.index')
            ->with('success', 'Izin setengah hari berhasil diajukan.');
    }
}

