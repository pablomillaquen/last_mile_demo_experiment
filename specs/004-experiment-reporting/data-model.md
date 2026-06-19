# Data Model — Experiment Reporting & Documentation System

**Spec**: SPEC-004 | **Date**: 2026-06-19

## 0. Source of Truth

> **The filesystem is the source of truth. The database is a read-optimized cache generated from the filesystem and must never be considered authoritative.**

All experiment metadata originates in `experiments/<identifier>/experiment.json`. The `experiments:sync` artisan command reads the filesystem and populates the database for fast querying. If the database and filesystem diverge, the filesystem wins.

## 1. New Database Table

### `experiments`

Read-optimized cache synced from `experiments/` directory via `experiments:sync`.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | bigint | PK, auto-increment | Internal ID |
| identifier | varchar(100) | UNIQUE, NOT NULL | Directory name (e.g. `001-baseline-comparison`) |
| name | varchar(255) | NOT NULL | Human-readable name |
| description | text | nullable | Free-text description |
| objective | text | NOT NULL | Research question |
| hypothesis | text | nullable | Expected outcome |
| baseline_evaluation_id | bigint | FK → evaluations.id, nullable | Reference evaluation for comparison |
| evaluation_ids | jsonb | NOT NULL, default '[]' | Ordered list of evaluation IDs that compose this experiment |
| author | varchar(255) | nullable | Author name |
| created_at | timestamp | auto | Sync creation time |
| updated_at | timestamp | auto | Sync update time |

### No changes to `evaluations` table

The relationship between experiments and evaluations is defined by `experiments.evaluation_ids`. Reverse traceability (evaluation → experiment) is achieved via `WHERE JSON_CONTAINS(evaluation_ids, ?)`.

This avoids redundant storage and keeps the `evaluations` table unchanged from SPEC-003.

**Future note**: If cross-experiment comparison requires an N:M relationship (an evaluation referenced by multiple experiments), add a pivot table `experiment_evaluation(experiment_id, evaluation_id)` in a later spec. This is backward-compatible — the JSON array continues to work for single-ownership queries.

## 2. Filesystem Structure

### Experiment Directory

```text
experiments/
├── 001-baseline-comparison/
│   ├── experiment.json      # Structured metadata (source of truth for DB sync)
│   ├── report.md            # Narrative informe experimental
│   ├── report.pdf           # Generated PDF (optional, created by build command)
│   └── assets/              # Artifacts associated with this experiment
│       ├── evaluation-2-report.pdf
│       └── ...
```

### `experiment.json` Schema

```json
{
  "id": 1,
  "identifier": "001-baseline-comparison",
  "name": "Comparación de Evaluaciones (Baseline)",
  "description": "Primer experimento del proyecto. Establece el baseline cuantitativo.",
  "objective": "Establecer baseline cuantitativo antes de optimización.",
  "hypothesis": null,
  "baseline_evaluation_id": 2,
  "evaluation_ids": [2, 3, 4, 5, 6, 7],
  "author": "Sistema",
  "created_at": "2026-06-19T13:00:00-04:00"
}
```

### PDF Storage

Generated PDFs are stored alongside evaluation data:

```text
storage/app/private/
├── evaluations/
│   └── YYYYMMDD_HHmmss/
│       ├── evaluation.json
│       ├── evaluation.csv
│       ├── deliveries.csv
│       ├── map_overview.png
│       ├── map_route_*.png
│       ├── map_anomalies.png
│       └── report.pdf               # ← NEW: generated evaluation PDF
```

## 3. Domain Entities

### PHP Models

```php
// backend/app/Models/Experiment.php
class Experiment extends Model {
    protected $fillable = [
        'identifier', 'name', 'description', 'objective',
        'hypothesis', 'baseline_evaluation_id', 'evaluation_ids', 'author'
    ];

    protected function casts(): array {
        return [
            'evaluation_ids' => 'array',
        ];
    }

    public function evaluations(): HasMany {
        return $this->hasMany(Evaluation::class)
            ->whereIn('id', $this->evaluation_ids ?? []);
    }

    public function baselineEvaluation(): BelongsTo {
        return $this->belongsTo(Evaluation::class, 'baseline_evaluation_id');
    }
}

// backend/app/Models/Evaluation.php (no changes)
// Evaluation model remains exactly as defined in SPEC-003.
// Reverse traceability: Experiment::whereJsonContains('evaluation_ids', $id)->first()
```

### TypeScript Interfaces

```typescript
// frontend/src/lib/api.ts (additions)

export interface Experiment {
  id: number;
  identifier: string;
  name: string;
  description: string | null;
  objective: string;
  hypothesis: string | null;
  baseline_evaluation_id: number | null;
  evaluation_ids: number[];
  author: string | null;
  created_at: string;
  updated_at: string;
  evaluations?: Evaluation[];
  report_url?: string;
  report_pdf_url?: string;
}

// Note: Evaluation interface is NOT modified. No experiment_id field.
// Reverse traceability from Evaluation → Experiment is resolved by the API
// endpoint which JOINs experiments via JSON_CONTAINS.
```

## 4. Filesystem Repository Pattern

The `ExperimentRepository` reads from the filesystem for report content and assets, while the `Experiment` Eloquent model provides fast metadata queries.

```php
class ExperimentRepository {
    /**
     * Get the markdown content of an experiment's report.
     */
    public function getReportContent(Experiment $experiment): ?string;

    /**
     * Get the list of asset files for an experiment.
     */
    public function getAssets(Experiment $experiment): array;

    /**
     * Get the filesystem path for an experiment's directory.
     */
    public function getPath(Experiment $experiment): string;
}
```

## 5. Sync Process

### `experiments:sync` Artisan Command

1. Scan `experiments/` directory for entries matching `NNN-*` pattern
2. For each, read `experiment.json`
3. Create or update `experiments` DB row (matched by `identifier`)
4. Delete DB rows whose identifier no longer has a matching directory
5. Validate `evaluation_ids` — if an evaluation ID doesn't exist, log a warning (don't fail, don't auto-create)
6. Output summary: `Created: N, Updated: N, Deleted: N, Warnings: N`

## 6. PDF Report Schema

### Reproducibility Metadata (embedded in PDF metadata or first page footer)

```
spec_version: SPEC-004
evaluation_id: {id}
algorithm: {parameters.algorithm}
algorithm_version: {parameters.algorithm_version}
dataset: {parameters.dataset}
generated_at: {current ISO timestamp}
```

### PDF Page Structure

1. **Cover page**: Title "Reporte de Evaluación #{id}", date, algorithm + version, dataset
2. **Resumen Ejecutivo**: Key metric cards (distance, coverage, anomalies, penalty)
3. **Parámetros**: Threshold, ratio, random seed, warehouse location
4. **Ranking de Rutas**: Ordered by proximity to warehouse (table)
5. **Métricas por Ruta**: Full metric table
6. **Anomalías**: Detection table (if any)
7. **Mapas**: Overview map PNG, anomalies map PNG (embedded)
8. **Footer**: Reproducibility metadata on each page
