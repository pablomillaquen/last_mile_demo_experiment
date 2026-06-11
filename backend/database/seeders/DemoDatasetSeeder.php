<?php

namespace Database\Seeders;

use App\Models\Package;
use App\Models\Route;
use App\Models\RoutePackage;
use Illuminate\Database\Seeder;

class DemoDatasetSeeder extends Seeder
{
    private array $districts = [
        ['city' => 'Valparaíso', 'district' => 'Cerro Alegre', 'lat' => -33.0458, 'lng' => -71.6197],
        ['city' => 'Valparaíso', 'district' => 'Cerro Concepción', 'lat' => -33.0440, 'lng' => -71.6220],
        ['city' => 'Valparaíso', 'district' => 'Cerro Bellavista', 'lat' => -33.0500, 'lng' => -71.6100],
        ['city' => 'Valparaíso', 'district' => 'Plan', 'lat' => -33.0478, 'lng' => -71.6260],
        ['city' => 'Valparaíso', 'district' => 'Puerto', 'lat' => -33.0400, 'lng' => -71.6300],
        ['city' => 'Valparaíso', 'district' => 'Cerro Placeres', 'lat' => -33.0550, 'lng' => -71.5950],
        ['city' => 'Valparaíso', 'district' => 'Cerro Barón', 'lat' => -33.0420, 'lng' => -71.6150],
        ['city' => 'Valparaíso', 'district' => 'Polanco', 'lat' => -33.0520, 'lng' => -71.6200],
        ['city' => 'Viña del Mar', 'district' => 'Centro', 'lat' => -33.0245, 'lng' => -71.5518],
        ['city' => 'Viña del Mar', 'district' => 'Reñaca', 'lat' => -32.9667, 'lng' => -71.5500],
        ['city' => 'Viña del Mar', 'district' => 'Concón', 'lat' => -32.9200, 'lng' => -71.5300],
        ['city' => 'Viña del Mar', 'district' => 'Jardín del Mar', 'lat' => -33.0100, 'lng' => -71.5400],
        ['city' => 'Viña del Mar', 'district' => 'Santa Inés', 'lat' => -33.0300, 'lng' => -71.5600],
        ['city' => 'Viña del Mar', 'district' => 'Miraflores', 'lat' => -33.0200, 'lng' => -71.5300],
        ['city' => 'Viña del Mar', 'district' => 'Bosque del Mar', 'lat' => -32.9900, 'lng' => -71.5400],
        ['city' => 'Viña del Mar', 'district' => 'Chorrillos', 'lat' => -33.0000, 'lng' => -71.5600],
        ['city' => 'Quilpué', 'district' => 'Centro', 'lat' => -33.0500, 'lng' => -71.4500],
        ['city' => 'Quilpué', 'district' => 'El Belloto', 'lat' => -33.0600, 'lng' => -71.4300],
        ['city' => 'Quilpué', 'district' => 'Villa Los Héroes', 'lat' => -33.0550, 'lng' => -71.4400],
        ['city' => 'Villa Alemana', 'district' => 'Centro', 'lat' => -33.0422, 'lng' => -71.3730],
        ['city' => 'Villa Alemana', 'district' => 'Población Vergara', 'lat' => -33.0480, 'lng' => -71.3650],
        ['city' => 'Villa Alemana', 'district' => 'El Sauce', 'lat' => -33.0380, 'lng' => -71.3800],
    ];

    public function run(): void
    {
        $packages = [];
        for ($i = 0; $i < 100; $i++) {
            $loc = $this->districts[array_rand($this->districts)];
            $packages[] = Package::create([
                'tracking_number' => 'DEMO-' . str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                'recipient_name' => fake()->name(),
                'delivery_address' => fake()->streetAddress() . ' #' . random_int(100, 9999),
                'district' => $loc['district'],
                'city' => $loc['city'],
                'latitude' => $loc['lat'] + fake()->randomFloat(5, -0.003, 0.003),
                'longitude' => $loc['lng'] + fake()->randomFloat(5, -0.003, 0.003),
                'received_at' => fake()->dateTimeBetween('-14 days', 'now'),
            ]);
        }

        $routes = [];
        for ($i = 0; $i < 5; $i++) {
            $routes[] = Route::create([
                'name' => 'Ruta ' . chr(65 + $i),
                'route_date' => now()->addDays($i)->format('Y-m-d'),
                'notes' => 'Ruta de demostración ' . ($i + 1),
            ]);
        }

        // Deliberately inefficient assignments:
        // Ruta A (index 0): Cerro Alegre + Villa Alemana + Concón — geographically scattered
        $assignments = [
            0 => [0, 1, 2, 19, 20, 21, 10, 11],     // Ruta A: scattered
            1 => [3, 4, 5, 12, 13, 14],               // Ruta B: mixed Valpo/Viña
            2 => [6, 7, 8, 15, 16, 17],               // Ruta C: mixed
            3 => [9, 18],                              // Ruta D: few packages
        ];

        $seq = 1;
        foreach ($assignments as $routeIdx => $districtIdxs) {
            $route = $routes[$routeIdx];
            foreach ($districtIdxs as $distIdx) {
                // Find a package with matching district
                $matching = null;
                foreach ($packages as $pkg) {
                    if ($pkg->district === $this->districts[$distIdx]['district']) {
                        $already = RoutePackage::where('package_id', $pkg->id)->exists();
                        if (!$already) {
                            $matching = $pkg;
                            break;
                        }
                    }
                }
                if ($matching) {
                    RoutePackage::create([
                        'route_id' => $route->id,
                        'package_id' => $matching->id,
                        'sequence' => $seq++,
                        'assigned_at' => now(),
                    ]);
                }
            }
        }
    }
}
