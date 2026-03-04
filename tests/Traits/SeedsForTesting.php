<?php

namespace Tests\Traits;

use Database\Seeders\DepartmentSeeder;
use Database\Seeders\JobLevelSeeder;
use Database\Seeders\PositionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Spatie\Permission\PermissionRegistrar;

/**
 * Seed roles, permissions, departments, positions, dan job levels
 * sebelum tiap test dan reset Spatie permission cache.
 */
trait SeedsForTesting
{
    protected function setUpSeedsForTesting(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->seed(RolePermissionSeeder::class);
        $this->seed(JobLevelSeeder::class);
        $this->seed(DepartmentSeeder::class);
        $this->seed(PositionSeeder::class);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
