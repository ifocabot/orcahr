<?php

namespace App\Http\Controllers\Attendance;

use App\Exports\TimesheetExport;
use App\Http\Controllers\Controller;
use App\Models\AttendanceSummary;
use App\Models\Department;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TimesheetController extends Controller
{
    public function index(Request $request): Response
    {
        $month = $request->get('month', now()->format('Y-m'));

        $summaries = AttendanceSummary::with(['employee.department', 'shift'])
            ->when($month, function ($q) use ($month) {
                [$y, $m] = explode('-', $month);
                $q->whereYear('work_date', $y)->whereMonth('work_date', $m);
            })
            ->when(
                $request->department_id,
                fn($q) =>
                $q->whereHas('employee', fn($eq) => $eq->where('department_id', $request->department_id))
            )
            ->orderBy('work_date', 'desc')
            ->paginate(50)
            ->withQueryString();

        return Inertia::render('Attendance/Timesheet/Index', [
            'summaries' => $summaries,
            'departments' => Department::orderBy('name')->get(['id', 'name']),
            'filters' => $request->only(['month', 'department_id']),
        ]);
    }

    public function export(Request $request): BinaryFileResponse
    {
        $month = $request->get('month', now()->format('Y-m'));
        $filename = "timesheet-{$month}.xlsx";

        return Excel::download(new TimesheetExport($month, $request->department_id), $filename);
    }
}
