<?php

namespace App\Http\Controllers;

use App\Models\Package;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PackageController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Package::with('routePackage');

        if ($request->has('assigned')) {
            $assigned = filter_var($request->input('assigned'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($assigned === true) {
                $query->whereHas('routePackage');
            } elseif ($assigned === false) {
                $query->whereDoesntHave('routePackage');
            }
        }

        $packages = $query->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 15));

        return response()->json($packages);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tracking_number' => 'required|string|max:100|unique:packages,tracking_number',
            'recipient_name' => 'required|string|max:255',
            'delivery_address' => 'required|string',
            'district' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'received_at' => 'nullable|date',
        ]);

        $package = Package::create($validated);
        $package->load('routePackage');

        return response()->json($package, Response::HTTP_CREATED);
    }

    public function show(Package $package): JsonResponse
    {
        $package->load('routePackage');

        return response()->json($package);
    }

    public function update(Request $request, Package $package): JsonResponse
    {
        $validated = $request->validate([
            'tracking_number' => 'sometimes|required|string|max:100|unique:packages,tracking_number,' . $package->id,
            'recipient_name' => 'sometimes|required|string|max:255',
            'delivery_address' => 'sometimes|required|string',
            'district' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'latitude' => 'sometimes|required|numeric|between:-90,90',
            'longitude' => 'sometimes|required|numeric|between:-180,180',
            'received_at' => 'nullable|date',
        ]);

        $package->update($validated);
        $package->load('routePackage');

        return response()->json($package);
    }

    public function destroy(Package $package): JsonResponse
    {
        $package->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
