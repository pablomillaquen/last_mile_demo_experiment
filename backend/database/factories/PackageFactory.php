<?php

namespace Database\Factories;

use App\Models\Package;
use Illuminate\Database\Eloquent\Factories\Factory;

class PackageFactory extends Factory
{
    protected $model = Package::class;

    private static array $locations = [
        ['city' => 'Valparaíso', 'district' => 'Cerro Alegre', 'lat' => -33.0458, 'lng' => -71.6197],
        ['city' => 'Valparaíso', 'district' => 'Cerro Concepción', 'lat' => -33.0440, 'lng' => -71.6220],
        ['city' => 'Valparaíso', 'district' => 'Cerro Bellavista', 'lat' => -33.0500, 'lng' => -71.6100],
        ['city' => 'Valparaíso', 'district' => 'Plan', 'lat' => -33.0478, 'lng' => -71.6260],
        ['city' => 'Valparaíso', 'district' => 'Puerto', 'lat' => -33.0400, 'lng' => -71.6300],
        ['city' => 'Viña del Mar', 'district' => 'Centro', 'lat' => -33.0245, 'lng' => -71.5518],
        ['city' => 'Viña del Mar', 'district' => 'Reñaca', 'lat' => -32.9667, 'lng' => -71.5500],
        ['city' => 'Viña del Mar', 'district' => 'Concón', 'lat' => -32.9200, 'lng' => -71.5300],
        ['city' => 'Viña del Mar', 'district' => 'Jardín del Mar', 'lat' => -33.0100, 'lng' => -71.5400],
        ['city' => 'Viña del Mar', 'district' => 'Santa Inés', 'lat' => -33.0300, 'lng' => -71.5600],
        ['city' => 'Quilpué', 'district' => 'Centro', 'lat' => -33.0500, 'lng' => -71.4500],
        ['city' => 'Quilpué', 'district' => 'El Belloto', 'lat' => -33.0600, 'lng' => -71.4300],
        ['city' => 'Villa Alemana', 'district' => 'Centro', 'lat' => -33.0422, 'lng' => -71.3730],
        ['city' => 'Villa Alemana', 'district' => 'Población Vergara', 'lat' => -33.0480, 'lng' => -71.3650],
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
            'latitude' => $location['lat'] + fake()->randomFloat(5, -0.002, 0.002),
            'longitude' => $location['lng'] + fake()->randomFloat(5, -0.002, 0.002),
            'received_at' => fake()->dateTimeBetween('-7 days', 'now'),
        ];
    }
}
