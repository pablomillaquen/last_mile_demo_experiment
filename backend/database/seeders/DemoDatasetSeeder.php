<?php

namespace Database\Seeders;

use App\Models\Package;
use App\Models\Route;
use App\Models\RoutePackage;
use Illuminate\Database\Seeder;

class DemoDatasetSeeder extends Seeder
{
    private array $locations = [
        // Valparaíso - Cerro Alegre
        ['address' => 'Paseo Yugoslavo 176', 'district' => 'Cerro Alegre', 'city' => 'Valparaíso', 'lat' => -33.044155, 'lng' => -71.628867],
        ['address' => 'Almirante Montt 453', 'district' => 'Cerro Alegre', 'city' => 'Valparaíso', 'lat' => -33.044234, 'lng' => -71.626244],
        ['address' => 'Miramar 175', 'district' => 'Cerro Alegre', 'city' => 'Valparaíso', 'lat' => -33.044812, 'lng' => -71.628118],
        
        // Valparaíso - Cerro Concepción
        ['address' => 'Paseo Gervasoni 210', 'district' => 'Cerro Concepción', 'city' => 'Valparaíso', 'lat' => -33.042788, 'lng' => -71.626644],
        ['address' => 'Templeman 176', 'district' => 'Cerro Concepción', 'city' => 'Valparaíso', 'lat' => -33.043512, 'lng' => -71.624733],
        ['address' => 'Papudo 540', 'district' => 'Cerro Concepción', 'city' => 'Valparaíso', 'lat' => -33.043255, 'lng' => -71.625411],
        
        // Valparaíso - Cerro Bellavista
        ['address' => 'Hector Calvo 215', 'district' => 'Cerro Bellavista', 'city' => 'Valparaíso', 'lat' => -33.049211, 'lng' => -71.616322],
        ['address' => 'Rudolph 112', 'district' => 'Cerro Bellavista', 'city' => 'Valparaíso', 'lat' => -33.049633, 'lng' => -71.616744],
        ['address' => 'Ferrari 284', 'district' => 'Cerro Bellavista', 'city' => 'Valparaíso', 'lat' => -33.048755, 'lng' => -71.616911],
        
        // Valparaíso - Plan
        ['address' => 'Avenida Brasil 850', 'district' => 'Plan', 'city' => 'Valparaíso', 'lat' => -33.042511, 'lng' => -71.620211],
        ['address' => 'Condell 1240', 'district' => 'Plan', 'city' => 'Valparaíso', 'lat' => -33.045433, 'lng' => -71.621255],
        ['address' => 'Avenida Pedro Montt 1820', 'district' => 'Plan', 'city' => 'Valparaíso', 'lat' => -33.047811, 'lng' => -71.617511],
        
        // Valparaíso - Puerto
        ['address' => 'Plaza Sotomayor 50', 'district' => 'Puerto', 'city' => 'Valparaíso', 'lat' => -33.038144, 'lng' => -71.629122],
        ['address' => 'Cochrane 812', 'district' => 'Puerto', 'city' => 'Valparaíso', 'lat' => -33.039011, 'lng' => -71.628122],
        ['address' => 'Blanco 620', 'district' => 'Puerto', 'city' => 'Valparaíso', 'lat' => -33.038455, 'lng' => -71.627233],
        
        // Valparaíso - Cerro Placeres
        ['address' => 'Avenida Placeres 1290', 'district' => 'Cerro Placeres', 'city' => 'Valparaíso', 'lat' => -33.048511, 'lng' => -71.596244],
        ['address' => 'Avenida España 1680', 'district' => 'Cerro Placeres', 'city' => 'Valparaíso', 'lat' => -33.043811, 'lng' => -71.597522],
        
        // Valparaíso - Cerro Barón
        ['address' => 'Avenida Diego Portales 542', 'district' => 'Cerro Barón', 'city' => 'Valparaíso', 'lat' => -33.042811, 'lng' => -71.609511],
        ['address' => 'Tocornal 450', 'district' => 'Cerro Barón', 'city' => 'Valparaíso', 'lat' => -33.041855, 'lng' => -71.608022],
        
        // Valparaíso - Polanco
        ['address' => 'Avenida Simpson 180', 'district' => 'Polanco', 'city' => 'Valparaíso', 'lat' => -33.051511, 'lng' => -71.612822],
        ['address' => 'Valdenegro 245', 'district' => 'Polanco', 'city' => 'Valparaíso', 'lat' => -33.053233, 'lng' => -71.612011],

        // Viña del Mar - Centro
        ['address' => 'Avenida Libertad 450', 'district' => 'Centro', 'city' => 'Viña del Mar', 'lat' => -33.018211, 'lng' => -71.549822],
        ['address' => 'Avenida Valparaíso 651', 'district' => 'Centro', 'city' => 'Viña del Mar', 'lat' => -33.024522, 'lng' => -71.553211],
        ['address' => '1 Norte 850', 'district' => 'Centro', 'city' => 'Viña del Mar', 'lat' => -33.026211, 'lng' => -71.545233],
        ['address' => 'Quillota 120', 'district' => 'Centro', 'city' => 'Viña del Mar', 'lat' => -33.029211, 'lng' => -71.550544],
        
        // Viña del Mar - Reñaca
        ['address' => 'Avenida Borgoño 14200', 'district' => 'Reñaca', 'city' => 'Viña del Mar', 'lat' => -32.969811, 'lng' => -71.543522],
        ['address' => 'Avenida Gastón Hamel 350', 'district' => 'Reñaca', 'city' => 'Viña del Mar', 'lat' => -32.973522, 'lng' => -71.536811],
        ['address' => 'General Carrera 240', 'district' => 'Reñaca', 'city' => 'Viña del Mar', 'lat' => -32.972233, 'lng' => -71.541544],
        
        // Viña del Mar - Concón
        ['address' => 'Avenida Maroto 1250', 'district' => 'Concón', 'city' => 'Viña del Mar', 'lat' => -32.923811, 'lng' => -71.518822],
        ['address' => 'Avenida Borgoño 25000', 'district' => 'Concón', 'city' => 'Viña del Mar', 'lat' => -32.928233, 'lng' => -71.523544],
        ['address' => 'Calle Las Pimpinelas 905', 'district' => 'Concón', 'city' => 'Viña del Mar', 'lat' => -32.932511, 'lng' => -71.521822],
        
        // Viña del Mar - Jardín del Mar
        ['address' => 'Los Sargazos 120', 'district' => 'Jardín del Mar', 'city' => 'Viña del Mar', 'lat' => -32.983211, 'lng' => -71.541233],
        ['address' => 'Los Alerces 240', 'district' => 'Jardín del Mar', 'city' => 'Viña del Mar', 'lat' => -32.986511, 'lng' => -71.539822],
        
        // Viña del Mar - Santa Inés
        ['address' => 'Calle 24 Norte 1025', 'district' => 'Santa Inés', 'city' => 'Viña del Mar', 'lat' => -33.011211, 'lng' => -71.544822],
        ['address' => 'Quillota 1850', 'district' => 'Santa Inés', 'city' => 'Viña del Mar', 'lat' => -33.012533, 'lng' => -71.546211],
        
        // Viña del Mar - Miraflores
        ['address' => 'Avenida Eduardo Frei 2050', 'district' => 'Miraflores', 'city' => 'Viña del Mar', 'lat' => -33.016511, 'lng' => -71.518522],
        ['address' => 'Calle Limache 3405', 'district' => 'Miraflores', 'city' => 'Viña del Mar', 'lat' => -33.031522, 'lng' => -71.525011],
        
        // Viña del Mar - Bosque del Mar
        ['address' => 'Avenida Bosques de Montemar 550', 'district' => 'Bosque del Mar', 'city' => 'Viña del Mar', 'lat' => -32.955511, 'lng' => -71.528522],
        ['address' => 'Los Arrayanes 180', 'district' => 'Bosque del Mar', 'city' => 'Viña del Mar', 'lat' => -32.958211, 'lng' => -71.531233],
        
        // Viña del Mar - Chorrillos
        ['address' => 'Avenida Alvarez 2390', 'district' => 'Chorrillos', 'city' => 'Viña del Mar', 'lat' => -33.029811, 'lng' => -71.538822],
        ['address' => 'Lusitania 110', 'district' => 'Chorrillos', 'city' => 'Viña del Mar', 'lat' => -33.028233, 'lng' => -71.541244],

        // Quilpué - Centro
        ['address' => 'Calle Blanco 1052', 'district' => 'Centro', 'city' => 'Quilpué', 'lat' => -33.048211, 'lng' => -71.442822],
        ['address' => 'Avenida Los Carrera 680', 'district' => 'Centro', 'city' => 'Quilpué', 'lat' => -33.049033, 'lng' => -71.446511],
        
        // Quilpué - El Belloto
        ['address' => 'Avenida Freire 1350', 'district' => 'El Belloto', 'city' => 'Quilpué', 'lat' => -33.056811, 'lng' => -71.412822],
        ['address' => 'Calle Baden Powell 1120', 'district' => 'El Belloto', 'city' => 'Quilpué', 'lat' => -33.059233, 'lng' => -71.409511],
        
        // Quilpué - Villa Los Héroes
        ['address' => 'Calle Los Carrera 850', 'district' => 'Villa Los Héroes', 'city' => 'Quilpué', 'lat' => -33.050511, 'lng' => -71.432822],
        ['address' => 'Teniente Serrano 142', 'district' => 'Villa Los Héroes', 'city' => 'Quilpué', 'lat' => -33.052211, 'lng' => -71.435533],

        // Villa Alemana - Centro
        ['address' => 'Avenida Valparaíso 720', 'district' => 'Centro', 'city' => 'Villa Alemana', 'lat' => -33.041811, 'lng' => -71.372822],
        ['address' => 'Santiago 650', 'district' => 'Centro', 'city' => 'Villa Alemana', 'lat' => -33.040522, 'lng' => -71.375511],
        
        // Villa Alemana - Población Vergara
        ['address' => 'Calle Santiago 1230', 'district' => 'Población Vergara', 'city' => 'Villa Alemana', 'lat' => -33.046511, 'lng' => -71.365211],
        ['address' => 'Avenida Segunda 142', 'district' => 'Población Vergara', 'city' => 'Villa Alemana', 'lat' => -33.045522, 'lng' => -71.363811],
        
        // Villa Alemana - El Sauce
        ['address' => 'Calle El Sauce 450', 'district' => 'El Sauce', 'city' => 'Villa Alemana', 'lat' => -33.035811, 'lng' => -71.383222],
        ['address' => 'Los Alerces 120', 'district' => 'El Sauce', 'city' => 'Villa Alemana', 'lat' => -33.034522, 'lng' => -71.385511],
    ];

    public function run(): void
    {
        $packages = [];
        for ($i = 0; $i < 100; $i++) {
            $loc = $this->locations[array_rand($this->locations)];
            $packages[] = Package::create([
                'tracking_number' => 'DEMO-' . str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                'recipient_name' => fake()->name(),
                'delivery_address' => $loc['address'],
                'district' => $loc['district'],
                'city' => $loc['city'],
                'latitude' => $loc['lat'],
                'longitude' => $loc['lng'],
                'received_at' => fake()->dateTimeBetween('-14 days', 'now'),
            ]);
        }

        $routes = [];
        for ($i = 0; $i < 5; $i++) {
            $routes[] = Route::create([
                'name' => 'Ruta ' . chr(65 + $i),
                'route_date' => now()->format('Y-m-d'),
                'notes' => 'Ruta de demostración ' . ($i + 1),
            ]);
        }

        $assignments = [
            0 => [0, 1, 2, 46, 47, 48, 28, 29],
            1 => [9, 10, 11, 30, 31, 32],
            2 => [15, 17, 18, 33, 34, 35],
            3 => [36, 44],
        ];

        $seq = 1;
        foreach ($assignments as $routeIdx => $locIdxs) {
            $route = $routes[$routeIdx];
            foreach ($locIdxs as $locIdx) {
                $targetDistrict = $this->locations[$locIdx]['district'];
                $matching = null;
                foreach ($packages as $pkg) {
                    if ($pkg->district === $targetDistrict) {
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
