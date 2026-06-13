<?php

namespace App\Http\Controllers;

use App\Models\Route;
use App\Services\RouteMetricsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RouteController extends Controller
{
    public function index(): JsonResponse
    {
        $routes = Route::withCount('routePackages')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $metricsService = app(RouteMetricsService::class);

        $routes->getCollection()->transform(function ($route) use ($metricsService) {
            $metrics = $metricsService->getRouteMetrics($route);
            $route->total_distance_km = $metrics['total_distance_km'];
            $route->avg_distance_per_delivery_km = $metrics['avg_distance_per_delivery_km'];
            $route->estimated_time = $metrics['estimated_time_formatted'];
            $route->deliveries_count = $route->route_packages_count;
            return $route;
        });

        return response()->json($routes);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'route_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $route = Route::create($validated);

        return response()->json($route, Response::HTTP_CREATED);
    }

    public function show(Route $route): JsonResponse
    {
        $route->loadCount('routePackages');
        $route->load(['routePackages' => function ($query) {
            $query->orderBy('sequence');
        }, 'routePackages.package']);

        $metrics = app(RouteMetricsService::class)->getRouteMetrics($route);
        $route->total_distance_km = $metrics['total_distance_km'];
        $route->avg_distance_per_delivery_km = $metrics['avg_distance_per_delivery_km'];
        $route->estimated_time = $metrics['estimated_time_formatted'];
        $route->deliveries_count = $route->route_packages_count;

        return response()->json($route);
    }

    public function update(Request $request, Route $route): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'route_date' => 'sometimes|required|date',
            'notes' => 'nullable|string',
        ]);

        $route->update($validated);

        return response()->json($route);
    }

    public function destroy(Route $route): JsonResponse
    {
        $route->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
