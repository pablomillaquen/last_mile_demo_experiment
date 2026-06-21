<?php

namespace App\Services;

class DistanceService
{
    private string $mode = 'geodesic';

    public function __construct(
        private HaversineService $haversine,
        private OsrmClient $osrm
    ) {}

    public function setMode(string $mode): void
    {
        if (!in_array($mode, ['geodesic', 'vial'])) {
            throw new \InvalidArgumentException("Modo inválido: $mode. Use 'geodesic' o 'vial'.");
        }
        $this->mode = $mode;
    }

    public function calculate(float $lat1, float $lng1, float $lat2, float $lng2): array
    {
        if ($this->mode === 'geodesic') {
            return [
                'distance_km' => HaversineService::calculate($lat1, $lng1, $lat2, $lng2),
                'duration_min' => null,
                'geometry' => [[$lat1, $lng1], [$lat2, $lng2]],
                'mode' => 'geodesic',
            ];
        }

        $result = $this->osrm->route($lng1, $lat1, $lng2, $lat2);

        if ($result['distance_km'] === null) {
            return [
                'distance_km' => HaversineService::calculate($lat1, $lng1, $lat2, $lng2),
                'duration_min' => null,
                'geometry' => [[$lat1, $lng1], [$lat2, $lng2]],
                'mode' => 'geodesic',
            ];
        }

        return [
            'distance_km' => $result['distance_km'],
            'duration_min' => $result['duration_min'],
            'geometry' => $result['geometry'],
            'mode' => 'vial',
        ];
    }
}
