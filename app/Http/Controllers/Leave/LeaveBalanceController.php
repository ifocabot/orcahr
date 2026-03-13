<?php

namespace App\Http\Controllers\Leave;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LeaveBalanceController extends Controller
{
    public function index(Request $request)
    {
        $year = $request->integer('year', now()->year);
        $employeeId = $request->input('employee_id');

        // Default to current user's employee record
        if (!$employeeId) {
            $employeeId = Employee::where('user_id', auth()->id())->value('id');
        }

        // Fetch latest balance snapshot per leave type for the employee/year
        $leaveTypes = LeaveType::active()->get();

        $balances = $leaveTypes->map(function ($type) use ($employeeId, $year) {
            $latest = LeaveBalance::where('employee_id', $employeeId)
                ->where('leave_type_id', $type->id)
                ->where('entitlement_year', $year)
                ->latest('balance_date')
                ->first();

            return [
                'leave_type_id' => $type->id,
                'leave_type_name' => $type->name,
                'leave_type_code' => $type->code,
                'is_paid' => $type->is_paid,
                'max_balance' => $type->max_balance,
                'opening_balance' => $latest?->opening_balance ?? 0,
                'accrued' => $latest?->accrued ?? 0,
                'used' => $latest?->used ?? 0,
                'adjustment' => $latest?->adjustment ?? 0,
                'closing_balance' => $latest?->closing_balance ?? 0,
                'expiry_date' => $latest?->expiry_date,
            ];
        });

        $employees = Employee::select('id', 'full_name', 'employee_code')
            ->orderBy('full_name')
            ->get();

        return Inertia::render('Leave/Balance', [
            'balances' => $balances,
            'employees' => $employees,
            'currentEmployee' => Employee::find($employeeId)?->only('id', 'full_name', 'employee_code'),
            'year' => $year,
        ]);
    }
}
