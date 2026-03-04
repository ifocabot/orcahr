<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Models\DailyAttendance;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('manage-attendance');

        $month = (int) $request->get('month', now()->month);
        $year = (int) $request->get('year', now()->year);

        $employees = Employee::with(['currentEmployment.department'])
            ->whereNull('terminated_at')
            ->orderBy('full_name')
            ->get();

        $attendances = $employees->map(function (Employee $emp) use ($month, $year) {
            $records = DailyAttendance::where('employee_id', $emp->id)
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
                ->get();

            return [
                'employee' => $emp,
                'present' => $records->whereIn('status', ['present', 'late', 'early_leave'])->count(),
                'late' => $records->where('status', 'late')->count(),
                'absent' => $records->where('status', 'absent')->count(),
                'leave' => $records->where('status', 'leave')->count(),
                'work_hours' => $records->sum('work_hours'),
            ];
        });

        return view('attendance.index', compact('attendances', 'month', 'year'));
    }
}
