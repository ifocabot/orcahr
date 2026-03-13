<?php

namespace App\Http\Controllers;

use App\Models\AttendanceSummary;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\PayrollRun;
use App\Models\RawTimeEvent;
use Carbon\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $today = Carbon::today()->toDateString();

        // --- Stats ---
        $totalEmployees = Employee::active()->count();

        $presentToday = AttendanceSummary::where('work_date', $today)
            ->where('status', '!=', 'absent')
            ->distinct('employee_id')
            ->count('employee_id');

        $pendingLeaves = LeaveRequest::where('status', 'pending')->count();

        $pendingPayroll = PayrollRun::where('status', 'calculated')->count();

        // --- Recent clock-ins today (last 8) ---
        $recentClockIns = RawTimeEvent::with('employee')
            ->whereDate('event_time', $today)
            ->where('event_type', 'IN')
            ->orderByDesc('event_time')
            ->limit(8)
            ->get()
            ->map(fn($e) => [
                'employee_name' => $e->employee?->full_name ?? '—',
                'employee_code' => $e->employee?->employee_code ?? '—',
                'event_time' => Carbon::parse($e->event_time)->format('H:i'),
                'selfie_path' => $e->selfie_path,
            ]);

        // --- Headcount by department ---
        $headcountByDept = Employee::active()
            ->with('department')
            ->get()
            ->groupBy(fn($e) => $e->department?->name ?? 'Tanpa Departemen')
            ->map(fn($group, $dept) => [
                'department' => $dept,
                'count' => $group->count(),
            ])
            ->values()
            ->sortByDesc('count')
            ->values();

        return Inertia::render('Dashboard', [
            'stats' => [
                'total_employees' => $totalEmployees,
                'present_today' => $presentToday,
                'pending_leaves' => $pendingLeaves,
                'pending_payroll' => $pendingPayroll,
            ],
            'recent_clock_ins' => $recentClockIns,
            'headcount_by_dept' => $headcountByDept,
        ]);
    }
}
