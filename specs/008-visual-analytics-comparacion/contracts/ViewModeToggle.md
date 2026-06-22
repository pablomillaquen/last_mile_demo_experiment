# Contract: ViewModeToggle

**File**: `frontend/src/components/ViewModeToggle.tsx`
**Status**: New component (SPEC-008 H4)
**Depends on**: Nothing

## Props

| Prop | Type | Required | Default | Description |
|------|------|----------|---------|-------------|
| viewMode | `'simple' \| 'split'` | Sí | — | Modo de visualización actual |
| onViewModeChange | `(mode: 'simple' \| 'split') => void` | Sí | — | Callback al cambiar modo |
| splitAvailable | `boolean` | Sí | — | Si el modo split está disponible (requiere datos viales) |

## Behavior

- Dos botones: "Vista simple" / "Comparativa"
- Modo activo → botón highlight
- `splitAvailable === false` → botón "Comparativa" deshabilitado con tooltip "No hay datos viales para esta evaluación"
- Mismo estilo visual que `RouteModeToggle` (consistencia UI)

## Edge Cases

| Caso | Comportamiento |
|------|---------------|
| splitAvailable = false, clic en split | No hace nada (deshabilitado) |
| En modo split, splitAvailable cambia a false | Cambia automáticamente a modo simple |
