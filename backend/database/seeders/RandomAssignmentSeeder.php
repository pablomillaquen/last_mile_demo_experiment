<?php

namespace Database\Seeders;

use App\Models\Package;
use App\Models\Route;
use App\Models\RoutePackage;
use Illuminate\Database\Seeder;

class RandomAssignmentSeeder extends Seeder
{
    public function run(): void
    {
        $routes = Route::all();
        if ($routes->isEmpty()) {
            $this->command->warn('No hay rutas. Ejecuta primero DemoDatasetSeeder.');
            return;
        }

        $packages = Package::whereDoesntHave('routePackage')->get();
        if ($packages->isEmpty()) {
            $this->command->info('Todos los paquetes ya están asignados.');
            return;
        }

        $seq = RoutePackage::max('sequence') + 1;
        $bar = $this->command->getOutput()->createProgressBar($packages->count());
        $bar->start();

        foreach ($packages as $package) {
            RoutePackage::create([
                'route_id' => $routes->random()->id,
                'package_id' => $package->id,
                'sequence' => $seq++,
                'assigned_at' => now(),
            ]);
            $bar->advance();
        }

        $bar->finish();
        $this->command->newLine();
        $this->command->info("Asignados {$packages->count()} paquetes a {$routes->count()} rutas.");
    }
}
