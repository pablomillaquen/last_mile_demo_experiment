# Implementation Plan: Visualización de Red Vial y Comparación Geodésico vs OSRM

**Branch**: `007-road-network-visualization` | **Date**: 2026-06-21 | **Spec**: [spec.md](spec.md)

**Input**: Feature specification from `specs/007-road-network-visualization/spec.md`

## Summary

Agregar geometría vial OSRM al pipeline de evaluación (backend) y modificar el visor Leaflet (frontend) para renderizar rutas geodésicas o viales según un selector de modo. No se crean nuevas métricas ni se modifican experimentos existentes.

## Technical Context

**Language/Version**: PHP 8.2 (Laravel 12 backend), TypeScript (NextJS 14 frontend)

**Primary Dependencies**: 
- Backend: GuzzleHttp (OSRM client), `pcrov/unicode` or custom decoder for OSRM polyline
- Frontend: Leaflet 1.9.4, react-leaflet 4.2.1

**Storage**: 
- Geometría vial se almacena en disco como parte del evaluation.json (artefacto D009)
- No se agregan columnas nuevas a la base de datos
- La API `GET /evaluations/{id}` ya lee evaluation.json → incluirá geometría automáticamente

**Testing**: 
- Backend: PHPUnit (modificar tests existentes de OsrmClient, DistanceService)
- Frontend: Sin framework de tests definido → validación manual vía quickstart

**Target Platform**: Docker Compose (Linux containers), navegador moderno

**Project Type**: Web application (Laravel API + NextJS frontend) con OSRM como servicio externo

**Performance Goals**: Cambio de modo <200ms (RNF1), geometría precalculada sin llamadas OSRM en frontend

**Constraints**: 
- EXP-001 inmutable, solo lectura
- Frontend no consume OSRM directamente (solo backend)
- Fallback explícito a geodésico cuando no hay geometría vial (RF10)
- Geometría respeta route_packages.sequence (RF11)

**Scale/Scope**: ~12 evaluaciones existentes (EXP-001 + EXP-002), ~10 rutas por evaluación, ~300 deliveries por evaluación

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Principio | Estado | Justificación |
|---|---|---|
| I. Evidencia Antes de Solución | ✅ Pasa | La evidencia vial ya existe (EXP-002), este SPEC la hace visible |
| II. Decisiones Medibles | ✅ Pasa | CA3 (coherencia visual vs métrica <1%) es verificable |
| III. Complejidad Incremental | ✅ Pasa | Solo visualización, sin nuevos modelos ni métricas |
| IV. Modelado de Escenarios Reales | ✅ Pasa | Rutas viales reflejan calles reales (OSRM) |
| V. Optimizaciones Comparables | ✅ Pasa | Modo dual geodésico/vial permite comparación visual directa |
| VI. Visualización como Análisis | ✅ Pasa | Aplicación directa del principio |
| VII. Conocimiento Reutilizable | ✅ Pasa | Las capturas de mapa vial serán material de portafolio |
| VIII. Docker First | ✅ Pasa | Sin cambios en infraestructura Docker; OSRM ya está contenerizado |

**Resultado**: GATE SUPERADO — Sin violaciones. No se requiere Complexity Tracking.

## Project Structure

### Documentation (this feature)

```text
specs/007-road-network-visualization/
├── plan.md              # This file
├── research.md          # Phase 0: decisions on geometry storage, OSRM params, API shape
├── data-model.md        # Phase 1: entity changes (geometry in evaluation.json, frontend types)
├── quickstart.md        # Phase 1: validation scenarios (1-8 from test protocol)
├── contracts/           # Phase 1: API contract for geometry, MapView component contract
└── tasks.md             # Future: /speckit.tasks output
```

### Source Code (repository root)

```text
backend/
├── app/
│   └── Services/
│       ├── OsrmClient.php           # [MODIFICAR] overview=full, devolver geometry
│       ├── DistanceService.php       # [MODIFICAR] pasar geometry en respuesta
│       └── MeasurementService.php    # [MODIFICAR] almacenar geometry por leg en evaluation.json
└── tests/
    └── ...                           # Actualizar tests existentes

frontend/
├── src/
│   ├── lib/
│   │   └── api.ts                   # [MODIFICAR] tipos RouteGeometry, Evaluation.legs
│   ├── components/
│   │   └── MapView.tsx              # [MODIFICAR] dual-mode + fallback
│   └── app/
│       ├── map/page.tsx             # [MODIFICAR] cargar geometry, toggle
│       └── evaluations/[id]/page.tsx # [MODIFICAR] mapa interactivo + toggle
```

**Structure Decision**: Proyecto web existente con frontend (NextJS) + backend (Laravel). Los cambios son puntuales sobre archivos existentes; no se crean nuevos directorios de código fuente.

## Complexity Tracking

*No aplica — sin violaciones constitucionales.*
