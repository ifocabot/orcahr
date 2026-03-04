<?php

use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    $this->seed(\Database\Seeders\RolePermissionSeeder::class);
    $this->seed(\Database\Seeders\JobLevelSeeder::class);
    $this->seed(\Database\Seeders\DepartmentSeeder::class);
    $this->seed(\Database\Seeders\PositionSeeder::class);
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
});

// ─── Guest (Unauthenticated) ──────────────────────────────────────────────────

it('guest diredirect ke login saat akses employee index', function () {
    $this->get(route('employees.index'))->assertRedirect(route('login'));
});

it('guest diredirect ke login saat akses employee create', function () {
    $this->get(route('employees.create'))->assertRedirect(route('login'));
});

// ─── HR Admin ─────────────────────────────────────────────────────────────────

it('hr-admin BISA melihat employee list', function () {
    actingAsRole('hr-admin');
    $this->get(route('employees.index'))->assertOk();
});

it('hr-admin BISA membuka form create', function () {
    actingAsRole('hr-admin');
    $this->get(route('employees.create'))->assertOk();
});

it('hr-admin TIDAK BISA delete employee', function () {
    actingAsRole('super-admin');
    $employee = makeEmployee();

    actingAsRole('hr-admin');
    $this->delete(route('employees.destroy', $employee))->assertForbidden();
});

// ─── Payroll Admin ────────────────────────────────────────────────────────────

it('payroll-admin BISA melihat employee list', function () {
    actingAsRole('payroll-admin');
    $this->get(route('employees.index'))->assertOk();
});

it('payroll-admin TIDAK BISA membuka form create', function () {
    actingAsRole('payroll-admin');
    $this->get(route('employees.create'))->assertForbidden();
});

// ─── Dept Head ────────────────────────────────────────────────────────────────

it('dept-head BISA melihat employee list', function () {
    actingAsRole('dept-head');
    $this->get(route('employees.index'))->assertOk();
});

it('dept-head TIDAK BISA create employee', function () {
    actingAsRole('dept-head');
    $this->get(route('employees.create'))->assertForbidden();
});
