<?php

namespace Database\Factories;

use App\Models\Package;
use Illuminate\Database\Eloquent\Factories\Factory;

class PackageFactory extends Factory
{
    protected $model = Package::class;

    private static array $cities = [
        ['city' => 'Valparaíso', 'lat' => -33.045, 'lng' => -71.620, 'districts' => [
            'Cerro Alegre', 'Cerro Concepción', 'Cerro Bellavista', 'Plan', 'Puerto',
            'Cerro Placeres', 'Cerro Barón', 'Polanco',
        ]],
        ['city' => 'Viña del Mar', 'lat' => -33.025, 'lng' => -71.540, 'districts' => [
            'Centro', 'Reñaca', 'Concón', 'Jardín del Mar', 'Santa Inés', 'Miraflores', 'Bosque del Mar', 'Chorrillos',
        ]],
        ['city' => 'Quilpué', 'lat' => -33.050, 'lng' => -71.440, 'districts' => [
            'Centro', 'El Belloto', 'Villa Los Héroes',
        ]],
        ['city' => 'Villa Alemana', 'lat' => -33.040, 'lng' => -71.375, 'districts' => [
            'Centro', 'Población Vergara', 'El Sauce',
        ]],
    ];

    public function definition(): array
    {
        $city = static::$cities[array_rand(static::$cities)];
        $district = $city['districts'][array_rand($city['districts'])];

        return [
            'tracking_number' => 'PKG-' . str_pad((string) fake()->unique()->randomNumber(5), 5, '0', STR_PAD_LEFT),
            'recipient_name' => fake()->name(),
            'delivery_address' => fake()->streetAddress(),
            'district' => $district,
            'city' => $city['city'],
            'latitude' => $city['lat'] + fake()->randomFloat(6, -0.025, 0.025),
            'longitude' => $city['lng'] + fake()->randomFloat(6, -0.025, 0.025),
            'received_at' => fake()->dateTimeBetween('-7 days', 'now'),
        ];
    }
}
