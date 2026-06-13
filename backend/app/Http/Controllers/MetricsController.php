<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\Route;
use App\Models\RoutePackage;
use App\Services\RouteMetricsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class MetricsController extends Controller
{
    public function index(): JsonResponse
    {
        $totalPackages = Package::count();
        $totalRoutes = Route::count();

        $perRoute = RoutePackage::select('route_id', DB::raw('COUNT(*) as count'))
            ->groupBy('route_id')
            ->pluck('count');

        $unassigned = Package::whereDoesntHave('routePackage')->count();

        $metricsService = app(RouteMetricsService::class);
        $allRoutes = Route::withCount('routePackages')->get();

        $longest = null;
        $shortest = null;

        foreach ($allRoutes as $route) {
            $metrics = $metricsService->getRouteMetrics($route);

            if ($metrics['total_distance_km'] <= 0) {
                continue;
            }

            $entry = [
                'name' => $route->name,
                'deliveries_count' => $route->route_packages_count,
                'total_distance_km' => $metrics['total_distance_km'],
                'avg_distance_per_delivery_km' => $metrics['avg_distance_per_delivery_km'],
                'estimated_time' => $metrics['estimated_time_formatted'],
            ];

            if ($longest === null || $metrics['total_distance_km'] > $longest['total_distance_km']) {
                $longest = $entry;
            }

            if ($shortest === null || $metrics['total_distance_km'] < $shortest['total_distance_km']) {
                $shortest = $entry;
            }
        }

        $speed = (int) round((float) \App\Models\Setting::where('key', 'average_speed_kmh')->value('value') ?: 30);

        return response()->json([
            'total_packages' => $totalPackages,
            'total_routes' => $totalRoutes,
            'packages_per_route' => [
                'average' => $totalRoutes > 0 ? round($totalPackages / $totalRoutes, 1) : 0,
                'min' => $perRoute->min() ?: 0,
                'max' => $perRoute->max() ?: 0,
            ],
            'unassigned_packages' => $unassigned,
            'route_metrics' => [
                'longest_route' => $longest,
                'shortest_route' => $shortest,
                'average_speed_kmh' => $speed,
            ],
        ]);
    }
}
