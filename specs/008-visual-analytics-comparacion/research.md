# Research — SPEC-008: Visual Analytics para Comparación de Rutas

**Estado**: Consolidado — sin unknowns pendientes.

## Decisiones Técnicas

### 1. SplitView con dos instancias de MapContainer
- **Decisión**: Instanciar dos `MapContainer` de react-leaflet en un contenedor flex, cada uno con su propio `TileLayer` y polilíneas.
- **Rationale**: `MapView` es un componente puro que recibe `mode` y datos como props. Reutilizarlo con `mode='geodesic'` fijo (izquierdo) y `mode='vial'` fijo (derecho) evita duplicar lógica de renderizado de polilíneas, markers y tooltips.
- **Alternativas consideradas**: 
  - Un solo `MapContainer` con dos capas superpuestas → descartado porque la superposición visual geodésico+vial crea confusión y no permite comparar trazados.
  - Un `MapContainer` con panel deslizante (swipe) → descartado por complejidad adicional sin beneficio claro sobre side-by-side.

### 2. Sincronización con flag `isSyncing`
- **Decisión**: Usar una ref booleana `isSyncing` que se activa al recibir un evento de sincronización y se desactiva después de `setView`. El event handler del otro mapa ignora eventos mientras `isSyncing` está activo.
- **Rationale**: Patrón probado en múltiples implementaciones Leaflet split-view. El bucle infinito (A→B→A→B) es el riesgo principal.
- **Alternativas consideradas**: 
  - Debouncing con `setTimeout` → menos preciso, introduce latencia artificial.
  - Comparación de centro/zoom antes de sincronizar → más costoso computacionalmente y no resuelve el loop.

### 3. Estado compartido en page component
- **Decisión**: `visibleRoutes: Set<number>` e `isolatedRoute: number | null` mantenidos en `evaluations/[id]/page.tsx`, pasados como props a `SplitMapView` y `MapView` (modo simple).
- **Rationale**: El estado debe preservarse al alternar entre modo simple y split (CA10). Mantenerlo en el ancestro común garantiza que ambas vistas compartan el mismo estado.
- **Alternativas consideradas**: 
  - Context API → sobreingeniería para dos niveles de profundidad.
  - Estado local en SplitMapView + elevación → rompe CA10.

### 4. Consistent colors entre mapas
- **Decisión**: `routeColorById` (ya calculado en page component) se pasa como prop idéntico a ambos `MapView` internos.
- **Rationale**: Garantiza CA5 sin modificar `MapView`. Los colores se asignan por orden en `routeMetrics`, no por `route_id`, y son consistentes entre modos porque el orden no cambia.
- **Alternativas consideradas**: N/A — heredado de SPEC-007.

### 5. Atenuación vs ocultación en aislamiento
- **Decisión**: Las rutas no aisladas reciben `opacity: 0.2` (RF10) en lugar de desaparecer completamente.
- **Rationale**: Mantiene el contexto geográfico sin distraer. El analista puede ver dónde están las demás rutas pero el foco visual está en la ruta aislada.
- **Alternativas consideradas**: Ocultación completa → pierde contexto geográfico.

### 6. Sin nuevas dependencias npm
- **Decisión**: No agregar nuevos paquetes. `react-leaflet` ya incluye todo lo necesario.
- **Rationale**: Los eventos `moveend`/`zoomend` son nativos de Leaflet y accesibles desde los componentes react-leaflet mediante `useMap()` o refs.
- **Alternativas consideradas**: N/A.

## Riesgos validados

### Sincronización Loop
- **Mitigación**: Flag `isSyncing` probado en implementaciones existentes.
- **Validación adicional**: Prueba manual con 10 iteraciones de zoom/pan rápidos.

### Rendimiento con 15+ rutas
- **Mitigación**: La cantidad actual de rutas por evaluación es ≤5 (EXP-002). El límite superior de diseño es 15 (RNF4).
- **Validación**: Probar con dataset sintético de 15 rutas durante QA.

### Estado perdido al alternar modo
- **Mitigación**: CA10 en plan de pruebas manuales. Verificar que `visibleRoutes` e `isolatedRoute` se preservan.

## Hallazgos técnicos

### FH01 — Leaflet refs en react-leaflet
react-leaflet proporciona `useMap()` dentro de componentes hijos de `MapContainer`, pero no expone directamente el evento `moveend` desde el componente `MapContainer`. La sincronización requiere un componente interno que use `useMap()` y suscriba `map.on('moveend', handler)`.

**Solución**: Crear un componente `MapSyncController` (interno a SplitMapView) que se coloque dentro de cada `MapContainer`, use `useMap()` y emita eventos de sincronización al hermano.

### FH02 — Sin duplicación de datos
Ambos mapas en SplitView reciben el mismo `polylines`, `routeLegs`, `routeColorById`, `routeNameById`. No se necesita duplicar llamadas API (RNF5).

### FH03 — Compatibilidad con EXP-001
Las evaluaciones que no tienen datos viales (EXP-001 geodésico) no muestran el botón de split view. `vialAvailable` es `false`, y el botón split se deshabilita. Esta lógica ya existe para `RouteModeToggle` y se reutiliza.

## Conclusiones

No se requieren cambios en backend, API, modelos, base de datos ni infraestructura Docker. Todos los cambios son frontend TypeScript/React. Ninguna decisión arquitectónica requiere prototipado previo — las técnicas son estándar en el ecosistema Leaflet.
