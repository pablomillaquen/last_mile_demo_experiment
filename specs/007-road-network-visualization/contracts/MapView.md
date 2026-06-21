# Component Contract: MapView

## Props

```typescript
interface MapViewProps {
  packages: Package[];
  getRouteColor?: (pkg: Package) => string;
  getSequence?: (pkg: Package) => number | null;
  polylines?: PolylineData[];          // Geodesic polylines (existing)
  routeLegs?: RouteLeg[];              // NEW: vial geometry from evaluation
  mode?: 'geodesic' | 'vial';         // NEW: active rendering mode
  onModeChange?: (mode: 'geodesic' | 'vial') => void;  // NEW
}
```

## Behavior

| mode prop | routeLegs prop | polylines prop | Renderizado |
|---|---|---|---|
| 'geodesic' | cualquier | cualquier | Polilíneas desde `polylines` (comportamiento actual) |
| 'vial' | definido | cualquier | Polilíneas desde `routeLegs.geometry`, agrupadas por route_id, coloreadas igual que polylines |
| 'vial' | undefined | cualquier | Fallback: renderiza `polylines` (geodésico). RF10 |

## Internal state

- `activeMode: 'geodesic' | 'vial'` — controla qué polilíneas se muestran
- Inicializado desde prop `mode`; si no se provee, default `'geodesic'`

## Toggle (RouteModeToggle)

Componente separado opcional. Botones:
- **Geodésico** (icono línea recta)
- **Vial** (icono ruta curva)

El toggle es persistente durante navegación del mapa. El cambio de modo no recarga datos del backend.
