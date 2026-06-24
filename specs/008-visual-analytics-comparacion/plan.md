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
No existen unknowns técnicos relevantes. El stack, los componentes existentes y el modelo de datos están completamente especificados por SPEC-007.

Persisten unknowns de investigación asociados a **PI-016** y **PI-017**, que constituyen el objetivo principal de SPEC-008:
- ¿El split view realmente reduce el tiempo de identificación de divergencias?
- ¿El filtrado de rutas realmente ayuda al análisis visual?
- ¿La atenuación funciona mejor que ocultar rutas no seleccionadas?
- ¿Cuál es el nivel óptimo de detalle visual sin sobrecarga cognitiva?

Estos unknowns no son técnicos — son las preguntas de investigación que SPEC-008 debe responder mediante el instrumento visual.

---

## Constitution Check

### I. Evidencia Antes de Solución
**Cumple**: SPEC-007 ya generó evidencia visual de la divergencia geodésico/vial (H012, capturas lado a lado manuales). SPEC-008 sistematiza el análisis visual que SPEC-007 demostró que era posible. No se introduce optimización sin representación previa del problema.

### II. Decisiones Medibles
**Cumple**: La spec define 4 métricas cuantitativas (M1–M4):
- M1: Tiempo de sincronización entre mapas (<200ms, medible)
- M2: Precisión de sincronización (diferencia de centro/zoom, medible)
- M3: Cobertura de rutas seleccionables (100%, verificable)
- M4: Tiempo de identificación de divergencia — tiempo requerido por un observador para identificar la ruta con mayor diferencia entre distancia geodésica y vial.
  **Procedimiento**:
  - Realizar 5 mediciones con vista simple (toggle SPEC-007)
  - Realizar 5 mediciones con split view (SPEC-008)
  - Registrar tiempo en segundos por medición
  - Comparar promedios entre modos
  - Criterio de éxito: split view debe reducir el tiempo promedio respecto a vista simple

### III. Complejidad Incremental
**Cumple**: SPEC-008 extiende SPEC-007 sin modificar modelos subyacentes (no toca backend, no modifica DistanceService, MeasurementService ni OSRM). Todos los cambios son frontend. No introduce nuevas rutas API ni tablas.

### IV. Modelado de Escenarios Reales
**Cumple**: Las rutas visualizadas corresponden a entregas reales de Valparaíso (EXP-002). El split view permite inspeccionar el impacto de la red vial real sobre la operación, no una abstracción teórica.

### V. Optimizaciones Comparables
**Cumple**: El split view es inherentemente comparativo: mapa geodésico a la izquierda, mapa vial a la derecha, sincronizados. Permite al analista contrastar directamente ambos modelos de distancia sin alternancia manual.

### VI. Visualización como Análisis
**Cumple**: Es el principio central de SPEC-008. El split view, el filtrado de rutas y el aislamiento individual convierten el mapa en una herramienta analítica, no solo decorativa. PI-016 y PI-017 guían explícitamente esta dimensión.

### VII. Conocimiento Reutilizable
**Cumple**: SPEC-008 genera evidencia experimental que alimentará documento-tecnico-v3 y eventualmente PUB-003 (ambos post-SPEC-008, con estándar editorial D014). Las capturas comparativas, mediciones M1–M4 y hallazgos son reutilizables en documentación técnica y portafolio.

### VIII. Docker First
**Cumple**: Los cambios son exclusivamente frontend (TypeScript/React). No se modifican servicios Docker, ni se requiere reconstruir contenedores. El entorno de desarrollo existente (`docker-compose up`) sigue funcionando sin cambios.

### Resultado de Gates
- **Violaciones**: 0
- **Advertencias**: 0
- **Estado**: APROBADO — la implementación es conforme a los 8 principios constitucionales.

---

## Decisiones de diseño experimental

### D015 — Split view, filtrado y aislamiento como intervenciones experimentales

**Decisión**: Split view, filtrado de rutas y aislamiento se consideran **intervenciones experimentales** para evaluar PI-016 y PI-017, no decisiones definitivas de producto.

**Impacto**:
- Los hallazgos de SPEC-008 pueden validar, modificar o rechazar estas visualizaciones.
- El éxito de la implementación técnica (SplitMapView funciona, se sincroniza) **no implica la validación de la hipótesis**.
- La validación de HYP-008-01 y HYP-008-02 depende de las mediciones M4 y la evaluación cualitativa, no del hecho de que el componente renderice correctamente.

**Relación con publicaciones**: Cualquier publicación derivada (documento-tecnico-v3, PUB-003) debe explicitar que estas visualizaciones son instrumentos experimentales, no necesariamente la solución final, y reportar si fueron validadas o refutadas por la evidencia.

---

## Contexto científico

SPEC-008 no es una mejora de UI. Es un **instrumento de investigación visual** para responder:

- **PI-016**: ¿Cómo influye la visualización selectiva y comparativa de rutas en la capacidad de interpretar diferencias entre métricas geodésicas y viales?
- **PI-017**: ¿Qué nivel de detalle visual es necesario para comunicar eficazmente diferencias operacionales entre modelos geodésicos y viales sin introducir sobrecarga cognitiva?

**Hallazgo habilitador (H012)**: +54.3% vial sobre geodésico (339→523 km). La evidencia visual de SPEC-007 (capturas lado a lado manuales) demostró que la divergencia es comunicable visualmente. SPEC-008 sistematiza ese proceso.

---

## Hipótesis

### HYP-008-01 — Reducción de tiempo de identificación

El modo SplitView reduce el tiempo necesario para identificar divergencias operacionales entre modelos geodésicos y viales respecto al modo de alternancia simple (toggle SPEC-007).

**Métrica**: M4 (tiempo de identificación de divergencia, 5+5 mediciones).
**Criterio**: El promedio en modo split debe ser menor que en modo simple.

### HYP-008-02 — Reducción de carga cognitiva

La visualización selectiva por ruta (RoutePanel + aislamiento) disminuye la carga cognitiva percibida durante el análisis de diferencias entre modelos de distancia, en comparación con la visualización simultánea de todas las rutas.

**Métrica**: Evaluación cualitativa del observador (cuestionario post-prueba).
**Criterio**: El observador reporta menor esfuerzo para identificar patrones usando filtrado de rutas.

---

## Diseño Experimental

### Tipo de evaluación

Esta fase utiliza **evaluación exploratoria por observador único** (investigador principal). No se requiere reclutamiento de participantes externos. Los resultados son indicativos y servirán para formular estudios controlados en fases posteriores.

### Protocolo M4 (tiempo de identificación de divergencia)

**Estímulo**: Mapa de evaluación EXP-002 con 5 rutas (A–E) en Valparaíso, mostrando polilíneas geodésicas en un modo y viales en el otro.

**Tarea del observador**: "Identifique la ruta con mayor diferencia entre distancia geodésica y distancia vial."

**Secuencia**:
1. El observador recibe la tarea por escrito.
2. El cronómetro inicia cuando el observador hace clic en "Iniciar".
3. El cronómetro se detiene cuando el observador dice verbalmente "Ruta [letra]".
4. Se registra: ruta identificada, tiempo en segundos, acierto/error.

**Mediciones**:
- 5 intentos con vista simple (toggle SPEC-007)
- 5 intentos con split view (SPEC-008)
- Los intentos se alternan para evitar efecto de aprendizaje:
  - Secuencia: simple, split, simple, split, simple, split, simple, split, simple, split
- La ruta de mayor divergencia (Ruta D, factor 2.00×) se mantiene constante en todos los intentos.

**Criterio de éxito**: El promedio de tiempo en modo split debe ser menor que en modo simple (HYP-008-01).

---

## Estado de hitos (SPEC-008 completada)

| Hito | Descripción | Estado |
|------|-------------|--------|
| H0 | Preparación de contratos: routeId en PolylineData, limpieza de tipos | ✅ Completado |
| H1 | SplitView: dos mapas sincronizados (geodésico/vial) | ✅ Completado |
| H2 | RoutePanel: listado interactivo de rutas con toggle on/off | ✅ Completado |
| H3 | RouteIsolation: selección individual + atenuación de rutas | ✅ Completado |
| H4 | Integration: toggle modo simple/split sin recarga | ✅ Completado |
| H5 | Evidencia experimental: hallazgos H014–H017, evidence matrix actualizada, M4 documentado como exploratorio (D016) | ✅ Completado |
| H6 | Trabajo derivado posterior: documento-tecnico-v3 (esbozo), PUB-003 (posterior) | Pendiente — post-SPEC-008 |

---

## Tareas

### H0 — Preparación de contratos (routeId en PolylineData)

**Objetivo**: Habilitar el filtrado de rutas agregando `routeId` a `PolylineData` y elevando el tipo al ámbito compartido.

**Problema identificado**: `PolylineData` (definido actualmente como tipo local en `MapView.tsx` y `evaluations/[id]/page.tsx`) no incluye `routeId`. Sin este campo, el filtrado por `visibleRoutes` no puede identificar qué polilínea pertenece a qué ruta.

| # | Tarea | Archivo(s) | Aceptación |
|---|-------|-----------|------------|
| 0.1 | Agregar `routeId` a la interfaz `PolylineData` en ambos sitios donde está definida | `frontend/src/components/MapView.tsx`, `frontend/src/app/evaluations/[id]/page.tsx` | `PolylineData` incluye `routeId: number` |
| 0.2 | Propagar `routeId` en la construcción de `geodesicPolylines` (evaluations page) y `vialPolylines` (MapView) | `frontend/src/app/evaluations/[id]/page.tsx`, `frontend/src/components/MapView.tsx` | Cada PolylineData construida incluye su `routeId` |
| 0.3 | Extraer `PolylineData` a tipo compartido (opcional, si hay duplicación) | `frontend/src/lib/api.ts` o tipos locales | Sin duplicación de tipo |
| 0.4 | Agregar `opacity?: number` a `PolylineData` para soportar atenuación en aislamiento | `frontend/src/components/MapView.tsx` | `PolylineData` soporta `opacity` |

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
| 2.6 | SplitMapView filtra las polilíneas (geodésicas y viales) usando `pl.routeId` contra `visibleRoutes` antes de pasarlas a MapView | `frontend/src/components/SplitMapView.tsx` | RF3: desactivar ruta la oculta en ambos mapas simultáneamente |

### H3 — RouteIsolation (selección individual + atenuación)

**Objetivo**: Seleccionar una ruta para verla aislada con atenuación visual de las demás.

| # | Tarea | Archivo(s) | Aceptación |
|---|-------|-----------|------------|
| 3.1 | Estado `isolatedRoute: number | null` en RoutePanel | `frontend/src/components/RoutePanel.tsx` | CA4: seleccionar ruta la aísla |
| 3.2 | Al hacer clic en una ruta, establecer `isolatedRoute` y desactivar las demás no aisladas | `frontend/src/components/RoutePanel.tsx` | Una ruta visible, las demás atenuadas |
| 3.3 | Las rutas no aisladas reciben `opacity: 0.2` (usando `pl.opacity`) en lugar de ocultarse (RF10) | `frontend/src/components/SplitMapView.tsx` + MapView lee `pathOptions.opacity` de cada PolylineData | RF10: atenuación, no ocultación |
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

### H5 — Generación de evidencia experimental

| # | Tarea | Archivo(s) | Aceptación |
|---|-------|-----------|------------|
| 5.1 | Ejecutar mediciones M1–M4 con el instrumento construido (5 mediciones por modo para M4) | `specs/008-visual-analytics-comparacion/assets/mediciones.md` | Datos crudos registrados |
| 5.2 | Generar capturas comparativas: split view, ruta aislada, filtrado activo | `specs/008-visual-analytics-comparacion/assets/captures/` | Al menos 4 capturas representativas |
| 5.3 | Documentar hallazgos de SPEC-008 en `research/hallazgos.md` (H013 en adelante) | `research/hallazgos.md` | Al menos un hallazgo por hipótesis evaluada |
| 5.4 | Actualizar `research/evidence-matrix.md` con validaciones de SPEC-008 | `research/evidence-matrix.md` | Trazabilidad entre hipótesis, métricas y resultados |

### H6 — Trabajo derivado posterior (post-SPEC-008)

PUB-003 queda fuera del alcance de SPEC-008. La publicación depende de los hallazgos que SPEC-008 produzca y se planificará después.

| # | Tarea | Archivo(s) | Aceptación |
|---|-------|-----------|------------|
| 6.1 | documento-tecnico-v3: esbozo de sección de análisis visual (posterior a hallazgos) | `publications/documentacion/documento-tecnico-v3.md` | Sección basada en evidencia de SPEC-008 |
| 6.2 | PUB-003-visual-comparison/: planificación separada, dependiente de hallazgos de SPEC-008 | *Pendiente* | Seguirá estándar editorial PUB-001 (D014) cuando se active |

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
4. **H4 después** (Integration): una vez que SplitMapView + RoutePanel funcionan de forma independiente, integrarlos en la página.
5. **H5 al final** (Evidencia): una vez que el instrumento visual funciona, usarlo para generar las mediciones experimentales.

---

## Validación

### Técnica
1. `npm run build` — Sin errores de compilación ✅
2. `npm run lint` — Sin errores de lint ✅

### Funcional (ejecutar en EXP-002)
3. SplitView: dos mapas sincronizados, <200ms de retardo ✅
4. RoutePanel: toggle on/off oculta rutas en ambos mapas ✅
5. Aislamiento: atenuación opacity 0.2 en rutas no aisladas ✅
6. Toggle simple/split: preserva estado visibleRoutes e isolatedRoute (CA10) ✅
7. EXP-001: split view deshabilitado, mensaje claro ✅

### Experimental (H5) — Ver D016
8. M4 no se ejecutó como medición cuantitativa formal por limitaciones metodológicas (n=1, respuesta conocida, efecto aprendizaje). Ver `research/decisiones.md` (D016).
9. Mediciones documentadas como exploratorias en `assets/mediciones.md` ✅
10. HYP-008-01: **No evaluada cuantitativamente**. La hipótesis sigue abierta para estudios controlados futuros.
11. HYP-008-02: **Respondida parcialmente** por H016. El observador reporta que los controles no aumentan la carga cognitiva.

### Editorial (D014)
Cualquier publicación derivada (documento-tecnico-v3, PUB-003) debe pasar el checklist editorial antes de marcarse como publicada. Ver `publications/PUB-001-geodesic-baseline/` como referencia de formato y profundidad.

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

### Componentes (H1–H4)
- `frontend/src/components/SplitMapView.tsx` — SplitView con sincronización
- `frontend/src/components/RoutePanel.tsx` — Panel de rutas con toggle y aislamiento
- `frontend/src/components/ViewModeToggle.tsx` — Toggle simple/split
- `frontend/src/app/evaluations/[id]/page.tsx` — Integración (modificado)
- `frontend/src/components/MapView.tsx` — Sin cambios (reutilizado)
- `frontend/src/components/RouteModeToggle.tsx` — Sin cambios (reutilizado)

### Evidencia (H5)
- `specs/008-visual-analytics-comparacion/assets/mediciones.md` — Mediciones M1–M4
- `specs/008-visual-analytics-comparacion/assets/captures/` — Capturas comparativas
- `research/hallazgos.md` — Hallazgos actualizados (H013+)
- `research/evidence-matrix.md` — Matriz actualizada

---

## Referencias

- PI-016, PI-017: `research/preguntas-investigacion.md`
- H012: `research/hallazgos.md`
- D014: `research/decisiones.md`
- HYP-008-01, HYP-008-02: sección de hipótesis en este plan
- SPEC-007 contracts: `specs/007-road-network-visualization/contracts/`
- Estándar editorial D014: `publications/PUB-001-geodesic-baseline/`
- Checklist editorial D014: verificar contra PUB-001 antes de publicar cualquier derivado (documento-tecnico-v3, PUB-003)
