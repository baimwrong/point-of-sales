<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole   = Role::where('name', 'admin')->first();
        $cashierRole = Role::where('name', 'cashier')->first();

        User::firstOrCreate(
            ['email' => 'admin@pos.local'],
            [
                'name'     => 'Administrator',
                'role_id'  => $adminRole->id,
                'password' => Hash::make('password'),
                'is_active'=> true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'cashier@pos.local'],
            [
                'name'     => 'Cashier One',
                'role_id'  => $cashierRole->id,
                'password' => Hash::make('password'),
                'is_active'=> true,
            ]
        );
    }
}
