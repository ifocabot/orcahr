<?php

namespace Database\Seeders;

use App\Models\JobLevel;
use Illuminate\Database\Seeder;

class JobLevelSeeder extends Seeder
{
    public function run(): void
    {
        $levels = [
            ['name' => 'Staff', 'level' => 1],
            ['name' => 'Senior Staff', 'level' => 2],
            ['name' => 'Supervisor', 'level' => 3],
            ['name' => 'Assistant Manager', 'level' => 4],
            ['name' => 'Manager', 'level' => 5],
            ['name' => 'Senior Manager', 'level' => 6],
            ['name' => 'General Manager', 'level' => 7],
            ['name' => 'Director', 'level' => 8],
        ];

        foreach ($levels as $level) {
            JobLevel::firstOrCreate(['level' => $level['level']], $level);
        }
    }
}
