<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        // Department induk (head_id nullable, tidak diset saat seed awal)
        $depts = [
            ['name' => 'Human Resources', 'code' => 'HR'],
            ['name' => 'Finance & Accounting', 'code' => 'FIN'],
            ['name' => 'Information Technology', 'code' => 'IT'],
            ['name' => 'Operations', 'code' => 'OPS'],
            ['name' => 'Marketing', 'code' => 'MKT'],
            ['name' => 'Sales', 'code' => 'SLS'],
            ['name' => 'Legal & Compliance', 'code' => 'LGL'],
        ];

        foreach ($depts as $dept) {
            Department::firstOrCreate(['code' => $dept['code']], array_merge($dept, ['parent_id' => null]));
        }

        // Sub-department IT
        $it = Department::where('code', 'IT')->first();
        if ($it) {
            $subs = [
                ['name' => 'Software Development', 'code' => 'IT-DEV', 'parent_id' => $it->id],
                ['name' => 'IT Infrastructure', 'code' => 'IT-INF', 'parent_id' => $it->id],
            ];
            foreach ($subs as $sub) {
                Department::firstOrCreate(['code' => $sub['code']], $sub);
            }
        }
    }
}
