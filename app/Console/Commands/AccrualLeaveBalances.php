<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AccrualLeaveBalances extends Command
{
    protected $signature = 'leave:accrue {year? : Tahun (default: tahun ini)} {month? : Bulan 1-12 (default: bulan ini)}';
    protected $description = 'Akrual saldo cuti bulanan untuk semua karyawan aktif';

    public function handle(): int
    {
        $year = (int) ($this->argument('year') ?? now()->year);
        $month = (int) ($this->argument('month') ?? now()->month);

        $this->info("▶ Accrual cuti: {$year}-{$month}");

        $balanceDate = Carbon::createFromDate($year, $month, 1)->toDateString();
        $prevDate = Carbon::createFromDate($year, $month, 1)->subMonth()->toDateString();

        $employees = Employee::active()->get(['id', 'join_date']);
        $leaveTypes = LeaveType::active()->get();

        $created = 0;
        $skipped = 0;

        foreach ($employees as $emp) {
            $monthsWorked = Carbon::parse($emp->join_date)->diffInMonths(Carbon::now());

            foreach ($leaveTypes as $type) {
                // Skip jika masa kerja kurang dari minimum
                if ($monthsWorked < $type->min_service_months) {
                    $skipped++;
                    continue;
                }

                // Jangan duplikat — skip jika sudah ada snapshot bulan ini
                $alreadyExists = LeaveBalance::where('employee_id', $emp->id)
                    ->where('leave_type_id', $type->id)
                    ->where('balance_date', $balanceDate)
                    ->exists();

                if ($alreadyExists) {
                    $skipped++;
                    continue;
                }

                // Ambil closing_balance bulan lalu
                $prev = LeaveBalance::where('employee_id', $emp->id)
                    ->where('leave_type_id', $type->id)
                    ->where('balance_date', '<=', $prevDate)
                    ->where('entitlement_year', $year)
                    ->latest('balance_date')
                    ->first();

                $opening = $prev?->closing_balance ?? 0.0;
                $accrued = (float) $type->accrual_rate_monthly;
                $closing = min($opening + $accrued, $type->max_balance);

                LeaveBalance::create([
                    'employee_id' => $emp->id,
                    'leave_type_id' => $type->id,
                    'balance_date' => $balanceDate,
                    'opening_balance' => $opening,
                    'accrued' => $accrued,
                    'used' => 0,
                    'adjustment' => 0,
                    'closing_balance' => $closing,
                    'entitlement_year' => $year,
                    'expiry_date' => Carbon::createFromDate($year, 12, 31)->toDateString(),
                ]);

                $created++;
            }
        }

        $this->info("✅ Selesai — Dibuat: {$created} snapshot | Dilewati: {$skipped}");

        return self::SUCCESS;
    }
}
