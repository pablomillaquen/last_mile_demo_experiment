# Tasks: Route Measurement — Distancia, Tiempo y Secuencia

**Input**: Design documents from `specs/002-route-measurement/`

**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/api.md, quickstart.md

**Tests**: No se incluyen tareas de tests (no solicitados en spec).

**Organization**: Tasks grouped by user story for independent implementation.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to
- Include exact file paths in descriptions

## Path Conventions

- **Backend**: `backend/app/`, `backend/database/`, `backend/routes/`
- **Frontend**: `frontend/src/`

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Verificar que el entorno existente funciona antes de comenzar.

- [ ] T001 Verificar que los contenedores Docker están activos: `docker compose ps`
- [ ] T002 Verificar que la API responde: `curl http://localhost:8000/api/metrics`
- [ ] T003 Verificar que el frontend responde: `curl http://localhost:3000`

**Checkpoint**: Entorno listo para desarrollo.

---

## Phase 2: Foundational — Backend Services & Modelos

**Purpose**: Crear la infraestructura compartida que todas las user stories necesitan:
tabla `settings`, `HaversineService`, `RouteMetricsService`, `SettingsController`.

**⚠️ CRITICAL**: No user story work can begin until this phase is complete.

- [ ] T004 [P] Crear migración `create_settings_table` en `backend/database/migrations/`
- [ ] T005 [P] Crear modelo `Setting` en `backend/app/Models/Setting.php`
- [ ] T006 [P] Crear `SettingsSeeder` con valores iniciales (warehouse_lat=-33.045, warehouse_lng=-71.62, average_speed_kmh=30) en `backend/database/seeders/SettingsSeeder.php`
- [ ] T007 [P] Crear `HaversineService` con método estático `calculate($lat1, $lng1, $lat2, $lng2): float` en `backend/app/Services/HaversineService.php`
- [ ] T008 [P] Crear `RouteMetricsService` en `backend/app/Services/RouteMetricsService.php` con métodos:
  - `calculateDistance(Route $route): float` — suma Haversine entre todos los pares consecutivos + bodega → P1 + PN → bodega
  - `calculateEstimatedTime(float $distanceKm): int` — minutos = (distancia / velocidad global) * 60
  - Obtiene velocidad global desde `Setting::where('key', 'average_speed_kmh')->first()->value`
- [ ] T009 [P] Crear `SettingsController` en `backend/app/Http/Controllers/SettingsController.php` con:
  - `GET /api/settings` — devuelve todas las settings como JSON plano
  - `PUT /api/settings` — actualización parcial (solo envía los campos a modificar), validando lat ∈ [-90,90], lng ∈ [-180,180], speed > 0
- [ ] T010 Registrar ruta `GET /api/settings` y `PUT /api/settings` en `backend/routes/api.php`
- [ ] T011 Ejecutar migración y seeder: `docker compose exec backend php artisan migrate:fresh --seed`

**Checkpoint**: Foundation ready — `GET /api/settings` responde con valores iniciales. User stories pueden comenzar.

---

## Phase 3: User Story 6 — Configuración del Centro de Distribución (Priority: P1) 🎯 MVP

**Goal**: El operador puede configurar la ubicación de la bodega y la velocidad promedio desde la interfaz.
La bodega se muestra en el mapa con marcador distintivo.

**Independent Test**: Al abrir `/settings`, el operador ve los valores actuales. Al cambiarlos,
`GET /api/settings` refleja los nuevos valores. El mapa muestra un marcador de bodega.

### Implementation for User Story 6

#### Frontend

- [ ] T012 [P] [US6] Extender `api.ts` en `frontend/src/lib/api.ts` con `settingsApi.get()` y `settingsApi.update(data)`
- [ ] T013 [P] [US6] Crear `SettingsForm` component en `frontend/src/components/SettingsForm.tsx` con campos:
  - warehouse_lat (number input)
  - warehouse_lng (number input)
  - average_speed_kmh (number input)
  - Botón guardar → PUT /api/settings
- [ ] T014 [P] [US6] Crear ruta `/settings` en `frontend/src/app/settings/page.tsx` que renderiza `SettingsForm`
- [ ] T015 [P] [US6] Agregar link "Configuración" en la barra de navegación en `frontend/src/app/layout.tsx`

#### Mapa — Marcador de Bodega

- [ ] T016 [US6] Modificar `MapView` en `frontend/src/components/MapView.tsx` para:
  - Obtener `warehouse_lat` y `warehouse_lng` desde `GET /api/settings`
  - Renderizar un marcador distintivo (icono diferente, ej. azul con ícono de casa) en esa posición
  - El marcador debe tener un tooltip "Bodega"
  - Todas las polylines de rutas deben comenzar y terminar en este marcador

**Checkpoint**: Settings funcionales, bodega visible en el mapa.

---

## Phase 4: User Stories 9 & 10 — Métricas de Distancia y Tiempo (Priority: P2)

**Goal**: Cada ruta muestra distancia total, distancia promedio por entrega y tiempo estimado.
El endpoint `/api/metrics` incluye ruta más larga y más corta.

**Independent Test**: `GET /api/routes/{id}` incluye `total_distance_km`, `avg_distance_per_delivery_km`,
`estimated_time`. `GET /api/metrics` incluye `route_metrics.longest_route` y `route_metrics.shortest_route`.

### Implementation for User Stories 9 & 10

#### Backend

- [ ] T017 [US9] Extender `RouteController@show` en `backend/app/Http/Controllers/RouteController.php` para incluir:
  - `total_distance_km` (calculado con `RouteMetricsService::calculateDistance`)
  - `avg_distance_per_delivery_km` (total_distance / route_packages_count, 0 si no hay paquetes)
  - `estimated_time` (calculado con `RouteMetricsService::calculateEstimatedTime`, formateado como "Xh Ym")
- [ ] T018 [US9] Extender `MetricsController` en `backend/app/Http/Controllers/MetricsController.php` para incluir:
  - `route_metrics.longest_route` (name, total_distance_km, estimated_time)
  - `route_metrics.shortest_route` (name, total_distance_km, estimated_time)
  - Excluir rutas sin paquetes de ambos rankings

#### Frontend

- [ ] T019 [P] [US9] Extender `RouteDetail` page en `frontend/src/app/routes/[id]/page.tsx` para mostrar:
  - Distancia total (km)
  - Distancia promedio por entrega (km)
  - Tiempo estimado (Xh Ym)
- [ ] T020 [P] [US10] Extender `MetricsCards` component en `frontend/src/components/MetricsCards.tsx` para mostrar:
  - Ruta más larga (nombre + distancia + tiempo)
  - Ruta más corta (nombre + distancia + tiempo)
  - Velocidad promedio configurada

**Checkpoint**: Cada ruta muestra sus métricas. Dashboard muestra ruta más larga/más corta.

---

## Phase 5: User Story 7 — Visualización de Secuencia (Priority: P2)

**Goal**: Los paquetes asignados a una ruta muestran su número de secuencia en la tabla de detalle
y en los marcadores del mapa.

**Independent Test**: Al abrir `/routes/{id}`, la tabla de paquetes muestra columna "Secuencia" con
números correlativos. En el mapa, los marcadores de paquetes muestran su número de secuencia.

### Implementation for User Story 7

#### Backend

- [ ] T021 [US7] Extender `RouteController@show` en `backend/app/Http/Controllers/RouteController.php` para incluir en cada paquete el campo `sequence` desde `route_packages`

#### Frontend — Tabla de Secuencia

- [ ] T022 [P] [US7] Modificar tabla de paquetes en `frontend/src/app/routes/[id]/page.tsx` para agregar columna "Secuencia" que muestre `package.sequence`
- [ ] T023 [P] [US7] Si no existe, crear `RouteSequenceTable` component en `frontend/src/components/RouteSequenceTable.tsx` que muestre: secuencia, tracking_number, recipient_name, address en orden de secuencia

#### Frontend — Mapa con Secuencia

- [ ] T024 [US7] Modificar `MapView` en `frontend/src/components/MapView.tsx` para mostrar el número de secuencia en los marcadores de paquetes
  - Formato sugerido: círculo con número dentro, o tooltip que incluya "Entrega #N"
  - Los marcadores deben mantener el color de su ruta

**Checkpoint**: Secuencia numérica visible en tabla y mapa.

---

## Phase 6: User Story 8 — Visualización de Recorrido (Priority: P3)

**Goal**: El mapa dibuja una línea poligonal que conecta bodega → P1 → P2 → ... → PN → bodega
para cada ruta, con colores distintivos. Las líneas se actualizan al asignar/desasignar.

**Independent Test**: En `/map`, cada ruta muestra una línea de color que conecta todos sus puntos
en orden. Al asignar un paquete nuevo, la línea se actualiza.

### Implementation for User Story 8

#### Frontend — Polylines

- [ ] T025 [P] [US8] Modificar `MapView` en `frontend/src/components/MapView.tsx` para dibujar `Polyline` por cada ruta:
  - Puntos: [bodega, P1, P2, ..., PN, bodega]
  - Color distinto por ruta (usar el mismo color que los marcadores de paquetes)
  - Opcional: tooltip en la polyline con el nombre de la ruta y distancia total
- [ ] T026 [US8] Asegurar que las polylines se actualicen dinámicamente al cambiar la asignación:
  - Cuando se asigna un paquete a una ruta (desde `/routes/{id}`), la polyline se redibuja al navegar al mapa
  - Opcional: el mapa recibe las rutas como prop y se refresca automáticamente
- [ ] T027 [US8] Asegurar que todas las rutas se muestren simultáneamente en el mapa con sus respectivas polylines (no una por una)

**Checkpoint**: Mapa muestra todas las rutas con líneas conectando entregas.

---

## Phase 7: Polish & Cross-Cutting Concerns

**Purpose**: Mejoras que afectan múltiples user stories.

- [ ] T028 [P] Ejecutar y verificar todos los escenarios de `quickstart.md` en `specs/002-route-measurement/quickstart.md`
- [ ] T029 [P] Verificar que el endpoint `/api/metrics` responde correctamente con rutas vacías y con datos
- [ ] T030 [P] Verificar que actualización parcial de settings funciona (enviar solo 1 campo)
- [ ] T031 [P] Verificar que validaciones de latitud/longitud/velocidad funcionan en `PUT /api/settings`

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies — verificar entorno existente
- **Foundational (Phase 2)**: No dependencies on other phases — BLOCKS all user stories
- **US6 — Settings (Phase 3)**: Depends on Phase 2 (needs Settings model + controller)
- **US9 & US10 — Metrics (Phase 4)**: Depends on Phase 2 (needs HaversineService, RouteMetricsService) and Phase 3 (needs speed setting). Can start after T008.
- **US7 — Sequence (Phase 5)**: Depends on Phase 2 (needs Route model). Can start after T004-T011.
- **US8 — Polylines (Phase 6)**: Depends on Phase 3 (needs bodega marker), Phase 4 (needs distance), Phase 5 (needs sequence). Can start after T016.

### User Story Dependencies

| User Story | Depends On | Can Start After |
|------------|------------|-----------------|
| US6 (Settings) | Phase 2 | T011 |
| US9 & US10 (Metrics) | Phase 2 + US6 (speed) | T008 (HaversineService) |
| US7 (Sequence) | Phase 2 | T011 |
| US8 (Polylines) | US6 + US7 | T016 |

### Within Each Phase

- Models before services
- Services before controllers
- Backend before frontend
- Core implementation before integration

### Parallel Opportunities

- **Phase 2**: T004-T011 can run in parallel (migration, model, seeder, services, controller)
- **Phase 3**: T012-T015 (frontend) and T016 (map marker) can run in parallel
- **Phase 4**: T017-T018 (backend) and T019-T020 (frontend) partially parallel
- **Phase 5**: T021 (backend) before T022-T024 (frontend)
- **Phase 6**: T025-T027 sequential (same component)

---

## Parallel Example: Phase 2 — Foundational

```bash
# Launch all independent tasks together:
Task: T004 Create migration
Task: T005 Create Setting model
Task: T006 Create SettingsSeeder
Task: T007 Create HaversineService
Task: T008 Create RouteMetricsService
Task: T009 Create SettingsController
Task: T010 Register API routes
```

---

## Implementation Strategy

### MVP First (US6 Only)

1. Complete Phase 1: Verificar entorno
2. Complete Phase 2: Foundational (backend services + settings)
3. Complete Phase 3: US6 — Settings (config page + bodega marker)
4. **STOP and VALIDATE**: Settings funcionan, bodega visible en mapa
5. Deploy/demo si es necesario

### Incremental Delivery

1. Setup + Foundational → Backend listo
2. Add US6 → Settings funcionales (MVP!)
3. Add US9 & US10 → Métricas de distancia y tiempo
4. Add US7 → Secuencia visible
5. Add US8 → Polylines en mapa
6. Each story adds value without breaking previous stories

### Parallel Team Strategy

With multiple developers:

1. Team completes Phase 1 + Phase 2 together
2. Once Foundational is done:
   - Developer A: US6 (Settings frontend + bodega marker)
   - Developer B: US9 & US10 (Metrics backend + frontend)
   - Developer C: US7 (Sequence)
3. Developer A completes US6 then helps with US8 (Polylines — depends on US6 + US7)

---

## Notes

- [P] tasks = different files, no dependencies
- [Story] label maps task to specific user story
- Each user story is independently completable and testable
- Commit after each task or logical group
- Stop at any checkpoint to validate story independently
- Backend changes require `docker compose exec backend php artisan migrate:fresh --seed` to take effect
- Frontend changes are hot-reloaded by Next.js dev server
