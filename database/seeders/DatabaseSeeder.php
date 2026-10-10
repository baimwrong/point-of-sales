<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\Setting;
use App\Models\Unit;
use App\Models\Category;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            SettingSeeder::class,
            UnitSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
        ]);
    }
}
