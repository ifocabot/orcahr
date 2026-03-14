<?php

namespace Database\Seeders;

use App\Models\Holiday;
use Illuminate\Database\Seeder;

class HolidaySeeder extends Seeder
{
    public function run(): void
    {
        $holidays2026 = [
            ['name' => 'Tahun Baru Masehi', 'date' => '2026-01-01', 'type' => 'national'],
            ['name' => 'Isra Mi\'raj Nabi Muhammad SAW', 'date' => '2026-01-27', 'type' => 'national'],
            ['name' => 'Tahun Baru Imlek 2577', 'date' => '2026-02-17', 'type' => 'national'],
            ['name' => 'Hari Raya Nyepi (Tahun Baru Saka 1948)', 'date' => '2026-03-09', 'type' => 'national'],
            ['name' => 'Cuti Bersama Nyepi', 'date' => '2026-03-10', 'type' => 'national'],
            ['name' => 'Wafat Isa Almasih', 'date' => '2026-04-03', 'type' => 'national'],
            ['name' => 'Hari Raya Idul Fitri 1447 H (1)', 'date' => '2026-03-20', 'type' => 'national'],
            ['name' => 'Hari Raya Idul Fitri 1447 H (2)', 'date' => '2026-03-21', 'type' => 'national'],
            ['name' => 'Cuti Bersama Idul Fitri', 'date' => '2026-03-19', 'type' => 'national'],
            ['name' => 'Cuti Bersama Idul Fitri', 'date' => '2026-03-23', 'type' => 'national'],
            ['name' => 'Cuti Bersama Idul Fitri', 'date' => '2026-03-24', 'type' => 'national'],
            ['name' => 'Hari Buruh Internasional', 'date' => '2026-05-01', 'type' => 'national'],
            ['name' => 'Kenaikan Isa Almasih', 'date' => '2026-05-14', 'type' => 'national'],
            ['name' => 'Hari Raya Waisak 2570', 'date' => '2026-05-24', 'type' => 'national'],
            ['name' => 'Hari Lahir Pancasila', 'date' => '2026-06-01', 'type' => 'national'],
            ['name' => 'Hari Raya Idul Adha 1447 H', 'date' => '2026-05-27', 'type' => 'national'],
            ['name' => 'Tahun Baru Islam 1448 H', 'date' => '2026-06-17', 'type' => 'national'],
            ['name' => 'Hari Kemerdekaan RI', 'date' => '2026-08-17', 'type' => 'national'],
            ['name' => 'Maulid Nabi Muhammad SAW', 'date' => '2026-08-26', 'type' => 'national'],
            ['name' => 'Hari Natal', 'date' => '2026-12-25', 'type' => 'national'],
            ['name' => 'Cuti Bersama Natal', 'date' => '2026-12-24', 'type' => 'national'],
            ['name' => 'Cuti Bersama Natal', 'date' => '2026-12-26', 'type' => 'national'],
        ];

        foreach ($holidays2026 as $holiday) {
            Holiday::updateOrCreate(
                ['holiday_date' => $holiday['date']],
                [
                    'name' => $holiday['name'],
                    'type' => $holiday['type'],
                    'is_paid' => true,
                    'year' => 2026,
                ]
            );
        }
    }
}
