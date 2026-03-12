<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'employees.view', 'employees.create', 'employees.edit', 'employees.delete',
            'departments.view', 'departments.create', 'departments.edit', 'departments.delete',
            'positions.view', 'positions.create', 'positions.edit', 'positions.delete',
            'attendance.view', 'attendance.clock', 'attendance.manage',
            'shifts.view', 'shifts.manage',
            'schedules.view', 'schedules.manage',
            'timesheet.view', 'timesheet.export',
            'leave.view', 'leave.request', 'leave.approve',
            'payroll.view', 'payroll.manage', 'payroll.approve',
            'dashboard.view',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        Role::firstOrCreate(['name' => 'super-admin'])
            ->givePermissionTo(Permission::all());

        Role::firstOrCreate(['name' => 'hr'])
            ->givePermissionTo([
                'employees.view', 'employees.create', 'employees.edit', 'employees.delete',
                'departments.view', 'departments.create', 'departments.edit', 'departments.delete',
                'positions.view', 'positions.create', 'positions.edit', 'positions.delete',
                'attendance.view', 'attendance.manage',
                'shifts.view', 'shifts.manage',
                'schedules.view', 'schedules.manage',
                'timesheet.view', 'timesheet.export',
                'leave.view', 'leave.approve',
                'payroll.view', 'payroll.manage',
                'dashboard.view',
            ]);

        Role::firstOrCreate(['name' => 'manager'])
            ->givePermissionTo([
                'employees.view',
                'attendance.view', 'attendance.clock',
                'schedules.view',
                'timesheet.view',
                'leave.view', 'leave.request', 'leave.approve',
                'payroll.view',
                'dashboard.view',
            ]);

        Role::firstOrCreate(['name' => 'employee'])
            ->givePermissionTo([
                'attendance.view', 'attendance.clock',
                'leave.view', 'leave.request',
                'payroll.view',
                'dashboard.view',
            ]);
    }
}
