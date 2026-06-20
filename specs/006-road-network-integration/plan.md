# Implementation Plan: Incorporación de Red Vial Real y Revalidación Experimental

**Branch**: `006-road-network-integration` | **Date**: 2026-06-20 | **Spec**: [spec.md](spec.md)

**Input**: Feature specification from `/specs/006-road-network-integration/spec.md`

## Summary

Integrar OpenStreetMap + OSRM en el stack Docker, adaptar el motor de evaluación (MeasurementService) para soportar dos modos de distancia (geodésico/vial), reejecutar las evaluaciones IDs 2–7 sobre la red vial, calcular métricas de error M001–M006, generar reporte comparativo y revalidar formalmente los hallazgos H001–H006 mediante la nueva categoría de evidencia V (Validaciones).

## Technical Context

**Language/Version**: PHP 8.2 (Laravel 12), TypeScript (Next.js 14), Python/Shell (OSRM data prep)

**Primary Dependencies**: OSRM Docker image (`ghcr.io/project-osrm/osrm-backend`), GuzzleHttp (PHP HTTP client para OSRM API interna), PHPUnit (testing)

**Storage**: PostgreSQL 16 — datos existentes sin cambios estructurales; OSRM graph en volumen Docker persistente; resultados de Exp002 en disco (`storage/app/evaluations/`)

**Testing**: PHPUnit (backend — OSRM client, DistanceService, MetricsCalculatorService vial mode, metric calculation M001–M006)

**Target Platform**: Docker Compose (Linux containers), OSRM como servicio adicional en `docker-compose.yml`

**Performance Goals**: Cálculo vial para evaluación completa (300 entregas, 10 rutas) ≤ 10× del geodésico actual (RNF2)

**Constraints**: Sin APIs externas (RNF1). Datos OSM de Valparaíso descargados y preprocesados en Dockerfile/build. OSRM sin conexión a Internet en runtime.

**Scale/Scope**: 1 bodega, ~300 entregas, 10 rutas, 6 evaluaciones (IDs 2–7), modo geodésico/vial intercambiable

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Principio | Estado | Justificación |
|-----------|--------|---------------|
| Evidencia Antes de Solución | ✅ Pasa | Esta fase no optimiza; aumenta fidelidad del modelo de evaluación y revalida hallazgos previos. |
| Decisiones Medibles | ✅ Pasa | 6 métricas M001–M006 cuantifican el impacto del cambio; 4 estados de validez para cada hallazgo. |
| Complejidad Incremental | ✅ Pasa | Extiende MeasurementService existente con un nuevo modo; no toca frontend ni clustering. |
| Optimizaciones Comparables | ✅ Pasa | Ambos modos coexisten; Exp002 compara directamente geodésico vs vial bajo parámetros idénticos. |
| Visualización como Análisis | ✅ Pasa | Mapas de rutas sobre red vial; mapa de distorsión territorial (M006). |
| Conocimiento Reutilizable | ✅ Pasa | Hallazgos H007+, validaciones V001+, PI-011 registrada para fases futuras. |
| Docker First | ✅ Pasa | OSRM como contenedor Docker; datos OSM preprocesados; sin dependencia externa en runtime. |

**Resultado**: GATE PASS — sin violaciones constitucionales.

## Project Structure

### Documentation (this feature)

```text
specs/006-road-network-integration/
├── plan.md              # This file
├── spec.md              # Feature specification
├── research.md          # Phase 0 output: OSRM research & alternatives decision
├── data-model.md        # Phase 1 output: DistanceMode, OSRM response schema
├── quickstart.md        # Phase 1 output: validation scenarios
├── contracts/
│   └── api.md           # Updated API contract (distance_mode param)
└── tasks.md             # Created by task generation
```

### Source Code (repository root)

```text
backend/
├── app/
│   ├── Services/
│   │   ├── HaversineService.php              # Existente — sin cambios
│   │   ├── DistanceService.php               # NUEVO — fachada que delega a OSRM o Haversine según modo
│   │   ├── OsrmClient.php                    # NUEVO — HTTP client para OSRM API interna
│   │   ├── MeasurementService.php            # MODIFICADO — acepta distance_mode, usa DistanceService
│   │   └── MetricsCalculatorService.php      # MODIFICADO — acepta DistanceService en lugar de Haversine directo
│   ├── Http/
│   │   └── Controllers/
│   │       └── EvaluationController.php      # MODIFICADO — acepta distance_mode en POST
│   └── Config/
│       └── evaluation.php                    # NUEVO — configuración global (distance_mode default)
├── docker/
│   └── osrm/
│       ├── Dockerfile                        # NUEVO — descarga datos OSM + preprocesa graph
│       ├── download.sh                       # NUEVO — descarga extracto OSM Valparaíso
│       └── profiles/
│           └── car.lua                       # NUEVO — perfil de routing vehicular
├── routes/
│   └── api.php                # Sin cambios (mismos endpoints, nuevo parámetro opcional)
├── composer.json              # (+ guzzlehttp/guzzle)
└── tests/
    └── Feature/
        └── RoadNetworkTest.php               # NUEVO — tests de ruteo vial

docker-compose.yml            # MODIFICADO — + osrm service

experiments/
└── 002-road-network/         # NUEVO — Experimento 002
    ├── experiment.json
    └── report.md             # Generado al final

research/
├── hallazgos.md              # MODIFICADO — + H007+, referencias V+
├── preguntas-investigacion.md # MODIFICADO — + PI-006 a PI-011
├── decisiones.md             # MODIFICADO — + D006+
├── contribuciones.md         # MODIFICADO — + C004+
└── evidence-matrix.md        # MODIFICADO — + V001+, IDs, categoría V
```

**Structure Decision**: OSRM como servicio separado en `docker-compose.yml` con volumen de datos persistente. `DistanceService` como fachada que implementa el patrón Strategy (HaversineStrategy vs OsrmStrategy). `MeasurementService` recibe `DistanceService` inyectado.

## Phases

### Phase 0: Research

**Tasks**:
1. Investigar perfil de routing adecuado para última milla en Valparaíso (car.lua — velocidad máxima por tipo de vía, restricciones de calles angostas, pasajes peatonales, calles sin salida).
2. Investigar tamaño esperado del extracto OSM para Valparaíso (región, no todo Chile) y estimar tiempo de preprocesado (osrm-extract, osrm-contract, osrm-partition, osrm-customize).
3. Documentar decisión de OSRM vs GraphHopper (confirmar alternativa tecnológica del spec).
4. Investigar cómo pasar coordenadas a OSRM (HTTP API: `/route/v1/driving/{lng},{lat};{lng},{lat}`) y schema de respuesta (distance en metros, duration en segundos, geometry opcional).
5. Investigar estrategia de volumen Docker para OSRM data (persistencia del graph entre reinicios, rebuild ante cambios).
6. Documentar en `research.md`.

**Output**: `research.md`

### Phase 1: Design & Contracts

**Tasks**:
1. Definir `data-model.md` con:
   - Interfaz `DistanceStrategy` (calculate(lat1, lng1, lat2, lng2): DistanceResult)
   - `DistanceResult` DTO: distance_km, duration_min, mode, metadata opcional
   - `OsrmClient` schema de request/response
   - `Evaluation` parameter `distance_mode` (geodesic|vial) en parameters jsonb
2. Definir contrato API actualizado en `contracts/api.md` (POST /api/evaluations acepta `distance_mode` opcional; default del config).
3. Crear `quickstart.md` con escenarios de validación:
   - Evaluación vial con ID 2 produce métricas M001–M006
   - Evaluación vial con ID 2 produce distinta distancia total que geodésica
   - OSRM responde a request directo (curl dentro de red Docker)
   - Mismo seed produce mismas métricas en modo vial
4. Crear `backend/config/evaluation.php` con `distance_mode` default.
5. Actualizar `AGENTS.md`.

**Outputs**: `data-model.md`, `contracts/api.md`, `quickstart.md`, `backend/config/evaluation.php`, AGENTS.md actualizado

### Phase 2: OSRM Infrastructure (Docker)

**Purpose**: OSRM corriendo como contenedor Docker con datos de Valparaíso preprocesados.

**CRITICAL**: All user stories depend on this phase.

**Tasks**:
1. Agregar volumen Docker `osrm-data` en `docker-compose.yml`.
2. Crear `backend/docker/osrm/Dockerfile` que:
   - Usa `ghcr.io/project-osrm/osrm-backend` como base
   - Descarga extracto OSM de Valparaíso (región Valparaíso desde GeoFabrik o interfaz similar)
   - Ejecuta `osrm-extract`, `osrm-contract`, `osrm-partition`, `osrm-customize` con perfil `car.lua`
   - Expone puerto 5000
3. Crear `backend/docker/osrm/profiles/car.lua` con perfil adaptado para última milla urbana (velocidades reducidas en zonas residenciales, calles angostas, pasajes peatonales).
4. Agregar servicio `osrm` en `docker-compose.yml` con el Dockerfile, volumen `osrm-data`, healthcheck (HTTP GET /health).
5. Verificar build y startup: `docker compose build osrm && docker compose up -d osrm`.
6. Verificar que OSRM responde: `curl http://osrm:5000/route/v1/driving/-71.62,-33.045;-71.61,-33.05`.

**Checkpoint**: OSRM corriendo en Docker, responde a requests de ruteo.

### Phase 3: Core Services — OsrmClient & DistanceService

**Purpose**: Capa de abstracción que permite al motor de evaluación usar Haversine o OSRM intercambiablemente.

**Independent Test**: OsrmClient devuelve distancia y duración para un par de coordenadas conocido. DistanceService con mode=vial delega a OSRM, mode=geodesic delega a Haversine.

**Tasks**:
1. Crear `backend/app/Services/OsrmClient.php`:
   - Constructor recibe base URL del OSRM container (`http://osrm:5000`)
   - Método `route(float $lng1, float $lat1, float $lng2, float $lat2): array` — llama a OSRM `/route/v1/driving/{lng},{lat};{lng},{lat}`
   - Manejo de errores: timeout, conexión rechazada, respuesta inválida
   - Extraer distance (m → km) y duration (s → min) del response JSON
2. Crear `backend/app/Services/DistanceService.php`:
   - Interfaz/contrato: `calculate(float $lat1, float $lng1, float $lat2, float $lng2): DistanceResult`
   - Método `setMode(string $mode)` — cambia entre 'geodesic' y 'vial'
   - Por defecto, inyecta `HaversineService` como implementación geodésica y `OsrmClient` como vial
   - `DistanceResult` puede ser un array tipado o DTO simple con claves: distance_km, duration_min, mode
3. Agregar `guzzlehttp/guzzle` a `backend/composer.json` y ejecutar `docker compose exec backend composer install`.
4. Tests unitarios para `OsrmClient` (mock HTTP client para respuestas OSRM) y `DistanceService` (verify delegation).

**Checkpoint**: `DistanceService::calculate()` devuelve distancias geodésicas o viales según modo configurado.

### Phase 4: MeasurementService Adaptation

**Purpose**: El orquestador de evaluaciones soporta modo vial sin romper el modo geodésico existente.

**Independent Test**: POST /api/evaluations con `distance_mode: geodesic` produce resultados idénticos a los actuales. POST /api/evaluations con `distance_mode: vial` produce resultados diferentes.

**Tasks**:
1. Modificar `MetricsCalculatorService`:
   - Constructor acepta `DistanceService $distanceService` en lugar de llamar directamente a `HaversineService::calculate()`
   - Todas las llamadas a `HaversineService::calculate()` se reemplazan por `$this->distanceService->calculate()`
   - `calculateRouteDistance()` usa DistanceService para distancia de ruta completa (warehouse→P1→...→PN)
   - `calculateRouteMetrics()` usa DistanceService para distancias entrega→bodega y centroide→entrega
2. Modificar `MeasurementService`:
   - Constructor acepta `DistanceService $distanceService`
   - `execute()` acepta `distance_mode` en `$parameters` y llama a `$this->distanceService->setMode($mode)` antes del pipeline
   - `buildDeliveriesFlat()` usa DistanceService para distancias
   - Inyecta DistanceService a MetricsCalculatorService
3. Modificar `AnomalyDetector`:
   - No requiere cambios si usa distances ya calculadas por MetricsCalculatorService
   - Verificar que las distancias lleguen en el modo correcto
4. Calcular nuevas métricas M001–M006:
   - En `MeasurementService::execute()`, si hay resultados de ambos modos (o comparando el resultado actual contra baseline), calcular: M001 (avg error), M002 (factor de desvío), M003 (max error), M004 (ranking variation), M005 (hallazgo persistence), M006 (distortion index per point/route)
   - Alternativa: calcular M001–M006 en un paso post-ejecución comparando con la evaluación geodésica correspondiente

**Checkpoint**: `MeasurementService` ejecuta el pipeline completo en modo vial y produce métricas comparativas M001–M006.

### Phase 5: EvaluationController & API Adaptation

**Purpose**: El endpoint POST /api/evaluations acepta y persiste el modo de distancia.

**Independent Test**: POST /api/evaluations con `distance_mode: vial` retorna `mode: vial` en la respuesta y las métricas reflejan distancias viales.

**Tasks**:
1. Modificar `EvaluationController::store()`:
   - Agregar `distance_mode` a la validación: `'distance_mode' => 'sometimes|in:geodesic,vial'`
   - Pasar `distance_mode` a `MeasurementService::execute()` via `$parameters`
   - Incluir `mode` en la respuesta y persistir en `Evaluation.parameters`
   - Cargar configuración default desde `config/evaluation.php`
2. Verificar que el recurso `EvaluationResource` incluya `distance_mode` en su respuesta.
3. Verificar que la ruta `GET /api/evaluations/{id}` incluya el modo en los detalles.
4. Verificar que el archivo `evaluation.json` exportado incluya `distance_mode` en parámetros.

**Checkpoint**: Evaluaciones en modo vial se ejecutan, persisten y recuperan correctamente.

### Phase 6: Experiment 002 — Geodésica vs Vial

**Purpose**: Reejecutar todas las evaluaciones baseline (IDs 2–7) en modo vial y generar reporte comparativo.

**CRITICAL**: Requires Phases 2–5 to be complete and verified.

**Tasks**:
1. Crear directorio `experiments/002-road-network/` con `experiment.json`:
   ```json
   {
     "identifier": "002-road-network",
     "name": "Comparación Geodésica vs Vial",
     "objective": "Cuantificar el impacto de reemplazar distancias geodésicas por distancias sobre red vial real en las métricas operacionales.",
     "hypothesis": "H1: La red vial modifica significativamente las métricas operacionales.",
     "baseline_evaluation_id": null,
     "evaluation_ids": [],
     "author": "Sistema"
   }
   ```
2. Para cada evaluación baseline (IDs 2–7), ejecutar POST /api/evaluations con:
   - Mismos `near_delivery_threshold_km`, `ignored_delivery_ratio`, `random_seed`, `algorithm`, `algorithm_version`
   - `distance_mode: vial`
   - Registrar IDs de nuevas evaluaciones en `experiment.json`
3. Verificar que cada evaluación vial produjo resultados ≠ geodésico.
4. Calcular M001–M006 para cada par (geodésico baseline vs vial nuevo).
5. Generar reporte `experiments/002-road-network/report.md` con:
   - Tabla comparativa por evaluación
   - Factor de desvío promedio (M002)
   - Índice de distorsión territorial (M006) por ruta y punto
   - Cambios en ranking de rutas (M004)
   - Cambios en penalidad operacional
   - Gráficos/mapas de distorsión
6. Verificar con `experiments:sync` que el experimento queda registrado.

**Checkpoint**: Exp002 completo con todas las evaluaciones ejecutadas y reporte generado.

### Phase 7: Research Documentation & Revalidation

**Purpose**: Documentar hallazgos, validaciones, decisiones y contribuciones derivadas del experimento.

**Tasks**:
1. Ejecutar script/consulta para calcular M005 (Persistencia de Hallazgos) comparando H001–H006 contra los nuevos resultados.
2. Crear entidades V001–V006 en `research/evidence-matrix.md`:
   ```text
   V001
   Hallazgo validado: H001
   Estado: {Válido | Válido con ajustes | Revisado | Rechazado}
   Evidencia: Exp002
   Observaciones: ...
   ```
3. Documentar nuevos hallazgos H007+ en `research/hallazgos.md`:
   - H007: Factor de desvío promedio entre modelo geodésico y vial
   - H008: Zonas de distorsión territorial identificadas
   - H009: Sensibilidad de métricas al cambio de modelo
   - H010: Impacto en ranking de rutas
4. Agregar PI-006 a PI-011 en `research/preguntas-investigacion.md`.
5. Agregar D006+ en `research/decisiones.md` (decisión OSRM, estructura DistanceService, perfil routing).
6. Agregar C004+ en `research/contribuciones.md`:
   - C004: Framework de revalidación experimental con categoría V
   - C005: Métrica de distorsión territorial (M006)
7. Actualizar `research/evidence-matrix.md` con todos los nuevos IDs.

**Checkpoint**: Todos los archivos de investigación actualizados con la nueva evidencia.

### Phase 8: Polish & Cross-Cutting

**Purpose**: Verificaciones finales que abarcan múltiples fases.

**Tasks**:
1. Verificar compatibilidad hacia atrás: todas las evaluaciones existentes (IDs 2–7) siguen funcionando en modo geodésico sin cambios en resultados (comparar metrics_summary antes/después).
2. Verificar casos borde en OsrmClient:
   - Coordenadas exactamente iguales → distancia 0
   - Coordenadas fuera del área OSM → error manejado gracefulmente
   - OSRM caído → fallback o error claro
3. Verificar rendering de mapas en modo vial (rutas sobre red vial vs línea recta geodésica).
4. Verificar que `experiments:sync` funciona con Exp002.
5. Ejecutar suite completa de tests PHPUnit.
6. Verificar `quickstart.md` escenarios uno por uno.

## Dependencies & Execution Order

### Phase Dependencies

- **Phase 0 (Research)**: No dependencies
- **Phase 1 (Design)**: Depends on Phase 0
- **Phase 2 (OSRM Docker)**: No dependencies on code — standalone infrastructure
- **Phase 3 (Core Services)**: Depends on Phase 2 (needs OSRM running for integration tests)
- **Phase 4 (MeasurementService)**: Depends on Phase 3 (needs DistanceService)
- **Phase 5 (Controller)**: Depends on Phase 4 (needs MeasurementService with vial mode)
- **Phase 6 (Experiment 002)**: Depends on Phase 5 (needs working vial evaluations)
- **Phase 7 (Research)**: Depends on Phase 6 (needs experiment results)
- **Phase 8 (Polish)**: Depends on Phases 2–7

### Parallel Opportunities

- **Phase 0 + Phase 2**: Research and Docker infra can run in parallel
- **Phase 1**: Can start after Phase 0; design docs
- **Phase 3 + Phase 4**: After Phase 2, OsrmClient + DistanceService (Phase 3) → MeasurementService adaptation (Phase 4) is sequential by nature because Phase 4 depends on DistanceService being ready
- **Phase 7 research docs**: Can be partially drafted during Phase 6 (structure known from spec)

### Execution Strategy

1. **Phase 0 + Phase 2 in parallel**: Research OSRM while building Docker infra
2. **Phase 1**: Design docs after research
3. **Phase 3 → 4 → 5**: Sequential core pipeline
4. **Phase 6**: Experiment execution after pipeline verified
5. **Phase 7**: Research docs after experiment results
6. **Phase 8**: Polish after everything stable

## Notes

- HaversineService NO se modifica. Permanece como implementación de referencia para el modo geodésico.
- El modo por defecto es 'geodesic' para no romper evaluaciones existentes (RNF3).
- OSRM usa el perfil `car.lua` por defecto. Si se requiere un perfil de última milla más restrictivo, se puede personalizar en Phase 0.
- Las evaluaciones IDs 2–7 deben conservar sus métricas originales. La comparación se hace contra esos datos guardados, no re-ejecutando en modo geodésico.
- `experiments:sync` es el mecanismo oficial para registrar experimentos. Existe en el stack actual (PHP artisan command).
- Sin cambios en frontend (Next.js) — toda la lógica es server-side.
