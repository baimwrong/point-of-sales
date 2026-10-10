<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Unit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create sample Suppliers
        $sup1 = Supplier::firstOrCreate(['name' => 'PT Sumber Pangan Lestari'], [
            'name'       => 'PT Sumber Pangan Lestari',
            'company'    => 'Sumber Pangan Group',
            'email'      => 'order@sumberpangan.co.id',
            'phone'      => '021-5551234',
            'city'       => 'Jakarta Timur',
            'country'    => 'Indonesia',
            'tax_number' => '01.234.567.8-012.000',
            'is_active'  => true,
        ]);

        $sup2 = Supplier::firstOrCreate(['name' => 'CV Mandiri Perkasa'], [
            'name'       => 'CV Mandiri Perkasa',
            'company'    => 'Mandiri Beverage & Snack',
            'email'      => 'sales@mandiriperkasa.com',
            'phone'      => '081298765432',
            'city'       => 'Bandung',
            'country'    => 'Indonesia',
            'tax_number' => '02.345.678.9-023.000',
            'is_active'  => true,
        ]);

        // 2. Create sample Customers
        Customer::firstOrCreate(['email' => 'budi.santoso@gmail.com'], [
            'name'           => 'Budi Santoso',
            'email'          => 'budi.santoso@gmail.com',
            'phone'          => '081234567890',
            'city'           => 'Jakarta Selatan',
            'address'        => 'Jl. Senopati No. 45',
            'loyalty_points' => 120,
            'is_active'      => true,
        ]);

        Customer::firstOrCreate(['email' => 'dewi.lestari@yahoo.com'], [
            'name'           => 'Dewi Lestari',
            'email'          => 'dewi.lestari@yahoo.com',
            'phone'          => '081398765432',
            'city'           => 'Jakarta Barat',
            'address'        => 'Apartemen Mediterania Tower B',
            'loyalty_points' => 350,
            'is_active'      => true,
        ]);

        // 3. Units & Categories references
        $pcs = Unit::where('abbreviation', 'pcs')->first() ?? Unit::first();
        $kg  = Unit::where('abbreviation', 'kg')->first() ?? $pcs;
        $btl = Unit::where('abbreviation', 'btl')->first() ?? $pcs;

        $catMakanan  = Category::where('name', 'Makanan')->first();
        $catMinuman  = Category::where('name', 'Minuman')->first();
        $catSnack    = Category::where('name', 'Snack & Camilan')->first() ?? $catMakanan;
        $catSembako  = Category::where('name', 'Sembako')->first() ?? $catMakanan;
        $catElektronik = Category::where('name', 'Elektronik')->first();

        // 4. Products data
        $products = [
            [
                'name'            => 'Kopi Arabika Gayo 250g',
                'sku'             => 'PRD-0001',
                'barcode'         => '8991001001',
                'category_id'     => $catMinuman?->id,
                'unit_id'         => $pcs->id,
                'cost_price'      => 45000,
                'selling_price'   => 65000,
                'wholesale_price' => 58000,
                'stock_quantity'  => 50,
                'min_stock_alert' => 10,
                'description'     => 'Biji kopi pilihan roasted medium dari tanah Gayo Aceh.',
                'track_stock'     => true,
                'is_active'       => true,
            ],
            [
                'name'            => 'Teh Hijau Celup Melati 25s',
                'sku'             => 'PRD-0002',
                'barcode'         => '8991001002',
                'category_id'     => $catMinuman?->id,
                'unit_id'         => $pcs->id,
                'cost_price'      => 12000,
                'selling_price'   => 18500,
                'wholesale_price' => 16000,
                'stock_quantity'  => 80,
                'min_stock_alert' => 15,
                'description'     => 'Teh melati wangi aroma alami isi 25 sachet.',
                'track_stock'     => true,
                'is_active'       => true,
            ],
            [
                'name'            => 'Air Mineral Pegunungan 600ml',
                'sku'             => 'PRD-0003',
                'barcode'         => '8991001003',
                'category_id'     => $catMinuman?->id,
                'unit_id'         => $btl->id,
                'cost_price'      => 2500,
                'selling_price'   => 4500,
                'wholesale_price' => 3800,
                'stock_quantity'  => 200,
                'min_stock_alert' => 30,
                'description'     => 'Air mineral murni kemasan botol praktis.',
                'track_stock'     => true,
                'is_active'       => true,
            ],
            [
                'name'            => 'Beras Pandan Wangi 5kg',
                'sku'             => 'PRD-0004',
                'barcode'         => '8991001004',
                'category_id'     => $catSembako?->id,
                'unit_id'         => $pcs->id,
                'cost_price'      => 72000,
                'selling_price'   => 89000,
                'wholesale_price' => 84000,
                'stock_quantity'  => 35,
                'min_stock_alert' => 8,
                'description'     => 'Beras pulen aromatik pandan wangi super.',
                'track_stock'     => true,
                'is_active'       => true,
            ],
            [
                'name'            => 'Minyak Goreng Sawit 2L',
                'sku'             => 'PRD-0005',
                'barcode'         => '8991001005',
                'category_id'     => $catSembako?->id,
                'unit_id'         => $pcs->id,
                'cost_price'      => 31000,
                'selling_price'   => 38000,
                'wholesale_price' => 35000,
                'stock_quantity'  => 4, // Intentionally low to trigger alert!
                'min_stock_alert' => 10,
                'description'     => 'Minyak goreng pouch 2 liter jernih dua kali penyaringan.',
                'track_stock'     => true,
                'is_active'       => true,
            ],
            [
                'name'            => 'Keripik Singkong Pedas Manis 150g',
                'sku'             => 'PRD-0006',
                'barcode'         => '8991001006',
                'category_id'     => $catSnack?->id,
                'unit_id'         => $pcs->id,
                'cost_price'      => 9000,
                'selling_price'   => 15000,
                'wholesale_price' => 12500,
                'stock_quantity'  => 45,
                'min_stock_alert' => 10,
                'description'     => 'Keripik singkong renyah bumbu balado pedas manis.',
                'track_stock'     => true,
                'is_active'       => true,
            ],
            [
                'name'            => 'Biskuit Cokelat Sandwich 120g',
                'sku'             => 'PRD-0007',
                'barcode'         => '8991001007',
                'category_id'     => $catSnack?->id,
                'unit_id'         => $pcs->id,
                'cost_price'      => 7500,
                'selling_price'   => 11500,
                'wholesale_price' => 9500,
                'stock_quantity'  => 60,
                'min_stock_alert' => 12,
                'description'     => 'Biskuit renyah dengan krim cokelat lezat.',
                'track_stock'     => true,
                'is_active'       => true,
            ],
            [
                'name'            => 'Kabel Data Type-C Fast Charging 1m',
                'sku'             => 'PRD-0008',
                'barcode'         => '8991001008',
                'category_id'     => $catElektronik?->id,
                'unit_id'         => $pcs->id,
                'cost_price'      => 18000,
                'selling_price'   => 35000,
                'wholesale_price' => 28000,
                'stock_quantity'  => 25,
                'min_stock_alert' => 5,
                'description'     => 'Kabel data braided kuat support 65W fast charge.',
                'track_stock'     => true,
                'is_active'       => true,
            ],
        ];

        foreach ($products as $item) {
            $item['slug'] = Str::slug($item['name']) . '-' . Str::random(4);
            Product::firstOrCreate(['sku' => $item['sku']], $item);
        }
    }
}
