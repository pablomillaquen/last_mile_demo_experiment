---

description: "Implementation tasks for Simulación de Operación Logística — Asignación Manual"
---

# Tasks: Simulación de Operación Logística — Asignación Manual

**Input**: Design documents from `specs/001-last-mile-operation/`

**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/api.md

**Tests**: No se incluyen tareas de testing automatizado. Cada fase incluye una tarea de validación manual como checklist de aceptación.

**Organization**: Tasks grouped by user story for independent implementation and testing.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (US1-US5)
- Include exact file paths in descriptions

## Path Conventions

- `backend/` → Laravel API (PHP 8.2)
- `frontend/` → NextJS 14 (TypeScript)
- Paths match plan.md project structure

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Project initialization and Docker orchestration

- [X] T001 Create `docker-compose.yml` with postgres, pgadmin, backend, frontend services
- [X] T002 [P] Create `backend/Dockerfile` with PHP 8.2 + Laravel 11 bootstrap
- [X] T003 [P] Create `frontend/Dockerfile` with Node 20 + NextJS 14 bootstrap

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core infrastructure that MUST be complete before ANY user story can be implemented

**⚠️ CRITICAL**: No user story work can begin until this phase is complete

- [ ] T004 Configure PostgreSQL connection and `.env` for Laravel backend
- [ ] T005 Setup API route file `backend/routes/api.php` with controller stubs
- [ ] T006 [P] Create API client wrapper in `frontend/src/lib/api.ts` (axios/fetch helper with base URL, error handling, typed methods)

**Checkpoint**: Foundation ready — user story implementation can now begin

---

## Phase 3: User Story 1 — Registro de Paquetes (Priority: P1) 🎯 MVP

**Goal**: Operador puede registrar, listar, editar y eliminar paquetes con dirección y coordenadas geográficas

**Independent Test**: `curl -X POST http://localhost:8000/api/packages` devuelve `201 Created`; `GET /api/packages` devuelve listado paginado; `DELETE /api/packages/{id}` devuelve `204`

### Implementation

- [ ] T007 [P] [US1] Create `Package` model in `backend/app/Models/Package.php` (campos: tracking_number, recipient_name, delivery_address, district, city, latitude, longitude, received_at; computed `assigned` boolean via `getAssignedAttribute()`, `routePackage` relationship)
- [ ] T008 [P] [US1] Create `create_packages_table` migration (Laravel Schema builder) in `backend/database/migrations/` — sin columna `status` (se infiere de `route_packages`)
- [ ] T009 [US1] Create `PackageController` with CRUD in `backend/app/Http/Controllers/PackageController.php`
- [ ] T010 [P] [US1] Create `PackageFactory` in `backend/database/factories/PackageFactory.php` with coordinates from Valparaíso (40%), Viña del Mar (30%), Quilpué (20%), Villa Alemana (10%) y solo los campos del modelo Package
- [ ] T011 [P] [US1] Create `DemoDataSeeder` in `backend/database/seeders/DemoDataSeeder.php` (50 packages usando PackageFactory, solo paquetes — rutas se agregan en T040)
- [ ] T012 [P] [US1] Create `PackageTable` component in `frontend/src/components/PackageTable.tsx`
- [ ] T013 [P] [US1] Create `PackageForm` component in `frontend/src/components/PackageForm.tsx`
- [ ] T014 [US1] Create packages list page in `frontend/src/app/packages/page.tsx`
- [ ] T015 [US1] Create package creation page in `frontend/src/app/packages/create/page.tsx`
- [ ] T016 [US1] Manual validation: Package CRUD via curl + browser listado + creación + edición + eliminación

**Checkpoint**: Packages CRUD fully functional — testable via curl and browser

---

## Phase 4: User Story 2 — Creación de Rutas (Priority: P1)

**Goal**: Operador puede crear, listar, editar y eliminar rutas de distribución; ver detalle de ruta con sus datos básicos

**Independent Test**: `curl -X POST http://localhost:8000/api/routes` devuelve `201`; `GET /api/routes` devuelve listado; `GET /api/routes/{id}` devuelve detalle

### Implementation

- [ ] T017 [P] [US2] Create `Route` model in `backend/app/Models/Route.php`
- [ ] T018 [P] [US2] Create `create_routes_table` migration (Laravel Schema builder) in `backend/database/migrations/`
- [ ] T019 [US2] Create `RouteController` with CRUD in `backend/app/Http/Controllers/RouteController.php`
- [ ] T020 [P] [US2] Create `RouteTable` component in `frontend/src/components/RouteTable.tsx`
- [ ] T021 [P] [US2] Create `RouteForm` component in `frontend/src/components/RouteForm.tsx`
- [ ] T022 [US2] Create routes list page in `frontend/src/app/routes/page.tsx`
- [ ] T023 [US2] Create route creation page in `frontend/src/app/routes/create/page.tsx`
- [ ] T024 [US2] Create route detail page (basic info, no assignments yet) in `frontend/src/app/routes/[id]/page.tsx`
- [ ] T025 [US2] Manual validation: Routes CRUD via curl + browser listado + detalle + edición + eliminación

**Checkpoint**: Routes CRUD fully functional — independent from packages

---

## Phase 5: User Story 3 — Asignación Manual (Priority: P1)

**Goal**: Operador puede asignar y desasignar paquetes a rutas desde el detalle de ruta, preservando orden de entrega

**Independent Test**: `POST /api/routes/{id}/assign` con `package_id` devuelve `201`; `POST /api/routes/{id}/unassign` devuelve `200`; `409` si el paquete ya está asignado

### Implementation

- [ ] T026 [P] [US3] Create `RoutePackage` model in `backend/app/Models/RoutePackage.php`
- [ ] T027 [P] [US3] Create `create_route_packages_table` migration (Laravel Schema builder) in `backend/database/migrations/`
- [ ] T028 [US3] Create `RouteAssignmentController` in `backend/app/Http/Controllers/RouteAssignmentController.php`
- [ ] T029 [P] [US3] Create `AssignmentPanel` component in `frontend/src/components/AssignmentPanel.tsx`
- [ ] T030 [US3] Integrate `AssignmentPanel` into existing route detail page `frontend/src/app/routes/[id]/page.tsx` (adds assign/unassign UI to the detail page created in T024)
- [ ] T031 [US3] Manual validation: Assign package → verify route detail shows it; unassign → package returns to pool; duplicate → 409

**Checkpoint**: Full assignment workflow — packages can be assigned/unassigned via API and UI

---

## Phase 6: User Story 4 — Visualización Geográfica (Priority: P2)

**Goal**: Operador puede ver paquetes y rutas en un mapa Leaflet + OpenStreetMap, con colores por ruta

**Independent Test**: Abrir `http://localhost:3000/map` muestra marcadores de paquetes; paquetes asignados tienen color distintivo por ruta

### Implementation

- [ ] T032 [US4] Add `leaflet`, `react-leaflet` and `@types/leaflet` dependencies to `frontend/package.json`
- [ ] T033 [P] [US4] Create `MapView` component with Leaflet in `frontend/src/components/MapView.tsx`
- [ ] T034 [US4] Create map page in `frontend/src/app/map/page.tsx`
- [ ] T035 [US4] Manual validation: Open map page → verify markers load → verify colors match route assignments

**Checkpoint**: Map renders all packages as markers with route-based color coding

---

## Phase 7: User Story 5 — Métricas Operativas (Priority: P2)

**Goal**: Operador puede ver métricas: total paquetes, total rutas, paquetes/ruta, paquetes sin asignar

**Independent Test**: `GET /api/metrics` devuelve JSON con los 4 indicadores; valores se actualizan tras asignación/desasignación

### Implementation

- [ ] T036 [US5] Create `MetricsController` in `backend/app/Http/Controllers/MetricsController.php`
- [ ] T037 [P] [US5] Create `MetricsCards` component in `frontend/src/components/MetricsCards.tsx`
- [ ] T038 [US5] Create dashboard page in `frontend/src/app/dashboard/page.tsx`
- [ ] T039 [US5] Manual validation: Check metrics after package creation, route creation, assignment, unassignment

**Checkpoint**: Metrics panel shows live data; curls return correct JSON

---

## Phase 8: Polish & Cross-Cutting Concerns

**Purpose**: Integration verification and final validation

- [ ] T040 Create demo dataset in `backend/database/seeders/DemoDatasetSeeder.php` (100 packages + 5 routes + assignments with deliberately inefficient distribution — e.g. Ruta A: Cerro Alegre + Villa Alemana + Concón; Ruta B: Centro Viña + Quilpué). Extiende o llama a `DemoDataSeeder` para paquetes.
- [ ] T041 [P] Run full quickstart.md validation workflow end-to-end
- [ ] T042 [P] Create main navigation layout in `frontend/src/app/layout.tsx` (navbar con links a packages, routes, map, dashboard)
- [ ] T043 [P] Final `docker compose up --build` verification of all services
- [ ] T044 [P] Manual performance validation: measure map load time (< 3s) and CRUD response time (< 2s), document results

**Checkpoint**: All services verified, performance baseline documented

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies — can start immediately
- **Foundational (Phase 2)**: Depends on Setup — BLOCKS all user stories
- **User Stories (Phase 3-7)**: All depend on Foundational
  - US3 (assignment) depends on data from US1 + US2
  - US4 (map) depends on packages from US1
  - US5 (metrics) depends on data from US1 + US2 + US3
  - US1 and US2 can start immediately after Foundational
- **Polish (Phase 8)**: Depends on all user stories being complete

### User Story Dependencies

- **US1 (P1)**: Can start after Foundational — no dependencies on other stories
- **US2 (P1)**: Can start after Foundational — no dependencies on other stories
- **US3 (P1)**: Depends on US1 (packages) and US2 (routes) existing in the system
- **US4 (P2)**: Depends on US1 (packages with coordinates) and US3 (route assignments for colors)
- **US5 (P2)**: Depends on US1 + US2 + US3 having data to aggregate

### Within Each User Story

- Models before controllers
- Controllers before frontend components
- Core implementation before UI polish
- Manual validation last

### Parallel Opportunities

| Phase | Parallel Tasks |
|-------|---------------|
| Setup | T002 and T003 (Dockerfiles) |
| Foundational | T006 is independent |
| US1 | T007+T008 (model+migration), T010+T011 (factory+seeder), T012+T013 (table+form) |
| US2 | T017+T018 (model+migration), T020+T021 (table+form) |
| US3 | T026+T027 (model+migration) |
| US4 | T033 can be parallel after T032 |
| US5 | T037 can be parallel after T036 |
| Polish | T041, T042, T044 (independientes), T043

---

## Parallel Example: User Story 1

```bash
# Launch model and migration together:
Task: "Create Package model in backend/app/Models/Package.php"
Task: "Create packages migration in backend/database/migrations/"

# Launch factory and seeder together:
Task: "Create PackageFactory in backend/database/factories/PackageFactory.php"
Task: "Create DemoDataSeeder in backend/database/seeders/DemoDataSeeder.php"

# Launch frontend components together:
Task: "Create PackageTable component in frontend/src/components/PackageTable.tsx"
Task: "Create PackageForm component in frontend/src/components/PackageForm.tsx"
```

---

## Implementation Strategy

### MVP First (Phase 3 Only)

1. Complete Phase 1: Setup — Docker orchestration
2. Complete Phase 2: Foundational — DB, routing, API client
3. Complete Phase 3: User Story 1 — Package CRUD + Factory + Seeder
4. **STOP and VALIDATE**: Test packages via curl + browser
5. Deploy/demo if ready

Versión 0.1:
- CRUD de paquetes
- PostgreSQL + Laravel + NextJS
- Docker
- Factory + Seeder con datos de Valparaíso

### Incremental Delivery

1. Setup + Foundational → Foundation ready
2. Add US1 (Packages CRUD + Factory + Seeder) → Test independently → MVP!
3. Add US2 (Routes CRUD + Detail) → Test independently
4. Add US3 (Assignment on Route Detail) → Test independently
5. Add US4 (Map visualization) → Test independently
6. Add US5 (Metrics) → Test independently
7. Phase 8: Demo Dataset + Validation
8. Each story adds value without breaking previous stories

### Parallel Team Strategy

With multiple developers:

1. Team completes Setup + Foundational together
2. Once Foundational is done:
   - Developer A: US1 (Packages)
   - Developer B: US2 (Routes)
3. After US1 + US2:
   - Developer A: US3 (Assignment) + US5 (Metrics)
   - Developer B: US4 (Map)
4. Stories integrate independently

---

## Notes

- [P] tasks = different files, no dependencies
- [Story] label maps task to specific user story for traceability
- Each user story is independently completable and testable
- Commit after each task or logical group
- Stop at any checkpoint to validate story independently
- Avoid: vague tasks, same file conflicts, cross-story dependencies that break independence
- Run `php artisan migrate` inside the backend container after each migration task
- Run `php artisan db:seed --class=DemoDataSeeder` to populate demo data
- Leaflet tiles load from OpenStreetMap (no API key needed)
- All table creation uses Laravel Schema builder (compatible with MySQL, SQLite, etc. if needed)
- Package `assigned` se renderiza en frontend como `assigned ? 'Asignado' : 'Pendiente'` — no existe columna `status` en DB
