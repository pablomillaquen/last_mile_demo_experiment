<?php

namespace App\Services;

use App\Models\Route;
use App\Models\Setting;

class RouteMetricsService
{
    private static ?array $_warehouse = null;
    private static ?float $_speed = null;

    public function getRouteMetrics(Route $route): array
    {
        $route->loadMissing('routePackages.package');

        $packages = $route->routePackages->sortBy('sequence')->values();
        $count = $packages->count();

        if ($count === 0) {
            return [
                'total_distance_km' => 0,
                'avg_distance_per_delivery_km' => 0,
                'estimated_time_minutes' => 0,
                'estimated_time_formatted' => '0h 0m',
            ];
        }

        $this->loadSettings();
        $warehouse = self::$_warehouse;
        $speed = self::$_speed;

        $totalDistance = 0.0;

        $prevLat = (float) $warehouse['lat'];
        $prevLng = (float) $warehouse['lng'];

        foreach ($packages as $rp) {
            $lat = (float) $rp->package->latitude;
            $lng = (float) $rp->package->longitude;

            $totalDistance += HaversineService::calculate($prevLat, $prevLng, $lat, $lng);

            $prevLat = $lat;
            $prevLng = $lng;
        }

        $totalDistance += HaversineService::calculate($prevLat, $prevLng, (float) $warehouse['lat'], (float) $warehouse['lng']);

        $totalDistance = round($totalDistance, 2);
        $avgDistance = $count > 0 ? round($totalDistance / $count, 2) : 0;

        $minutes = $speed > 0 ? (int) round(($totalDistance / $speed) * 60) : 0;
        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;
        $formatted = "{$hours}h {$remainingMinutes}m";

        return [
            'total_distance_km' => $totalDistance,
            'avg_distance_per_delivery_km' => $avgDistance,
            'estimated_time_minutes' => $minutes,
            'estimated_time_formatted' => $formatted,
        ];
    }

    private function loadSettings(): void
    {
        if (self::$_warehouse !== null && self::$_speed !== null) {
            return;
        }

        $all = Setting::whereIn('key', ['warehouse_lat', 'warehouse_lng', 'average_speed_kmh'])
            ->pluck('value', 'key');

        self::$_warehouse = [
            'lat' => (float) ($all['warehouse_lat'] ?? -33.045),
            'lng' => (float) ($all['warehouse_lng'] ?? -71.62),
        ];
        self::$_speed = (float) ($all['average_speed_kmh'] ?? 30);
    }
}
