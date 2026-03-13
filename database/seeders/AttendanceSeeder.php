<?php

namespace Database\Seeders;

use App\Models\ShiftMaster;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $shifts = [
            [
                'name' => 'Pagi',
                'start_time' => '08:00:00',
                'end_time' => '17:00:00',
                'is_overnight' => false,
                'break_minutes' => 60,
                'overtime_threshold_minutes' => 30,
                'is_active' => true,
            ],
            [
                'name' => 'Siang',
                'start_time' => '12:00:00',
                'end_time' => '21:00:00',
                'is_overnight' => false,
                'break_minutes' => 60,
                'overtime_threshold_minutes' => 30,
                'is_active' => true,
            ],
            [
                'name' => 'Malam',
                'start_time' => '21:00:00',
                'end_time' => '06:00:00',
                'is_overnight' => true,
                'break_minutes' => 60,
                'overtime_threshold_minutes' => 30,
                'is_active' => true,
            ],
            [
                'name' => 'Flexible',
                'start_time' => '08:00:00',
                'end_time' => '17:00:00',
                'is_overnight' => false,
                'break_minutes' => 60,
                'overtime_threshold_minutes' => 0,
                'is_active' => true,
            ],
        ];

        foreach ($shifts as $shift) {
            ShiftMaster::firstOrCreate(['name' => $shift['name']], $shift);
        }
    }
}
