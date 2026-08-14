<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Seed the application's default roles.
     */
    public function run(): void
    {
        $roles = [
            'Admin',
            'Manager',
            'Sales',
            'Service Advisor',
            'Customer',
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }
    }
}
