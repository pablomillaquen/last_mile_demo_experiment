<?php

namespace App\Services;

class AnomalyDetector
{
    public function detect(array $routeMetricsList, array $allDeliveries, float $thresholdKm, float $ratio): array
    {
        $anomalies = [];

        $routeMap = [];
        foreach ($routeMetricsList as $m) {
            $routeMap[$m['route_id']] = $m;
        }

        foreach ($allDeliveries as $delivery) {
            $distToWarehouse = $delivery['distance_to_warehouse_km'] ?? 0;
            if ($distToWarehouse > $thresholdKm) {
                continue;
            }

            $routeId = $delivery['route_id'];
            $routeMetric = $routeMap[$routeId] ?? null;
            if (!$routeMetric) {
                continue;
            }

            $centroidDistance = $routeMetric['centroid_to_warehouse_km'];
            if ($centroidDistance <= 0) {
                continue;
            }

            $currentRatio = $centroidDistance / $distToWarehouse;
            if ($currentRatio >= $ratio) {
                $anomalies[] = [
                    'delivery_id' => $delivery['delivery_id'],
                    'route_id' => $routeId,
                    'distance_to_warehouse_km' => round($distToWarehouse, 4),
                    'centroid_distance_km' => round($centroidDistance, 4),
                    'ratio' => round($currentRatio, 4),
                ];
            }
        }

        return $anomalies;
    }

    public function calculateOperationalPenalty(array $anomalies): float
    {
        $total = 0.0;
        foreach ($anomalies as $a) {
            $total += $a['ratio'];
        }
        return round($total, 4);
    }
}
