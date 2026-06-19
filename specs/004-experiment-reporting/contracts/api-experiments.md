# API Contract — Experiments

## `GET /api/experiments`

List all experiments.

**Response `200`**:
```json
{
  "data": [
    {
      "id": 1,
      "identifier": "001-baseline-comparison",
      "name": "Comparación de Evaluaciones (Baseline)",
      "description": "Primer experimento del proyecto.",
      "objective": "Establecer baseline cuantitativo antes de optimización.",
      "hypothesis": null,
      "baseline_evaluation_id": 2,
      "evaluation_ids": [2, 3, 4, 5, 6, 7],
      "author": "Sistema",
      "created_at": "2026-06-19T13:00:00-04:00",
      "updated_at": "2026-06-19T13:00:00-04:00",
      "evaluations_count": 6,
      "report_url": "/api/experiments/1/report",
      "report_pdf_url": "/api/experiments/1/report.pdf"
    }
  ]
}
```

## `GET /api/experiments/{id}`

Get experiment detail with full evaluation list.

**Response `200`**:
```json
{
  "id": 1,
  "identifier": "001-baseline-comparison",
  "name": "Comparación de Evaluaciones (Baseline)",
  "description": "Primer experimento del proyecto.",
  "objective": "Establecer baseline cuantitativo antes de optimización.",
  "hypothesis": null,
  "baseline_evaluation_id": 2,
  "evaluation_ids": [2, 3, 4, 5, 6, 7],
  "author": "Sistema",
  "created_at": "2026-06-19T13:00:00-04:00",
  "updated_at": "2026-06-19T13:00:00-04:00",
  "evaluations": [
    {
      "id": 2,
      "executed_at": "2026-06-19T12:00:00-04:00",
      "total_deliveries": 300,
      "total_routes": 10,
      "metrics_summary": { ... }
    }
  ],
  "report_url": "/api/experiments/1/report",
  "report_pdf_url": "/api/experiments/1/report.pdf"
}
```

**Response `404`**: `{ "message": "Experiment not found" }`

## `GET /api/experiments/{id}/report`

Serve the experiment's report markdown content.

**Response `200`**: Plain text (`Content-Type: text/markdown`)
```
# Experimento 001: ...
```

**Response `404`**: No report.md found

## `GET /api/experiments/{id}/report.pdf`

Serve the experiment's PDF report (if generated).

**Response `200`**: Application PDF (`Content-Type: application/pdf`)

**Response `404`**: No report.pdf found

## `GET /api/experiments/{id}/assets/{filename}`

Serve an asset file from the experiment's `assets/` directory.

**Response `200`**: File with appropriate Content-Type (image/png, application/json, text/csv)

**Response `404`**: File not found

## `GET /api/evaluations/{id}` (modified)

Resolve experiment from `experiments` table via `JSON_CONTAINS(evaluation_ids, ?)`.

**Additional field in Response `200`** (only if a matching experiment exists):
```json
{
  "experiment": {
    "id": 1,
    "identifier": "001-baseline-comparison",
    "name": "Comparación de Evaluaciones (Baseline)"
  }
}
```

Note: No changes to the `evaluations` database table. The experiment relationship is computed at query time from the `experiments` table.

## `GET /api/evaluations/{id}/pdf`

Generate and serve PDF report for a specific evaluation.

**Response `200`**: Application PDF (`Content-Type: application/pdf`)
- First call generates PDF and caches to disk
- Subsequent calls serve the cached file
- Includes reproducibility metadata on each page

**Response `404`**: Evaluation not found
