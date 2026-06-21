# Implementation Plan: Incorporación de Red Vial Real y Revalidación Experimental

**Branch**: `006-road-network-integration` | **Date**: 2026-06-20 | **Spec**: [spec.md](spec.md)

**Input**: Feature specification from `/specs/006-road-network-integration/spec.md`

## Summary

Integrar OpenStreetMap + OSRM en el stack Docker, adaptar el motor de evaluación (MeasurementService) para soportar dos modos de distancia (geodésico/vial) mediante el patrón Strategy (DistanceService), reejecutar evaluaciones baseline emparejadas por parámetros equivalentes sobre la red vial, calcular métricas de error M001–M006 desde pares experimentales en Exp002, generar reporte comparativo, y revalidar formalmente los hallazgos H001–H006 mediante la nueva categoría de evidencia V (Validaciones).

## Technical Context

**Language/Version**: PHP 8.2 (Laravel 12), Python/Shell (OSRM data prep)

**Primary Dependencies**: OSRM Docker (`ghcr.io/project-osrm/osrm-backend`), GuzzleHttp (PHP HTTP client para OSRM API interna), PHPUnit (testing)

**Storage**: PostgreSQL 16 — sin cambios estructurales (Evaluation.parameters JSONB existente almacena `distance_mode`). OSRM graph en volumen Docker persistente. Resultados de Exp002 en disco (`storage/app/evaluations/`).

**Testing**: PHPUnit existente (no se desarrollan suites nuevas para SPEC-006). Validación manual y experimental siguiendo `quickstart.md`.

**Target Platform**: Docker Compose (Linux containers), OSRM como servicio adicional

**Performance Goals**: Cálculo vial para 300 entregas / 10 rutas ≤ 10× del geodésico actual (RNF2)

**Constraints**: Sin APIs externas (RNF1). Datos OSM de Gran Valparaíso (Chile PBF + bounding box) descargados y preprocesados. OSRM sin Internet en runtime.

**Scale/Scope**: 1 bodega, ~300 entregas, 10 rutas, 6 pares de evaluaciones (geodésica + vial por set de parámetros), modo intercambiable

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Principio | Estado | Justificación |
|-----------|--------|---------------|
| Evidencia Antes de Solución | ✅ Pasa | No optimiza; aumenta fidelidad del modelo de evaluación y revalida hallazgos previos. |
| Decisiones Medibles | ✅ Pasa | 6 métricas M001–M006 cuantifican impacto; 4 estados de validez por hallazgo. |
| Complejidad Incremental | ✅ Pasa | Extiende MeasurementService existente con un nuevo modo; no toca frontend ni clustering. |
| Optimizaciones Comparables | ✅ Pasa | Ambos modos coexisten; Exp002 compara directamente geodésico vs vial bajo parámetros idénticos. |
| Visualización como Análisis | ✅ Pasa | Mapas sobre red vial; mapa de distorsión territorial (M006). |
| Conocimiento Reutilizable | ✅ Pasa | Hallazgos H007+, validaciones V001+, PI-011 registrada para fases futuras. |
| Docker First | ✅ Pasa | OSRM como contenedor Docker; datos OSM preprocesados; sin dependencia externa en runtime. |

**Resultado**: GATE PASS — sin violaciones constitucionales.

## Project Structure

### Documentation (this feature)

```text
specs/006-road-network-integration/
├── plan.md              # This file
├── spec.md              # Feature specification
├── research.md          # Phase 0: OSRM research & decisions
├── data-model.md        # Phase 1: entities, DTOs, schemas
├── quickstart.md        # Phase 1: validation scenarios
├── contracts/
│   └── api.md           # Phase 1: updated API contract
└── tasks.md             # Created by /speckit.tasks
```

### Source Code (repository root)

```text
backend/
├── app/
│   ├── Services/
│   │   ├── HaversineService.php              # Existente — sin cambios
│   │   ├── DistanceService.php               # NUEVO — fachada Strategy
│   │   ├── OsrmClient.php                    # NUEVO — HTTP client OSRM
│   │   ├── MeasurementService.php            # MODIFICADO — distance_mode en execute()
│   │   ├── MetricsCalculatorService.php      # MODIFICADO — recibe DistanceService
│   │   └── AnomalyDetector.php               # Sin cambios (usa distancias precaculadas)
│   ├── Http/
│   │   └── Controllers/
│   │       └── EvaluationController.php      # MODIFICADO — valida distance_mode
│   └── Config/
│       └── evaluation.php                    # NUEVO — config global
├── docker/
│   └── osrm/
│       ├── Dockerfile                        # NUEVO — servidor osrm-routed
│       ├── scripts/
│       │   ├── download-osm.sh               # NUEVO — descarga + bounding box GV
│       │   └── preprocess.sh                 # NUEVO — osrm-extract/contract/partition/customize
│       └── profiles/
│           └── car.lua                       # NUEVO — perfil routing urbano
├── composer.json              # (+ guzzlehttp/guzzle)

docker-compose.yml            # MODIFICADO — + osrm service + volumen osrm-data

experiments/
└── 002-road-network/         # NUEVO — Experimento 002
    ├── experiment.json
    └── report.md

research/                     # Actualizados al final
├── hallazgos.md              # + H007+
├── preguntas-investigacion.md # + PI-006 a PI-011
├── decisiones.md             # + D006+
├── contribuciones.md         # + C004+
└── evidence-matrix.md        # + V001+

frontend/                     # Sin cambios
```

**Structure Decision**: Misma estructura existente. OSRM como servicio Docker separado. `DistanceService` como fachada que implementa patrón Strategy inyectado en `MeasurementService`. Sin cambios en frontend.

## Phases

### Phase 1: OSRM Docker Infrastructure

**Purpose**: OSRM corriendo como contenedor Docker con datos de Gran Valparaíso preprocesados.

**CRITICAL**: All subsequent phases depend on OSRM being available.

**Tasks**:
1. Crear volumen `osrm-data` en `docker-compose.yml`.
2. Crear `backend/docker/osrm/Dockerfile` liviano (solo `osrm-routed`, sin preprocesamiento en build).
3. Crear scripts de descarga + extracción por bounding box (Gran Valparaíso: -71.70,-33.15,-71.20,-32.90).
4. Crear script de preprocesamiento (`osrm-extract`, `osrm-contract`, `osrm-partition`, `osrm-customize`).
5. Crear `backend/docker/osrm/profiles/car.lua` con velocidades adaptadas (residential=30, living_street=15).
6. Agregar servicios `osrm-prepare` y `osrm` en `docker-compose.yml`.
7. Crear `Makefile` con target `prepare-osrm`.
8. Verificar: `curl http://osrm:5000/route/v1/driving/-71.62,-33.045;-71.61,-33.05`
9. **Validar bounding box**: confirmar que todas las coordenadas de entregas y bodega de Exp001 caen dentro de -71.70,-33.15,-71.20,-32.90. Si no, ajustar bounding box antes de continuar.

**Checkpoint**: OSRM corriendo en Docker, responde a requests de ruteo.

### Phase 2: Core Services — OsrmClient & DistanceService

**Purpose**: Capa de abstracción que permite al motor de evaluación usar Haversine u OSRM intercambiablemente.

**Tasks**:
1. Agregar `guzzlehttp/guzzle` a `composer.json` y ejecutar `composer install`.
2. Crear `backend/app/Services/OsrmClient.php`:
   - Constructor: `__construct(string $baseUrl = 'http://osrm:5000')`
   - Método `route(float $lng1, float $lat1, float $lng2, float $lat2): array` — retorna `{distance_km, duration_min, code}`
   - Manejar errores: NoRoute, NoSegment, timeout, conexión
3. Crear `backend/app/Services/DistanceService.php`:
   - Método `setMode(string $mode)`: `'geodesic'` o `'vial'`
   - Método `calculate(float $lat1, float $lng1, float $lat2, float $lng2): array` — retorna `DistanceResult` (distance_km, duration_min, mode)
   - Inyecta `HaversineService` y `OsrmClient` en constructor
4. Tests: mockear OsrmClient, verificar que DistanceService delega correctamente según modo.

**Checkpoint**: `DistanceService::calculate()` retorna distancias según modo.

### Phase 3: MeasurementService Adaptation (US1 + US2 + US4)

**Purpose**: El orquestador de evaluaciones soporta modo vial sin romper modo geodésico. Produce solo métricas crudas (distancia, duración, ranking, balance, cobertura, tiempo de ejecución). No realiza análisis comparativo — esa responsabilidad es de Exp002.

**Tasks**:
1. Modificar `MetricsCalculatorService`:
   - Constructor acepta `DistanceService` en lugar de llamar directamente a `HaversineService::calculate()`
   - Reemplazar todas las llamadas a `HaversineService::calculate()` por `$this->distanceService->calculate()`
   - Extraer `distance_km` y `duration_min` del resultado del DistanceService
   - Incluir `estimated_time_min` en route metrics (solo modo vial)
2. Modificar `MeasurementService`:
   - Constructor acepta `DistanceService`
   - `execute()` lee `distance_mode` de `$parameters`, llama a `setMode()` antes del pipeline
   - `buildDeliveriesFlat()` usa DistanceService
3. Inyectar DistanceService a MetricsCalculatorService en MeasurementService.
4. Registrar tiempo de ejecución (`microtime(true)` antes/después del pipeline) como `execution_time_sec` en métricas crudas.
5. Verificar retrocompatibilidad: modo geodésico produce resultados idénticos a los originales.
6. Verificar que NO se calculan métricas comparativas (M001–M006) aquí.

**Checkpoint**: MeasurementService ejecuta pipeline completo en ambos modos, produce solo métricas crudas.

### Phase 4: EvaluationController & Config

**Purpose**: Endpoint acepta, valida y persiste `distance_mode`.

**Tasks**:
1. Crear `backend/config/evaluation.php` con `'distance_mode' => env('EVALUATION_DISTANCE_MODE', 'geodesic')`.
2. Modificar `EvaluationController::store()`:
   - Validar `distance_mode`: `'sometimes|in:geodesic,vial'`
   - Pasar a MeasurementService.execute()
   - Incluir `mode` en respuesta
3. Verificar que `Evaluation.parameters` guarda `distance_mode`.
4. Verificar que `GET /api/evaluations/{id}` incluye `mode`.

**Checkpoint**: API acepta, valida y persiste modo de distancia.

### Phase 5: Experiment 002 — Geodésica vs Vial (US3 + US5)

**Purpose**: Reejecutar evaluaciones baseline en modo vial y generar reporte comparativo. El experimento es responsable de TODO el análisis comparativo (M001–M006). Los pares se vinculan por `parameters_hash`, no por IDs fijos.

**Tasks**:
1. Crear `experiments/002-road-network/experiment.json` con `baseline_reference` y `evaluation_pairs` vacío.
2. Para cada conjunto único de parámetros en las evaluaciones baseline, ejecutar un par:
   - Evaluation A: `distance_mode: geodesic`, mismos parámetros que baseline
   - Evaluation B: `distance_mode: vial`, mismos parámetros que A
   - Registrar `{ geodesic_id, vial_id, parameters_hash }` en `evaluation_pairs`
   - Iterar sobre evaluaciones del experimento 1 sin hardcodear IDs 2–7
3. Verificar que cada evaluación vial produce `estimated_route_distance_km` ≠ geodésico, `estimated_time_min` > 0, `execution_time_sec` registrado.
4. Calcular por par: M001 (Error Geodésico Medio), M002 (Factor de Desvío), M003 (Error Máximo), M004 (Variación de Ranking), M006 (Distorsión Territorial).
5. Generar `experiments/002-road-network/report.md` con:
   - Tablas comparativas por par de evaluación
   - Factor de desvío promedio general (M002 global)
   - Análisis de distorsión territorial por zona (M006): normal ≤1.2, elevada ≤1.5, alta ≤2.0, crítica >2.0
   - Mapas de distorsión
   - Tabla de degradación computacional: tiempo_vial / tiempo_geodésico
6. Verificar con `experiments:sync`.

**Checkpoint**: Exp002 completo con reporte generado.

### Phase 6: Research Documentation & Revalidation

**Purpose**: Documentar hallazgos, validaciones V001+, y actualizar research/.

**Tasks**:
1. Evaluar cada hallazgo H001–H006 contra Exp002 → crear V001–V006 en `research/evidence-matrix.md`:
   ```text
   V001 | H001 | Válido | Exp002 | Sin cambios significativos
   V002 | H002 | Válido con ajustes | Exp002 | Magnitudes cambiaron +15%
   ```
2. Documentar H007–H010 en `research/hallazgos.md`.
3. Agregar PI-006 a PI-014 en `research/preguntas-investigacion.md`.
4. Agregar D006+ en `research/decisiones.md` (OSRM, DistanceService, car.lua).
5. Agregar C004–C005 en `research/contribuciones.md`.
6. Actualizar `research/evidence-matrix.md` con todos los nuevos IDs.

**Checkpoint**: Todos los archivos de investigación actualizados.

### Phase 7: Polish & Cross-Cutting

**Purpose**: Verificaciones finales.

**Tasks**:
1. Verificar retrocompatibilidad: evaluaciones geodésicas producen resultados idénticos a las baseline con mismos parámetros.
2. Casos borde en OsrmClient: coordenadas idénticas → 0, fuera de rango → null.
3. Verificar que los mapas se renderizan correctamente en modo vial.
4. Ejecutar suite completa de tests PHPUnit.
5. Verificar `quickstart.md` escenarios 1–8.

**Checkpoint**: Feature completa y validada.

## Dependencies & Execution Order

```
Phase 1 (OSRM Docker)
    │
    ▼
Phase 2 (OsrmClient + DistanceService)
    │
    ▼
Phase 3 (MeasurementService) ──── Phase 4 (Controller + Config)
    │                                      │
    └──────────────────┬───────────────────┘
                       ▼
              Phase 5 (Experiment 002)
                       │
                       ▼
              Phase 6 (Research Docs)
                       │
                       ▼
              Phase 7 (Polish)
```

**Parallel opportunities**:
- Phase 1 standalone (Docker infra no depende de código PHP)
- Phase 4 puede empezar después de Phase 2 (config y validación no dependen de MeasurementService)
- Phase 6 puede redactarse parcialmente durante Phase 5 (estructura conocida)

## Complexity Tracking

Sin violaciones constitucionales.

## Artifacts Generated by This Plan

| Artifact | Description |
|----------|-------------|
| `research.md` | OSRM decision, car.lua profile, API schema, storage strategy |
| `data-model.md` | DistanceResult, DistanceMode, metrics_summary extension, M006 schema |
| `contracts/api.md` | Updated API with distance_mode parameter |
| `quickstart.md` | 8 validation scenarios |
