# Data Model: Visualización de Red Vial

**Branch**: `007-road-network-visualization` | **Date**: 2026-06-21

---

## 1. Entidades existentes (sin cambios)

| Entidad | Tabla/Archivo | Estado |
|---|---|---|
| Route | `routes` (BD) | Sin cambios |
| Package | `packages` (BD) | Sin cambios |
| RoutePackage | `route_packages` (BD) | Sin cambios |
| Evaluation | `evaluations` (BD) + `evaluation.json` (disco) | Sin cambios en BD, extensión en JSON |
| Experiment | `experiments` (BD) + `experiment.json` (disco) | Solo lectura |

## 2. Extensiones a evaluation.json

### 2.1 Nuevo campo: `route_legs`

Se agrega al nivel raíz de `evaluation.json`. **Obligatorio para toda evaluación generada desde SPEC-007 en adelante**, independientemente del modo de distancia.

- **Modo vial**: `geometry` contiene la polyline decodificada de OSRM (overview=full).
- **Modo geodésico**: `geometry` contiene exactamente 2 puntos: origen y destino (línea recta).

Esta decisión sigue D009 (artefactos como fuente de verdad) y garantiza un contrato único para el frontend, eliminando lógica condicional por modo.

```json
{
  "route_legs": [
    {
      "route_id": 1,
      "from_delivery_id": 1,
      "to_delivery_id": 2,
      "from_lat": -33.045,
      "from_lng": -71.62,
      "to_lat": -32.929,
      "to_lng": -71.521,
      "distance_km": 15.5,
      "duration_min": 18.2,
      "geometry": [[-33.045, -71.62], [-33.044, -71.619], ...],
      "mode": "vial"
    }
  ]
}
```

### 2.2 Campos

| Campo | Tipo | Obligatorio | Descripción |
|---|---|---|---|
| route_id | int | sí | ID de la ruta (corresponde a route_metrics[].route_id) |
| from_delivery_id | int \| null | sí | ID de la entrega origen (null si es salida desde warehouse) |
| to_delivery_id | int | sí | ID de la entrega destino |
| from_lat | float | sí | Latitud del punto origen |
| from_lng | float | sí | Longitud del punto origen |
| to_lat | float | sí | Latitud del punto destino |
| to_lng | float | sí | Longitud del punto destino |
| distance_km | float | sí | Distancia del tramo (geodésica o vial) |
| duration_min | float \| null | sí | Duración estimada (null si modo geodésico) |
| geometry | [number, number][] | sí | Lista ordenada de coordenadas [lat, lng] que representa el trazado |
| mode | 'geodesic' \| 'vial' | sí | Modo de cálculo del tramo |

### 2.3 Reglas de generación

1. **Modo vial**: `geometry` contiene la polyline decodificada de OSRM (overview=full). Cada tramo entre dos entregas consecutivas (ordenadas por route_packages.sequence) genera un elemento.
2. **Modo geodésico**: `geometry` contiene exactamente 2 puntos: `[from_lat, from_lng]` y `[to_lat, to_lng]` (línea recta).
3. **Tramos**: Siempre incluye warehouse → primera entrega y última entrega → warehouse.
4. **Secuencia**: El orden de los tramos dentro de route_legs sigue estrictamente route_packages.sequence (RF11).

## 3. Extensiones a tipos frontend (api.ts)

### 3.1 Nueva interfaz

```typescript
interface RouteLeg {
  route_id: number;
  from_delivery_id: number | null;
  to_delivery_id: number;
  from_lat: number;
  from_lng: number;
  to_lat: number;
  to_lng: number;
  distance_km: number;
  duration_min: number | null;
  geometry: [number, number][];
  mode: 'geodesic' | 'vial';
}
```

### 3.2 Modificación a Evaluation

```typescript
interface Evaluation {
  // ... campos existentes
  route_legs?: RouteLeg[];  // NUEVO: opcional, presente en evaluaciones con geometría
}
```

### 3.3 Modificación a PolylineData (MapView.tsx)

Sin cambios. `PolylineData` ya soporta `positions: [number, number][]` que es compatible con `RouteLeg.geometry`.

## 4. Flujo de datos

```
OSRM (overview=full)
  → OsrmClient::route() devuelve geometry (encoded polyline)
    → DistanceService::calculate() pasa geometry + distance + duration
      → MeasurementService construye route_legs[] con geometry decodificada
        → EvaluationController escribe evaluation.json con route_legs[]
          → Frontend GET /evaluations/{id} recibe route_legs[]
            → MapView renderiza Polyline según modo activo
```

## 5. Estados posibles de route_legs

| Estado | Condición | Comportamiento |
|---|---|---|
| Presente con mode='vial' | Evaluación vial con OSRM disponible | Renderizar polyline vial |
| Presente con mode='geodesic' | Evaluación geodésica | Renderizar polyline recta (idéntico a modo geodésico) |
| Ausente (undefined) | Evaluación sin geometría almacenada (EXP-001 existente) | Fallback a geodésico vía route_packages (RF10) |
| Parcial (algunos legs sin geometry) | Error parcial de OSRM en algunos tramos | Fallback por leg: los que tienen geometry se renderizan vial, los que no → recta |
