<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Users & System
            'manage-users',
            'system-settings',
            'view-audit',

            // Employee
            'manage-employees',
            'view-employees',
            'view-sensitive-data',      // NIK, NPWP, rekening

            // Attendance
            'manage-attendance',
            'view-attendance',
            'approve-attendance',

            // Leave
            'manage-leave',
            'view-leave',
            'approve-leave',

            // Payroll
            'manage-payroll',
            'run-payroll',
            'view-payslip',

            // Recruitment
            'manage-recruitment',
            'view-recruitment',
            'create-manpower',
            'approve-manpower',

            // Announcement
            'manage-announcements',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // === ROLES ===

        // Super Admin — akses penuh
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::all());

        // HR Admin
        $hrAdmin = Role::firstOrCreate(['name' => 'hr-admin', 'guard_name' => 'web']);
        $hrAdmin->syncPermissions([
            'manage-employees',
            'view-employees',
            'view-sensitive-data',
            'manage-attendance',
            'view-attendance',
            'approve-attendance',
            'manage-leave',
            'view-leave',
            'approve-leave',
            'manage-recruitment',
            'view-recruitment',
            'create-manpower',
            'approve-manpower',
            'manage-announcements',
        ]);

        // Payroll Admin
        $payrollAdmin = Role::firstOrCreate(['name' => 'payroll-admin', 'guard_name' => 'web']);
        $payrollAdmin->syncPermissions([
            'view-employees',
            'manage-payroll',
            'run-payroll',
            'view-payslip',
            'view-attendance',
        ]);

        // Department Head
        $deptHead = Role::firstOrCreate(['name' => 'dept-head', 'guard_name' => 'web']);
        $deptHead->syncPermissions([
            'view-employees',
            'view-attendance',
            'approve-attendance',
            'view-leave',
            'approve-leave',
            'create-manpower',
        ]);

        // Employee — self-service only
        Role::firstOrCreate(['name' => 'employee', 'guard_name' => 'web']);
    }
}
