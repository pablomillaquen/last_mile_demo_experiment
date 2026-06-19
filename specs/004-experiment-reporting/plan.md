# Implementation Plan: Experiment Reporting & Documentation System

**Branch**: `004-experiment-reporting` | **Date**: 2026-06-19 | **Spec**: [spec.md](../spec.md)

**Input**: Feature specification from `specs/004-experiment-reporting/spec.md`

**Note**: This template is filled in by the `/speckit.plan` command. See `.specify/templates/plan-template.md` for the execution workflow.

## Summary

Agregar una capa de documentación y reporting sobre el sistema de evaluación existente (SPEC-003). Incluye: guía de interpretación de métricas, documentación de pantallas, generación de reportes PDF por evaluación, informes experimentales narrativos, repositorio formal de experimentos con trazabilidad, y un explorador web de solo lectura.

## Technical Context

**Language/Version**: PHP 8.2 (backend), TypeScript (frontend/Next.js 14)

**Primary Dependencies**:
- Backend: Laravel 12, `barryvdh/laravel-dompdf` (generación de PDF, funciona sin binarios externos en Docker)
- Frontend: Next.js 14, `react-markdown` (renderizado de informes markdown en explorador de experimentos)
- Filesystem: Almacenamiento local en `storage/app/private/experiments/`

**Storage**: 
- Evaluaciones: PostgreSQL 16 (existente, SPEC-003)
- Experimentos: Sistema de archivos (directorios estructurados con metadatos en YAML front-matter o JSON sidecar)
- Reportes PDF: Sistema de archivos

**Testing**: Sin tests automatizados (no especificados en spec)

**Target Platform**: Linux (Docker Compose)

**Project Type**: Web application (frontend + backend)

**Performance Goals**: 
- Generación de PDF en menos de 10 segundos por evaluación (CA-06)
- Listado de experimentos en menos de 2 segundos (CA-16)

**Constraints**: 
- Sin dependencias externas de APIs de mapas o direcciones
- Sin binarios adicionales en Docker (dompdf evita wkhtmltopdf/snappy)
- El explorador de experimentos es de solo lectura (los experimentos se crean manualmente)

**Scale/Scope**: Decenas de experimentos, cientos de evaluaciones

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Principio | Evaluación | Estado |
|-----------|-----------|--------|
| I. Evidencia Antes de Solución | No introduce optimización antes de medición. Documenta evidencia existente. | ✅ Pasa |
| II. Decisiones Medibles | PDF reports, experiment structure, portfolio artifacts comunican decisiones medibles. | ✅ Pasa |
| III. Complejidad Incremental | Excluye auth, multi-tenant, formatos adicionales, comparación multi-experimento. | ✅ Pasa |
| IV. Modelado de Escenarios Reales | No aplica directamente (no introduce nuevas entidades de dominio logístico). | ✅ Pasa |
| V. Optimizaciones Comparables | La estructura de experimentos está diseñada para permitir comparación. | ✅ Pasa |
| VI. Visualización como Análisis | PDF incluye mapas generados. Excluye gráficos interactivos Leaflet. | ✅ Pasa |
| VII. Conocimiento Reutilizable | Objetivo central del spec. Guía, informes, y artefactos de portafolio. | ✅ Pasa |
| VIII. Docker First | dompdf funciona sin binarios externos en Docker. Sin dependencias de sistema. | ✅ Pasa |

**Resultado**: Todas las puertas pasan. No se requieren justificaciones de complejidad.

## Project Structure

### Documentation (this feature)

```text
specs/004-experiment-reporting/
├── plan.md              # This file
├── research.md          # Phase 0 output — PDF library selection, markdown rendering approach
├── data-model.md        # Phase 1 output — Experiment entity, metadata schema
├── quickstart.md        # Phase 1 output — validation scenarios
├── contracts/           # Phase 1 output — API endpoints for PDF generation, experiment explorer
└── tasks.md             # Phase 2 output (/speckit.tasks command)
```

### Source Code (repository root)

```text
backend/
├── app/
│   ├── Services/
│   │   └── PdfReportService.php      # Generación de PDF con dompdf
│   ├── Http/Controllers/
│   │   └── ExperimentController.php  # Endpoints para explorador de experimentos
│   └── Exports/
│       └── ExperimentExport.php      # Exportación de artefactos
├── storage/app/private/
│   ├── evaluations/                  # Existente (SPEC-003)
│   └── experiments/                  # Reportes PDF generados
├── routes/
│   └── api.php                       # + rutas de experimentos
└── resources/
    └── docs/
        ├── guia-de-metricas.md       # Guía de interpretación
        └── pantallas/                # Documentación de pantallas
            ├── evaluaciones.md
            └── detalle-evaluacion.md

frontend/
├── src/
│   ├── app/
│   │   ├── experiments/
│   │   │   ├── page.tsx              # Explorador de experimentos (lista)
│   │   │   └── [id]/
│   │   │       └── page.tsx          # Detalle de experimento
│   │   └── docs/
│   │       ├── guia/
│   │       │   └── page.tsx          # Guía de interpretación
│   │       └── pantallas/
│   │           └── page.tsx          # Documentación de pantallas
│   ├── components/
│   │   ├── ExperimentCard.tsx        # Tarjeta de experimento
│   │   └── PdfDownloadButton.tsx     # Botón de descarga PDF
│   └── lib/
│       └── api.ts                    # + tipos/funciones para experimentos
```

**Structure Decision**: Web application (frontend + backend), misma estructura que SPEC-003 y fases anteriores. No se requiere cambiar la estructura existente.
