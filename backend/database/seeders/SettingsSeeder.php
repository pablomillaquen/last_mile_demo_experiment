<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        Setting::upsert([
            ['key' => 'warehouse_lat', 'value' => '-33.0450000'],
            ['key' => 'warehouse_lng', 'value' => '-71.6200000'],
            ['key' => 'average_speed_kmh', 'value' => '30'],
        ], 'key');
    }
}
