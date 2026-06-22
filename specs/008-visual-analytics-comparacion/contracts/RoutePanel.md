# Contract: RoutePanel

**File**: `frontend/src/components/RoutePanel.tsx`
**Status**: New component (SPEC-008 H2)
**Depends on**: Nothing (componente React puro, sin dependencias de mapa)

## Props

| Prop | Type | Required | Default | Description |
|------|------|----------|---------|-------------|
| routes | `{ id: number; name: string }[]` | Sí | — | Lista de rutas disponibles |
| routeColorById | `Record<number, string>` | Sí | — | Color asignado a cada ruta |
| visibleRoutes | `Set<number>` | Sí | — | IDs de rutas actualmente visibles |
| isolatedRoute | `number \| null` | Sí | — | ID de ruta aislada (null = ninguna) |
| onToggleRoute | `(routeId: number) => void` | Sí | — | Callback al activar/desactivar ruta |
| onIsolateRoute | `(routeId: number \| null) => void` | Sí | — | Callback al aislar/restaurar ruta |
| onSelectAll | `() => void` | Sí | — | Seleccionar todas las rutas |
| onDeselectAll | `() => void` | Sí | — | Deseleccionar todas las rutas |

## Behavior

### Visualización
- Lista vertical de rutas, cada una con:
  - Indicador de color (círculo del color de la ruta)
  - Nombre de la ruta
  - Checkbox/switch para activar/desactivar (RF9)
  - Al hacer clic en el nombre → aísla la ruta (CA4)
- Botones "Seleccionar todas" / "Deseleccionar todas"
- Panel colapsable con botón toggle (RNF2)

### Estados visuales
| Estado | Apariencia |
|--------|-----------|
| Ruta activa | Checkbox marcado, texto normal |
| Ruta inactiva | Checkbox sin marcar, texto atenuado (opacity 0.5) |
| Ruta aislada | Checkbox marcado, texto con fondo highlight, borde izquierdo coloreado |
| Sin rutas | Mensaje "Sin rutas disponibles" (puede no aplicarse si routeMetrics existe) |

### Interacciones
| Acción | Resultado |
|--------|-----------|
| Clic en checkbox | `onToggleRoute(routeId)` — agrega/remueve de `visibleRoutes` |
| Clic en nombre (ruta no aislada) | `onIsolateRoute(routeId)` — aísla la ruta |
| Clic en nombre (ruta aislada) | `onIsolateRoute(null)` — restaura todas |
| Clic "Seleccionar todas" | `onSelectAll()` — todas las rutas en `visibleRoutes` |
| Clic "Deseleccionar todas" | `onDeselectAll()` — `visibleRoutes` vacío |
| Clic toggle panel | Panel se colapsa/expande |

## Edge Cases

| Caso | Comportamiento |
|------|---------------|
| Todas las rutas desactivadas | Mapas sin polilíneas. "Seleccionar todas" disponible. |
| Una sola ruta disponible | RoutePanel funciona normalmente. Aislamiento tiene poco efecto visual. |
| 15+ rutas | Lista scrollable. Panel mantiene altura máxima. |
| Panel colapsado | Solo se muestra el botón toggle. El estado interior no se pierde. |
