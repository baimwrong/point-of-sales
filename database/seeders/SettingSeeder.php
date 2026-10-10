<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'store_name',     'value' => 'My POS Store',                'group' => 'store'],
            ['key' => 'store_address',  'value' => 'Jl. Contoh No. 1, Jakarta',   'group' => 'store'],
            ['key' => 'store_phone',    'value' => '021-123456',                   'group' => 'store'],
            ['key' => 'store_email',    'value' => 'store@pos.local',              'group' => 'store'],
            ['key' => 'currency_symbol','value' => 'Rp',                           'group' => 'finance'],
            ['key' => 'currency_code',  'value' => 'IDR',                          'group' => 'finance'],
            ['key' => 'tax_rate',       'value' => '11',                           'group' => 'finance'],
            ['key' => 'tax_name',       'value' => 'PPN',                          'group' => 'finance'],
            ['key' => 'invoice_prefix', 'value' => 'INV',                          'group' => 'numbering'],
            ['key' => 'receipt_prefix', 'value' => 'RCT',                          'group' => 'numbering'],
            ['key' => 'purchase_prefix','value' => 'PO',                           'group' => 'numbering'],
            ['key' => 'return_prefix',  'value' => 'RTN',                          'group' => 'numbering'],
            ['key' => 'loyalty_rate',   'value' => '1',                            'group' => 'loyalty'],
            ['key' => 'low_stock_threshold','value' => '5',                        'group' => 'inventory'],
            ['key' => 'timezone',       'value' => 'Asia/Jakarta',                 'group' => 'system'],
            ['key' => 'date_format',    'value' => 'd/m/Y',                        'group' => 'system'],
            ['key' => 'receipt_footer', 'value' => 'Terima kasih telah berbelanja! Barang yang sudah dibeli tidak dapat dikembalikan.', 'group' => 'receipt'],
        ];

        foreach ($settings as $setting) {
            Setting::firstOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
