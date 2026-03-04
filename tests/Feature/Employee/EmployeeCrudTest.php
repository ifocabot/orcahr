<?php

use App\Models\Employee;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    $this->seed(\Database\Seeders\RolePermissionSeeder::class);
    $this->seed(\Database\Seeders\JobLevelSeeder::class);
    $this->seed(\Database\Seeders\DepartmentSeeder::class);
    $this->seed(\Database\Seeders\PositionSeeder::class);
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
});

// ─── Index ────────────────────────────────────────────────────────────────────

it('super-admin dapat melihat employee list', function () {
    actingAsRole('super-admin');
    $this->get(route('employees.index'))->assertOk()->assertViewIs('employees.index');
});

it('hr-admin dapat melihat employee list', function () {
    actingAsRole('hr-admin');
    $this->get(route('employees.index'))->assertOk();
});

// ─── Create ───────────────────────────────────────────────────────────────────

it('super-admin dapat membuka form create', function () {
    actingAsRole('super-admin');
    $this->get(route('employees.create'))->assertOk()->assertViewIs('employees.create');
});

it('super-admin dapat store employee baru', function () {
    actingAsRole('super-admin');
    $employee = makeEmployee(['full_name' => 'Budi Santoso', 'email' => 'budi@orcahr.local']);

    expect(Employee::where('email', 'budi@orcahr.local')->exists())->toBeTrue();
    expect($employee->employee_number)->toStartWith('RKS-');
});

it('employee number di-generate otomatis dengan format RKS-YYYY-NNNN', function () {
    actingAsRole('super-admin');
    $employee = makeEmployee();

    expect($employee->employee_number)->toMatch('/^RKS-\d{4}-\d{4}$/');
});

// ─── Show ─────────────────────────────────────────────────────────────────────

it('super-admin dapat melihat employee detail', function () {
    actingAsRole('super-admin');
    $employee = makeEmployee();

    $this->get(route('employees.show', $employee))
        ->assertOk()
        ->assertViewIs('employees.show')
        ->assertSee($employee->full_name);
});

// ─── Update ───────────────────────────────────────────────────────────────────

it('super-admin dapat membuka form edit', function () {
    actingAsRole('super-admin');
    $employee = makeEmployee();

    $this->get(route('employees.edit', $employee))->assertOk()->assertViewIs('employees.edit');
});

// ─── Delete ───────────────────────────────────────────────────────────────────

it('super-admin dapat soft delete employee', function () {
    actingAsRole('super-admin');
    $employee = makeEmployee();

    $this->delete(route('employees.destroy', $employee))
        ->assertRedirect(route('employees.index'));

    expect(Employee::find($employee->id))->toBeNull();
    expect(Employee::withTrashed()->find($employee->id))->not->toBeNull();
});

// ─── NIK Uniqueness ───────────────────────────────────────────────────────────

it('NIK duplikat ditolak', function () {
    actingAsRole('super-admin');
    makeEmployee(['nik' => '3171234567890001']);

    $service = app(\App\Services\EmployeeService::class);
    expect($service->isNikTaken('3171234567890001'))->toBeTrue();
    expect($service->isNikTaken('3171234567890002'))->toBeFalse();
});
