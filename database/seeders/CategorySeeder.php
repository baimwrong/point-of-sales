<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Makanan',
            'Minuman',
            'Elektronik',
            'Pakaian',
            'Kesehatan & Kecantikan',
            'Perlengkapan Rumah',
            'Snack & Camilan',
            'Sembako',
        ];
        foreach ($categories as $i => $name) {
            Category::firstOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name'       => $name,
                    'slug'       => Str::slug($name),
                    'is_active'  => true,
                    'sort_order' => $i,
                ]
            );
        }
    }
}
