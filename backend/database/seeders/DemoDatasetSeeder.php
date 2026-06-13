<?php

namespace Database\Seeders;

use App\Models\Package;
use App\Models\Route;
use App\Models\RoutePackage;
use Illuminate\Database\Seeder;

class DemoDatasetSeeder extends Seeder
{
    private array $baseLocations = [
        ['address' => 'Paseo Yugoslavo 176',    'district' => 'Cerro Alegre',      'city' => 'Valparaíso',    'lat' => -33.044155, 'lng' => -71.628867],
        ['address' => 'Almirante Montt 453',    'district' => 'Cerro Alegre',      'city' => 'Valparaíso',    'lat' => -33.044234, 'lng' => -71.626244],
        ['address' => 'Miramar 175',            'district' => 'Cerro Alegre',      'city' => 'Valparaíso',    'lat' => -33.044812, 'lng' => -71.628118],
        ['address' => 'Paseo Gervasoni 210',    'district' => 'Cerro Concepción',  'city' => 'Valparaíso',    'lat' => -33.042788, 'lng' => -71.626644],
        ['address' => 'Templeman 176',          'district' => 'Cerro Concepción',  'city' => 'Valparaíso',    'lat' => -33.043512, 'lng' => -71.624733],
        ['address' => 'Papudo 540',             'district' => 'Cerro Concepción',  'city' => 'Valparaíso',    'lat' => -33.043255, 'lng' => -71.625411],
        ['address' => 'Hector Calvo 215',       'district' => 'Cerro Bellavista',  'city' => 'Valparaíso',    'lat' => -33.049211, 'lng' => -71.616322],
        ['address' => 'Rudolph 112',            'district' => 'Cerro Bellavista',  'city' => 'Valparaíso',    'lat' => -33.049633, 'lng' => -71.616744],
        ['address' => 'Ferrari 284',            'district' => 'Cerro Bellavista',  'city' => 'Valparaíso',    'lat' => -33.048755, 'lng' => -71.616911],
        ['address' => 'Av. Brasil 850',         'district' => 'Plan',              'city' => 'Valparaíso',    'lat' => -33.042511, 'lng' => -71.620211],
        ['address' => 'Condell 1240',           'district' => 'Plan',              'city' => 'Valparaíso',    'lat' => -33.045433, 'lng' => -71.621255],
        ['address' => 'Av. Pedro Montt 1820',   'district' => 'Plan',              'city' => 'Valparaíso',    'lat' => -33.047811, 'lng' => -71.617511],
        ['address' => 'Plaza Sotomayor 50',     'district' => 'Puerto',            'city' => 'Valparaíso',    'lat' => -33.038144, 'lng' => -71.629122],
        ['address' => 'Cochrane 812',           'district' => 'Puerto',            'city' => 'Valparaíso',    'lat' => -33.039011, 'lng' => -71.628122],
        ['address' => 'Blanco 620',             'district' => 'Puerto',            'city' => 'Valparaíso',    'lat' => -33.038455, 'lng' => -71.627233],
        ['address' => 'Av. Placeres 1290',      'district' => 'Cerro Placeres',   'city' => 'Valparaíso',    'lat' => -33.048511, 'lng' => -71.596244],
        ['address' => 'Av. España 1680',        'district' => 'Cerro Placeres',   'city' => 'Valparaíso',    'lat' => -33.043811, 'lng' => -71.597522],
        ['address' => 'Av. Diego Portales 542', 'district' => 'Cerro Barón',       'city' => 'Valparaíso',    'lat' => -33.042811, 'lng' => -71.609511],
        ['address' => 'Tocornal 450',           'district' => 'Cerro Barón',       'city' => 'Valparaíso',    'lat' => -33.041855, 'lng' => -71.608022],
        ['address' => 'Av. Simpson 180',        'district' => 'Polanco',           'city' => 'Valparaíso',    'lat' => -33.051511, 'lng' => -71.612822],
        ['address' => 'Valdenegro 245',         'district' => 'Polanco',           'city' => 'Valparaíso',    'lat' => -33.053233, 'lng' => -71.612011],
        ['address' => 'Av. Libertad 450',       'district' => 'Centro',            'city' => 'Viña del Mar', 'lat' => -33.018211, 'lng' => -71.549822],
        ['address' => 'Av. Valparaíso 651',     'district' => 'Centro',            'city' => 'Viña del Mar', 'lat' => -33.024522, 'lng' => -71.553211],
        ['address' => '1 Norte 850',            'district' => 'Centro',            'city' => 'Viña del Mar', 'lat' => -33.026211, 'lng' => -71.545233],
        ['address' => 'Quillota 120',           'district' => 'Centro',            'city' => 'Viña del Mar', 'lat' => -33.029211, 'lng' => -71.550544],
        ['address' => 'Av. Borgoño 14200',      'district' => 'Reñaca',            'city' => 'Viña del Mar', 'lat' => -32.969811, 'lng' => -71.543522],
        ['address' => 'Av. Gastón Hamel 350',   'district' => 'Reñaca',            'city' => 'Viña del Mar', 'lat' => -32.973522, 'lng' => -71.536811],
        ['address' => 'General Carrera 240',    'district' => 'Reñaca',            'city' => 'Viña del Mar', 'lat' => -32.972233, 'lng' => -71.541544],
        ['address' => 'Av. Maroto 1250',        'district' => 'Concón',            'city' => 'Viña del Mar', 'lat' => -32.923811, 'lng' => -71.518822],
        ['address' => 'Av. Borgoño 25000',      'district' => 'Concón',            'city' => 'Viña del Mar', 'lat' => -32.928233, 'lng' => -71.523544],
        ['address' => 'Calle Las Pimpinelas 905','district' => 'Concón',            'city' => 'Viña del Mar', 'lat' => -32.932511, 'lng' => -71.521822],
        ['address' => 'Los Sargazos 120',       'district' => 'Jardín del Mar',    'city' => 'Viña del Mar', 'lat' => -32.983211, 'lng' => -71.541233],
        ['address' => 'Los Alerces 240',        'district' => 'Jardín del Mar',    'city' => 'Viña del Mar', 'lat' => -32.986511, 'lng' => -71.539822],
        ['address' => 'Calle 24 Norte 1025',    'district' => 'Santa Inés',        'city' => 'Viña del Mar', 'lat' => -33.011211, 'lng' => -71.544822],
        ['address' => 'Quillota 1850',          'district' => 'Santa Inés',        'city' => 'Viña del Mar', 'lat' => -33.012533, 'lng' => -71.546211],
        ['address' => 'Av. Eduardo Frei 2050',  'district' => 'Miraflores',        'city' => 'Viña del Mar', 'lat' => -33.016511, 'lng' => -71.518522],
        ['address' => 'Calle Limache 3405',     'district' => 'Miraflores',        'city' => 'Viña del Mar', 'lat' => -33.031522, 'lng' => -71.525011],
        ['address' => 'Av. Bosques de Montemar 550','district' => 'Bosque del Mar','city' => 'Viña del Mar', 'lat' => -32.955511, 'lng' => -71.528522],
        ['address' => 'Los Arrayanes 180',      'district' => 'Bosque del Mar',    'city' => 'Viña del Mar', 'lat' => -32.958211, 'lng' => -71.531233],
        ['address' => 'Av. Alvarez 2390',       'district' => 'Chorrillos',        'city' => 'Viña del Mar', 'lat' => -33.029811, 'lng' => -71.538822],
        ['address' => 'Lusitania 110',          'district' => 'Chorrillos',        'city' => 'Viña del Mar', 'lat' => -33.028233, 'lng' => -71.541244],
        ['address' => 'Calle Blanco 1052',      'district' => 'Centro',            'city' => 'Quilpué',      'lat' => -33.048211, 'lng' => -71.442822],
        ['address' => 'Av. Los Carrera 680',    'district' => 'Centro',            'city' => 'Quilpué',      'lat' => -33.049033, 'lng' => -71.446511],
        ['address' => 'Av. Freire 1350',        'district' => 'El Belloto',        'city' => 'Quilpué',      'lat' => -33.056811, 'lng' => -71.412822],
        ['address' => 'Calle Baden Powell 1120','district' => 'El Belloto',        'city' => 'Quilpué',      'lat' => -33.059233, 'lng' => -71.409511],
        ['address' => 'Calle Los Carrera 850',  'district' => 'Villa Los Héroes',  'city' => 'Quilpué',      'lat' => -33.050511, 'lng' => -71.432822],
        ['address' => 'Teniente Serrano 142',   'district' => 'Villa Los Héroes',  'city' => 'Quilpué',      'lat' => -33.052211, 'lng' => -71.435533],
        ['address' => 'Av. Valparaíso 720',     'district' => 'Centro',            'city' => 'Villa Alemana','lat' => -33.041811, 'lng' => -71.372822],
        ['address' => 'Santiago 650',           'district' => 'Centro',            'city' => 'Villa Alemana','lat' => -33.040522, 'lng' => -71.375511],
        ['address' => 'Calle Santiago 1230',    'district' => 'Población Vergara', 'city' => 'Villa Alemana','lat' => -33.046511, 'lng' => -71.365211],
        ['address' => 'Av. Segunda 142',        'district' => 'Población Vergara', 'city' => 'Villa Alemana','lat' => -33.045522, 'lng' => -71.363811],
        ['address' => 'Calle El Sauce 450',     'district' => 'El Sauce',          'city' => 'Villa Alemana','lat' => -33.035811, 'lng' => -71.383222],
        ['address' => 'Los Alerces 120',        'district' => 'El Sauce',          'city' => 'Villa Alemana','lat' => -33.034522, 'lng' => -71.385511],
    ];

    public function run(): void
    {
        $allLocations = $this->baseLocations;

        $streets = [
            'Valparaíso::Cerro Alegre'      => ['Paseo Dimalow', 'Calle Hein', 'Calle Lautaro Rosas', 'Monte Alegre', 'Calle Urriola'],
            'Valparaíso::Cerro Concepción'  => ['Baquedano', 'Dinamarca', 'Calle Beethoven', 'Calle Schubert', 'Av. Alemania'],
            'Valparaíso::Cerro Bellavista'  => ['Av. Bellavista', 'Calle Aldunate', 'Calle Merlet', 'Calle Washington', 'Paseo Dimalow'],
            'Valparaíso::Plan'              => ['Av. Argentina', 'Calle Rodríguez', 'Calle Colón', 'Calle Independencia', 'Av. Errázuriz'],
            'Valparaíso::Puerto'            => ['Av. Altamirano', 'Calle Serrano', 'Calle Esmeralda', 'Calle Prat', 'Calle Bustamante'],
            'Valparaíso::Cerro Placeres'    => ['Calle Los Placeres', 'Calle La Cruz', 'Calle El Parque', 'Calle Santa Elena', 'Av. Playa Ancha'],
            'Valparaíso::Cerro Barón'       => ['Calle Barón', 'Calle El Cerro', 'Calle San Juan de Dios', 'Calle Los Olivos', 'Calle Miraflores'],
            'Valparaíso::Polanco'           => ['Calle Polanco', 'Calle El Mirador', 'Calle Vista Hermosa', 'Pasaje Los Naranjos', 'Calle Los Geranios'],
            'Viña del Mar::Centro'          => ['Av. San Martín', 'Calle Valparaíso', 'Calle Arlegui', 'Calle Von Schroeders', 'Calle 2 Norte'],
            'Viña del Mar::Reñaca'          => ['Av. Borgoño Sur', 'Calle Los Héroes', 'Pasaje Las Brisas', 'Calle El Golf', 'Av. del Mar'],
            'Viña del Mar::Concón'          => ['Av. Concón', 'Calle Los Lilenes', 'Pasaje Los Pinos', 'Calle Las Hortensias', 'Av. Costanera'],
            'Viña del Mar::Jardín del Mar'  => ['Calle Los Crisantemos', 'Calle Las Margaritas', 'Pasaje Los Claveles', 'Av. Jardín del Mar', 'Calle Las Rosas'],
            'Viña del Mar::Santa Inés'      => ['Calle Santa Inés', 'Av. Padre Hurtado', 'Calle Los Olmos', 'Pasaje Los Acacios', 'Calle Los Laureles'],
            'Viña del Mar::Miraflores'      => ['Calle Miraflores', 'Av. Gómez Carreño', 'Calle Las Dalias', 'Pasaje Los Cipreses', 'Calle La Pradera'],
            'Viña del Mar::Bosque del Mar'  => ['Av. Los Bravos', 'Calle Los Peumos', 'Pasaje Los Robles', 'Calle Los Coihues', 'Av. Las Araucarias'],
            'Viña del Mar::Chorrillos'      => ['Av. Chorrillos', 'Calle Los Maquis', 'Calle Los Quillayes', 'Pasaje Los Canelos', 'Calle Los Avellanos'],
            'Quilpué::Centro'               => ['Calle Andacollo', 'Av. Ramón Freire', 'Calle Arturo Prat', 'Calle Portales', 'Calle Manuel Rodríguez'],
            'Quilpué::El Belloto'           => ['Calle Las Dalias', 'Av. El Belloto', 'Calle Los Jazmines', 'Pasaje Los Lirios', 'Calle Los Pensamientos'],
            'Quilpué::Villa Los Héroes'     => ['Calle Los Aviadores', 'Calle Los Tanques', 'Pasaje Los Libertadores', 'Av. Las Torres', 'Calle Los Insignias'],
            'Villa Alemana::Centro'         => ['Calle Baquedano', 'Av. Santiago', 'Av. Urmeneta', 'Calle Bulnes', 'Calle Latorre'],
            'Villa Alemana::Población Vergara' => ['Calle Vergara', 'Av. Los Alerces', 'Calle Las Hortensias', 'Pasaje Los Álamos', 'Calle El Trébol'],
            'Villa Alemana::El Sauce'       => ['Calle Los Lingues', 'Av. El Sauce', 'Calle Los Boldos', 'Pasaje Los Notros', 'Calle Los Coigües'],
        ];

        $districts = [
            'Cerro Alegre', 'Cerro Concepción', 'Cerro Bellavista', 'Plan', 'Puerto',
            'Cerro Placeres', 'Cerro Barón', 'Polanco',
        ];
        $viniaDistricts = ['Centro', 'Reñaca', 'Concón', 'Jardín del Mar', 'Santa Inés', 'Miraflores', 'Bosque del Mar', 'Chorrillos'];
        $quilpueDistricts = ['Centro', 'El Belloto', 'Villa Los Héroes'];
        $villaDistricts = ['Centro', 'Población Vergara', 'El Sauce'];

        $cityForDistrict = [];
        foreach ($districts as $d) { $cityForDistrict[$d] = 'Valparaíso'; }
        foreach ($viniaDistricts as $d) { $cityForDistrict[$d] = 'Viña del Mar'; }
        foreach ($quilpueDistricts as $d) { $cityForDistrict[$d] = 'Quilpué'; }
        foreach ($villaDistricts as $d) { $cityForDistrict[$d] = 'Villa Alemana'; }

        $seenPairs = [];
        foreach ($this->baseLocations as $loc) {
            $seenPairs[$loc['city']][$loc['district']][] = [$loc['lat'], $loc['lng']];
        }

        $existingCount = count($this->baseLocations);
        $needed = 150 - $existingCount;

        $streetIndex = [];
        foreach ($this->baseLocations as $loc) {
            $key = $loc['city'] . '::' . $loc['district'];
            if (!isset($streetIndex[$key])) { $streetIndex[$key] = 0; }
        }

        for ($i = 0; $i < $needed; $i++) {
            $base = $this->baseLocations[$i % $existingCount];
            $city = $base['city'];
            $district = $base['district'];
            $key = $city . '::' . $district;

            if (!isset($streetIndex[$key])) { $streetIndex[$key] = 0; }
            $idx = $streetIndex[$key];
            $streetList = $streets[$key] ?? $streets[$district] ?? ['Calle ' . ($idx + 1)];
            $street = $streetList[$idx % count($streetList)];
            $number = rand(100, 9999);
            $streetIndex[$key]++;

            $attempts = 0;
            do {
                $lat = $base['lat'] + (($i % 3) + 1) * (($i % 2 === 0 ? 1 : -1) * 0.0008 * ($i / $existingCount + 1));
                $lng = $base['lng'] + (($i % 2) + 1) * (($i % 2 === 0 ? 1 : -1) * 0.0006 * ($i / $existingCount + 1));
                $lat = round($lat, 6);
                $lng = round($lng, 6);
                $attempts++;
                if ($attempts > 50) {
                    $lat += ($attempts * 0.0001);
                    $lng += ($attempts * 0.0001);
                }
            } while ($this->isDuplicateCoord($seenPairs, $city, $district, $lat, $lng));

            $seenPairs[$city][$district][] = [$lat, $lng];

            $allLocations[] = [
                'address' => $street . ' ' . $number,
                'district' => $district,
                'city' => $city,
                'lat' => $lat,
                'lng' => $lng,
            ];
        }

        $this->command->info('Generando ' . count($allLocations) . ' paquetes con coordenadas únicas...');
        $bar = $this->command->getOutput()->createProgressBar(count($allLocations));
        $bar->start();

        $packages = [];
        foreach ($allLocations as $j => $loc) {
            $packages[] = Package::create([
                'tracking_number' => 'DEMO-' . str_pad((string) ($j + 1), 4, '0', STR_PAD_LEFT),
                'recipient_name' => fake()->name(),
                'delivery_address' => $loc['address'],
                'district' => $loc['district'],
                'city' => $loc['city'],
                'latitude' => $loc['lat'],
                'longitude' => $loc['lng'],
                'received_at' => fake()->dateTimeBetween('-14 days', 'now'),
            ]);
            $bar->advance();
        }
        $bar->finish();
        $this->command->newLine();

        $routes = [];
        for ($i = 0; $i < 5; $i++) {
            $routes[] = Route::create([
                'name' => 'Ruta ' . chr(65 + $i),
                'route_date' => now()->format('Y-m-d'),
                'notes' => 'Ruta de demostración ' . ($i + 1),
            ]);
        }

        usort($packages, fn($a, $b) => $a->latitude <=> $b->latitude);

        $chunkSize = intdiv(count($packages), 5);
        $remainder = count($packages) % 5;
        $offset = 0;
        $seq = 1;

        $this->command->info('Asignando paquetes a rutas por proximidad geográfica...');
        $routeBar = $this->command->getOutput()->createProgressBar(count($packages));
        $routeBar->start();

        for ($r = 0; $r < 5; $r++) {
            $size = $chunkSize + ($r < $remainder ? 1 : 0);
            $chunk = array_slice($packages, $offset, $size);
            $offset += $size;
            $route = $routes[$r];

            usort($chunk, fn($a, $b) => $a->longitude <=> $b->longitude);

            foreach ($chunk as $pkg) {
                RoutePackage::create([
                    'route_id' => $route->id,
                    'package_id' => $pkg->id,
                    'sequence' => $seq++,
                    'assigned_at' => now(),
                ]);
                $routeBar->advance();
            }
        }

        $routeBar->finish();
        $this->command->newLine();
        $this->command->info('Creados ' . count($packages) . ' paquetes y ' . count($routes) . ' rutas con asignaciones.');
    }

    private function isDuplicateCoord(array &$seen, string $city, string $district, float $lat, float $lng): bool
    {
        $tolerance = 0.00005;
        if (!isset($seen[$city][$district])) { return false; }
        foreach ($seen[$city][$district] as $coord) {
            if (abs($coord[0] - $lat) < $tolerance && abs($coord[1] - $lng) < $tolerance) {
                return true;
            }
        }
        return false;
    }
}
