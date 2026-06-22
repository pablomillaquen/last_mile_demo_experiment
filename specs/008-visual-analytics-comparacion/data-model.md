# Data Model — SPEC-008: Visual Analytics

## 1. View State (evaluations/[id]/page.tsx)

```typescript
// Modo de visualización del mapa
type ViewMode = 'simple' | 'split';

// Conjunto de IDs de rutas visibles
type VisibleRoutes = Set<number>;

// Ruta actualmente aislada (null = ninguna)
type IsolatedRoute = number | null;
```

### Estado compartido

```typescript
interface VisualAnalyticsState {
  viewMode: ViewMode;                    // simple ↔ split (CA8, CA10)
  visibleRoutes: VisibleRoutes;           // rutas activas (RF3)
  isolatedRoute: IsolatedRoute;           // ruta aislada (RF10)
}
```

**Reglas de transición:**
- `visibleRoutes` inicia como todas las rutas disponibles
- Al desactivar una ruta, se remueve de `visibleRoutes`
- Al activar una ruta, se agrega a `visibleRoutes`
- `isolatedRoute` es independiente de `visibleRoutes`:
  - Si `isolatedRoute !== null`, las rutas en `visibleRoutes \ {isolatedRoute}` se atenúan (opacity 0.2)
  - Si `isolatedRoute === null`, no hay atenuación
- Al cambiar `viewMode`, `visibleRoutes` e `isolatedRoute` se preservan

## 2. Component Props

### SplitMapView

```typescript
interface SplitMapViewProps {
  polylines: PolylineData[];              // geodésico (heredado MapView)
  routeLegs?: RouteLeg[];                 // vial (heredado MapView)
  routeColorById: Record<number, string>; // color por route_id (heredado)
  routeNameById: Record<number, string>;  // nombre por route_id (heredado)
  visibleRoutes: Set<number>;             // rutas visibles (nuevo)
  isolatedRoute: number | null;           // ruta aislada (nuevo)
}
```

### RoutePanel

```typescript
interface RoutePanelProps {
  routes: { id: number; name: string }[];
  routeColorById: Record<number, string>;
  visibleRoutes: Set<number>;
  isolatedRoute: number | null;
  onToggleRoute: (routeId: number) => void;
  onIsolateRoute: (routeId: number | null) => void;
  onSelectAll: () => void;
  onDeselectAll: () => void;
}
```

### ViewModeToggle

```typescript
interface ViewModeToggleProps {
  viewMode: 'simple' | 'split';
  onViewModeChange: (mode: 'simple' | 'split') => void;
  splitAvailable: boolean;  // vialAvailable (misma lógica SPEC-007)
}
```

### MapSyncController (interno, no exportado)

```typescript
interface MapSyncControllerProps {
  mapId: 'left' | 'right';
  onMoveEnd: (center: [number, number], zoom: number) => void;
  targetCenter?: [number, number];
  targetZoom?: number;
}
```

## 3. PolylineData (heredado, sin cambios)

```typescript
interface PolylineData {
  positions: [number, number][];  // [[lat, lng], ...]
  color: string;
  name: string;
}
```

## 4. Filtrado de polilíneas

En SplitMapView, antes de renderizar, las polilíneas se filtran:

```typescript
// Paso 1: filtrar por visibleRoutes
let filtered = allPolylines.filter(pl => visibleRoutes.has(/* routeId */));

// Paso 2: aplicar atenuación si hay ruta aislada
if (isolatedRoute !== null) {
  filtered = filtered.map(pl => ({
    ...pl,
    opacity: pl.routeId === isolatedRoute ? 1.0 : 0.2
  }));
}
```

**Nota**: `PolylineData` no incluye `routeId`. El filtrado requiere que las polilíneas incluyan el `routeId` o que se derive del orden. Ver implementación concreta en componentes.

## 5. Validación

| Condición | Comportamiento esperado |
|-----------|------------------------|
| `vialAvailable === false` | SplitView no se muestra; ViewModeToggle deshabilitado (CA6) |
| `routeMetrics.length === 0` | RoutePanel muestra mensaje "Sin rutas disponibles" (RF11) |
| `visibleRoutes.size === 0` | Mapas muestran solo tile layer + bodega |
| `isolatedRoute !== null` y ruta no está en `visibleRoutes` | Se ignora `isolatedRoute` (inconsistencia de estado no debería ocurrir) |
| Alternar modo simple→split→simple | `visibleRoutes` e `isolatedRoute` preservados (CA10) |
