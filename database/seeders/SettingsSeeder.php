<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['group' => 'general', 'key' => 'app_name', 'value' => 'PharmaCore', 'type' => 'string'],
            ['group' => 'general', 'key' => 'currency', 'value' => 'PKR', 'type' => 'string'],
            ['group' => 'general', 'key' => 'currency_symbol', 'value' => 'Rs', 'type' => 'string'],
            ['group' => 'general', 'key' => 'timezone', 'value' => 'Asia/Karachi', 'type' => 'string'],
            ['group' => 'tax', 'key' => 'default_tax_percent', 'value' => '0', 'type' => 'integer'],
            ['group' => 'invoice', 'key' => 'invoice_prefix', 'value' => 'INV', 'type' => 'string'],
            ['group' => 'invoice', 'key' => 'invoice_footer', 'value' => 'Thank you for your purchase.', 'type' => 'string'],
            ['group' => 'pos', 'key' => 'enable_fefo', 'value' => '1', 'type' => 'boolean'],
            ['group' => 'pos', 'key' => 'block_expired_sale', 'value' => '1', 'type' => 'boolean'],
            ['group' => 'inventory', 'key' => 'near_expiry_days', 'value' => '90', 'type' => 'integer'],
            ['group' => 'security', 'key' => 'session_timeout_minutes', 'value' => '120', 'type' => 'integer'],
            ['group' => 'security', 'key' => 'enable_two_factor', 'value' => '0', 'type' => 'boolean'],
        ];

        foreach ($settings as $s) {
            Setting::firstOrCreate(
                ['branch_id' => null, 'key' => $s['key']],
                ['group' => $s['group'], 'value' => $s['value'], 'type' => $s['type']]
            );
        }
    }
}
