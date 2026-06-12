<?php

namespace Database\Seeders;

use App\Models\Package;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        Package::factory(50)->create();
    }
}
