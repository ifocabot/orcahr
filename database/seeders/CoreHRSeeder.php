<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Employee;
use App\Models\JobLevel;
use App\Models\Position;
use App\Models\User;
use Illuminate\Database\Seeder;

class CoreHRSeeder extends Seeder
{
    public function run(): void
    {
        $levels = collect([
            ['name' => 'Staff', 'level_order' => 1],
            ['name' => 'Senior Staff', 'level_order' => 2],
            ['name' => 'Supervisor', 'level_order' => 3],
            ['name' => 'Manager', 'level_order' => 4],
            ['name' => 'Senior Manager', 'level_order' => 5],
            ['name' => 'Director', 'level_order' => 6],
        ])->map(fn($l) => JobLevel::create($l));

        $deptHR = Department::create(['name' => 'Human Resources', 'code' => 'HR']);
        $deptIT = Department::create(['name' => 'Information Technology', 'code' => 'IT']);
        $deptFin = Department::create(['name' => 'Finance & Accounting', 'code' => 'FIN']);
        $deptOps = Department::create(['name' => 'Operations', 'code' => 'OPS']);

        Position::create(['name' => 'HR Admin', 'code' => 'HR-ADM', 'department_id' => $deptHR->id]);
        Position::create(['name' => 'HR Manager', 'code' => 'HR-MGR', 'department_id' => $deptHR->id]);
        Position::create(['name' => 'Software Engineer', 'code' => 'IT-SE', 'department_id' => $deptIT->id]);
        Position::create(['name' => 'IT Manager', 'code' => 'IT-MGR', 'department_id' => $deptIT->id]);
        Position::create(['name' => 'Accountant', 'code' => 'FIN-ACC', 'department_id' => $deptFin->id]);
        Position::create(['name' => 'Finance Manager', 'code' => 'FIN-MGR', 'department_id' => $deptFin->id]);
        Position::create(['name' => 'Operator', 'code' => 'OPS-OP', 'department_id' => $deptOps->id]);

        // Sample employee linked to admin user
        $adminUser = User::where('email', 'admin@orcahr.test')->first();
        if ($adminUser) {
            Employee::create([
                'employee_code' => 'EMP-20260101-001',
                'full_name' => 'Admin OrcaHR',
                'email' => 'admin@orcahr.test',
                'nik' => '3201010101010001',
                'department_id' => $deptHR->id,
                'position_id' => Position::where('code', 'HR-MGR')->first()->id,
                'job_level_id' => JobLevel::where('name', 'Manager')->first()->id,
                'user_id' => $adminUser->id,
                'join_date' => '2026-01-01',
                'employment_status' => 'active',
                'gender' => 'male',
            ]);
        }
    }
}
