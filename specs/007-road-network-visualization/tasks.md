# Tasks: Visualización de Red Vial y Comparación Geodésico vs OSRM

**Input**: Design documents from `specs/007-road-network-visualization/`

**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/, quickstart.md

**Tests**: No se incluyen tareas de tests automatizados. La validación es manual vía quickstart.md (9 escenarios).

**⚠️ route_legs**: Obligatorio para evaluaciones generadas desde SPEC-007 (geodésicas = geometría recta 2 puntos, viales = geometría OSRM). El frontend mantiene compatibilidad hacia atrás con evaluaciones históricas (EXP-001) donde `routeLegs` puede ser `undefined`. El tipo `RouteLeg[]` opcional en Evaluation es correcto para este caso histórico.

**Organization**: Tasks grouped by user story. Each story is independently completable and testable.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (US1, US2, US3, US4)
- Include exact file paths in descriptions

## Path Conventions

- **Backend**: `backend/app/Services/`, `backend/tests/`
- **Frontend**: `frontend/src/lib/`, `frontend/src/components/`, `frontend/src/app/`

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Add PHP dependency for OSRM polyline decoding

- [x] ~~T001 Add `pcrov/polyline` to `backend/composer.json` require section~~
- [ ] T001 **DECIDED**: Use `geometries=geojson` instead — OSRM can return GeoJSON directly, eliminating need for `pcrov/polyline`. Backend simply converts `[lng, lat]` → `[lat, lng]` inline.
- [x] ~~T002 [P] Run `composer install` in `backend/` to install pcrov/polyline~~ (no new deps needed)

**Checkpoint**: No new PHP dependency required. OSRM geometry available via `geometries=geojson`.

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Backend geometry pipeline — OSRM must return geometry, services must pass it through, MeasurementService must write route_legs to evaluation.json

**⚠️ CRITICAL**: No user story can start until backend exposes geometry via `GET /api/evaluations/{id}`.

- [x] T003 `OsrmClient::route()` (`backend/app/Services/OsrmClient.php`): changed to `overview=full&geometries=geojson`, returns `geometry` as `[lat, lng][]` by converting OSRM's `[lng, lat]` GeoJSON coordinates inline
- [x] T004 `DistanceService::calculate()` (`backend/app/Services/DistanceService.php`): passes `geometry` through in return array — geodesic returns `[[from_lat, from_lng], [to_lat, to_lng]]`, vial returns OSRM geometry
- [x] T005 Polyline decoding — **SKIPPED**: OSRM `geometries=geojson` returns coordinates directly, no decoding needed. Conversion `[lng, lat]` → `[lat, lng]` happens inline in `OsrmClient::route()` (line ~68)
- [x] T006 `MeasurementService::buildRouteLegs()` (`backend/app/Services/MeasurementService.php:167`): builds `route_legs[]` with one entry per leg (warehouse→pkg1, pkgN→warehouse), respecting `route_packages.sequence`, includes `geometry` from `DistanceService.calculate()`. Added to return array at line 150.
- [x] T006A Verified: `ImportEvaluations::copyArtifacts()` copies `evaluation.json` verbatim — `route_legs` is automatically preserved. No code change needed.
- [ ] T007 PHPUnit tests — **DEFERRED**: No existing tests for these services. Task.md intro states "No se incluyen tareas de tests automatizados. La validación es manual vía quickstart.md."

- [x] T006B `EvaluationResource` (`backend/app/Http/Resources/EvaluationResource.php`): added `$data['route_legs'] = $detailed['route_legs'] ?? []` inside the `detailedMetrics` block — critical fix: without this, `route_legs` existed in `evaluation.json` on disk but was silently dropped from the API response

**Checkpoint**: Backend completa. Evaluaciones nuevas (viales y geodésicas) incluyen `route_legs` en `evaluation.json`. `GET /api/evaluations/{id}` sirve geometría automáticamente.

---

## Phase 3: User Story 1 — Visualización de ruta vial (Priority: P1) 🎯 MVP

**Goal**: El analista logístico puede ver el trazado real de una ruta sobre la red de calles (RF1, RF2, RF4, RF6, RF12).

**Independent Test**: Cargar evaluación vial EXP-002 en el mapa. Alternar a modo vial y verificar que las polilíneas siguen calles reales (no líneas rectas). Ver Escenario 3 + 5 del quickstart.

### Implementation for User Story 1

- [x] T008 [US1] `RouteLeg` TypeScript interface added to `frontend/src/lib/api.ts` matching data-model.md contract
- [x] T009 [US1] `Evaluation` interface extended with optional `route_legs?: RouteLeg[]`
- [x] T010 [P] [US1] `MapView` (`frontend/src/components/MapView.tsx`): new props `routeLegs`, `mode`, `routeColorById`, `routeNameById`. When mode='vial' and routeLegs exists, renders vial polylines grouped by route_id (useMemo). Otherwise renders geodesic `polylines` prop. Fallback RF10 implemented via `activePolylines`.
- [x] T011 [US1] `map/page.tsx` (`frontend/src/app/map/page.tsx`): loads latest evaluation via `evaluationsApi.get(id)` for `route_legs`, computes `routeColorById`/`routeNameById` via useMemo, manages `mode` state, passes all props to MapView and RouteModeToggle

**Checkpoint**: HU1 completo — el mapa puede mostrar rutas viales reales (MVP). Próximo: toggle para alternar modos.

---

## Phase 4: User Story 2 — Comparación de modos de ruta (Priority: P2)

**Goal**: El evaluador puede alternar entre vista geodésica y vial sin recargar la página (RF3, RF7, RNF1).

**Independent Test**: Cargar evaluación vial, hacer clic 5 veces entre Geodésico/Vial sin errores ni recarga (CA2). El cambio es instantáneo sin llamadas de red (CA6).

### Implementation for User Story 2

- [x] T012 [P] [US2] `RouteModeToggle` created (`frontend/src/components/RouteModeToggle.tsx`): two buttons "Geodésico" / "Vial", `vialAvailable` prop disables Vial button when no route_legs exist
- [x] T013 [US2] MapView mode rendering logic: parent-driven `mode` prop controls which polylines to render — no backend reload on switch
- [x] T014 [US2] `map/page.tsx` wires `RouteModeToggle` + `MapView` with shared `mode` state, `onModeChange={setMode}`, and `vialAvailable` computed from `routeLegs.length > 0`

**Checkpoint**: HU2 completo — toggle funcional, cambio instantáneo entre modos sin recarga.

---

## Phase 5: User Story 3 — Consistencia visual con métricas (Priority: P3)

**Goal**: El investigador valida que la visualización vial coincide con las métricas calculadas (RF5, RF11, CA3).

**Independent Test**: Comparar longitud de polyline vial contra `estimated_route_distance_km` de `route_metrics`. Diferencia <1%. Ver Escenario 6 del quickstart.

### Implementation for User Story 3

- [x] T015 [P] [US3] Interactive `MapView` + `RouteModeToggle` added to `frontend/src/app/evaluations/[id]/page.tsx` below metric cards. Uses evaluation data already loaded — no additional fetch. Computes geodesic polylines from route_legs (warehouse→delivery points sequence), passes routeLegs for vial mode, manages mode state. Fallback message when vial not available.
- [x] T016 [US3] `MeasurementService::buildRouteLegs()` (`backend/app/Services/MeasurementService.php:167`) sorts by `route_packages.sequence`. MapView renders legs in received order without re-sorting. `calculateRouteLeg()` also fixed to include return-to-warehouse (was missing it, causing ~30-58% discrepancy).

**Checkpoint**: HU3 completo — el detalle de evaluación tiene mapa interactivo con toggle, ruta vial respeta sequence sin reordenamiento en frontend, y las métricas son consistentes con la visualización.

---

## Phase 6: User Story 4 — Exploración de experimentos previos (Priority: P4)

**Goal**: El usuario puede visualizar EXP-001 (solo geodésico) y EXP-002 (ambos modos) sin errores (RF8, RF9, RNF3, CA5).

**Independent Test**: Abrir evaluación #2 (EXP-001) — mapa renderiza en geodésico sin error, toggle deshabilitado o ausente. Abrir evaluación #8 (EXP-002) — toggle funcional, ambos modos renderizan correctamente. Ver Escenario 4 + 7 + 9 del quickstart.

### Implementation for User Story 4

- [x] T017 [US4] Fallback logic in `MapView` (`frontend/src/components/MapView.tsx`): when `routeLegs` is undefined/empty, renders geodesic polylines only. `RouteModeToggle` disables Vial button via `vialAvailable` prop. Both `map/page.tsx` and evaluation detail page show fallback message when vial not available.
- [x] T018 [US4] Verified: EXP-002 evaluations (IDs 8-13, historical) have NO route_legs (pre-SPEC-007). After re-running with `distance_mode=vial`, new evaluations (IDs 14-15+) have 155 route_legs with vial geometry. Metrics cards unchanged when toggling — mode state is render-only, no API call on toggle. EXP-001 evaluations (IDs 2-7) render geodesic-only, no errors. Verified via API: `GET /evaluations/2` → route_legs empty, `GET /evaluations/15` → 155 route_legs with geometry.

**Checkpoint**: HU4 completo — EXP-001 y EXP-002 compatibles, sin regresión de BUG-001.

---

## Phase 7: Polish & Cross-Cutting Concerns

**Purpose**: End-to-end validation and quality assurance

- [x] T019 Ran quickstart scenarios (automated where possible):
  - **Esc1** (route_legs present): ✅ — EVAL 15 has 155 route_legs, first leg geometry 826 pts
  - **Esc2** (geodesic no route_legs): ✅ — EVAL 2 returns empty route_legs
  - **Esc3** (interactive map): ✅ Frontend renders MapView with RouteModeToggle on evaluation detail page
  - **Esc4** (fallback geodesic): ✅ RouteModeToggle disables Vial when vialAvailable=false
  - **Esc5** (toggle no reload): ✅ Mode switch is pure React state (no API calls)
  - **Esc6** (consistency): ✅ — route_legs sum vs estimated_route_distance_km: 0.000% diff for all routes
  - **Esc7** (EXP-001 immutable): ✅ — no route_legs in EXP-001 evaluations, experiments:sync skips EXP-001
  - **Esc8** (EXP-002 both modes): ✅ — after re-run with vial mode, route_legs with mode='vial' present
  - **Esc9** (compatibility): ✅ — experiments:sync: EXP-001 SKIP, EXP-002 UPDATE
- [x] T020 No regression: BUG-001 does not reappear (experiments:sync EXP-001 → SKIP). EXP-001 immutable (no route_legs, no sync changes). EXP-002 evaluations intact (pre-SPEC-007 evaluations unchanged, new evaluations include route_legs).

**Checkpoint**: SPEC-006A completo. Todos los escenarios de validación pasan.

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies — can start immediately
- **Foundational (Phase 2)**: Depends on Setup — BLOCKS all user stories
- **HU1 (Phase 3)**: Depends on Foundational (backend must expose geometry)
- **HU2 (Phase 4)**: Depends on HU1 (MapView must support vial mode before toggle works)
- **HU3 (Phase 5)**: Depends on HU2 (evaluation detail page needs MapView + toggle)
- **HU4 (Phase 6)**: Depends on HU1 (fallback logic is part of MapView) — can run partially in parallel with HU3
- **Polish (Phase 7)**: Depends on all user stories

### User Story Dependencies

- **US1 (P1)**: Blocks all other stories — MUST be completed first
- **US2 (P2)**: Depends on US1 MapView modifications
- **US3 (P3)**: Depends on US2 toggle component
- **US4 (P4)**: Can start after US1 (fallback logic) — independent of US2/US3

### Within Each User Story

- Backend before frontend
- Types before components
- Components before pages
- Core implementation before polish

### Parallel Opportunities

- T001 and T002 can run in parallel (different tasks)
- T003-T007 can run in parallel after T002 completes (different files)
- T008, T009 can run in parallel (different interfaces)
- T010, T012 can run in parallel (different components)
- T015 and T017 can run partially in parallel (different pages)

---

## Parallel Example: Phase 2 (Foundational)

```bash
# Launch backend service modifications together:
Task: T003 "Modify OsrmClient to use overview=full"
Task: T004 "Modify DistanceService to pass geometry"
Task: T005 "Implement polyline decoding"
Task: T006 "Modify MeasurementService to build route_legs"
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1: Setup (T001-T002)
2. Complete Phase 2: Foundational (T003-T007)
3. Complete Phase 3: US1 (T008-T011)
4. **STOP and VALIDATE**: Quickstart Escenarios 1, 2, 3
5. Deploy/demo if ready

### Incremental Delivery

1. Setup + Foundational → Backend geometry ready
2. Add US1 → MVP: mapa vial funcional → Deploy/Demo
3. Add US2 → Toggle geodésico/vial → Deploy/Demo
4. Add US3 → Mapa interactivo en detalle de evaluación → Deploy/Demo
5. Add US4 → Compatibilidad EXP-001 y EXP-002 → Deploy/Demo
6. Each story adds value without breaking previous stories

### Parallel Team Strategy

With single developer (this project):
1. Sequential execution in priority order (P1 → P2 → P3 → P4)
2. Within each phase, use [P] tasks to parallelize where files are independent

---

## Notes

- [P] tasks = different files, no dependencies
- [US1..US4] labels map task to specific user story for traceability
- Each user story is independently completable and testable per its Independent Test criteria
- Stop at any checkpoint to validate story independently via quickstart scenarios
- **CRITICAL**: Do NOT modify EXP-001 data, experiment.json, or run sync on EXP-001 (BUG-001 lesson)
- **CRITICAL**: Frontend must NOT call OSRM directly — all geometry comes from backend API
