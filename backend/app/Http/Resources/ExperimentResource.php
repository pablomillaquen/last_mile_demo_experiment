<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExperimentResource extends JsonResource
{
    protected array $resolvedEvaluations = [];

    public function resolvedEvaluations(array $evaluations): static
    {
        $this->resolvedEvaluations = $evaluations;
        return $this;
    }

    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'identifier' => $this->identifier,
            'name' => $this->name,
            'description' => $this->description,
            'objective' => $this->objective,
            'hypothesis' => $this->hypothesis,
            'baseline_evaluation_id' => $this->baseline_evaluation_id,
            'evaluation_ids' => $this->evaluation_ids ?? [],
            'author' => $this->author,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'evaluations_count' => count($this->evaluation_ids ?? []),
            'report_url' => url("/api/experiments/{$this->id}/report"),
            'report_pdf_url' => url("/api/experiments/{$this->id}/report.pdf"),
        ];

        if (!empty($this->resolvedEvaluations)) {
            $data['evaluations'] = EvaluationResource::collection($this->resolvedEvaluations);
        }

        return $data;
    }
}
