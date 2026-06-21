<?php

namespace App\Services;

use App\Models\Route;
use App\Models\Setting;

class MeasurementService
{
    private MetricsCalculatorService $metricsCalculator;
    private ?AnomalyDetector $anomalyDetector;
    private ?MapRendererService $mapRenderer;
    private DistanceService $distanceService;

    public function __construct(
        MetricsCalculatorService $metricsCalculator,
        DistanceService $distanceService,
        ?AnomalyDetector $anomalyDetector = null,
        ?MapRendererService $mapRenderer = null
    ) {
        $this->metricsCalculator = $metricsCalculator;
        $this->distanceService = $distanceService;
        $this->anomalyDetector = $anomalyDetector;
        $this->mapRenderer = $mapRenderer;
    }

    public function execute(array $parameters, ?string $mapOutputPath = null): array
    {
        $startTime = microtime(true);

        $mode = $parameters['distance_mode'] ?? 'geodesic';
        $this->distanceService->setMode($mode);

        $warehouse = $this->loadWarehouse();

        $routes = Route::with('routePackages.package')->get();
        $allDeliveries = $routes->flatMap(fn($r) => $r->routePackages->pluck('package'));

        $routeMetrics = [];
        foreach ($routes as $route) {
            $routeMetrics[] = $this->metricsCalculator->calculateRouteMetrics(
                $route, $warehouse['lat'], $warehouse['lng']
            );
        }

        $globalIndicators = $this->metricsCalculator->calculateGlobalIndicators(
            $routeMetrics, $allDeliveries, $warehouse['lat'], $warehouse['lng']
        );

        $ranking = collect($routeMetrics)
            ->sortBy('avg_distance_to_warehouse_km')
            ->values()
            ->map(fn($m, $idx) => [
                'rank' => $idx + 1,
                'route_id' => $m['route_id'],
                'route_name' => $m['route_name'],
                'avg_distance_km' => $m['avg_distance_to_warehouse_km'],
            ])
            ->toArray();

        $deliveriesFlat = $this->buildDeliveriesFlat($routes, $routeMetrics, $warehouse);

        $anomalies = [];
        $operationalPenalty = 0;

        if ($this->anomalyDetector !== null) {
            $threshold = (float) ($parameters['near_delivery_threshold_km'] ?? 1.0);
            $ratio = (float) ($parameters['ignored_delivery_ratio'] ?? 2.0);

            $anomalies = $this->anomalyDetector->detect($routeMetrics, $deliveriesFlat, $threshold, $ratio);
            $operationalPenalty = $this->anomalyDetector->calculateOperationalPenalty($anomalies);
        }

        $globalIndicators['total_anomalias_detectadas'] = count($anomalies);
        $globalIndicators['operational_penalty_total'] = $operationalPenalty;

        $mapFiles = [];

        if ($this->mapRenderer !== null && $mapOutputPath !== null) {
            if (!is_dir($mapOutputPath)) {
                mkdir($mapOutputPath, 0755, true);
            }

            $routeMapData = [];
            foreach ($routes as $route) {
                $deliveries = [];
                foreach ($route->routePackages->sortBy('sequence') as $rp) {
                    $deliveries[] = [
                        'latitude' => (float) $rp->package->latitude,
                        'longitude' => (float) $rp->package->longitude,
                    ];
                }
                $routeMapData[] = [
                    'route_name' => $route->name,
                    'deliveries' => $deliveries,
                    'warehouse_lat' => $warehouse['lat'],
                    'warehouse_lng' => $warehouse['lng'],
                ];
            }

            $allDeliveriesForMap = [];
            foreach ($deliveriesFlat as $d) {
                $allDeliveriesForMap[] = [
                    'delivery_id' => $d['delivery_id'],
                    'latitude' => $d['latitude'],
                    'longitude' => $d['longitude'],
                ];
            }

            $overviewPath = $mapOutputPath . '/map_overview.png';
            $this->mapRenderer->renderOverview(
                $warehouse['lat'], $warehouse['lng'],
                $routeMapData, $allDeliveriesForMap, $overviewPath
            );
            $mapFiles['overview'] = basename($overviewPath);

            $routeMapPaths = [];
            foreach ($routeMapData as $rd) {
                $path = $mapOutputPath . '/map_route_' . \Illuminate\Support\Str::slug($rd['route_name']) . '.png';
                $this->mapRenderer->renderRouteMap(
                    $warehouse['lat'], $warehouse['lng'],
                    $rd['deliveries'], $rd['route_name'], $path
                );
                $routeMapPaths[] = basename($path);
            }
            $mapFiles['routes'] = $routeMapPaths;

            if (!empty($anomalies)) {
                $anomalyPath = $mapOutputPath . '/map_anomalies.png';
                $this->mapRenderer->renderAnomalyMap(
                    $warehouse['lat'], $warehouse['lng'],
                    $routeMapData, $anomalies, $allDeliveriesForMap, $anomalyPath
                );
                $mapFiles['anomalies'] = basename($anomalyPath);
            }
        }

        return [
            'parameters' => $parameters,
            'total_deliveries' => $allDeliveries->count(),
            'total_routes' => $routes->count(),
            'metrics_summary' => $globalIndicators,
            'route_metrics' => $routeMetrics,
            'anomalies' => $anomalies,
            'ranking' => $ranking,
            'deliveries_flat' => $deliveriesFlat,
            'map_files' => $mapFiles,
            'execution_time_sec' => round(microtime(true) - $startTime, 4),
        ];
    }

    public function loadWarehouse(): array
    {
        $all = Setting::whereIn('key', ['warehouse_lat', 'warehouse_lng'])
            ->pluck('value', 'key');

        return [
            'lat' => (float) ($all['warehouse_lat'] ?? -33.045),
            'lng' => (float) ($all['warehouse_lng'] ?? -71.62),
        ];
    }

    private function buildDeliveriesFlat($routes, array $routeMetrics, array $warehouse): array
    {
        $flat = [];
        foreach ($routes as $route) {
            $metric = collect($routeMetrics)->firstWhere('route_id', $route->id);
            $centroidLat = $metric['centroid_lat'] ?? 0;
            $centroidLng = $metric['centroid_lng'] ?? 0;

            foreach ($route->routePackages as $rp) {
                $pkg = $rp->package;
                $distToWarehouse = $this->distanceService->calculate(
                    $warehouse['lat'], $warehouse['lng'],
                    (float) $pkg->latitude, (float) $pkg->longitude
                )['distance_km'];
                $distToCentroid = $this->distanceService->calculate(
                    $centroidLat, $centroidLng,
                    (float) $pkg->latitude, (float) $pkg->longitude
                )['distance_km'];

                $flat[] = [
                    'delivery_id' => $pkg->id,
                    'route_id' => $route->id,
                    'latitude' => (float) $pkg->latitude,
                    'longitude' => (float) $pkg->longitude,
                    'distance_to_warehouse_km' => round($distToWarehouse, 4),
                    'distance_to_centroid_km' => round($distToCentroid, 4),
                ];
            }
        }
        return $flat;
    }
}
