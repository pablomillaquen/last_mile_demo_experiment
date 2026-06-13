<?php

namespace Database\Seeders;

use App\Models\Package;
use App\Models\Route;
use App\Models\RoutePackage;
use Illuminate\Database\Seeder;

class RouteMeasurementDemoSeeder extends Seeder
{
    private string $bodegaLat = '-33.045';
    private string $bodegaLng = '-71.620';

    private array $usedCoords = [];

    public function run(): void
    {
        $routeProfiles = [
            'Ruta A' => $this->buildCompactaEficiente(),
            'Ruta B' => $this->buildDispersa(),
            'Ruta C' => $this->buildZigzag(),
            'Ruta D' => $this->buildBarridoLimpio(),
            'Ruta E' => $this->buildOutlier(),
        ];

        $bar = $this->command->getOutput()->createProgressBar(150 + 5 + 150);
        $bar->start();

        $routes = [];
        foreach ($routeProfiles as $name => $points) {
            $route = Route::create([
                'name' => $name,
                'route_date' => now()->format('Y-m-d'),
                'notes' => $this->routeNotes($name),
            ]);
            $routes[$name] = $route;
            $bar->advance();
        }

        foreach ($routeProfiles as $name => $points) {
            $route = $routes[$name];
            foreach ($points as $seq => $loc) {
                $pkg = Package::create([
                    'tracking_number' => $loc['tracking'],
                    'recipient_name' => fake()->name(),
                    'delivery_address' => $loc['address'],
                    'district' => $loc['district'],
                    'city' => $loc['city'],
                    'latitude' => $loc['lat'],
                    'longitude' => $loc['lng'],
                    'received_at' => fake()->dateTimeBetween('-14 days', 'now'),
                ]);
                RoutePackage::create([
                    'route_id' => $route->id,
                    'package_id' => $pkg->id,
                    'sequence' => $seq + 1,
                    'assigned_at' => now(),
                ]);
                $bar->advance();
            }
        }

        $bar->finish();
        $this->command->newLine();
        $this->command->info('Creados 150 paquetes en 5 rutas con perfiles diferenciados.');
    }

    private function routeNotes(string $name): string
    {
        return match ($name) {
            'Ruta A' => 'Cluster compacto en Concón — recorrido eficiente (servido en espiral)',
            'Ruta B' => 'Puntos dispersos en las 5 comunas — alta dispersión geográfica',
            'Ruta C' => 'Alternancia Viña Centro↔Miraflores — secuencia zigzag con cruces',
            'Ruta D' => 'Barrido limpio costa Concón→Reñaca — sin cruces ni retrocesos',
            'Ruta E' => '26 pts Concón + 4 pts Quilpué — dos outliers muy alejados del cluster principal',
            default => '',
        };
    }

    private function coord(float $lat, float $lng, string $address, string $district, string $city, string $trackingPrefix, int $index): array
    {
        $key = round($lat, 6) . ',' . round($lng, 6);
        if (isset($this->usedCoords[$key])) {
            $lat += 0.0002 * ($index % 5);
            $lng += 0.0003 * ($index % 3);
            $key = round($lat, 6) . ',' . round($lng, 6);
        }
        $this->usedCoords[$key] = true;

        return [
            'lat' => round($lat, 6),
            'lng' => round($lng, 6),
            'address' => $address,
            'district' => $district,
            'city' => $city,
            'tracking' => $trackingPrefix . '-' . str_pad((string) $index, 4, '0', STR_PAD_LEFT),
        ];
    }

    private function near(float $baseLat, float $baseLng, float $maxDlat, float $maxDlng, int $attempt = 0): array
    {
        $dlat = (fake()->randomFloat(5, 0, $maxDlat)) * (fake()->boolean() ? 1 : -1);
        $dlng = (fake()->randomFloat(5, 0, $maxDlng)) * (fake()->boolean() ? 1 : -1);
        $lat = round($baseLat + $dlat, 6);
        $lng = round($baseLng + $dlng, 6);
        $key = "$lat,$lng";
        if (isset($this->usedCoords[$key]) && $attempt < 20) {
            return $this->near($baseLat, $baseLng, $maxDlat, $maxDlng, $attempt + 1);
        }
        return [$lat, $lng];
    }

    private function buildCompactaEficiente(): array
    {
        $points = [];
        $streets = ['Av. Maroto', 'Av. Borgoño', 'Calle Las Pimpinelas', 'Calle Los Lilenes', 'Av. Concón'];

        for ($i = 0; $i < 30; $i++) {
            [$lat, $lng] = $this->near(-32.929, -71.522, 0.0025, 0.0025);
            $street = $streets[$i % count($streets)];
            $points[] = $this->coord(
                $lat, $lng,
                "$street " . fake()->numberBetween(100, 9999),
                'Concón', 'Viña del Mar',
                'RTAA', $i + 1
            );
        }

        $centerLat = array_sum(array_column($points, 'lat')) / count($points);
        $centerLng = array_sum(array_column($points, 'lng')) / count($points);
        usort($points, function ($a, $b) use ($centerLat, $centerLng) {
            $distA = hypot($a['lat'] - $centerLat, $a['lng'] - $centerLng);
            $distB = hypot($b['lat'] - $centerLat, $b['lng'] - $centerLng);
            return $distA <=> $distB;
        });

        return $points;
    }

    private function buildDispersa(): array
    {
        $clusters = [
            ['city' => 'Viña del Mar', 'district' => 'Concón',         'lat' => -32.928, 'lng' => -71.522],
            ['city' => 'Viña del Mar', 'district' => 'Reñaca',         'lat' => -32.972, 'lng' => -71.540],
            ['city' => 'Viña del Mar', 'district' => 'Centro',         'lat' => -33.025, 'lng' => -71.548],
            ['city' => 'Valparaíso',   'district' => 'Cerro Alegre',   'lat' => -33.044, 'lng' => -71.627],
            ['city' => 'Quilpué',      'district' => 'El Belloto',     'lat' => -33.057, 'lng' => -71.413],
            ['city' => 'Villa Alemana','district' => 'Población Vergara', 'lat' => -33.046, 'lng' => -71.364],
        ];

        $streetsByDistrict = [
            'Concón'            => ['Av. Maroto', 'Av. Borgoño', 'Calle Las Pimpinelas', 'Calle Los Lilenes', 'Av. Concón'],
            'Reñaca'            => ['Av. Borgoño', 'Calle Los Héroes', 'Av. del Mar', 'Calle El Golf', 'Pasaje Las Brisas'],
            'Centro'            => ['Av. Libertad', 'Av. Valparaíso', '1 Norte', 'Quillota', 'Av. San Martín'],
            'Cerro Alegre'      => ['Paseo Yugoslavo', 'Almirante Montt', 'Miramar', 'Calle Hein', 'Calle Urriola'],
            'El Belloto'        => ['Av. Freire', 'Calle Baden Powell', 'Calle Las Dalias', 'Calle Los Jazmines', 'Av. El Belloto'],
            'Población Vergara' => ['Calle Santiago', 'Av. Segunda', 'Calle Vergara', 'Av. Los Alerces', 'Calle Las Hortensias'],
        ];

        $allStreets = [
            'Concón' => ['Av. Maroto', 'Av. Borgoño', 'Calle Las Pimpinelas'],
            'Reñaca' => ['Av. Borgoño', 'Av. del Mar'],
            'Centro' => ['Av. Libertad', 'Av. Valparaíso', '1 Norte', 'Quillota'],
            'Cerro Alegre' => ['Paseo Yugoslavo', 'Almirante Montt', 'Miramar'],
            'El Belloto' => ['Av. Freire', 'Calle Baden Powell'],
            'Población Vergara' => ['Calle Santiago', 'Av. Segunda'],
        ];

        $points = [];
        $ptsPerCluster = 5;

        foreach ($clusters as $cluster) {
            $streets = $allStreets[$cluster['district']];
            for ($j = 0; $j < $ptsPerCluster; $j++) {
                [$lat, $lng] = $this->near($cluster['lat'], $cluster['lng'], 0.004, 0.004);
                $street = $streets[$j % count($streets)];
                $number = fake()->numberBetween(100, 9999);

                $points[] = $this->coord(
                    $lat, $lng,
                    "$street $number",
                    $cluster['district'], $cluster['city'],
                    'RTAB', count($points) + 1
                );
            }
        }

        $order = [
            0, 1, 2, 3, 4,
            5, 6, 7, 8, 9,
            10, 11, 12, 13, 14,
            15, 16, 17, 18, 19,
            20, 21, 22, 23, 24,
            25, 26, 27, 28, 29,
        ];
        $sorted = [];
        foreach ($order as $idx) {
            $sorted[] = $points[$idx];
        }

        return $sorted;
    }

    private function buildZigzag(): array
    {
        $clusterA = ['lat' => -33.024, 'lng' => -71.555, 'city' => 'Viña del Mar', 'district' => 'Centro'];
        $clusterB = ['lat' => -33.024, 'lng' => -71.522, 'city' => 'Viña del Mar', 'district' => 'Miraflores'];

        $streetsA = ['Av. Libertad', 'Av. Valparaíso', '1 Norte', 'Quillota', 'Av. San Martín'];
        $streetsB = ['Av. Eduardo Frei', 'Calle Limache', 'Av. Gómez Carreño', 'Calle Miraflores', 'Calle Las Dalias'];

        $groupA = [];
        $groupB = [];

        for ($i = 0; $i < 15; $i++) {
            [$lat, $lng] = $this->near($clusterA['lat'], $clusterA['lng'], 0.005, 0.005);
            $street = $streetsA[$i % count($streetsA)];
            $groupA[] = $this->coord(
                $lat, $lng,
                "$street " . fake()->numberBetween(100, 9999),
                $clusterA['district'], $clusterA['city'],
                'RTAC', $i + 1
            );

            [$lat2, $lng2] = $this->near($clusterB['lat'], $clusterB['lng'], 0.005, 0.005);
            $street2 = $streetsB[$i % count($streetsB)];
            $groupB[] = $this->coord(
                $lat2, $lng2,
                "$street2 " . fake()->numberBetween(100, 9999),
                $clusterB['district'], $clusterB['city'],
                'RTAC', $i + 16
            );
        }

        $points = [];
        for ($i = 0; $i < 15; $i++) {
            $points[] = $groupA[$i];
            $points[] = $groupB[$i];
        }

        return $points;
    }

    private function buildBarridoLimpio(): array
    {
        $points = [];
        $streets = ['Av. Concón', 'Av. Maroto', 'Av. Borgoño', 'Calle Los Lilenes', 'Av. del Mar'];

        for ($i = 0; $i < 30; $i++) {
            $latBase = -32.928 - ($i * 0.002);
            $lngBase = -71.522 + ($i * 0.0008);
            [$lat, $lng] = $this->near($latBase, $lngBase, 0.001, 0.001);
            $street = $streets[$i % count($streets)];
            $points[] = $this->coord(
                $lat, $lng,
                "$street " . fake()->numberBetween(100, 9999),
                'Concón', 'Viña del Mar',
                'RTAD', $i + 1
            );
        }

        return $points;
    }

    private function buildOutlier(): array
    {
        $points = [];

        $conconStreets = ['Av. Maroto', 'Av. Borgoño', 'Calle Las Pimpinelas', 'Calle Los Lilenes', 'Av. Concón'];

        for ($i = 0; $i < 26; $i++) {
            [$lat, $lng] = $this->near(-32.928, -71.522, 0.005, 0.005);
            $street = $conconStreets[$i % count($conconStreets)];
            $points[] = $this->coord(
                $lat, $lng,
                "$street " . fake()->numberBetween(100, 9999),
                'Concón', 'Viña del Mar',
                'RTAE', $i + 1
            );
        }

        $outlierStreets = ['Av. Freire', 'Calle Baden Powell', 'Calle Las Dalias', 'Av. El Belloto'];
        for ($i = 0; $i < 4; $i++) {
            [$lat, $lng] = $this->near(-33.057, -71.413, 0.003, 0.003);
            $street = $outlierStreets[$i % count($outlierStreets)];
            $points[] = $this->coord(
                $lat, $lng,
                "$street " . fake()->numberBetween(100, 9999),
                'El Belloto', 'Quilpué',
                'RTAE', $i + 27
            );
        }

        return $points;
    }
}
