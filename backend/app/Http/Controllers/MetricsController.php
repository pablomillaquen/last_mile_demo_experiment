<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\Route;
use App\Models\RoutePackage;
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

        return response()->json([
            'total_packages' => $totalPackages,
            'total_routes' => $totalRoutes,
            'packages_per_route' => [
                'average' => $totalRoutes > 0 ? round($totalPackages / $totalRoutes, 1) : 0,
                'min' => $perRoute->min() ?: 0,
                'max' => $perRoute->max() ?: 0,
            ],
            'unassigned_packages' => $unassigned,
        ]);
    }
}
