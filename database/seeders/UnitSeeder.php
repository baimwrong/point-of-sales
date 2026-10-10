<?php

namespace Database\Seeders;

use App\Models\Unit;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            ['name' => 'Piece',   'abbreviation' => 'pcs'],
            ['name' => 'Kilogram','abbreviation' => 'kg'],
            ['name' => 'Gram',    'abbreviation' => 'g'],
            ['name' => 'Liter',   'abbreviation' => 'L'],
            ['name' => 'Meter',   'abbreviation' => 'm'],
            ['name' => 'Box',     'abbreviation' => 'box'],
            ['name' => 'Pack',    'abbreviation' => 'pack'],
            ['name' => 'Lusin',   'abbreviation' => 'lusin'],
            ['name' => 'Botol',   'abbreviation' => 'btl'],
            ['name' => 'Karton',  'abbreviation' => 'krt'],
        ];
        foreach ($units as $unit) {
            Unit::firstOrCreate(['name' => $unit['name']], $unit);
        }
    }
}
