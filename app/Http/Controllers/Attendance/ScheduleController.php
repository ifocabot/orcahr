<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeSchedule;
use App\Models\ShiftMaster;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ScheduleController extends Controller
{
    public function index(Request $request): Response
    {
        $schedules = EmployeeSchedule::with(['employee', 'shift'])
            ->when($request->employee_id, fn($q) => $q->where('employee_id', $request->employee_id))
            ->when($request->month, function ($q) use ($request) {
                $month = Carbon::parse($request->month . '-01');
                $q->where('start_date', '<=', $month->endOfMonth())
                    ->where(fn($sq) => $sq->whereNull('end_date')->orWhere('end_date', '>=', $month->startOfMonth()));
            })
            ->orderBy('start_date', 'desc')
            ->paginate(30)
            ->withQueryString();

        return Inertia::render('Attendance/Schedules/Index', [
            'schedules' => $schedules,
            'employees' => Employee::active()->orderBy('full_name')->get(['id', 'full_name', 'employee_code']),
            'filters' => $request->only(['employee_id', 'month']),
        ]);
    }

    public function generateForm(): Response
    {
        return Inertia::render('Attendance/Schedules/Generate', [
            'employees' => Employee::active()->orderBy('full_name')->get(['id', 'full_name', 'employee_code']),
            'shifts' => ShiftMaster::active()->orderBy('name')->get(['id', 'name', 'start_time', 'end_time']),
        ]);
    }

    public function generate(Request $request)
    {
        $data = $request->validate([
            'employee_ids' => 'required|array|min:1',
            'employee_ids.*' => 'exists:employees,id',
            'shift_id' => 'required|exists:shift_masters,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $created = 0;

        foreach ($data['employee_ids'] as $employeeId) {
            // Deactivate any overlapping schedule
            EmployeeSchedule::where('employee_id', $employeeId)
                ->where('status', 'active')
                ->where('start_date', '<=', $data['end_date'])
                ->where(fn($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', $data['start_date']))
                ->update(['status' => 'inactive']);

            EmployeeSchedule::create([
                'employee_id' => $employeeId,
                'shift_id' => $data['shift_id'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'status' => 'active',
            ]);

            $created++;
        }

        return redirect('/attendance/schedules')
            ->with('success', "Jadwal berhasil dibuat untuk {$created} karyawan.");
    }
}
