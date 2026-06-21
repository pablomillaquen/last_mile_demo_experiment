<?php

namespace App\Services;

use App\Models\Route;

class MetricsCalculatorService
{
    public function __construct(
        private DistanceService $distanceService
    ) {}

    public function calculateRouteMetrics(Route $route, float $warehouseLat, float $warehouseLng): array
    {
        $route->loadMissing('routePackages.package');
        $routePackages = $route->routePackages->sortBy('sequence');
        $packages = $routePackages->pluck('package');
        $count = $packages->count();

        if ($count === 0) {
            return [
                'route_id' => $route->id,
                'route_name' => $route->name,
                'total_deliveries' => 0,
                'min_distance_to_warehouse_km' => 0,
                'max_distance_to_warehouse_km' => 0,
                'avg_distance_to_warehouse_km' => 0,
                'centroid_lat' => $warehouseLat,
                'centroid_lng' => $warehouseLng,
                'centroid_to_warehouse_km' => 0,
                'cluster_radius_km' => 0,
                'avg_distance_to_centroid_km' => 0,
                'estimated_route_distance_km' => 0,
            ];
        }

        $distances = [];
        foreach ($packages as $pkg) {
            $distances[] = $this->distanceService->calculate(
                $warehouseLat, $warehouseLng,
                (float) $pkg->latitude, (float) $pkg->longitude
            )['distance_km'];
        }

        $minDist = min($distances);
        $maxDist = max($distances);
        $avgDist = array_sum($distances) / $count;

        $centroid = $this->calculateCentroid($packages);

        $centroidToWarehouse = $this->distanceService->calculate(
            $centroid['lat'], $centroid['lng'],
            $warehouseLat, $warehouseLng
        )['distance_km'];

        $distsToCentroid = [];
        foreach ($packages as $pkg) {
            $distsToCentroid[] = $this->distanceService->calculate(
                $centroid['lat'], $centroid['lng'],
                (float) $pkg->latitude, (float) $pkg->longitude
            )['distance_km'];
        }
        $clusterRadius = max($distsToCentroid);
        $avgDistToCentroid = array_sum($distsToCentroid) / $count;

        $routeLeg = $this->calculateRouteLeg($routePackages, $warehouseLat, $warehouseLng);

        return [
            'route_id' => $route->id,
            'route_name' => $route->name,
            'total_deliveries' => $count,
            'min_distance_to_warehouse_km' => round($minDist, 4),
            'max_distance_to_warehouse_km' => round($maxDist, 4),
            'avg_distance_to_warehouse_km' => round($avgDist, 4),
            'centroid_lat' => round($centroid['lat'], 7),
            'centroid_lng' => round($centroid['lng'], 7),
            'centroid_to_warehouse_km' => round($centroidToWarehouse, 4),
            'cluster_radius_km' => round($clusterRadius, 4),
            'avg_distance_to_centroid_km' => round($avgDistToCentroid, 4),
            'estimated_route_distance_km' => round($routeLeg['distance_km'], 4),
            'estimated_time_min' => $routeLeg['duration_min'] !== null ? round($routeLeg['duration_min'], 2) : null,
        ];
    }

    public function calculateCentroid(iterable $packages): array
    {
        $latSum = 0.0;
        $lngSum = 0.0;
        $count = 0;

        foreach ($packages as $pkg) {
            $latSum += (float) $pkg->latitude;
            $lngSum += (float) $pkg->longitude;
            $count++;
        }

        if ($count === 0) {
            return ['lat' => 0.0, 'lng' => 0.0];
        }

        return [
            'lat' => $latSum / $count,
            'lng' => $lngSum / $count,
        ];
    }

    public function calculateRouteLeg(iterable $routePackages, float $warehouseLat, float $warehouseLng): array
    {
        $sorted = collect($routePackages)->sortBy('sequence');
        $totalDistance = 0.0;
        $totalDuration = 0.0;
        $hasDuration = true;
        $prevLat = $warehouseLat;
        $prevLng = $warehouseLng;

        foreach ($sorted as $rp) {
            $lat = (float) $rp->package->latitude;
            $lng = (float) $rp->package->longitude;
            $leg = $this->distanceService->calculate($prevLat, $prevLng, $lat, $lng);
            $totalDistance += $leg['distance_km'];
            if ($leg['duration_min'] === null) {
                $hasDuration = false;
            } else {
                $totalDuration += $leg['duration_min'];
            }
            $prevLat = $lat;
            $prevLng = $lng;
        }

        return [
            'distance_km' => $totalDistance,
            'duration_min' => $hasDuration ? $totalDuration : null,
        ];
    }

    public function calculateGlobalIndicators(array $allRouteMetrics, iterable $allDeliveries, float $warehouseLat, float $warehouseLng): array
    {
        $coverage = 0.0;
        $allDistances = [];

        foreach ($allDeliveries as $pkg) {
            $dist = $this->distanceService->calculate(
                $warehouseLat, $warehouseLng,
                (float) $pkg->latitude, (float) $pkg->longitude
            )['distance_km'];
            $allDistances[] = $dist;
            if ($dist > $coverage) {
                $coverage = $dist;
            }
        }

        $count = count($allDistances);
        $avg = $count > 0 ? array_sum($allDistances) / $count : 0.0;

        $variance = 0.0;
        if ($count > 0) {
            foreach ($allDistances as $d) {
                $variance += ($d - $avg) ** 2;
            }
            $variance /= $count;
        }
        $stdDev = sqrt($variance);

        $deliveriesPerRoute = array_map(fn($m) => $m['total_deliveries'], $allRouteMetrics);
        $routeCount = count($deliveriesPerRoute);
        $meanDel = $routeCount > 0 ? array_sum($deliveriesPerRoute) / $routeCount : 0.0;

        $routeVar = 0.0;
        if ($routeCount > 0) {
            foreach ($deliveriesPerRoute as $d) {
                $routeVar += ($d - $meanDel) ** 2;
            }
            $routeVar /= $routeCount;
        }
        $cv = $meanDel > 0 ? sqrt($routeVar) / $meanDel : 0.0;

        $maxDel = $deliveriesPerRoute ? max($deliveriesPerRoute) : 0;
        $minDel = $deliveriesPerRoute ? min($deliveriesPerRoute) : 0;
        $balanceIndex = $minDel > 0 ? $maxDel / $minDel : 0.0;

        $minClusterDist = PHP_FLOAT_MAX;
        for ($i = 0; $i < $routeCount; $i++) {
            for ($j = $i + 1; $j < $routeCount; $j++) {
                $dist = $this->distanceService->calculate(
                    $allRouteMetrics[$i]['centroid_lat'],
                    $allRouteMetrics[$i]['centroid_lng'],
                    $allRouteMetrics[$j]['centroid_lat'],
                    $allRouteMetrics[$j]['centroid_lng']
                )['distance_km'];
                if ($dist < $minClusterDist) {
                    $minClusterDist = $dist;
                }
            }
        }

        if ($minClusterDist === PHP_FLOAT_MAX) {
            $minClusterDist = 0.0;
        }

        return [
            'coverage_territorial_km' => round($coverage, 4),
            'distancia_promedio_general_km' => round($avg, 4),
            'desviacion_estandar_distancias_km' => round($stdDev, 4),
            'balance_general_cv' => round($cv, 4),
            'balance_index' => round($balanceIndex, 4),
            'inter_cluster_min_distance_km' => round($minClusterDist, 4),
        ];
    }
}
