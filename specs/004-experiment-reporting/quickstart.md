# Quickstart — Experiment Reporting & Documentation System

**Spec**: SPEC-004 | **Branch**: `004-experiment-reporting`

## Prerequisites

- SPEC-003 evaluation system is operational
- Docker Compose environment is running
- At least one evaluation exists (run `php artisan eval:run` or use the "Run Evaluation" button)

## Setup

### 1. Install backend dependencies

```bash
docker compose exec backend composer require barryvdh/laravel-dompdf
```

### 2. Run database migrations

```bash
docker compose exec backend php artisan migrate
```

Adds `experiments` table (no changes to the existing `evaluations` table).

### 3. Install frontend dependencies

```bash
docker compose exec frontend npm install react-markdown remark-gfm
```

### 4. Migrate existing experiment to formal structure

The `001-baseline-comparison` experiment exists as a bare `report.md`. Create its metadata file:

```bash
cat > experiments/001-baseline-comparison/experiment.json << 'EOF'
{
  "identifier": "001-baseline-comparison",
  "name": "Comparación de Evaluaciones (Baseline)",
  "objective": "Establecer baseline cuantitativo antes de optimización.",
  "hypothesis": null,
  "baseline_evaluation_id": 2,
  "evaluation_ids": [2, 3, 4, 5, 6, 7],
  "author": "Sistema"
}
EOF
```

Move the existing report to the new structure:
```bash
mv experiments/001-baseline-comparison/report.md experiments/001-baseline-comparison/report.md.bak
# Re-create report.md with front-matter removed if needed, or keep as-is
```

### 5. Sync experiments to database

```bash
docker compose exec backend php artisan experiments:sync
```

This reads `experiments/` directory and populates the database.

### 6. Restart containers

```bash
docker compose restart
```

## Validation Scenarios

### Scenario 1: View experiment list

1. Open `http://localhost:3000/experiments`
2. Verify at least "001 - Comparación de Evaluaciones (Baseline)" appears
3. Verify metrics (evaluations count, date) are displayed

### Scenario 2: View experiment detail

1. Click on an experiment
2. Verify report.md content renders with markdown formatting (tables, headings, etc.)
3. Verify associated evaluations are listed with links
4. Verify "Descargar PDF" button is present

### Scenario 3: Reverse traceability

1. Open `http://localhost:3000/evaluations/2`
2. Verify the page shows a linked experiment name (if associated)
3. Click the experiment link → should navigate to experiment detail

### Scenario 4: PDF generation

1. Open `http://localhost:3000/evaluations/2`
2. Click "Descargar PDF" button
3. Verify a PDF file downloads with:
   - Cover page with evaluation title and date
   - Metric cards
   - Ranking table
   - Route metrics table
   - Anomaly table (if applicable)
   - Embedded map images (overview, anomalies)
   - Reproducibility footer

### Scenario 5: Guía de interpretación

1. Open `http://localhost:3000/docs/guias`
2. Verify all 15 metrics are documented with name, definition, formula, interpretation, examples
3. Time a non-technical reader: should understand any metric in < 30 min (CA-01)

## Creating a New Experiment

Experiments are created manually:

```bash
mkdir -p experiments/002-nombre-experimento/assets
```

Create `experiments/002-nombre-experimento/experiment.json`:
```json
{
  "identifier": "002-nombre-experimento",
  "name": "Nombre del Experimento",
  "objective": "Pregunta de investigación",
  "hypothesis": "Resultado esperado",
  "baseline_evaluation_id": 2,
  "evaluation_ids": [8, 9, 10]
}
```

Create `experiments/002-nombre-experimento/report.md` with narrative content.

Run sync:
```bash
docker compose exec backend php artisan experiments:sync
```

## File Reference

| File | Purpose |
|------|---------|
| `backend/app/Models/Experiment.php` | Experiment Eloquent model |
| `backend/app/Http/Controllers/ExperimentController.php` | Experiment API endpoints |
| `backend/app/Services/PdfReportService.php` | PDF generation using dompdf |
| `backend/app/Repositories/ExperimentRepository.php` | Filesystem access for experiments |
| `backend/app/Console/Commands/SyncExperiments.php` | `experiments:sync` artisan command |
| `backend/database/migrations/xxxx_create_experiments_table.php` | Migration for experiments table (no changes to evaluations) |
| `backend/routes/api.php` | Added experiment routes |
| `backend/resources/docs/guia-de-metricas.md` | Metric interpretation guide |
| `backend/resources/docs/pantallas/evaluaciones.md` | Evaluations list page docs |
| `backend/resources/docs/pantallas/detalle-evaluacion.md` | Evaluation detail page docs |
| `frontend/src/app/experiments/page.tsx` | Experiment list page |
| `frontend/src/app/experiments/[id]/page.tsx` | Experiment detail page |
| `frontend/src/app/docs/guias/page.tsx` | Metric guide page |
| `frontend/src/app/docs/pantallas/page.tsx` | Screen documentation page |
| `frontend/src/components/ExperimentCard.tsx` | Experiment card component |
| `frontend/src/components/PdfDownloadButton.tsx` | PDF download button component |
