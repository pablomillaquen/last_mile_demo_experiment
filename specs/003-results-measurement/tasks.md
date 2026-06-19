# Tasks: Sistema de Medición, Evaluación y Validación de Resultados

**Input**: Design documents from `specs/003-results-measurement/`

**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/api.md, quickstart.md

**Tests**: No se incluyen tareas de tests (no solicitados en spec).

**Organization**: Tasks grouped by user story for independent implementation.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to
- Include exact file paths in descriptions

## Path Conventions

- **Backend**: `backend/app/`, `backend/database/`, `backend/routes/`
- **Frontend**: No cambios en frontend

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Verificar entorno existente y agregar dependencias necesarias.

- [ ] T001 Verificar que los contenedores Docker están activos: `docker compose ps`
- [ ] T002 Verificar que la API responde: `curl http://localhost:8000/api/metrics`
- [ ] T003 Agregar dependencia `league/csv` en `backend/composer.json` y ejecutar `docker compose exec backend composer install`

**Checkpoint**: Entorno listo con league/csv disponible.

---

## Phase 2: Foundational — Evaluation Model & Storage Layer

**Purpose**: Crear la infraestructura compartida que todas las user stories necesitan:
tabla `evaluations`, modelo `Evaluation`, directorio de exports.

**⚠️ CRITICAL**: No user story work can begin until this phase is complete.

- [ ] T004 [P] Crear migración `create_evaluations_table` en `backend/database/migrations/[timestamp]_create_evaluations_table.php` con columnas: id (bigIncrements), executed_at (timestamp), parameters (jsonb — incluirá algorithm, algorithm_version, random_seed, near_delivery_threshold_km, ignored_delivery_ratio), total_deliveries (integer), total_routes (integer), metrics_summary (jsonb — incluirá coverage_territorial_km, distancia_promedio_general_km, desviacion_estandar_distancias_km, balance_general_cv, balance_index, inter_cluster_min_distance_km, operational_penalty_total, total_anomalias_detectadas), output_path (string 255), created_at (timestamps). Ver data-model.md para estructura exacta.
- [ ] T005 [P] Crear modelo `Evaluation` en `backend/app/Models/Evaluation.php` con:
  - `$fillable`: executed_at, parameters, total_deliveries, total_routes, metrics_summary, output_path
  - `$casts`: parameters (array), metrics_summary (array), executed_at (datetime)
  - `$guarded`: id
- [ ] T006 [P] Crear directorio `backend/storage/app/evaluations/.gitkeep` para exportaciones
- [ ] T007 Ejecutar migración: `docker compose exec backend php artisan migrate`

**Checkpoint**: `Evaluation` model listo, tabla creada, directorio de exports existente.

---

## Phase 3: User Story 1 — Evaluación de Agrupamientos (Priority: P1) 🎯 MVP

**Goal**: El investigador puede calcular métricas cuantitativas de cada ruta: entregas por ruta,
distancias a bodega (mín, máx, promedio), centroide, radio del cluster, compactación,
distancia total estimada, separación entre clusters, indicadores globales.

**Independent Test**: `MeasurementService::calculateAllMetrics()` retorna array con todas las
rutas y sus métricas pobladas con valores coherentes (km positivos, centroides dentro del
bounding box de los datos).

### Implementation for User Story 1

- [ ] T008 [P] [US1] Crear `MetricsCalculatorService` en `backend/app/Services/MetricsCalculatorService.php` con métodos públicos:
  - `calculateRouteMetrics($route, $warehouseLat, $warehouseLng): array` — retorna todas las métricas por ruta según RouteMetrics en data-model.md (total_deliveries, min/max/avg distance to warehouse, centroid lat/lng, centroid_to_warehouse_km, cluster_radius_km, avg_distance_to_centroid_km). Usa `HaversineService` existente para distancias.
  - `calculateCentroid($packages): array` — retorna ['lat' => float, 'lng' => float] promedio de coordenadas.
  - `calculateGlobalIndicators($allRouteMetrics, $allDeliveries, $warehouseLat, $warehouseLng): array` — retorna coverage_territorial_km, distancia_promedio_general_km, desviacion_estandar_distancias_km, balance_general_cv, balance_index, inter_cluster_min_distance_km según RF-11 y RF-17.
  - `calculateRouteDistance($packages, $warehouseLat, $warehouseLng): float` — suma Haversine bodega→P1→P2→...→PN según sequence o orden de asignación según RF-16.

- [ ] T009 [P] [US1] Crear `MeasurementService` en `backend/app/Services/MeasurementService.php` como **orquestador exclusivamente** — NO debe contener lógica de cálculo, detección, renderizado ni exportación. Solo orquesta servicios especializados inyectados vía constructor. Método:
  - `execute($parameters): array` — recibe parámetros (warehouse, thresholds, etc.), orquesta:
    1. Obtener bodega desde settings
    2. Obtener todas las rutas con sus paquetes ordenados por sequence
    3. Para cada ruta: invocar `MetricsCalculatorService->calculateRouteMetrics()`
    4. Calcular distancia total estimada por ruta con `MetricsCalculatorService->calculateRouteDistance()`
    5. Calcular indicadores globales con `MetricsCalculatorService->calculateGlobalIndicators()`
    6. Calcular ranking de rutas por cercanía
    7. (Fase 5+) Invocar `AnomalyDetector->detect()` e incluir resultados
    8. (Fase 6+) Invocar `MapRendererService->renderOverview()` y similares
    9. (Fase 4+) Invocar `MetricsExporter->exportJson()` y `exportCsv()`
    10. Retornar array completo estructurado igual a la respuesta del contrato API

- [ ] T010 [US1] Implementar manejo de casos borde en `MetricsCalculatorService`:
  - Ruta con 0 paquetes: retornar métricas en cero, centroide = bodega, radio = 0
  - Ruta con 1 paquete: centroide = posición del paquete, radio = 0
  - Múltiples rutas con 1 paquete cada una: separación mínima = distancia Haversine entre ambos

**Checkpoint**: `MeasurementService::execute()` retorna métricas completas para todas las rutas.

---

## Phase 4: User Story 5 — Exportación de Resultados (Priority: P2)

**Goal**: El investigador puede exportar métricas en JSON, CSV y datos por entrega en deliveries.csv.
Los resultados se almacenan en el sistema de archivos con trazabilidad en BD.

**Independent Test**: `POST /api/evaluations` retorna `201 Created` con métricas. Los archivos
exportados son accesibles vía `GET /api/evaluations/{id}/files/{filename}`.

### Implementation for User Story 5

- [ ] T011 [P] [US5] Crear `MetricsExporter` en `backend/app/Exports/MetricsExporter.php` con métodos:
  - `exportJson(array $data, string $outputPath): string` — escribe evaluation.json
  - `exportCsv(array $routeMetrics, string $outputPath): string` — escribe evaluation.csv (una fila por ruta)
  - `exportDeliveriesCsv(array $deliveries, string $outputPath): string` — escribe deliveries.csv con campos: delivery_id, route_id, latitude, longitude, distance_to_warehouse_km, distance_to_centroid_km (usa League\Csv\Writer)
  - Cada método retorna la ruta del archivo creado

- [ ] T012 [P] [US5] Crear `EvaluationController` en `backend/app/Http/Controllers/EvaluationController.php` con:
  - `POST /api/evaluations` — ejecuta MeasurementService::execute(), guarda Evaluation en BD, exporta archivos, retorna respuesta completa según contrato en contracts/api.md
  - `GET /api/evaluations` — lista todas las evaluaciones (solo metadata)
  - `GET /api/evaluations/{id}` — retorna evaluación completa con métricas (reconstruye desde archivos o desde BD)
  - `GET /api/evaluations/{id}/files/{filename}` — sirve archivo desde storage. **CRÍTICO**: validar filename contra la lista de archivos registrados para esa evaluación en la BD (no permitir rutas relativas como `../../.env`). Sanitizar: solo letras, números, guiones, puntos y extensión permitida (json, csv, png)

- [ ] T013 [P] [US5] Crear `EvaluationResource` en `backend/app/Http/Resources/EvaluationResource.php` que estructura la respuesta JSON según el contrato (route_metrics, anomalies, ranking, metrics_summary, files)

- [ ] T014 [US5] Registrar rutas en `backend/routes/api.php`:
  ```php
  Route::post('/evaluations', [EvaluationController::class, 'store']);
  Route::get('/evaluations', [EvaluationController::class, 'index']);
  Route::get('/evaluations/{id}', [EvaluationController::class, 'show']);
  Route::get('/evaluations/{id}/files/{filename}', [EvaluationController::class, 'file']);
  ```

- [ ] T015 [US5] Implementar validación de parámetros en `EvaluationController@store`:
  - `near_delivery_threshold_km` > 0 (default: 1.0)
  - `ignored_delivery_ratio` > 1.0 (default: 2.0)
  - `random_seed` integer (default: null)
  - `algorithm` string (default: "unknown")
  - `algorithm_version` string (default: "1.0")

**Checkpoint**: `POST /api/evaluations` ejecuta evaluación completa y retorna resultados.

---

## Phase 5: User Story 2 — Detección de Anomalías (Priority: P2)

**Goal**: El analista puede detectar automáticamente entregas cercanas a la bodega que fueron
asignadas a rutas cuyo centroide está significativamente más lejos. Se calcula la penalización
operacional total.

**Independent Test**: `POST /api/evaluations` con `near_delivery_threshold_km: 3.0` e
`ignored_delivery_ratio: 1.5` retorna anomalies con al menos 1 caso detectado.

### Implementation for User Story 2

- [ ] T016 [P] [US2] Crear `AnomalyDetector` en `backend/app/Services/AnomalyDetector.php` con:
  - Método `detect(array $routeMetricsList, array $allDeliveries, float $thresholdKm, float $ratio): array` — para cada entrega, si distance_to_warehouse <= thresholdKm y centroid_distance / delivery_distance >= ratio, registra anomalía según AnomalyReport en data-model.md
  - Método `calculateOperationalPenalty(array $anomalies): float` — suma de `centroid_distance / delivery_distance` para todas las anomalías (RF-18)

- [ ] T017 [US2] Integrar `AnomalyDetector` en `MeasurementService::execute()`:
  - Después de calcular métricas, invocar AnomalyDetector
  - Incluir anomalies y operational_penalty_total en el resultado

**Checkpoint**: Evaluación incluye detección de anomalías y penalización operacional total.

---

## Phase 6: User Story 4 — Generación de Evidencia Visual (Priority: P3)

**Goal**: El documentador puede generar mapas PNG con GD que muestren la distribución de rutas,
clusters y casos relevantes para artículos y documentación.

**Independent Test**: `POST /api/evaluations` genera archivos `map_overview_*.png`,
`map_route_*.png` y `map_anomalies_*.png` accesibles vía API.

### Implementation for User Story 4

- [ ] T018 [P] [US4] Crear `MapRendererService` en `backend/app/Services/MapRendererService.php` con:
  - Método privado `latLngToPixel($lat, $lng, $bounds, $width, $height): array` — proyección Mercator simplificada escalada al bounding box de los datos
  - Método privado `calculateBounds(array $allDeliveries, $warehouseLat, $warehouseLng): array` — bounding box con margen 10% sobre datos + bodega
  - Método `renderOverview($warehouseLat, $warehouseLng, array $routes, array $allDeliveries, string $outputPath): string` — genera PNG con: marcador bodega (cuadrado rojo), entregas por ruta (círculos de colores distintos por ruta), polilínea por ruta conectando bodega→P1→...→PN, leyenda de rutas, escala km
  - Método `renderRouteMap($warehouseLat, $warehouseLng, array $deliveries, string $routeName, string $outputPath): string` — genera PNG individual de una ruta
  - Método `renderAnomalyMap($warehouseLat, $warehouseLng, array $routes, array $anomalies, array $allDeliveries, string $outputPath): string` — genera PNG destacando anomalías (marcadores en rojo, diferente tamaño)

- [ ] T019 [US4] Integrar `MapRendererService` en `MeasurementService::execute()`:
  - Después de calcular métricas y anomalías, generar mapas
  - Incluir referencias a archivos de imagen en el resultado

**Checkpoint**: Evaluación incluye mapas PNG descriptivos por ruta y generales.

---

## Phase 7: User Story 3 — Comparación de Estrategias (Priority: P3)

**Goal**: El responsable de optimización puede comparar indicadores de distintas ejecuciones
para seleccionar la mejor estrategia.

**Independent Test**: `GET /api/evaluations` lista múltiples ejecuciones. Cada una tiene
`metrics_summary` comparable (mismos campos, mismas unidades).

### Implementation for User Story 3

- [ ] T020 [P] [US3] Extender `GET /api/evaluations` en `EvaluationController` para incluir en el listado todos los campos de `metrics_summary` necesarios para comparación rápida: coverage_territorial_km, distancia_promedio_general_km, balance_index, inter_cluster_min_distance_km, operational_penalty_total, total_anomalias_detectadas
- [ ] T021 [US3] Extender `GET /api/evaluations/{id}` para incluir el ranking de rutas en la respuesta (mismos campos que POST según contrato)

**Checkpoint**: Listado de evaluaciones permite comparación visual de resultados entre ejecuciones.

---

## Phase 8: Polish & Cross-Cutting Concerns

**Purpose**: Mejoras que afectan múltiples user stories.

- [ ] T022 [P] Verificar casos borde en `MetricsCalculatorService`:
  - Ejecutar evaluación con 0 rutas en BD
  - Ejecutar evaluación con rutas pero 0 entregas asignadas
  - Ejecutar evaluación con 1 sola ruta (separación entre clusters = N/A o 0)
  - Ejecutar evaluación con 500 entregas / 10 rutas y medir tiempo < 30s
- [ ] T023 [P] Verificar exportación: archivos JSON y CSV válidos, deliveries.csv con datos correctos
- [ ] T024 [P] Verificar mapas: archivos PNG generados, bounding box contiene todos los puntos, colores distintos por ruta
- [ ] T025 [P] Verificar reproducibilidad: dos ejecuciones con mismos parámetros producen métricas idénticas (excepto id, executed_at, output_path)
- [ ] T026 Ejecutar y verificar todos los escenarios de `quickstart.md` en `specs/003-results-measurement/quickstart.md`

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies — verificar entorno existente
- **Foundational (Phase 2)**: Depends on Setup — BLOCKS all user stories
- **US1 — Metrics (Phase 3)**: Depends on Phase 2 (needs HaversineService existente)
- **US5 — Export (Phase 4)**: Depends on Phase 2 (needs Evaluation model) + Phase 3 (needs metrics data). Can start after T008.
- **US2 — Anomalies (Phase 5)**: Depends on Phase 3 (needs MetricsCalculatorService for route metrics). Can start after T008.
- **US4 — Maps (Phase 6)**: Depends on Phase 3 (needs route data + metrics). Can start after T008.
- **US3 — Comparison (Phase 7)**: Depends on Phase 4 (needs Evaluation list endpoint). Can start after T014.

### User Story Dependencies

| User Story | Depends On | Can Start After |
|------------|------------|-----------------|
| US1 (Metrics) | Phase 2 | T007 |
| US5 (Export) | Phase 2 + US1 | T008 (MetricsCalculatorService core) |
| US2 (Anomalies) | US1 | T008 (needs per-route metrics) |
| US4 (Maps) | US1 | T008 (needs delivery coordinates) |
| US3 (Comparison) | US5 | T014 (Evaluation API routes) |

### Within Each Phase

- Services before controllers
- Core calculation before export/visualization
- Backend before any frontend (none needed here)

### Parallel Opportunities

- **Phase 2**: T004-T006 can run in parallel (migration, model, directory)
- **Phase 3**: T008 (MetricsCalculator) and T009 (MeasurementService orchestrator) can be parallelized: T008 creates the pure math, T009 the orchestration
- **Phase 4**: T011 (MetricsExporter) and T012 (EvaluationController) can be partially parallel: T011 is the writer, T012 is the HTTP layer
- **Phase 5**: T016 (AnomalyDetector) independent, can run in parallel with Phase 4
- **Phase 6**: T018 (MapRendererService) independent, can run in parallel with Phase 4 and 5
- **Phase 8**: T022-T025 can all run in parallel (independent verifications)

---

## Parallel Example: Phase 2 — Foundational

```bash
# Launch all independent tasks together:
Task: T004 Create evaluations migration
Task: T005 Create Evaluation model
Task: T006 Create exports directory
```

## Parallel Example: Phase 3 — US1 Core

```bash
# Tasks T008 and T009 can be done in parallel:
Task: T008 Create MetricsCalculatorService (pure math)
Task: T009 Create MeasurementService (orchestrator)
```

---

## Implementation Strategy

### MVP First (US1 Only)

1. Complete Phase 1: Verificar entorno
2. Complete Phase 2: Foundational (Evaluation model)
3. Complete Phase 3: US1 — MetricsCalculatorService (core metrics)
4. **STOP and VALIDATE**: `MeasurementService::execute()` retorna métricas correctas
5. Deploy/demo si es necesario

### Incremental Delivery

1. Setup + Foundational → Evaluations model listo
2. Add US1 → Métricas calculables programáticamente (MVP!)
3. Add US5 → Exportación a JSON/CSV + API endpoints
4. Add US2 → Detección de anomalías activa
5. Add US4 → Mapas PNG generados automáticamente
6. Add US3 → Comparación entre ejecuciones
7. Each story adds value without breaking previous stories

### Parallel Team Strategy

With multiple developers:

1. Team completes Phase 1 + Phase 2 together
2. Once Foundational is done:
   - Developer A: US1 (MetricsCalculatorService — core)
   - Developer B: US5 (Export — EvaluationController)
   - Developer C: US2 (AnomalyDetector)
3. Developer A completes US1 then helps with US4 (MapRenderer) or US3 (Comparison)
4. Developer B completes US5 then integrates with US1 output

---

## Notes

- [P] tasks = different files, no dependencies
- [Story] label maps task to specific user story
- Each user story is independently completable and testable
- Commit after each task or logical group
- Stop at any checkpoint to validate story independently
- Backend changes require `docker compose exec backend php artisan migrate` to take effect
- HaversineService existente en `backend/app/Services/HaversineService.php` — reutilizar sin modificar
- RouteMetricsService existente en `backend/app/Services/RouteMetricsService.php` — NO modificar (es de Fase 2)
- Sin cambios en frontend — toda la feature es server-side
- `random_seed` es metadata en esta fase (las métricas actuales no usan aleatoriedad). Dos ejecuciones con distinta semilla deben producir los mismos resultados. Esto cambiará cuando se incorporen algoritmos que usen muestreo aleatorio en fases futuras.
