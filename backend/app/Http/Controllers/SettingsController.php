<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(
            Setting::pluck('value', 'key')
        );
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'warehouse_lat' => 'sometimes|numeric|between:-90,90',
            'warehouse_lng' => 'sometimes|numeric|between:-180,180',
            'average_speed_kmh' => 'sometimes|integer|min:1',
        ]);

        foreach ($validated as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => (string) $value]
            );
        }

        return response()->json(
            Setting::pluck('value', 'key')
        );
    }
}
