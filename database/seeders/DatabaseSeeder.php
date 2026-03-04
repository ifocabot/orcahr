<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);

        // Super Admin default — unsetEventDispatcher agar Auditable tidak jalan saat seed
        User::unsetEventDispatcher();

        $admin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@orcahr.local',
            'password' => Hash::make('password'),
        ]);

        User::setEventDispatcher(app('events'));

        $admin->assignRole('super-admin');
    }
}
