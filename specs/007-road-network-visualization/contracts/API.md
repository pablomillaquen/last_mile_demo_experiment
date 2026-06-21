# API Contract: Geometry de Rutas Viales

## Endpoint

`GET /api/evaluations/{id}`

## Response (cambio)

El response existente de `GET /api/evaluations/{id}` se extiende con un campo `route_legs` opcional:

```json
{
  "id": 8,
  "executed_at": "2026-06-21T12:00:00Z",
  "parameters": { "...": "..." },
  "metrics_summary": { "...": "..." },
  "route_metrics": [ "..." ],
  "deliveries_flat": [ "..." ],
  "route_legs": [
    {
      "route_id": 1,
      "from_delivery_id": null,
      "to_delivery_id": 1,
      "from_lat": -33.045,
      "from_lng": -71.62,
      "to_lat": -32.929,
      "to_lng": -71.521,
      "distance_km": 15.5,
      "duration_min": 18.2,
      "geometry": [[-33.045, -71.62], [-33.044, -71.619]],
      "mode": "vial"
    }
  ]
}
```

## Notes

- El endpoint **no cambia su implementación**. `route_legs` se incluye automáticamente porque `EvaluationController::show()` lee `evaluation.json` del disco, y ahora ese archivo contiene el nuevo campo.
- `route_legs` puede ser `undefined`/ausente para evaluaciones antiguas (EXP-001) o evaluaciones sin geometría.
- El frontend debe manejar la ausencia de `route_legs` haciendo fallback a geodésico (RF10).
