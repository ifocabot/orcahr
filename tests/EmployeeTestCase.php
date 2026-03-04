<?php

namespace Tests;

use Database\Seeders\DepartmentSeeder;
use Database\Seeders\JobLevelSeeder;
use Database\Seeders\PositionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;

abstract class EmployeeTestCase extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Reset Spatie cache sebelum setiap test
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->seed(RolePermissionSeeder::class);
        $this->seed(JobLevelSeeder::class);
        $this->seed(DepartmentSeeder::class);
        $this->seed(PositionSeeder::class);

        // Reset lagi setelah seed agar role/permission baru terbaca
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
