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
        // Call RoleSeeder to ensure roles exist
        $this->call(RoleSeeder::class);

        // Create an admin user
        $admin = User::factory()->create([
            'user_name' => 'Admin User',
            'email' => 'admin@example.com',
            'phone' => '01012345678',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole('admin');

        // Create 5 customers
        User::factory(5)->create()->each(function ($user) {
            $user->assignRole('customer');
        });

        // Create 5 merchants
        User::factory(5)->create()->each(function ($user) {
            $user->assignRole('merchant');
        });
    }
}
