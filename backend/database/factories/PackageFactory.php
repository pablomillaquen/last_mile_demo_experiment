<?php

namespace Database\Factories;

use App\Models\Package;
use Illuminate\Database\Eloquent\Factories\Factory;

class PackageFactory extends Factory
{
    protected $model = Package::class;

    private static array $locations = [
        ['city' => 'Valparaíso', 'district' => 'Cerro Alegre', 'lat' => -33.0448, 'lng' => -71.6212],
        ['city' => 'Valparaíso', 'district' => 'Cerro Alegre', 'lat' => -33.0455, 'lng' => -71.6200],
        ['city' => 'Valparaíso', 'district' => 'Cerro Alegre', 'lat' => -33.0452, 'lng' => -71.6218],
        ['city' => 'Valparaíso', 'district' => 'Cerro Concepción', 'lat' => -33.0432, 'lng' => -71.6228],
        ['city' => 'Valparaíso', 'district' => 'Cerro Concepción', 'lat' => -33.0438, 'lng' => -71.6220],
        ['city' => 'Valparaíso', 'district' => 'Cerro Concepción', 'lat' => -33.0428, 'lng' => -71.6235],
        ['city' => 'Valparaíso', 'district' => 'Cerro Bellavista', 'lat' => -33.0505, 'lng' => -71.6100],
        ['city' => 'Valparaíso', 'district' => 'Cerro Bellavista', 'lat' => -33.0512, 'lng' => -71.6090],
        ['city' => 'Valparaíso', 'district' => 'Cerro Bellavista', 'lat' => -33.0500, 'lng' => -71.6110],
        ['city' => 'Valparaíso', 'district' => 'Plan', 'lat' => -33.0475, 'lng' => -71.6250],
        ['city' => 'Valparaíso', 'district' => 'Plan', 'lat' => -33.0468, 'lng' => -71.6240],
        ['city' => 'Valparaíso', 'district' => 'Plan', 'lat' => -33.0470, 'lng' => -71.6260],
        ['city' => 'Valparaíso', 'district' => 'Puerto', 'lat' => -33.0395, 'lng' => -71.6285],
        ['city' => 'Valparaíso', 'district' => 'Puerto', 'lat' => -33.0388, 'lng' => -71.6295],
        ['city' => 'Valparaíso', 'district' => 'Puerto', 'lat' => -33.0405, 'lng' => -71.6275],
        ['city' => 'Valparaíso', 'district' => 'Cerro Placeres', 'lat' => -33.0565, 'lng' => -71.5940],
        ['city' => 'Valparaíso', 'district' => 'Cerro Placeres', 'lat' => -33.0558, 'lng' => -71.5920],
        ['city' => 'Valparaíso', 'district' => 'Cerro Barón', 'lat' => -33.0418, 'lng' => -71.6145],
        ['city' => 'Valparaíso', 'district' => 'Cerro Barón', 'lat' => -33.0410, 'lng' => -71.6135],
        ['city' => 'Valparaíso', 'district' => 'Polanco', 'lat' => -33.0528, 'lng' => -71.6190],
        ['city' => 'Valparaíso', 'district' => 'Polanco', 'lat' => -33.0535, 'lng' => -71.6180],
        ['city' => 'Viña del Mar', 'district' => 'Centro', 'lat' => -33.0245, 'lng' => -71.5505],
        ['city' => 'Viña del Mar', 'district' => 'Centro', 'lat' => -33.0238, 'lng' => -71.5515],
        ['city' => 'Viña del Mar', 'district' => 'Centro', 'lat' => -33.0250, 'lng' => -71.5495],
        ['city' => 'Viña del Mar', 'district' => 'Centro', 'lat' => -33.0240, 'lng' => -71.5525],
        ['city' => 'Viña del Mar', 'district' => 'Reñaca', 'lat' => -32.9675, 'lng' => -71.5475],
        ['city' => 'Viña del Mar', 'district' => 'Reñaca', 'lat' => -32.9660, 'lng' => -71.5490],
        ['city' => 'Viña del Mar', 'district' => 'Reñaca', 'lat' => -32.9685, 'lng' => -71.5465],
        ['city' => 'Viña del Mar', 'district' => 'Concón', 'lat' => -32.9225, 'lng' => -71.5285],
        ['city' => 'Viña del Mar', 'district' => 'Concón', 'lat' => -32.9215, 'lng' => -71.5295],
        ['city' => 'Viña del Mar', 'district' => 'Concón', 'lat' => -32.9235, 'lng' => -71.5275],
        ['city' => 'Viña del Mar', 'district' => 'Jardín del Mar', 'lat' => -33.0108, 'lng' => -71.5395],
        ['city' => 'Viña del Mar', 'district' => 'Jardín del Mar', 'lat' => -33.0100, 'lng' => -71.5385],
        ['city' => 'Viña del Mar', 'district' => 'Santa Inés', 'lat' => -33.0315, 'lng' => -71.5585],
        ['city' => 'Viña del Mar', 'district' => 'Santa Inés', 'lat' => -33.0305, 'lng' => -71.5595],
        ['city' => 'Viña del Mar', 'district' => 'Miraflores', 'lat' => -33.0195, 'lng' => -71.5315],
        ['city' => 'Viña del Mar', 'district' => 'Miraflores', 'lat' => -33.0188, 'lng' => -71.5305],
        ['city' => 'Viña del Mar', 'district' => 'Bosque del Mar', 'lat' => -32.9915, 'lng' => -71.5395],
        ['city' => 'Viña del Mar', 'district' => 'Bosque del Mar', 'lat' => -32.9908, 'lng' => -71.5385],
        ['city' => 'Viña del Mar', 'district' => 'Chorrillos', 'lat' => -33.0015, 'lng' => -71.5585],
        ['city' => 'Viña del Mar', 'district' => 'Chorrillos', 'lat' => -33.0008, 'lng' => -71.5575],
        ['city' => 'Quilpué', 'district' => 'Centro', 'lat' => -33.0495, 'lng' => -71.4515],
        ['city' => 'Quilpué', 'district' => 'Centro', 'lat' => -33.0488, 'lng' => -71.4505],
        ['city' => 'Quilpué', 'district' => 'El Belloto', 'lat' => -33.0585, 'lng' => -71.4325],
        ['city' => 'Quilpué', 'district' => 'El Belloto', 'lat' => -33.0578, 'lng' => -71.4315],
        ['city' => 'Quilpué', 'district' => 'Villa Los Héroes', 'lat' => -33.0545, 'lng' => -71.4415],
        ['city' => 'Quilpué', 'district' => 'Villa Los Héroes', 'lat' => -33.0538, 'lng' => -71.4405],
        ['city' => 'Villa Alemana', 'district' => 'Centro', 'lat' => -33.0420, 'lng' => -71.3745],
        ['city' => 'Villa Alemana', 'district' => 'Centro', 'lat' => -33.0412, 'lng' => -71.3735],
        ['city' => 'Villa Alemana', 'district' => 'Población Vergara', 'lat' => -33.0475, 'lng' => -71.3665],
        ['city' => 'Villa Alemana', 'district' => 'Población Vergara', 'lat' => -33.0468, 'lng' => -71.3655],
        ['city' => 'Villa Alemana', 'district' => 'El Sauce', 'lat' => -33.0375, 'lng' => -71.3815],
        ['city' => 'Villa Alemana', 'district' => 'El Sauce', 'lat' => -33.0368, 'lng' => -71.3805],
    ];

    public function definition(): array
    {
        $location = static::$locations[array_rand(static::$locations)];

        return [
            'tracking_number' => 'PKG-' . str_pad((string) fake()->unique()->randomNumber(5), 5, '0', STR_PAD_LEFT),
            'recipient_name' => fake()->name(),
            'delivery_address' => fake()->streetAddress(),
            'district' => $location['district'],
            'city' => $location['city'],
            'latitude' => $location['lat'],
            'longitude' => $location['lng'],
            'received_at' => fake()->dateTimeBetween('-7 days', 'now'),
        ];
    }
}
