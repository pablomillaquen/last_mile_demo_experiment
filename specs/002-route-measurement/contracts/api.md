# API Contract: Route Measurement

Base URL: `http://localhost:8000/api`

Extensión del contrato de la Fase 1. Se añaden endpoints para configuración
global y se extienden los endpoints existentes de rutas y métricas.

---

## Settings

### Obtener configuraciones

```
GET /settings
```

Response: `200 OK`
```json
{
  "warehouse_lat": "-33.0450000",
  "warehouse_lng": "-71.6200000",
  "average_speed_kmh": "30"
}
```

### Actualizar configuraciones

```
PUT /settings
```

Request (actualización parcial — solo enviar los campos a modificar):
```json
{
  "average_speed_kmh": 40
}
```

o

```json
{
  "warehouse_lat": -33.04,
  "warehouse_lng": -71.62
}
```

Response: `200 OK` con todas las configuraciones (incluyendo las no enviadas).
Valores numéricos se aceptan como number o string.

**Validación**:
- `warehouse_lat` debe ser un número entre -90 y 90
- `warehouse_lng` debe ser un número entre -180 y 180
- `average_speed_kmh` debe ser un número entero positivo (> 0)

---

## Routes (extendido)

### Obtener ruta

```
GET /routes/{id}
```

Response extendida con métricas:
```json
{
  "id": 1,
  "name": "Ruta A",
  "route_date": "2026-06-12",
  "notes": "Ruta de demostración",
  "route_packages_count": 30,
  "total_distance_km": 87.34,
  "avg_distance_per_delivery_km": 2.91,
  "estimated_time": "2h 55m",
  "packages": [
    {
      "id": 1,
      "sequence": 1,
      "tracking_number": "DEMO-0001",
      "recipient_name": "Juan Pérez",
      "latitude": -33.044155,
      "longitude": -71.628867
    }
  ]
}
```

### Listar rutas

```
GET /routes
```

Response extendida con métricas (mismos campos que show, paginado).

---

## Metrics (extendido)

### Obtener métricas

```
GET /metrics
```

Nuevos campos de distancia y tiempo:
```json
{
  "total_packages": 150,
  "total_routes": 5,
  "packages_per_route": {
    "average": 30,
    "min": 19,
    "max": 41
  },
  "unassigned_packages": 0,
  "route_metrics": {
    "longest_route": {
      "name": "Ruta C",
      "total_distance_km": 95.20,
      "estimated_time": "3h 10m"
    },
    "shortest_route": {
      "name": "Ruta B",
      "total_distance_km": 42.10,
      "estimated_time": "1h 24m"
    },
    "average_speed_kmh": 30
  }
}
```

Nota: Rutas sin paquetes asignados son excluidas del cálculo de ruta más
larga y más corta.
