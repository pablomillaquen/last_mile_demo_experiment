# Frontend Routes — Experiment Reporting

## `/experiments`

Experiment explorer — list all experiments.

**Components**: `ExperimentList`, `ExperimentCard`

**Data source**: `GET /api/experiments`

**Navigation**: Main nav link (alongside "Evaluaciones")

## `/experiments/[id]`

Experiment detail — show report, evaluations, and assets.

**Components**: `ExperimentDetail`, `ReportViewer`, `EvaluationList`, `AssetDownload`

**Data source**: `GET /api/experiments/{id}` + `GET /api/experiments/{id}/report`

**Features**:
- Render report markdown with react-markdown
- List evaluations with links to `/evaluations/[id]`
- Download buttons for report.pdf, individual evaluation PDFs, assets

## `/docs/guias`

Guía de Interpretación de Métricas.

**Content**: Static markdown rendered with react-markdown
- Each metric has its own section
- Navigation sidebar with metric names

## `/docs/pantallas`

Documentación de Pantallas.

**Content**: Static markdown rendered with react-markdown
- Section for evaluations list page
- Section for evaluation detail page

## Evaluation Detail (modified)

Add to existing `/evaluations/[id]`:
- **PDF Download button** → calls `GET /api/evaluations/{id}/pdf`
- **Experiment link** → if evaluation.experiment is set, show link to `/experiments/[experiment.id]`
