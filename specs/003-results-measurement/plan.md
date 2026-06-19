# Implementation Plan: Sistema de Medición, Evaluación y Validación de Resultados

**Branch**: `003-results-measurement` | **Date**: 2026-06-19 | **Spec**: [spec.md](spec.md)

**Input**: Feature specification from `/specs/003-results-measurement/spec.md`

## Summary

Implementar un sistema formal de métricas para evaluar la calidad de los agrupamientos de rutas. El sistema calculará 17 métricas (por ruta y globales), detectará anomalías operativas (entregas cercanas ignoradas), medirá separación entre clusters, calculará penalización operacional total, generará evidencia visual (mapas con GD), y exportará resultados en JSON/CSV (incluyendo datos por entrega) con reproducibilidad garantizada mediante `random_seed`, `algorithm` y `algorithm_version`. Todo el cómputo vive en el backend (Laravel Services), con exportación de archivos al sistema de archivos del contenedor.

## Technical Context

**Language/Version**: PHP 8.2 (Laravel 12), TypeScript (Next.js 14) — stack existente

**Primary Dependencies**: phpunit (testing), League\\Csv (exportación CSV), GD Library (PHP nativa — generación de mapas server-side)

**Storage**: PostgreSQL 16 — lectura de datos (packages, routes, route_packages, settings); los resultados de métricas se exportan como archivos (JSON/CSV) y se almacenan en disco, con metadata opcional en BD para trazabilidad

**Testing**: PHPUnit (backend — cálculo de métricas, detección de anomalías, exportación)

**Target Platform**: Docker Compose (Linux containers), sistema de archivos para exportaciones

**Project Type**: Web application (backend API + frontend SPA) — la medición es server-side

**Performance Goals**: Cálculo completo de 17 métricas para 500 entregas / 10 rutas en < 30s (RF-16 incluido)

**Constraints**: Sin APIs externas de mapas/direcciones, sin dependencias de terceros no contempladas en Docker, sin frontend interactivo para métricas

**Scale/Scope**: 1 bodega, hasta 500 entregas, hasta 10 rutas, parámetros configurables (`near_delivery_threshold`, `ignored_delivery_ratio`)

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Principio | Estado | Justificación |
|-----------|--------|---------------|
| Evidencia Antes de Solución | ✅ Pasa | Fase solo mide, no optimiza. Crea la línea base para futuras optimizaciones. |
| Decisiones Medibles | ✅ Pasa | 17 métricas cuantificables definidas en la spec. |
| Complejidad Incremental | ✅ Pasa | Servicio server-side que extiende lógica existente; sin frontend nuevo, sin dashboards. |
| Optimizaciones Comparables | ✅ Pasa | Exportación estructurada permite comparar ejecuciones con diferentes parámetros. |
| Visualización como Análisis | ✅ Pasa | Mapas estáticos generados automáticamente por ruta y a nivel general. |
| Conocimiento Reutilizable | ✅ Pasa | Resultados exportables en JSON/CSV con semilla para reproducibilidad. |
| Docker First | ✅ Pasa | Todo dentro de contenedores existentes; solo se agrega librería PHP para CSV y generación de imágenes server-side. |

**Resultado**: GATE PASS — sin violaciones constitucionales.

## Project Structure

### Documentation (this feature)

```text
specs/003-results-measurement/
├── plan.md              # This file
├── research.md          # Phase 0 output
├── data-model.md        # Phase 1 output
├── quickstart.md        # Phase 1 output
├── contracts/
│   └── api.md           # API contract
├── checklists/
│   └── requirements.md  # Spec quality checklist
└── tasks.md             # Phase 2 output (not created here)
```

### Source Code (repository root)

```text
backend/
├── app/
│   ├── Models/
│   │   ├── Package.php              # Existente
│   │   ├── Route.php                # Existente
│   │   ├── RoutePackage.php         # Existente
│   │   └── Setting.php              # Existente (Fase 2)
│   ├── Services/
│   │   ├── HaversineService.php           # Existente (Fase 2)
│   │   ├── RouteMetricsService.php        # Existente (Fase 2)
│   │   └── MeasurementService.php         # NUEVO — sistema completo de métricas
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── MetricsController.php      # Existente (+ endpoint de evaluación completa)
│   │   │   └── EvaluationController.php   # NUEVO — ejecutar evaluación y exportar
│   │   └── Resources/
│   │       └── EvaluationResource.php     # NUEVO — resource para resultados
│   └── Exports/
│       └── MetricsExporter.php            # NUEVO — exportación JSON/CSV
├── storage/
│   └── app/
│       └── evaluations/                   # NUEVO — directorio para exports
├── database/
│   └── migrations/
│       └── [timestamp]_create_evaluations_table.php  # NUEVO — metadata de ejecuciones
├── composer.json              # (+ league/csv)
├── routes/
│   └── api.php                # (+ POST /api/evaluations, GET /api/evaluations)
└── tests/
    └── Feature/
        └── MeasurementTest.php            # NUEVO — tests de métricas
```

**Sin cambios en frontend**: la especificación excluye explícitamente UI interactiva. La evidencia visual se genera como archivos de imagen desde el backend.

**Structure Decision**: Option 2 (Web application) — misma estructura existente. El nuevo servicio (`MeasurementService`) sigue el patrón de `RouteMetricsService`.

## Complexity Tracking

Sin violaciones constitucionales detectadas.

---

## Phases

### Phase 0: Research

**Tasks**:
1. Documentar decisión de generación de mapas server-side (GD nativa vs alternativas)
2. Documentar estrategia de exportación (formato, estructura de directorios, nomenclatura de archivos)
3. Documentar esquema de tabla evaluations (metadatos de ejecución)
4. Verificar stack actual: dependencias PHP disponibles en Docker

**Output**: `research.md`

### Phase 1: Design & Contracts

**Tasks**:
1. Definir `data-model.md` con entidad Evaluation, campos de exportación y estructuras de datos de métricas
2. Definir contrato API en `contracts/api.md` (endpoints de evaluación)
3. Crear `quickstart.md` con escenarios de validación
4. Actualizar `AGENTS.md`

**Outputs**: `data-model.md`, `contracts/api.md`, `quickstart.md`, AGENTS.md actualizado

### Phase 2: Tasks (future — `/speckit.tasks`)

**Nota**: La descomposición en tareas concretas se generará con el comando `/speckit.tasks`.
