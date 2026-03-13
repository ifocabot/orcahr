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

class YearlyLeaveCarryover implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $lastYear = now()->year - 1;
        $thisYear = now()->year;
        $activeEmployees = Employee::where('employment_status', 'active')->get();
        $leaveTypes = LeaveType::active()->where('max_carryover', '>', 0)->get();

        foreach ($activeEmployees as $employee) {
            foreach ($leaveTypes as $type) {
                // Get closing balance from last year's last snapshot
                $lastYearBalance = LeaveBalance::where('employee_id', $employee->id)
                    ->where('leave_type_id', $type->id)
                    ->where('entitlement_year', $lastYear)
                    ->latest('balance_date')
                    ->first();

                if (!$lastYearBalance)
                    continue;

                $carryover = min($lastYearBalance->closing_balance, $type->max_carryover);

                // Avoid duplicate
                $exists = LeaveBalance::where('employee_id', $employee->id)
                    ->where('leave_type_id', $type->id)
                    ->where('entitlement_year', $thisYear)
                    ->exists();

                if ($exists)
                    continue;

                LeaveBalance::create([
                    'employee_id' => $employee->id,
                    'leave_type_id' => $type->id,
                    'balance_date' => Carbon::create($thisYear, 1, 1)->toDateString(),
                    'opening_balance' => $carryover,
                    'accrued' => 0,
                    'used' => 0,
                    'adjustment' => 0,
                    'closing_balance' => $carryover,
                    'entitlement_year' => $thisYear,
                    'expiry_date' => Carbon::create($thisYear, 6, 30),
                ]);
            }
        }
    }
}
