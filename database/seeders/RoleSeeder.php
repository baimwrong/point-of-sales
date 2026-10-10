<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'admin',   'display_name' => 'Administrator', 'description' => 'Full system access'],
            ['name' => 'manager', 'display_name' => 'Manager',       'description' => 'Manage products, view reports'],
            ['name' => 'cashier', 'display_name' => 'Cashier',       'description' => 'Process sales at POS terminal'],
        ];
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role['name']], $role);
        }
    }
}
