// database/seeders/UserSeeder.php
<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Create roles first
        $roles = [
            ['id' => 1, 'name' => 'admin', 'display_name' => 'Administrator'],
            ['id' => 2, 'name' => 'user', 'display_name' => 'Regular User'],
            ['id' => 3, 'name' => 'investor', 'display_name' => 'Investor'],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['id' => $role['id']], $role);
        }

        // Create admin user
        User::updateOrCreate(
            ['email' => 'admin@codebase.com'],
            [
                'username' => 'admin',
                'phone' => '081234567890',
                'password' => Hash::make('admin123'),
                'role_id' => 1,
                'balance' => 10000000,
                'verified_phone' => true,
            ]
        );

        // Create regular user
        User::updateOrCreate(
            ['email' => 'user@codebase.com'],
            [
                'username' => 'user',
                'phone' => '081234567891',
                'password' => Hash::make('user123'),
                'role_id' => 2,
                'balance' => 500000,
                'verified_phone' => true,
            ]
        );

        // Create investor user
        User::updateOrCreate(
            ['email' => 'investor@codebase.com'],
            [
                'username' => 'investor',
                'phone' => '081234567892',
                'password' => Hash::make('investor123'),
                'role_id' => 3,
                'balance' => 5000000,
                'verified_phone' => true,
            ]
        );
    }
}