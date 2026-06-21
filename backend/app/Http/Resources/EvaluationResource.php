<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EvaluationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'executed_at' => $this->executed_at->toIso8601String(),
            'total_deliveries' => $this->total_deliveries,
            'total_routes' => $this->total_routes,
            'metrics_summary' => $this->metrics_summary,
        ];

        if ($this->relationLoaded('detailedMetrics')) {
            $detailed = $this->detailedMetrics;
            $data['route_metrics'] = $detailed['route_metrics'] ?? [];
            $data['anomalies']     = $detailed['anomalies']  ?? [];
            $data['ranking']       = $detailed['ranking']    ?? [];
            $data['route_legs']    = $detailed['route_legs'] ?? [];
            $raw = $detailed['files'] ?? null;
            $data['files'] = is_array($raw) && !empty($raw) ? $raw : null;
        }

        if ($this->output_path !== null) {
            $data['output_path'] = $this->output_path;
        }

        if ($this->parameters !== null) {
            $data['parameters'] = $this->parameters;
        }

        return $data;
    }
}
