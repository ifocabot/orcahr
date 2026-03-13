<?php

namespace Database\Seeders;

use App\Models\LeaveType;
use Illuminate\Database\Seeder;

class LeaveTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'name' => 'Cuti Tahunan',
                'code' => 'CT',
                'accrual_rate_monthly' => 1.00,
                'max_balance' => 12,
                'max_carryover' => 6,
                'min_service_months' => 12,
                'is_paid' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Cuti Sakit',
                'code' => 'CS',
                'accrual_rate_monthly' => 0.00,
                'max_balance' => 30,
                'max_carryover' => 0,
                'min_service_months' => 0,
                'is_paid' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Cuti Melahirkan',
                'code' => 'CM',
                'accrual_rate_monthly' => 0.00,
                'max_balance' => 90,
                'max_carryover' => 0,
                'min_service_months' => 0,
                'is_paid' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Cuti Menikah',
                'code' => 'CN',
                'accrual_rate_monthly' => 0.00,
                'max_balance' => 3,
                'max_carryover' => 0,
                'min_service_months' => 0,
                'is_paid' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Izin Tidak Ditanggung',
                'code' => 'ITD',
                'accrual_rate_monthly' => 0.00,
                'max_balance' => 0,
                'max_carryover' => 0,
                'min_service_months' => 0,
                'is_paid' => false,
                'is_active' => true,
            ],
        ];

        foreach ($types as $type) {
            LeaveType::firstOrCreate(['code' => $type['code']], $type);
        }
    }
}
