# Contract: SplitMapView

**File**: `frontend/src/components/SplitMapView.tsx`
**Status**: New component (SPEC-008 H1)
**Depends on**: `MapView.tsx`, `RoutePanel.tsx`

## Props

| Prop | Type | Required | Default | Description |
|------|------|----------|---------|-------------|
| polylines | `PolylineData[]` | Sí | — | Polilíneas geodésicas (mismo formato MapView) |
| routeLegs | `RouteLeg[]` | No | `undefined` | Legs viales con geometría |
| routeColorById | `Record<number, string>` | Sí | — | Color por route_id |
| routeNameById | `Record<number, string>` | Sí | — | Nombre por route_id |
| visibleRoutes | `Set<number>` | Sí | — | IDs de rutas visibles |
| isolatedRoute | `number \| null` | Sí | — | ID de ruta aislada (null = ninguna) |

## Behavior

### Renderizado
- Renderiza dos `MapContainer` de react-leaflet lado a lado en un contenedor flex
- Mapa izquierdo: `mode='geodesic'` (fijo)
- Mapa derecho: `mode='vial'` (fijo)
- Cada mapa recibe el mismo `routeColorById`, `routeNameById`, `visibleRoutes`, `isolatedRoute`

### Filtrado de polilíneas
- Filtra polilíneas usando `pl.routeId` contra `visibleRoutes`
- Asigna `opacity: 0.2` (vía `PolylineData.opacity`) a rutas no aisladas cuando `isolatedRoute !== null`
- Pasa las polilíneas filtradas a cada `MapView` como `polylines` (geodésico) y como polilíneas construidas desde `routeLegs` (vial)

### Sincronización
- Ambos mapas mantienen el mismo centro y zoom
- Al hacer pan/zoom en un mapa, el otro se actualiza en <200ms (CA2)
- Usa flag `isSyncing` para evitar bucle infinito

### Handling vial no disponible
- Si `routeLegs` está vacío o no tiene legs con `mode='vial'`, muestra solo el mapa izquierdo (geodésico) con un mensaje informativo (CA6)

### Responsividad
- En viewports ≥1024px: ambos mapas ocupan 50% del ancho
- En viewports <1024px: los mapas se apilan verticalmente (stack)
- Cada mapa mantiene `h-[600px]` (heredado de MapView)

## Events

| Event | Type | Description |
|-------|------|-------------|
| (ninguno) | — | SplitMapView es puramente presentacional. Las interacciones con rutas se manejan desde RoutePanel (componente hermano, no hijo). |

## Edge Cases

| Caso | Comportamiento |
|------|---------------|
| `visibleRoutes` vacío | Ambos mapas sin polilíneas. Solo tile layer + bodega. |
| `isolatedRoute` no está en `visibleRoutes` | Ignorar `isolatedRoute` (estado inconsistente — no debería ocurrir por diseño). |
| Un solo RouteLeg con vial | SplitView funciona normalmente. Mapa derecho muestra geometría vial. |
| routeLegs.length > 0 pero todas son mode='geodesic' | Tratar como vial no disponible (CA6). |
