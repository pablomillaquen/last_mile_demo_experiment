# Tasks: Incorporación de Red Vial Real y Revalidación Experimental

**Input**: Design documents from `specs/006-road-network-integration/`

**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/api.md, quickstart.md

**Tests**: No se incluyen tareas de desarrollo de tests automatizados (no solicitados en spec). Sí hay tareas de verificación manual y validación experimental.

**Organization**: Tasks grouped by implementation phase from plan.md.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to
- Include exact file paths in descriptions

## Path Conventions

- **Backend**: `backend/app/`, `backend/docker/`, `backend/config/`, `backend/routes/`
- **Infrastructure**: `docker-compose.yml`, `Makefile` (raíz del proyecto)
- **Research**: `research/`
- **Experiments**: `experiments/`
- **Frontend**: Sin cambios

---

## Phase 1: OSRM Docker Infrastructure (Shared)

**Purpose**: OSRM corriendo como contenedor Docker con datos de Gran Valparaíso preprocesados.
El preprocesamiento se separa del servidor para evitar builds pesados: el grafo se genera una vez en un volumen persistente y se reutiliza. El área cubierta incluye Valparaíso, Viña del Mar, Concón, Quilpué, Villa Alemana, Belloto y Limache (bounding box: -71.70,-33.15,-71.20,-32.90).

**CRITICAL**: All subsequent phases depend on OSRM being available.

- [X] T001 Agregar volumen `osrm-data` en `docker-compose.yml` (raíz del proyecto) para persistencia del grafo OSRM.
- [X] T002 Crear `backend/docker/osrm/Dockerfile` para el servidor OSRM:
  - Usar `ghcr.io/project-osrm/osrm-backend` como imagen base
  - Entrypoint: `osrm-routed --algorithm=mld /data/valparaiso.osrm`
  - Expone puerto 5000
  - Sin preprocesamiento en el build — la imagen es liviana y reutilizable
- [X] T003 [P] Crear `backend/docker/osrm/scripts/download-osm.sh`:
  - Descarga datos OSM del Gran Valparaíso (bounding box: -71.70,-33.15,-71.20,-32.90)
  - Extrae el área con `osmium extract` y produce `/data/valparaiso.osm.pbf` (~30 MB)
  - Implementa cacheo de la fuente OSM para evitar descargas redundantes
- [X] T004 [P] Crear `backend/docker/osrm/scripts/preprocess.sh`:
  - Ejecuta `osrm-extract`, `osrm-contract`, `osrm-partition`, `osrm-customize` en secuencia sobre `valparaiso.osm.pbf`
  - Parámetro: ruta al perfil car.lua
  - Para Gran Valparaíso (~30 MB PBF, ~7 min, ~1 GB RAM, ~250 MB disco)
- [X] T005 [P] Crear perfil de routing `backend/docker/osrm/profiles/car.lua` con velocidades adaptadas para última milla urbana (residential=30, living_street=15, tertiary=40).
- [X] T006 Agregar servicios `osrm-prepare` y `osrm` en `docker-compose.yml`:
  - `osrm-prepare`: servicio one-shot para preprocesamiento manual (`docker compose run --rm osrm-prepare`)
    - Misma imagen que `osrm`
    - Volumen: `osrm-data:/data`
    - Vincula scripts: `./backend/docker/osrm/scripts:/scripts`
  - `osrm`: servicio servidor persistente
    - Build context: `backend/docker/osrm`
    - Volumen: `osrm-data:/data`
    - Healthcheck: `curl -f http://localhost:5000/health`
    - Puerto: 5000
    - Depende de: volumen `osrm-data` poblado (preprocesamiento completado)
- [X] T007 Crear `Makefile` en raíz del proyecto con target `prepare-osrm`:
  - Obtiene datos OSM del Gran Valparaíso
  - Ejecuta preprocesamiento completo (~7 min, ~1 GB RAM)
  - Documenta requisitos y tiempo esperado
- [X] T008 Verificar: `make prepare-osrm && docker compose up -d osrm` y confirmar que `curl http://localhost:5000/route/v1/driving/-71.62,-33.045;-71.61,-33.05` retorna `{"code":"Ok"}`. Confirmar también que coordenadas fuera del bounding box retornan `{"code":"NoRoute"}`.

**Checkpoint**: OSRM corriendo en Docker, responde a requests de ruteo.

---

## Phase 2: Core Services — OsrmClient & DistanceService

**Purpose**: Capa de abstracción que permite al motor de evaluación usar Haversine u OSRM intercambiablemente.

**⚠️ CRITICAL**: No US1 work can begin until this phase is complete.

- [X] T009 Agregar `guzzlehttp/guzzle` a `backend/composer.json` y ejecutar `docker compose exec backend composer install`.
- [X] T010 [P] Crear `backend/app/Services/OsrmClient.php`:
  - Constructor: `__construct(string $baseUrl = 'http://osrm:5000')`
  - Método `route(float $lng1, float $lat1, float $lng2, float $lat2): array` — llama a OSRM `/route/v1/driving/{lng},{lat};{lng},{lat}`, parsea response, retorna `['distance_km' => float, 'duration_min' => float, 'code' => string]`
  - Manejo de errores: NoRoute, NoSegment, timeout, conexión rechazada
- [X] T011 [P] Crear `backend/app/Services/DistanceService.php`:
  - Constructor inyecta `HaversineService` y `OsrmClient`
  - Método `setMode(string $mode)`: acepta `'geodesic'` o `'vial'`
  - Método `calculate(float $lat1, float $lng1, float $lat2, float $lng2): array` — retorna `['distance_km' => float|null, 'duration_min' => float|null, 'mode' => string]`
  - Si mode='geodesic', delega a `HaversineService::calculate()` (duration_min = null)
  - Si mode='vial', delega a `OsrmClient::route()` y convierte respuesta
- [X] T012 Verificar que `DistanceService::calculate()` retorna distancias correctas en ambos modos (comparar contra HaversineService conocido y contra response OSRM directo).

**Checkpoint**: `DistanceService` funcional con ambos modos.

---

## Phase 3: User Story 1 — Distancia Vial + US2 Compatibilidad + US4 Tiempo Estimado (Priority: P1) 🎯 MVP

**Goal**: El motor de evaluación puede calcular distancias sobre red vial real, manteniendo compatibilidad con el modo geodésico, e incluye tiempo estimado de viaje.
MeasurementService produce métricas crudas (distancia, duración, ranking, balance, cobertura, tiempo de ejecución) respetando el modo configurado. NO realiza análisis comparativo entre modos — esa responsabilidad pertenece al experimento.

**Independent Test**: `POST /api/evaluations` con `distance_mode: vial` retorna `estimated_route_distance_km` diferente al modo geodésico e incluye `estimated_time_min` > 0.

### Implementation

- [X] T013 [P] [US1] Modificar `MetricsCalculatorService` en `backend/app/Services/MetricsCalculatorService.php`:
  - Constructor acepta `DistanceService $distanceService`
  - Reemplazar todas las llamadas a `HaversineService::calculate()` por `$this->distanceService->calculate()`
  - Extraer `distance_km` y `duration_min` del resultado del DistanceService
  - En `calculateRouteMetrics()`: incluir `estimated_time_min` en el array de retorno (extraído de DurationResult, solo para modo vial)
  - En `calculateRouteLeg()`: usar DistanceService para cada tramo de la ruta (warehouse→P1→P2→...→PN) y retornar `['distance_km' => float, 'duration_min' => float|null]`
- [X] T014 [P] [US1] Modificar `MeasurementService` en `backend/app/Services/MeasurementService.php`:
  - Constructor acepta `DistanceService $distanceService`
  - `execute()` lee `distance_mode` de `$parameters`, llama a `$this->distanceService->setMode($mode)` al inicio del pipeline
  - `buildDeliveriesFlat()` usa `DistanceService` para distancias en lugar de `HaversineService::calculate()`
  - Inyectar `DistanceService` a `MetricsCalculatorService` en el constructor
- [X] T015 [P] [US1] Agregar registro de tiempo de ejecución en `MeasurementService::execute()`:
  - Capturar `microtime(true)` antes y después del pipeline completo
  - Incluir `execution_time_sec` en el array de retorno (para PI-012 y RNF2)
- [X] T016 [P] [US2] Verificar retrocompatibilidad en `MeasurementService::execute()`: con mode='geodesic', el resultado debe ser idéntico al actual (misma secuencia de cálculos Haversine a través de DistanceService).
- [X] T017 [US1] Verificar que MeasurementService produce, en ambos modos:
  - `route_distance_km`, `route_duration_min` (vial) o null (geodesic)
  - `route_ranking`, `balance_per_route`, `coverage_per_route`
  - `execution_time_sec`
  - Sin métricas comparativas (M001–M006 no se calculan aquí)

**Checkpoint**: MeasurementService ejecuta pipeline completo en ambos modos y produce métricas crudas.

---

## Phase 4: EvaluationController & Config (US1 + US2)

**Goal**: API acepta, valida y persiste `distance_mode`.

**Independent Test**: `POST /api/evaluations` con `distance_mode: vial` retorna `mode: vial` en respuesta y el campo se persiste en `Evaluation.parameters`.

### Implementation

- [X] T018 [P] [US1] Crear `backend/config/evaluation.php`:
  ```php
  <?php
  return [
      'distance_mode' => env('EVALUATION_DISTANCE_MODE', 'geodesic'),
  ];
  ```
- [X] T019 [US1] Modificar `EvaluationController::store()` en `backend/app/Http/Controllers/EvaluationController.php`:
  - Agregar `distance_mode` a validación: `'distance_mode' => 'sometimes|in:geodesic,vial'`
  - Pasar `distance_mode` desde request a `$parameters['distance_mode']`
  - Cargar default desde `config('evaluation.distance_mode')`
  - Incluir `mode` en la respuesta JSON
- [X] T020 [US1] Incluir `execution_time_sec` en `metrics_summary` de la respuesta.
- [X] T021 [US2] Verificar que `GET /api/evaluations/{id}` incluye `mode` y `distance_mode` en los detalles. Verificar retrocompatibilidad: evaluaciones existentes sin `distance_mode` no se rompen.

**Checkpoint**: API acepta, valida y persiste modo de distancia.

---

## Phase 5: Experiment 002 — Geodésica vs Vial (US3 + US5)

**Goal**: Reejecutar evaluaciones históricas en modo vial y generar reporte comparativo.
El experimento es el responsable de TODO el análisis comparativo: M001–M006 no se calculan en MeasurementService, sino aquí, a partir de pares de evaluaciones (geodésica + vial) con los mismos parámetros.

**Independent Test**: `experiments/002-road-network/report.md` existe con tablas comparativas y M006 por zona.

**Nota**: La comparación no depende de IDs fijos (2–7). Se basa en pares de evaluaciones con mismos parámetros vinculados por `baseline_reference` en experiment.json. Esto garantiza reproducibilidad en cualquier entorno.

### Implementation

- [X] T022 [P] [US3] Crear `experiments/002-road-network/experiment.json`:
  ```json
  {
    "identifier": "002-road-network",
    "name": "Comparación Geodésica vs Vial",
    "objective": "Cuantificar el impacto de reemplazar distancias geodésicas por distancias sobre red vial real en las métricas operacionales del sistema.",
    "hypothesis": "H1: La red vial modifica significativamente las métricas operacionales.",
    "baseline_reference": {
      "experiment_id": 1,
      "description": "Evaluaciones originales del experimento baseline (modo geodésico)"
    },
    "evaluation_pairs": [],
    "author": "Sistema"
  }
  ```
- [X] T023 [US3] Script automatizado `experiments/002-road-network/setup.sh` para:
  - Leer evaluaciones baseline desde API
  - Extraer parámetros excluyendo distance_mode
  - Crear par geodesic+vial por cada conjunto único de parámetros
  - Generar parameters_hash por par
  - Poblar evaluation_pairs en experiment.json
- [ ] T024 [US3] Verificar que cada evaluación vial produjo:
  - `estimated_route_distance_km` ≠ geodésico
  - `estimated_time_min` > 0
  - `execution_time_sec` registrado
- [ ] T025 [US3] Verificar que cada par geodésica-vial comparte exactamente los mismos parámetros de entrada (near_delivery_threshold_km, ignored_delivery_ratio, random_seed, algorithm, algorithm_version).
- [ ] T026 [US5] Para cada par (geodésica vs vial), calcular y registrar:
  - M001: Error Geodésico Medio = avg(d_vial − d_geodésica) por ruta
  - M002: Factor de Desvío promedio = avg(d_vial / d_geodésica) por ruta
  - M003: Error Máximo de Trayecto = max(d_vial − d_geodésica) entre todos los warehouse→delivery
  - M004: Variación de Ranking = contar cambios de posición en ranking respecto al baseline
  - M006: Índice de Distorsión Territorial = d_vial / d_geodésica por punto y por ruta, clasificado en rangos (normal ≤1.2, elevada ≤1.5, alta ≤2.0, crítica >2.0)
  - Tabla de degradación computacional: tiempo_vial / tiempo_geodésico por evaluación
- [ ] T027 [US5] Generar `experiments/002-road-network/report.md` con:
  - Resumen ejecutivo: factor de desvío promedio general (M002 global)
  - Tabla comparativa por par de evaluación (distancia total, promedio por ruta, penalidad, cobertura, balance, ranking)
  - Mapa/análisis de distorsión territorial (M006 por zona geográfica)
  - Clasificación de zonas según M006: normal, elevada, alta, crítica
  - Tabla de tiempos de ejecución comparativos y factor de degradación
  - Interpretación de resultados
- [ ] T028 [US3] Verificar registro: `docker compose exec backend php artisan experiments:sync` y confirmar que Exp002 aparece con evaluation_pairs poblados via `GET /api/experiments`.

**Checkpoint**: Exp002 completo con reporte generado y sincronizado.

---

## Phase 6: Research Documentation & Revalidation (US6)

**Goal**: Documentar hallazgos, validaciones V001+, y actualizar research/. La métrica M005 (Persistencia de Hallazgos) se calcula aquí, a partir de los resultados de Exp002, como métrica experimental — no por evaluación individual.

**Independent Test**: Todos los archivos en `research/` actualizados con nuevos IDs.

### Implementation

- [ ] T029 [US6] Evaluar cada hallazgo H001–H006 contra resultados de Exp002 y crear V001–V006 en `research/evidence-matrix.md`:
  ```text
  V001 | H001 | {Válido|Válido con ajustes|Revisado|Rechazado} | Exp002 | {observaciones}
  ```
- [ ] T030 [US6] Calcular M005 (Persistencia de Hallazgos = Válidos / Totales × 100) e incluir en `experiments/002-road-network/report.md`.
- [ ] T031 [US6] Documentar nuevos hallazgos H007–H010 en `research/hallazgos.md`:
  - H007: Factor de desvío promedio entre modelo geodésico y vial
  - H008: Zonas de distorsión territorial identificadas
  - H009: Sensibilidad de métricas al cambio de modelo
  - H010: Impacto en ranking de rutas
- [X] T032 [P] [US6] Agregar PI-006 a PI-014 en `research/preguntas-investigacion.md`.
- [X] T033 [P] [US6] Agregar D006–D008 en `research/decisiones.md` (OSRM, DistanceService Strategy, parameters_hash).
- [X] T034 [P] [US6] Agregar C004–C005 en `research/contribuciones.md`:
  - C004: Framework de revalidación experimental con categoría V
  - C005: Métrica de distorsión territorial (M006)
- [X] T035 [US6] Actualizar `research/evidence-matrix.md` con todos los nuevos IDs (H007+, V001+, PI-006+, D006+, C004+).

**Checkpoint**: Todos los archivos de investigación actualizados con la nueva evidencia.

---

## Phase 7: Polish & Cross-Cutting

**Purpose**: Verificaciones finales que abarcan múltiples fases.

- [X] T036 [P] Verificar retrocompatibilidad: ejecutar `POST /api/evaluations` sin `distance_mode` (default geodesic) y comparar metrics_summary contra evaluation baseline (mismos parámetros) — debe ser idéntico.
- [ ] T037 [P] Verificar casos borde en `OsrmClient`:
  - Coordenadas idénticas → distance_km = 0, duration_min = 0
  - Coordenadas fuera del área del grafo (Gran Valparaíso) → code = "NoRoute", distance_km = null
  - OSRM caído → error manejado sin crash
- [ ] T038 [P] Verificar que los mapas se renderizan correctamente en modo vial (rutas sobre red vial).
- [ ] T039 [P] Ejecutar suite completa de tests del backend: `docker compose exec backend php artisan test`.
- [ ] T040 Verificar escenarios 1–8 de `specs/006-road-network-integration/quickstart.md` uno por uno.
- [X] T041 Actualizar `AGENTS.md` con estado final de SPEC-006.

---

## Dependencies & Execution Order

### Phase Dependencies

- **Phase 1 (OSRM Docker)**: No dependencies — standalone infrastructure
- **Phase 2 (Core Services)**: Depends on Phase 1 (needs OSRM for integration verification)
- **Phase 3 (US1+US2+US4)**: Depends on Phase 2 (needs DistanceService)
- **Phase 4 (Controller & Config)**: Depends on Phase 2, can overlap with Phase 3
- **Phase 5 (Experiment 002)**: Depends on Phase 3+4 (needs working vial evaluations via API)
- **Phase 6 (Research Docs)**: Depends on Phase 5 (needs experiment results)
- **Phase 7 (Polish)**: Depends on Phases 1–6

### User Story Dependencies

| User Story | Phase | Depends On | Can Start After |
|------------|-------|------------|-----------------|
| US1 (vial distance) | 3 | Phase 2 | T012 |
| US2 (compatibilidad) | 3 | Phase 2 | T012 |
| US4 (tiempo estimado) | 3 | Phase 2 | T012 (built into US1) |
| US3 (reejecutar + pares) | 5 | US1+US2+Controller | T021 |
| US5 (métricas comparativas) | 5 | US3 | T024 (built into US3) |
| US6 (revalidar hallazgos) | 6 | US3+US5 | T028 |

### Within Each Phase

- Services before controllers
- Core calculation before export/reporting
- Backend before any research docs

### Parallel Opportunities

- **Phase 1 tasks**: T003, T004, T005 can run in parallel (scripts + car.lua)
- **Phase 2 tasks**: T010 (OsrmClient) and T011 (DistanceService) can be parallelized
- **Phase 3 tasks**: T013 (MetricsCalculator) and T014 (MeasurementService) can be parallelized
- **Phase 6 tasks**: T032, T033, T034 can run in parallel (different research files)
- **Phase 7 tasks**: T036, T037, T038, T039 can all run in parallel

---

## Implementation Strategy

### MVP First (US1 + US2)

1. Complete Phase 1: OSRM Docker infra
2. Complete Phase 2: Core Services
3. Complete Phase 3: US1+US2 (vial distance + compatibility, raw metrics only)
4. Complete Phase 4: Controller & Config
5. **STOP and VALIDATE**: `POST /api/evaluations` con `distance_mode: vial` produce métricas crudas correctas

### Incremental Delivery

1. OSRM Docker + Core Services → Infraestructura lista
2. US1+US2 (vial distance + compatibilidad) → MVP! modo vial funcional
3. Phase 4 (Controller & Config) → API completa
4. US3+US5 (Experiment 002) → Evidencia comparativa generada con M001–M006
5. US6 (Research Docs + M005) → Conocimiento documentado y revalidación de hallazgos
6. Each step builds on previous without breaking existing functionality

---

## Notes

- HaversineService NO se modifica. Permanece como implementación de referencia.
- El modo por defecto es 'geodesic' para no romper evaluaciones existentes (RNF3).
- Las evaluaciones baseline conservan sus métricas originales. El experimento crea pares nuevos.
- MeasurementService produce métricas crudas para el modo seleccionado. No hace comparación entre modos.
- M001–M006 se calculan exclusivamente en Experiment 002, a partir de pares de evaluaciones.
- Los pares se vinculan por `parameters_hash` + `baseline_reference`, no por IDs fijos. Esto garantiza reproducibilidad.
- No hay cambios en frontend (Next.js) — toda la lógica es server-side.
- El preprocesamiento OSRM se separa del servidor (`prepare-osrm` como paso único, `osrm-routed` como servidor persistente).
- `execution_time_sec` se registra en ambas modalidades para PI-012 y RNF2.
- Sin tests automatizados nuevos (no solicitados en spec).
