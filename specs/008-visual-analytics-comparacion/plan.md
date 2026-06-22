# Plan de Implementación — SPEC-008: Visual Analytics para Comparación de Rutas

**Branch**: `008-visual-analytics-comparacion`
**Spec**: `specs/008-visual-analytics-comparacion/spec.md`
**Checklist**: `specs/008-visual-analytics-comparacion/checklists/requirements.md`
**Created**: 2026-06-22

## Technical Context

### Stack
| Capa | Tecnología | Versión |
|------|-----------|---------|
| Frontend | NextJS (App Router) + TypeScript | 14 |
| Mapas | Leaflet + react-leaflet | 1.9.x / 4.x |
| UI | TailwindCSS | (configuración del proyecto) |
| API | Laravel (backend Docker) | 12 |
| Datos | PostgreSQL 16 + evaluation.json | — |

### Arquitectura actual (SPEC-007)
```
evaluations/[id]/page.tsx
├── RouteModeToggle          (geodésico ↔ vial, simple toggle)
└── MapView                  (único mapa, recibe mode prop)
    ├── TileLayer (OSM)
    ├── Polyline[]           (rutas activas: geodésicas o viales)
    ├── Marker (bodega)
    └── Marker[]             (paquetes — vacío en vista evaluación)
```

### Flujo de datos
1. `GET /api/evaluations/{id}` → `EvaluationResource` → `route_legs[]`, `route_metrics[]`
2. `route_legs` contiene `mode: 'geodesic' | 'vial'` y `geometry: [lat,lng][] | null`
3. `geodesicPolylines` se construyen a partir de `from_lat/from_lng/to_lat/to_lng`
4. `vialPolylines` se construyen a partir de `geometry` concatenado por `route_id`
5. `activePolylines = mode === 'vial' ? vialPolylines : polylines`
6. `routeColorById` y `routeNameById` se derivan del índice en `routeMetrics`

### API (`RouteLeg`)
```typescript
interface RouteLeg {
  route_id: number;
  from_delivery_id: number | null;
  to_delivery_id: number | null;
  from_lat: number;
  from_lng: number;
  to_lat: number;
  to_lng: number;
  distance_km: number;
  duration_min: number | null;
  geometry: [number, number][] | null;  // [[lat, lng], ...] — polyline points
  mode: 'geodesic' | 'vial';
}
```

### Dependencias externas
- Leaflet (npm: `leaflet`, `react-leaflet`, `@types/leaflet`)
- OSM tiles (CDN, sin API key)
- No se requieren nuevas dependencias npm

### Integraciones
- **SplitMapView** → instancia dos `MapContainer` de react-leaflet
- **Sincronización** → eventos `moveend`/`zoomend` de Leaflet, con flag `isSyncing`
- **RoutePanel** → componente React puro, sin dependencias de mapa

### Desconocidos
No hay desconocidos técnicos. El stack, los componentes existentes y el modelo de datos están completamente especificados por SPEC-007 y explorados durante la fase de análisis.

---

## Constitution Check

### I. Evidencia Antes de Solución
**Cumple**: SPEC-007 ya generó evidencia visual de la divergencia geodésico/vial (H012, capturas lado a lado manuales). SPEC-008 sistematiza el análisis visual que SPEC-007 demostró que era posible. No se introduce optimización sin representación previa del problema.

### II. Decisiones Medibles
**Cumple**: La spec define 4 métricas cuantitativas (M1–M4):
- M1: Tiempo de sincronización entre mapas (<200ms, medible)
- M2: Precisión de sincronización (diferencia de centro/zoom, medible)
- M3: Cobertura de rutas seleccionables (100%, verificable)
- M4: Tiempo de identificación de divergencia (comparable SPEC-007 vs SPEC-008)

### III. Complejidad Incremental
**Cumple**: SPEC-008 extiende SPEC-007 sin modificar modelos subyacentes (no toca backend, no modifica DistanceService, MeasurementService ni OSRM). Todos los cambios son frontend. No introduce nuevas rutas API ni tablas.

### IV. Modelado de Escenarios Reales
**Cumple**: Las rutas visualizadas corresponden a entregas reales de Valparaíso (EXP-002). El split view permite inspeccionar el impacto de la red vial real sobre la operación, no una abstracción teórica.

### V. Optimizaciones Comparables
**Cumple**: El split view es inherentemente comparativo: mapa geodésico a la izquierda, mapa vial a la derecha, sincronizados. Permite al analista contrastar directamente ambos modelos de distancia sin alternancia manual.

### VI. Visualización como Análisis
**Cumple**: Es el principio central de SPEC-008. El split view, el filtrado de rutas y el aislamiento individual convierten el mapa en una herramienta analítica, no solo decorativa. PI-016 y PI-017 guían explícitamente esta dimensión.

### VII. Conocimiento Reutilizable
**Cumple**: SPEC-008 alimenta documento-tecnico-v3 y PUB-003 (publicación derivada con estándar editorial D014). La evidencia visual generada (capturas de split view, aislamiento, filtrado) es reutilizable en documentación técnica y portafolio.

### VIII. Docker First
**Cumple**: Los cambios son exclusivamente frontend (TypeScript/React). No se modifican servicios Docker, ni se requiere reconstruir contenedores. El entorno de desarrollo existente (`docker-compose up`) sigue funcionando sin cambios.

### Resultado de Gates
- **Violaciones**: 0
- **Advertencias**: 0
- **Estado**: APROBADO — la implementación es conforme a los 8 principios constitucionales.

---

## Contexto científico

SPEC-008 no es una mejora de UI. Es un **instrumento de investigación visual** para responder:

- **PI-016**: ¿Cómo influye la visualización selectiva y comparativa de rutas en la capacidad de interpretar diferencias entre métricas geodésicas y viales?
- **PI-017**: ¿Qué nivel de detalle visual es necesario para comunicar eficazmente diferencias operacionales entre modelos geodésicos y viales sin introducir sobrecarga cognitiva?

**Hallazgo habilitador (H012)**: +54.3% vial sobre geodésico (339→523 km). La evidencia visual de SPEC-007 (capturas lado a lado manuales) demostró que la divergencia es comunicable visualmente. SPEC-008 sistematiza ese proceso.

---

## Hitos

| Hito | Descripción | Dependencias |
|------|-------------|-------------|
| H1 | SplitView: dos mapas sincronizados (geodésico/vial) | Ninguna |
| H2 | RoutePanel: listado interactivo de rutas con toggle on/off | H1 |
| H3 | RouteIsolation: selección individual + atenuación de rutas | H2 |
| H4 | Integration: toggle modo simple/split sin recarga | H1, H2, H3 |
| H5 | Documentation: plan.md + assets + checklist | Ninguna |
| H6 | Publication: documento-tecnico-v3 + PUB-003 | H1–H4 |

---

## Tareas

### H1 — SplitView (dos mapas sincronizados)

**Objetivo**: Mostrar dos instancias de MapView sincronizadas (geodésico a la izquierda, vial a la derecha).

| # | Tarea | Archivo(s) | Aceptación |
|---|-------|-----------|------------|
| 1.1 | Crear componente `SplitMapView` que reciba los mismos datos que MapView pero renderice dos instancias | `frontend/src/components/SplitMapView.tsx` | Renderiza dos mapas con idéntico centro/zoom |
| 1.2 | Implementar sincronización de eventos: `moveend`, `zoomend` de un mapa → `setView` en el otro | `frontend/src/components/SplitMapView.tsx` | CA2: <200ms de retardo entre mapas |
| 1.3 | Evitar bucle infinito (evento A→B→A→B...) con flag de sincronización en curso | `frontend/src/components/SplitMapView.tsx` | No hay loop infinito |
| 1.4 | Cada mapa usa su propio modo: fijo `geodesic` en izquierdo, fijo `vial` en derecho | `frontend/src/components/SplitMapView.tsx` | RF1: mapa izquierdo = geodésico, derecho = vial |
| 1.5 | Asignar colores consistentes: `routeColorById` compartido entre ambos mapas | `frontend/src/components/SplitMapView.tsx` | CA5: colores idénticos en ambos mapas |
| 1.6 | Si vial no está disponible (EXP-001), SplitView se oculta o muestra solo un mapa | `frontend/src/components/SplitMapView.tsx` | CA6: EXP-001 sin error |
| 1.7 | Wrap responsivo: reducir tamaño de cada mapa al 50% del contenedor | `frontend/src/components/SplitMapView.tsx` | Split visible en viewports ≥1024px |

### H2 — RoutePanel (listado interactivo de rutas)

**Objetivo**: Listado de rutas con toggle on/off que controla la visibilidad en ambos mapas.

| # | Tarea | Archivo(s) | Aceptación |
|---|-------|-----------|------------|
| 2.1 | Crear componente `RoutePanel` que reciba `routeColorById` y `routeNameById` | `frontend/src/components/RoutePanel.tsx` | CA3: desactivar ruta la oculta en ambos mapas |
| 2.2 | Estado `visibleRoutes: Set<number>` que controle qué rutas se muestran | `frontend/src/components/RoutePanel.tsx` | Estado compartido con SplitMapView |
| 2.3 | Checkbox o switch por ruta con el color asignado | `frontend/src/components/RoutePanel.tsx` | RF9: identificar ruta + estado activo |
| 2.4 | Botón "Seleccionar todas" / "Deseleccionar todas" | `frontend/src/components/RoutePanel.tsx` | Bulk toggle |
| 2.5 | Panel colapsable para no obstruir el mapa | `frontend/src/components/RoutePanel.tsx` | RNF2: colapsable |
| 2.6 | SplitMapView filtra `activePolylines` por `visibleRoutes` antes de renderizar | `frontend/src/components/SplitMapView.tsx` | RF3: desactivar ruta la oculta |

### H3 — RouteIsolation (selección individual + atenuación)

**Objetivo**: Seleccionar una ruta para verla aislada con atenuación visual de las demás.

| # | Tarea | Archivo(s) | Aceptación |
|---|-------|-----------|------------|
| 3.1 | Estado `isolatedRoute: number | null` en RoutePanel | `frontend/src/components/RoutePanel.tsx` | CA4: seleccionar ruta la aísla |
| 3.2 | Al hacer clic en una ruta, establecer `isolatedRoute` y desactivar las demás no aisladas | `frontend/src/components/RoutePanel.tsx` | Una ruta visible, las demás atenuadas |
| 3.3 | Las rutas no aisladas reciben `opacity: 0.2` en lugar de ocultarse (RF10) | `frontend/src/components/SplitMapView.tsx` | RF10: atenuación, no ocultación |
| 3.4 | Botón "Salir de aislamiento" o clic en la ruta aislada para restaurar todas | `frontend/src/components/RoutePanel.tsx` | Restaura visibilidad completa |
| 3.5 | El estado de aislamiento se preserva al alternar entre split y modo simple | — | CA10: estado preservado |

### H4 — Integration (modo simple/split + RoutePanel en evaluación)

**Objetivo**: Integrar SplitMapView y RoutePanel en la página de detalle de evaluación, con toggle entre modo simple y split.

| # | Tarea | Archivo(s) | Aceptación |
|---|-------|-----------|------------|
| 4.1 | Agregar estado `viewMode: 'simple' | 'split'` en `evaluations/[id]/page.tsx` | `frontend/src/app/evaluations/[id]/page.tsx` | CA8: alternar sin recarga |
| 4.2 | Botón de toggle "Vista simple" / "Vista comparativa" que cambie `viewMode` | `frontend/src/app/evaluations/[id]/page.tsx` | Alterna entre MapView y SplitMapView |
| 4.3 | En modo simple: renderizar `MapView` + `RouteModeToggle` (comportamiento SPEC-007) | `frontend/src/app/evaluations/[id]/page.tsx` | CA7: toggle SPEC-007 funciona |
| 4.4 | En modo split: renderizar `SplitMapView` + `RoutePanel` (sin RouteModeToggle) | `frontend/src/app/evaluations/[id]/page.tsx` | RF1, CA1 funcionales |
| 4.5 | `RoutePanel` visible en modo split (colapsable); puede estar presente también en modo simple | `frontend/src/app/evaluations/[id]/page.tsx` | Panel disponible en ambos modos |
| 4.6 | Estado de selección de rutas visible/aislada se preserva al cambiar entre modos | `frontend/src/app/evaluations/[id]/page.tsx` | CA10 |
| 4.7 | Si `vialAvailable === false`, el botón split se deshabilita con tooltip explicativo | `frontend/src/app/evaluations/[id]/page.tsx` | Mensaje claro para EXP-001 |
| 4.8 | Agregar indicador visual de qué modo está activo (geodésico/vial o split) | `frontend/src/app/evaluations/[id]/page.tsx` | Claridad UX |

### H5 — Documentation

| # | Tarea | Archivo(s) | Aceptación |
|---|-------|-----------|------------|
| 5.1 | Documentar hallazgos de SPEC-008 en `research/hallazgos.md` (H013 en adelante) | `research/hallazgos.md` | Hallazgos de la implementación |
| 5.2 | Actualizar `research/evidence-matrix.md` con validaciones de SPEC-008 | `research/evidence-matrix.md` | Trazabilidad completa |
| 5.3 | Generar assets visuales para documentación (capturas de split view, aislamiento) | `specs/008-visual-analytics-comparacion/assets/` | Material para PUB-003 |

### H6 — Publication (PUB-003)

| # | Tarea | Archivo(s) | Aceptación |
|---|-------|-----------|------------|
| 6.1 | documento-tecnico-v3: nueva sección de análisis visual comparativo | `publications/documentacion/documento-tecnico-v3.md` | Sección basada en evidencia de SPEC-008 |
| 6.2 | PUB-003-visual-comparison/: estructura de publicación completa | `publications/PUB-003-visual-comparison/` | Sigue estándar editorial PUB-001 (D014) |
| 6.3 | Assets visuales de split view, filtraje y aislamiento para PUB-003 | `publications/PUB-003-visual-comparison/assets/` | Capturas reproducibles |

---

## Arquitectura de Componentes

```
evaluations/[id]/page.tsx
├── RouteModeToggle            (modo simple — heredado SPEC-007)
├── [nuevo] ViewModeToggle     (simple ↔ split)
├── MapView                    (modo simple — heredado SPEC-007)
└── [nuevo] SplitMapView       (modo split)
    ├── MapView (geodesic)     (izquierdo, siempre modo='geodesic')
    ├── MapView (vial)         (derecho, siempre modo='vial')
    └── [nuevo] RoutePanel     (listado + toggle + aislamiento)
```

### Estado compartido

```
┌─────────────────────────────────────────────┐
│  evaluations/[id]/page.tsx                   │
│                                             │
│  viewMode: 'simple' | 'split'               │
│  visibleRoutes: Set<number>                 │
│  isolatedRoute: number | null               │
│                                             │
│  ↓ pasa props a ambos modos                 │
└─────────────────────────────────────────────┘
```

**Flujo**: El estado de selección de rutas se mantiene en el page component y se pasa como props tanto a `MapView` (modo simple) como a `SplitMapView` (modo split). Esto garantiza CA10 (preservación del estado al alternar modos).

---

## Estrategia de implementación

1. **H1 primero** (SplitMapView): es la pieza central y más riesgosa (sincronización de mapas). Validar con cualquier evaluación que tenga datos viales (EXP-002).
2. **H2 después** (RoutePanel): construye sobre SplitMapView. El panel no requiere sincronización, solo pasar el Set<number> como prop.
3. **H3 después** (RouteIsolation): lógica de atenuación en SplitMapView + interacción en RoutePanel.
4. **H4 al final** (Integration): una vez que SplitMapView + RoutePanel funcionan de forma independiente, integrarlos en la página.

---

## Validación

Ejecutar en orden:

1. `npm run build` — Sin errores de compilación
2. Verificación visual manual (cargar EXP-002 en evaluación):
   - SplitView muestra dos mapas sincronizados
   - RoutePanel permite ocultar/mostrar rutas
   - Aislamiento atenúa rutas no seleccionadas
   - Toggle simple/split preserva estado
3. `npm run lint` — Sin errores de lint

---

## Riesgos y mitigaciones

| Riesgo | Impacto | Mitigación |
|--------|---------|------------|
| Sincronización de mapas crea bucle infinito | Alto | Flag `isSyncing` en el event handler |
| SplitView duplica llamadas API | Medio | RNF5: un solo juego de datos, dos instancias |
| Rendimiento con 15+ rutas | Medio | RNF4: probar con dataset máximo antes de merge |
| Panel colapsable no es responsive | Bajo | Probar en 1024px y 768px |
| Vial no disponible (EXP-001) bloquea split | Bajo | Split deshabilitado con mensaje claro |
| Estado de selección se pierde al alternar modo | Medio | CA10 explícito en pruebas manuales |

---

## Entregables

- `frontend/src/components/SplitMapView.tsx` — SplitView con sincronización
- `frontend/src/components/RoutePanel.tsx` — Panel de rutas
- `frontend/src/app/evaluations/[id]/page.tsx` — Integración (modificado)
- `frontend/src/components/MapView.tsx` — Sin cambios (reutilizado)
- `frontend/src/components/RouteModeToggle.tsx` — Sin cambios (reutilizado)
- `specs/008-visual-analytics-comparacion/plan.md` — Este plan
- `publications/PUB-003-visual-comparison/` — Publicación derivada (H6)

---

## Referencias

- PI-016: `research/preguntas-investigacion.md`
- PI-017: `research/preguntas-investigacion.md`
- H012: `research/hallazgos.md`
- D014: `research/decisiones.md`
- SPEC-007 contracts: `specs/007-road-network-visualization/contracts/`
- Estándar editorial: `publications/PUB-001-geodesic-baseline/` (D014)
