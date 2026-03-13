<?php

namespace App\Jobs;

use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MonthlyLeaveAccrual implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $activeEmployees = Employee::where('employment_status', 'active')->get();
        $leaveTypes = LeaveType::active()->get();
        $currentMonth = now()->startOfMonth()->toDateString();

        foreach ($activeEmployees as $employee) {
            $serviceMonths = $employee->join_date->diffInMonths(now());

            foreach ($leaveTypes as $type) {
                // Skip if accrual_rate is 0 or employee hasn't met min service months
                if ($type->accrual_rate_monthly <= 0)
                    continue;
                if ($serviceMonths < $type->min_service_months)
                    continue;

                $currentBalance = LeaveBalance::where('employee_id', $employee->id)
                    ->where('leave_type_id', $type->id)
                    ->where('entitlement_year', now()->year)
                    ->latest('balance_date')
                    ->first();

                $opening = $currentBalance?->closing_balance ?? 0;
                $accrual = $type->accrual_rate_monthly;
                $newBalance = min($opening + $accrual, $type->max_balance);
                $actualAccrual = $newBalance - $opening;

                // Avoid duplicate: skip if snapshot for this month already exists
                $exists = LeaveBalance::where('employee_id', $employee->id)
                    ->where('leave_type_id', $type->id)
                    ->where('balance_date', $currentMonth)
                    ->exists();

                if ($exists)
                    continue;

                LeaveBalance::create([
                    'employee_id' => $employee->id,
                    'leave_type_id' => $type->id,
                    'balance_date' => $currentMonth,
                    'opening_balance' => $opening,
                    'accrued' => $actualAccrual,
                    'used' => 0,
                    'adjustment' => 0,
                    'closing_balance' => $newBalance,
                    'entitlement_year' => now()->year,
                    'expiry_date' => Carbon::create(now()->year + 1, 6, 30),
                ]);
            }
        }
    }
}
