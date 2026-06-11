<?php

namespace App\Http\Controllers;

use App\Models\RoutePackage;
use App\Models\Route;
use App\Models\Package;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RouteAssignmentController extends Controller
{
    public function assign(Request $request, Route $route): JsonResponse
    {
        $validated = $request->validate([
            'package_id' => 'required|exists:packages,id',
            'sequence' => 'nullable|integer|min:1',
        ]);

        $exists = RoutePackage::where('package_id', $validated['package_id'])->exists();
        if ($exists) {
            return response()->json(['message' => 'El paquete ya está asignado a una ruta'], Response::HTTP_CONFLICT);
        }

        $routePackage = RoutePackage::create([
            'route_id' => $route->id,
            'package_id' => $validated['package_id'],
            'sequence' => $validated['sequence'] ?? null,
            'assigned_at' => now(),
        ]);

        $routePackage->load('package');

        return response()->json($routePackage, Response::HTTP_CREATED);
    }

    public function unassign(Request $request, Route $route): JsonResponse
    {
        $validated = $request->validate([
            'package_id' => 'required|exists:packages,id',
        ]);

        RoutePackage::where('route_id', $route->id)
            ->where('package_id', $validated['package_id'])
            ->delete();

        return response()->json(['message' => 'Paquete desasignado']);
    }
}
