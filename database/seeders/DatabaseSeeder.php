<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);

        $user = User::factory()->create([
            'name' => 'Admin OrcaHR',
            'email' => 'admin@orcahr.test',
        ]);
        $user->assignRole('super-admin');

        $this->call(CoreHRSeeder::class);
        $this->call(AttendanceSeeder::class);
        $this->call(LeaveTypeSeeder::class);
        $this->call(PayrollComponentSeeder::class);
    }
}
