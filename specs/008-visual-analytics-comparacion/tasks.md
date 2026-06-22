---
description: "Task list for SPEC-008: Visual Analytics para Comparacion de Rutas"
---

# Tasks: SPEC-008 — Visual Analytics para Comparación de Rutas

**Input**: Design documents from `specs/008-visual-analytics-comparacion/`

**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/

**Tests**: No se requieren tareas de test. La validación es visual y experimental (H5).

**Organization**: Tasks grouped by hito (H0–H5) del plan, cada hito es un incremento validable independientemente.

---

## Phase 1: Setup — Contratos y Tipos (H0)

**Purpose**: Habilitar el filtrado de rutas agregando `routeId` a `PolylineData` y `opacity` para atenuación.

**⚠️ CRITICAL**: Bloquea todos los hitos siguientes.

- [ ] T001 Agregar `routeId: number` a la interfaz `PolylineData` en `frontend/src/components/MapView.tsx`
- [ ] T002 Agregar `opacity?: number` a la interfaz `PolylineData` en `frontend/src/components/MapView.tsx`
- [ ] T003 Agregar `routeId: number` a la interfaz `PolylineData` en `frontend/src/app/evaluations/[id]/page.tsx`
- [ ] T004 [P] Propagar `routeId` en la construcción de `geodesicPolylines` en `frontend/src/app/evaluations/[id]/page.tsx` (extraer route_id del groupBy)
- [ ] T005 [P] Propagar `routeId` en la construcción de `vialPolylines` en `frontend/src/components/MapView.tsx` (usar `leg.route_id` de cada RouteLeg — disponible directamente en los datos, no derivar desde `routeColorById`)
- [ ] T006 Leer `opacity` desde cada `PolylineData` en el renderizado de `<Polyline>` en `frontend/src/components/MapView.tsx` (pathOptions.opacity)

**Checkpoint**: PolylineData incluye routeId y opacity. MapView respeta opacity individual por polilínea.

---

## Phase 2: SplitView — Mapas Sincronizados (H1) 🎯 MVP

**Goal**: HU1 + HU4 — Dos mapas sincronizados (geodésico/vial) lado a lado.

**Independent Test**: Navegar a `/evaluations/{id}` de EXP-002, activar split view, verificar dos mapas con mismo centro/zoom.

- [ ] T007 [US1] Crear componente `SplitMapView` en `frontend/src/components/SplitMapView.tsx` que renderice dos `MapContainer` en contenedor flex (geodésico izquierdo, vial derecho)
- [ ] T008 [US1] Implementar componente interno `MapSyncController` que use `useMap()` de react-leaflet y suscriba eventos `moveend`/`zoomend`
- [ ] T009 [US1] Implementar sincronización con flag `isSyncing` (ref booleana) para evitar bucle infinito A→B→A→B
- [ ] T010 [P] [US1] Asignar colores consistentes: pasar `routeColorById` compartido a ambos `MapView` internos
- [ ] T011 [P] [US1] Filtrar polilíneas por `visibleRoutes` usando `pl.routeId` antes de pasar a cada `MapView`
- [ ] T012 [P] [US1] Aplicar atenuación `opacity: 0.2` a polilíneas no aisladas cuando `isolatedRoute !== null`
- [ ] T013 [US1] Implementar wrap responsivo: `flex-row` en ≥1024px, `flex-col` en <1024px, cada mapa `w-1/2` o `w-full`
- [ ] T014 [US1] Si `routeLegs` vacío o sin `mode='vial'`, mostrar solo mapa geodésico con mensaje informativo
- [ ] T015 [US1] Crear componente `ViewModeToggle` en `frontend/src/components/ViewModeToggle.tsx` con botones "Vista simple" / "Comparativa" y soporte para `splitAvailable`

**Checkpoint**: SplitView funcional con dos mapas sincronizados, colores consistentes, responsivo, y fallback para EXP-001.

---

## Phase 3: RoutePanel — Listado Interactivo de Rutas (H2)

**Goal**: HU2 — Listado de rutas con toggle on/off que controla visibilidad en ambos mapas.

**Independent Test**: En split view, desactivar una ruta desde RoutePanel y verificar que se oculta en ambos mapas simultáneamente.

- [ ] T016 [P] [US2] Crear componente `RoutePanel` en `frontend/src/components/RoutePanel.tsx` que reciba `routes`, `routeColorById`, `visibleRoutes`, `isolatedRoute` y callbacks
- [ ] T017 [US2] Implementar checkbox/switch por ruta con indicador de color (círculo del color de la ruta) y nombre
- [ ] T018 [US2] Implementar callbacks `onToggleRoute` (agrega/remueve de Set) y propagar estado a SplitMapView
- [ ] T019 [US2] Implementar botones "Seleccionar todas" / "Deseleccionar todas"
- [ ] T020 [US2] Implementar panel colapsable con botón toggle (para no obstruir mapa, RNF2)

**Checkpoint**: RoutePanel permite activar/desactivar rutas. SplitMapView respeta el filtrado en ambos mapas.

---

## Phase 4: RouteIsolation — Aislamiento y Atenuación (H3)

**Goal**: HU3 — Seleccionar una ruta para verla aislada con atenuación visual de las demás.

**Independent Test**: En split view, hacer clic en una ruta del RoutePanel → la ruta seleccionada queda con opacidad normal, las demás con opacity 0.2.

- [ ] T021 [US3] RoutePanel recibe `isolatedRoute` como prop y llama a `onIsolateRoute(routeId | null)` — el estado vive en `evaluations/[id]/page.tsx` (T028), no en RoutePanel
- [ ] T022 [US3] Al hacer clic en el nombre de una ruta en RoutePanel: si la ruta no está aislada, llamar `onIsolateRoute(routeId)`; si ya lo está, llamar `onIsolateRoute(null)`
- [ ] T023 [US3] Agregar indicación visual en RoutePanel para la ruta aislada (fondo highlight, borde izquierdo coloreado, basado en prop `isolatedRoute`)
- [ ] T024 [US3] Implementar botón "Salir de aislamiento" en RoutePanel que llama `onIsolateRoute(null)`
- [ ] T025 [US3] Verificar que SplitMapView lee `pl.opacity` en el pathOptions de Polyline y aplica atenuación

**Checkpoint**: Aislamiento funcional. Una ruta visible con opacidad completa, las demás atenuadas. Restauración funciona.

---

## Phase 5: Integración en Página de Evaluación (H4)

**Goal**: HU5 — Integrar SplitMapView, RoutePanel y ViewModeToggle en `evaluations/[id]/page.tsx` con toggle simple/split que preserva estado.

**Independent Test**: Alternar entre modo simple y split varias veces. El estado de selección de rutas se preserva (CA10). EXP-001 solo muestra modo simple.

- [ ] T026 [US5] Agregar estado `viewMode: 'simple' | 'split'` en `frontend/src/app/evaluations/[id]/page.tsx`
- [ ] T027 [P] [US5] Agregar estado `visibleRoutes: Set<number>` inicializado con todos los routeIds disponibles
- [ ] T028 [P] [US5] Agregar estado `isolatedRoute: number | null` inicializado en `null`
- [ ] T029 [US5] En modo simple: renderizar `MapView` + `RouteModeToggle` (comportamiento SPEC-007 sin cambios)
- [ ] T030 [US5] En modo split: renderizar `SplitMapView` + `ViewModeToggle` + `RoutePanel` (sin RouteModeToggle)
- [ ] T031 [US5] Pasar `visibleRoutes` e `isolatedRoute` a MapView (modo simple) preservando CA10
- [ ] T032 [US5] Si `vialAvailable === false`, deshabilitar botón split con tooltip "No hay datos viales para esta evaluación"
- [ ] T033 [US5] RoutePanel visible en ambos modos (simple y split), estado compartido

**Checkpoint**: Página de evaluación integrada. Toggle simple/split funcional. Estado preservado entre modos.

---

## Phase 6: Evidencia Experimental (H5)

**Goal**: HU6 — Generar mediciones M1–M4, capturas comparativas, actualizar hallazgos y evidence matrix.

**Independent Test**: `assets/mediciones.md` contiene 10 registros (5 simple + 5 split) con tiempos, ruta identificada y acierto.

- [ ] T034 [US6] Ejecutar protocolo M4: 5 mediciones modo simple + 5 modo split (secuencia alternada), registrar en `specs/008-visual-analytics-comparacion/assets/mediciones.md`
- [ ] T035 [US6] Generar capturas: split view, ruta aislada, filtrado activo, vista simple en `specs/008-visual-analytics-comparacion/assets/captures/`
- [ ] T036 [US6] Evaluar HYP-008-01: comparar promedios de tiempo simple vs split, documentar resultado
- [ ] T037 [US6] Evaluar HYP-008-02: reporte cualitativo del observador sobre carga cognitiva con filtrado
- [ ] T038 [US6] Documentar hallazgos de SPEC-008 en `research/hallazgos.md` (H013+)
- [ ] T039 [US6] Actualizar `research/evidence-matrix.md` con validaciones de SPEC-008
- [ ] T040 [US6] Verificar speccheck (trazabilidad entre hipótesis, métricas, hallazgos y evidence matrix)

**Checkpoint**: Evidencia experimental completa. Hallazgos documentados. Trazabilidad verificada.

---

## Phase 7: Trabajo Derivado Posterior (H6 — post-SPEC-008)

**Purpose**: Esbozo de documento-tecnico-v3 y planificación de PUB-003. No forma parte del scope principal.

- [ ] T041 Esbozar sección de análisis visual comparativo en `publications/documentacion/documento-tecnico-v3.md`
- [ ] T042 Planificar PUB-003-visual-comparison: dependerá de hallazgos de SPEC-008, seguir estándar editorial D014

---

## Dependencies & Execution Order

### Phase Dependencies

```
Phase 1 (Setup/H0) ──► Phase 2 (SplitView/H1) ──► Phase 3 (RoutePanel/H2)
                                                          │
                                                          ▼
                                                   Phase 4 (Isolation/H3)
                                                          │
                                                          ▼
                                                   Phase 5 (Integration/H4)
                                                          │
                                                          ▼
                                                   Phase 6 (Evidencia/H5)
                                                          │
                                                          ▼
                                                   Phase 7 (Post/H6)
```

- **Phase 1 (H0)**: Sin dependencias — bloquea todas las fases siguientes
- **Phase 2 (H1)**: Depende de H0 — MVP
- **Phase 3 (H2)**: Depende de H1
- **Phase 4 (H3)**: Depende de H2
- **Phase 5 (H4)**: Depende de H1, H2, H3
- **Phase 6 (H5)**: Depende de H1–H4
- **Phase 7 (H6)**: Posterior — fuera de scope principal

### User Story Mapping

| User Story | Phase | Hito | Dependencias |
|-----------|-------|------|-------------|
| HU1 (comparación) + HU4 (sincronización) | Phase 2 | H1 | H0 |
| HU2 (selección de rutas) | Phase 3 | H2 | H1 |
| HU3 (aislamiento) | Phase 4 | H3 | H2 |
| HU5 (compatibilidad) | Phase 5 | H4 | H1, H2, H3 |
| HU6 (evidencia visual) | Phase 6 | H5 | H1–H4 |

---

## Implementation Strategy

### MVP (Phases 1 + 2)

1. Completar Phase 1: contratos (routeId + opacity en PolylineData)
2. Completar Phase 2: SplitView + sincronización + ViewModeToggle
3. **STOP y VALIDAR**: Evaluar EXP-002 en split view, verificar sincronización
4. Si el MVP es funcional, el instrumento visual base está listo

### Incremental Delivery

1. **MVP**: SplitView funcional (Phase 1 + 2) → permite comparación visual básica
2. **+ RoutePanel**: filtrado de rutas (Phase 3) → análisis selectivo
3. **+ Isolation**: atenuación (Phase 4) → análisis focalizado
4. **+ Integration**: toggle simple/split (Phase 5) → experiencia completa
5. **+ Evidence**: mediciones y hallazgos (Phase 6) → cierre experimental
6. **Posterior**: documento-técnico y PUB-003 (Phase 7)

### Parallel Opportunities

- **Phase 1**: T004 y T005 pueden ejecutarse en paralelo (geodesicPolylines y vialPolylines son independientes)
- **Phase 2**: T010 (colores), T011 (filtrado) y T012 (atenuación) pueden ejecutarse en paralelo
- **Phase 3**: T016 (componente) es independiente del resto
- **Phase 5**: T027 (visibleRoutes) y T028 (isolatedRoute) pueden ejecutarse en paralelo
- **Phase 6**: T034 (mediciones) y T035 (capturas) pueden ejecutarse en paralelo

---

## Notes

- [P] tasks = diferentes archivos, sin dependencias entre sí
- [US*] label = user story correspondiente
- No se requieren tests automatizados; validación visual y experimental
- La validación de HYP-008-01 y HYP-008-02 depende de las mediciones de Phase 6, no del hecho de que los componentes rendericen
- Cualquier publicación derivada (Phase 7) debe pasar checklist editorial D014 antes de marcarse como publicada
