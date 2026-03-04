<?php

use App\Models\Department;
use App\Models\Employee;
use App\Models\JobLevel;
use App\Models\Position;
use App\Models\User;
use App\Services\EmployeeService;

/*
|--------------------------------------------------------------------------
| Test Case Setup
|--------------------------------------------------------------------------
*/

pest()->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature');

pest()->extend(Tests\TestCase::class)
    ->in('Unit');

/*
|--------------------------------------------------------------------------
| Global Helpers
|--------------------------------------------------------------------------
*/

function actingAsRole(string $role): User
{
    $user = User::factory()->create();
    $user->assignRole($role);
    test()->actingAs($user);
    return $user;
}

function makeEmployee(array $personalOverrides = [], array $employmentOverrides = []): Employee
{
    $dept = Department::first();
    $position = $dept ? Position::where('department_id', $dept->id)->first() : null;
    $level = JobLevel::first();

    $personal = array_merge([
        'full_name' => 'Test Employee',
        'email' => 'test.' . uniqid() . '@orcahr.local',
        'gender' => 'male',
    ], $personalOverrides);

    $employment = array_merge([
        'department_id' => $dept?->id,
        'position_id' => $position?->id,
        'job_level_id' => $level?->id,
        'employment_status' => 'permanent',
        'join_date' => '2024-01-01',
    ], $employmentOverrides);

    return app(EmployeeService::class)->create($personal, $employment);
}
