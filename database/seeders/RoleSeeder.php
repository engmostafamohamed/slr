<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::updateOrCreate(['name' => 'admin', 'guard_name' => 'web'], ['name' => 'admin']);
        Role::updateOrCreate(['name' => 'customer', 'guard_name' => 'web'], ['name' => 'customer']);
        Role::updateOrCreate(['name' => 'merchant', 'guard_name' => 'web'], ['name' => 'merchant']);
    }
}
