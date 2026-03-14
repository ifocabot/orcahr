<?php

namespace App\Http\Controllers\Leave;

use App\Http\Controllers\Controller;
use App\Models\AttendanceException;
use App\Models\AttendanceSummary;
use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class LeaveRequestController extends Controller
{
    public function index(Request $request)
    {
        $employeeId = Employee::where('user_id', auth()->id())->value('id');

        $requests = LeaveRequest::with('leaveType')
            ->where('employee_id', $employeeId)
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $leaveTypes = LeaveType::active()->get(['id', 'name', 'code']);

        return Inertia::render('Leave/Request/Index', [
            'requests' => $requests,
            'leaveTypes' => $leaveTypes,
            'filters' => $request->only('status'),
        ]);
    }

    public function create()
    {
        $employeeId = Employee::where('user_id', auth()->id())->value('id');
        $year = now()->year;

        $leaveTypes = LeaveType::active()->get(['id', 'name', 'code', 'max_balance']);

        // Available balance per leave type
        $balances = $leaveTypes->map(function ($type) use ($employeeId, $year) {
            $latest = LeaveBalance::where('employee_id', $employeeId)
                ->where('leave_type_id', $type->id)
                ->where('entitlement_year', $year)
                ->latest('balance_date')
                ->first();

            return [
                'leave_type_id' => $type->id,
                'leave_type_name' => $type->name,
                'available' => $latest?->closing_balance ?? 0,
            ];
        });

        return Inertia::render('Leave/Request/Create', [
            'leaveTypes' => $leaveTypes,
            'balances' => $balances,
        ]);
    }

    public function store(Request $request)
    {
        $employeeId = Employee::where('user_id', auth()->id())->value('id');

        $validated = $request->validate([
            'leave_type_id' => ['required', 'exists:leave_types,id'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $totalDays = Carbon::parse($validated['start_date'])
            ->diffInWeekdays(Carbon::parse($validated['end_date'])->addDay());

        // Check sufficient balance
        $year = now()->year;
        $latest = LeaveBalance::where('employee_id', $employeeId)
            ->where('leave_type_id', $validated['leave_type_id'])
            ->where('entitlement_year', $year)
            ->latest('balance_date')
            ->first();

        $availableBalance = $latest?->closing_balance ?? 0;

        if ($availableBalance < $totalDays) {
            return back()->withErrors([
                'total_days' => "Saldo cuti tidak cukup. Tersedia: {$availableBalance} hari, Dibutuhkan: {$totalDays} hari.",
            ]);
        }

        LeaveRequest::create([
            'employee_id' => $employeeId,
            'leave_type_id' => $validated['leave_type_id'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'total_days' => $totalDays,
            'reason' => $validated['reason'],
            'status' => 'pending',
        ]);

        return redirect()->route('leave.requests.index')
            ->with('success', 'Pengajuan cuti berhasil dikirim.');
    }

    public function approve(LeaveRequest $request)
    {
        if ($request->status !== 'pending') {
            return back()->withErrors(['status' => 'Request sudah diproses.']);
        }

        $employee = $request->employee;
        $user = auth()->user();
        $isManager = $employee->manager_id && $employee->manager->user_id === $user->id;
        $isHR = $user->hasRole(['super-admin', 'hr']);

        // 1. Manager Approval
        if ($isManager && $request->manager_approval_status === 'pending') {
            $request->update([
                'manager_approval_status' => 'approved',
                'manager_approved_by' => $user->id,
                'manager_approved_at' => now(),
            ]);
            return back()->with('success', 'Persetujuan manager berhasil dicatat.');
        }

        // 2. HR Final Approval
        if ($isHR) {
            if ($employee->manager_id && $request->manager_approval_status === 'pending') {
                return back()->withErrors(['status' => 'Menunggu persetujuan manager terlebih dahulu.']);
            }

            $request->update([
                'status' => 'approved',
                'approved_by' => $user->id,
                'approved_at' => now(),
            ]);

            // Deduct leave balance: update the latest snapshot by reducing closing_balance
            $year = now()->year;
            $balance = LeaveBalance::where('employee_id', $request->employee_id)
                ->where('leave_type_id', $request->leave_type_id)
                ->where('entitlement_year', $year)
                ->latest('balance_date')
                ->first();

            if ($balance) {
                $balance->increment('used', (float) $request->total_days);
                $balance->decrement('closing_balance', (float) $request->total_days);
            }

            // Insert attendance_exception per work day
            $period = CarbonPeriod::create($request->start_date, $request->end_date);
            foreach ($period as $date) {
                if ($date->isWeekend())
                    continue;

                AttendanceException::updateOrCreate(
                    [
                        'employee_id' => $request->employee_id,
                        'work_date' => $date->toDateString(),
                        'exception_type' => 'leave',
                    ],
                    [
                        'reason' => $request->reason ?? 'Cuti disetujui',
                        'approval_status' => 'approved',
                        'approved_by' => $user->id,
                    ]
                );

                AttendanceSummary::where('employee_id', $request->employee_id)
                    ->where('work_date', $date->toDateString())
                    ->update(['dirty_flag' => true]);
            }

            return back()->with('success', 'Cuti disetujui (Final).');
        }

        return back()->withErrors(['status' => 'Anda tidak memiliki otoritas untuk menyetujui request ini.']);
    }

    public function reject(LeaveRequest $request)
    {
        request()->validate([
            'reject_reason' => ['required', 'string', 'min:5'],
        ]);

        if ($request->status !== 'pending') {
            return back()->withErrors(['status' => 'Request sudah diproses.']);
        }

        $user = auth()->user();
        $isManager = $request->employee->manager_id && $request->employee->manager->user_id === $user->id;
        $isHR = $user->hasRole(['super-admin', 'hr']);

        if (!$isManager && !$isHR) {
            return back()->withErrors(['status' => 'Anda tidak memiliki otoritas.']);
        }

        $request->update([
            'status' => 'rejected',
            'reject_reason' => request('reject_reason'),
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Cuti ditolak.');
    }

    public function cancel(LeaveRequest $leaveRequest)
    {
        $employeeId = Employee::where('user_id', auth()->id())->value('id');

        if ($leaveRequest->employee_id !== $employeeId) {
            abort(403);
        }

        if (!in_array($leaveRequest->status, ['pending', 'approved'])) {
            return back()->withErrors(['status' => 'Request tidak dapat dibatalkan.']);
        }

        // Refund balance if was approved
        if ($leaveRequest->status === 'approved') {
            $year = now()->year;
            $balance = LeaveBalance::where('employee_id', $leaveRequest->employee_id)
                ->where('leave_type_id', $leaveRequest->leave_type_id)
                ->where('entitlement_year', $year)
                ->latest('balance_date')
                ->first();

            if ($balance) {
                $balance->decrement('used', (float) $leaveRequest->total_days);
                $balance->increment('closing_balance', (float) $leaveRequest->total_days);
            }

            // Remove attendance exceptions for this leave
            AttendanceException::where('employee_id', $leaveRequest->employee_id)
                ->whereBetween('work_date', [$leaveRequest->start_date, $leaveRequest->end_date])
                ->where('exception_type', 'leave')
                ->delete();

            // Mark summaries dirty
            AttendanceSummary::where('employee_id', $leaveRequest->employee_id)
                ->whereBetween('work_date', [$leaveRequest->start_date, $leaveRequest->end_date])
                ->update(['dirty_flag' => true]);
        }

        $leaveRequest->update(['status' => 'cancelled']);

        return back()->with('success', 'Pengajuan cuti dibatalkan.');
    }

    // Manager approval list
    public function approval(Request $request)
    {
        $user = auth()->user();
        $isHR = $user->hasRole(['super-admin', 'hr']);
        $employee = Employee::where('user_id', $user->id)->first();

        $query = LeaveRequest::with(['employee.manager', 'leaveType']);

        // Filtering for manager: only see subordinates
        if (!$isHR) {
            if (!$employee) {
                return Inertia::render('Leave/Approval/Index', [
                    'requests' => [],
                    'employees' => [],
                    'filters' => [],
                ]);
            }
            $query->whereHas('employee', function ($q) use ($employee) {
                $q->where('manager_id', $employee->id);
            });
        }

        $pending = $query->when($request->status, fn($q, $s) => $q->where('status', $s), fn($q) => $q->where('status', 'pending'))
            ->when($request->employee_id, fn($q, $id) => $q->where('employee_id', $id))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $employees = $isHR
            ? Employee::select('id', 'full_name', 'employee_code')->orderBy('full_name')->get()
            : ($employee ? $employee->subordinates()->select('id', 'full_name', 'employee_code')->get() : []);

        return Inertia::render('Leave/Approval/Index', [
            'requests' => $pending,
            'employees' => $employees,
            'filters' => $request->only('status', 'employee_id'),
        ]);
    }
}
