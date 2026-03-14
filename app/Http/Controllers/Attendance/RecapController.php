<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSummary;
use App\Models\Department;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RecapExport;

class RecapController extends Controller
{
    public function index(Request $request): Response
    {
        $month = (int) ($request->month ?? now()->month);
        $year = (int) ($request->year ?? now()->year);

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth()->toDateString();
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth()->toDateString();

        $employees = Employee::active()
            ->with('department')
            ->when($request->department_id, fn($q, $v) => $q->where('department_id', $v))
            ->orderBy('full_name')
            ->get();

        $recap = $employees->map(function (Employee $emp) use ($startDate, $endDate) {
            $summaries = AttendanceSummary::where('employee_id', $emp->id)
                ->whereBetween('work_date', [$startDate, $endDate])
                ->get();

            $total = $summaries->count();
            $present = $summaries->where('status', 'present')->count();
            $late = $summaries->where('status', 'late')->count();
            $absent = $summaries->where('status', 'absent')->count();
            $leave = $summaries->where('status', 'leave')->count();
            $holiday = $summaries->where('status', 'holiday')->count();
            $lateMin = $summaries->sum('late_minutes');
            $otMin = $summaries->sum('overtime_minutes');

            return [
                'employee_id' => $emp->id,
                'employee_code' => $emp->employee_code,
                'full_name' => $emp->full_name,
                'department' => $emp->department?->name ?? '—',
                'total_days' => $total,
                'present' => $present,
                'late' => $late,
                'absent' => $absent,
                'leave' => $leave,
                'holiday' => $holiday,
                'late_minutes' => $lateMin,
                'ot_minutes' => $otMin,
                'attendance_pct' => $total > 0 ? round((($present + $late) / $total) * 100, 1) : 0,
            ];
        });

        return Inertia::render('Attendance/Recap/Index', [
            'recap' => $recap,
            'departments' => Department::where('is_active', true)->get(['id', 'name']),
            'filters' => $request->only(['month', 'year', 'department_id']),
            'month' => $month,
            'year' => $year,
        ]);
    }

    public function export(Request $request)
    {
        $month = (int) ($request->month ?? now()->month);
        $year = (int) ($request->year ?? now()->year);
        $filename = "rekap-kehadiran-{$year}-{$month}.xlsx";

        return Excel::download(new RecapExport($month, $year, $request->department_id), $filename);
    }
}
