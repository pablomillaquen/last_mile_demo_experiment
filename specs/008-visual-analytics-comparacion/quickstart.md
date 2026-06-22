# Quickstart — Validación SPEC-008

## Prerequisitos

- Docker Compose ejecutándose (`docker compose up -d`)
- Evaluación EXP-002 con datos viales (al menos una evaluación con `route_legs[].mode === 'vial'`)
- Evaluación EXP-001 sin datos viales (para probar fallback)
- Navegador con viewport ≥1024px para split view

## Setup

```bash
# Verificar que el frontend compila
cd frontend && npm run build
```

## Escenarios de validación

### Escenario 1: SplitView básico
1. Navegar a `/evaluations/{id}` de EXP-002
2. Hacer clic en "Comparativa" (ViewModeToggle)
3. **Esperado**: Dos mapas sincronizados lado a lado
   - Mapa izquierdo: polilíneas geodésicas
   - Mapa derecho: polilíneas viales
   - Ambos con mismo centro y zoom
4. Hacer zoom/pan en un mapa
5. **Esperado**: El otro mapa se sincroniza en <200ms

### Escenario 2: Sincronización precisa
1. En SplitView, hacer 5 zooms y 5 pans rápidos secuencialmente
2. **Esperado**: Sin bucle infinito, ambos mapas terminan en la misma posición
3. Verificar CA2 visualmente o con medición manual

### Escenario 3: RoutePanel — toggle de rutas
1. En modo split, localizar RoutePanel (colapsable a la derecha o izquierda)
2. Desactivar una ruta (clic en checkbox)
3. **Esperado**: La ruta se oculta en ambos mapas simultáneamente (CA3)
4. Reactivar la ruta
5. **Esperado**: La ruta reaparece en ambos mapas
6. Probar "Seleccionar todas" / "Deseleccionar todas"

### Escenario 4: Aislamiento de ruta
1. En RoutePanel, hacer clic en el nombre de una ruta
2. **Esperado**: 
   - La ruta seleccionada permanece con opacidad normal
   - Las demás rutas se atenúan (opacity 0.2) (RF10)
   - La ruta aislada tiene indicación visual en el panel
3. Hacer clic nuevamente en la ruta aislada
4. **Esperado**: Todas las rutas vuelven a opacidad normal

### Escenario 5: Vista simple (regresión SPEC-007)
1. Estando en modo split, hacer clic en "Vista simple"
2. **Esperado**: Un solo mapa con RouteModeToggle (geodésico/vial) (CA7)
3. Alternar entre geodésico y vial en modo simple
4. **Esperado**: Funciona exactamente como en SPEC-007
5. Volver a "Comparativa"
6. **Esperado**: SplitView preserva estado de selección de rutas (CA10)

### Escenario 6: EXP-001 sin datos viales
1. Navegar a una evaluación EXP-001 (sin `route_legs`)
2. **Esperado**: Botón "Comparativa" deshabilitado (CA6)
3. El modo simple funciona sin cambios

### Escenario 7: Vista simple con RoutePanel
1. En modo simple, abrir RoutePanel
2. **Esperado**: RoutePanel funciona igual que en modo split
3. Desactivar/aislar rutas desde RoutePanel en modo simple
4. Cambiar a split
5. **Esperado**: El estado de selección se preserva (CA10)

### Escenario 8: Responsividad
1. Reducir viewport a <1024px
2. **Esperado**: En modo split, los mapas se apilan verticalmente
3. RoutePanel se puede colapsar para no obstruir el mapa

### Escenario 9: Ruta única
1. Cargar evaluación con una sola ruta (si existe)
2. **Esperado**: SplitView muestra la misma ruta en ambos mapas sin error (CA9)

### Escenario 10: Sin rutas en visibleRoutes
1. Deseleccionar todas las rutas
2. **Esperado**: Mapas renderizados sin polilíneas, solo tile layer + bodega

## Validación de métricas

| Métrica | Cómo validar |
|---------|-------------|
| M1: Tiempo de sincronización | Inspección visual + console.time (objetivo: <200ms) |
| M2: Precisión de sincronización | Comparar centro/zoom entre mapas tras interacción (diferencia debe ser 0) |
| M3: Cobertura de rutas seleccionables | Contar rutas en panel vs routeMetrics (debe ser 100%) |
| M4: Tiempo de identificación de divergencia | Usuario identifica ruta con mayor divergencia en modo simple (toggle) vs modo split (split + filtros). Medir tiempo. |

## Referencias

- Data model: `specs/008-visual-analytics-comparacion/data-model.md`
- Component contracts:
  - `specs/008-visual-analytics-comparacion/contracts/SplitMapView.md`
  - `specs/008-visual-analytics-comparacion/contracts/RoutePanel.md`
  - `specs/008-visual-analytics-comparacion/contracts/ViewModeToggle.md`
- Spec: `specs/008-visual-analytics-comparacion/spec.md`
- Research: `specs/008-visual-analytics-comparacion/research.md`
