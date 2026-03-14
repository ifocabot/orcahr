<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'key' => 'attendance.office_latitude',
                'value' => '-6.200000',
                'type' => 'decimal',
                'group' => 'attendance',
                'label' => 'Latitude Kantor',
                'description' => 'Koordinat lintang lokasi kantor untuk validasi absensi',
            ],
            [
                'key' => 'attendance.office_longitude',
                'value' => '106.816666',
                'type' => 'decimal',
                'group' => 'attendance',
                'label' => 'Longitude Kantor',
                'description' => 'Koordinat bujur lokasi kantor untuk validasi absensi',
            ],
            [
                'key' => 'attendance.radius_meters',
                'value' => '100',
                'type' => 'integer',
                'group' => 'attendance',
                'label' => 'Radius Absensi (meter)',
                'description' => 'Jarak maksimum dari kantor agar clock-in diterima',
            ],
            [
                'key' => 'attendance.late_tolerance_minutes',
                'value' => '15',
                'type' => 'integer',
                'group' => 'attendance',
                'label' => 'Toleransi Keterlambatan (menit)',
                'description' => 'Menit setelah jam masuk sebelum dihitung terlambat',
            ],
            [
                'key' => 'payroll.working_days_per_month',
                'value' => '22',
                'type' => 'integer',
                'group' => 'payroll',
                'label' => 'Hari Kerja per Bulan (Default)',
                'description' => 'Digunakan untuk menghitung potongan per hari',
            ],
            [
                'key' => 'company.name',
                'value' => 'PT OrcaHR Indonesia',
                'type' => 'string',
                'group' => 'company',
                'label' => 'Nama Perusahaan',
                'description' => 'Muncul di header slip gaji',
            ],
            [
                'key' => 'company.address',
                'value' => 'Jakarta, Indonesia',
                'type' => 'string',
                'group' => 'company',
                'label' => 'Alamat Perusahaan',
                'description' => 'Muncul di slip gaji',
            ],
        ];

        foreach ($settings as $s) {
            SystemSetting::updateOrCreate(['key' => $s['key']], $s);
        }
    }
}
