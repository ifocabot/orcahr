<?php

namespace Database\Seeders;

use App\Models\PayrollComponent;
use Illuminate\Database\Seeder;

class PayrollComponentSeeder extends Seeder
{
    public function run(): void
    {
        $components = [
            ['name' => 'Gaji Pokok', 'code' => 'GAPOK', 'type' => 'earning', 'is_taxable' => true, 'is_fixed' => true, 'formula' => null, 'sort_order' => 1],
            ['name' => 'Tunjangan Transportasi', 'code' => 'T-TRANS', 'type' => 'earning', 'is_taxable' => true, 'is_fixed' => true, 'formula' => null, 'sort_order' => 2],
            ['name' => 'Tunjangan Makan', 'code' => 'T-MAKAN', 'type' => 'earning', 'is_taxable' => true, 'is_fixed' => true, 'formula' => null, 'sort_order' => 3],
            ['name' => 'Tunjangan Jabatan', 'code' => 'T-JABATAN', 'type' => 'earning', 'is_taxable' => true, 'is_fixed' => true, 'formula' => null, 'sort_order' => 4],
            ['name' => 'Lembur', 'code' => 'OT', 'type' => 'earning', 'is_taxable' => true, 'is_fixed' => false, 'formula' => 'overtime_minutes * (gapok / 173 / 60) * 1.5', 'sort_order' => 5],
            ['name' => 'BPJS Kesehatan', 'code' => 'BPJS-KES', 'type' => 'deduction', 'is_taxable' => false, 'is_fixed' => false, 'formula' => 'min(gapok * 0.01, 120000)', 'sort_order' => 10],
            ['name' => 'BPJS Ketenagakerjaan', 'code' => 'BPJS-TK', 'type' => 'deduction', 'is_taxable' => false, 'is_fixed' => false, 'formula' => 'gapok * 0.02', 'sort_order' => 11],
            ['name' => 'PPh 21', 'code' => 'PPH21', 'type' => 'deduction', 'is_taxable' => false, 'is_fixed' => false, 'formula' => 'pph21_calculated', 'sort_order' => 12],
        ];

        foreach ($components as $component) {
            PayrollComponent::firstOrCreate(
                ['code' => $component['code']],
                $component
            );
        }
    }
}
