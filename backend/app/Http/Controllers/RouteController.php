<?php

namespace App\Http\Controllers;

use App\Models\Route;
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
        $route->load('routePackages.package');

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
