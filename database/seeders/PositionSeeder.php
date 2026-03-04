<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\JobLevel;
use App\Models\Position;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    public function run(): void
    {
        $map = [
            // [department_code, job_level_level, position_name]
            ['HR', 1, 'HR Staff'],
            ['HR', 3, 'HR Supervisor'],
            ['HR', 5, 'HR Manager'],
            ['FIN', 1, 'Finance Staff'],
            ['FIN', 3, 'Finance Supervisor'],
            ['FIN', 5, 'Finance Manager'],
            ['IT-DEV', 1, 'Junior Developer'],
            ['IT-DEV', 2, 'Senior Developer'],
            ['IT-DEV', 3, 'Tech Lead'],
            ['IT-DEV', 5, 'Engineering Manager'],
            ['IT-INF', 1, 'IT Support'],
            ['IT-INF', 3, 'IT Supervisor'],
            ['OPS', 1, 'Operations Staff'],
            ['OPS', 3, 'Operations Supervisor'],
            ['OPS', 5, 'Operations Manager'],
            ['MKT', 1, 'Marketing Staff'],
            ['MKT', 5, 'Marketing Manager'],
            ['SLS', 1, 'Sales Executive'],
            ['SLS', 3, 'Sales Supervisor'],
            ['SLS', 5, 'Sales Manager'],
            ['LGL', 1, 'Legal Staff'],
            ['LGL', 5, 'Legal Manager'],
        ];

        foreach ($map as [$deptCode, $levelNum, $posName]) {
            $dept = Department::where('code', $deptCode)->first();
            $level = JobLevel::where('level', $levelNum)->first();

            if (!$dept || !$level) {
                continue;
            }

            Position::firstOrCreate(
                ['name' => $posName, 'department_id' => $dept->id],
                ['job_level_id' => $level->id]
            );
        }
    }
}
