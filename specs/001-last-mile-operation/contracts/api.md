# API Contract: Simulación de Operación Logística

Base URL: `http://localhost:8000/api`

## Packages

### Listar paquetes

```
GET /packages
```

Query params: `?assigned=true&page=1&per_page=15`

Response (listado — campos principales):
```json
{
  "data": [
    {
      "id": 1,
      "tracking_number": "PKG-001",
      "recipient_name": "Juan Pérez",
      "delivery_address": "Av. Siempre Viva 123",
      "latitude": -33.456,
      "longitude": -70.648,
      "assigned": false,
      "created_at": "2026-06-10T12:00:00Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "total": 10
  }
}
```

### Crear paquete

```
POST /packages
```

Request:
```json
{
  "tracking_number": "PKG-001",
  "recipient_name": "Juan Pérez",
  "delivery_address": "Av. Siempre Viva 123",
  "district": "Santiago",
  "city": "Santiago",
  "latitude": -33.456,
  "longitude": -70.648
}
```

Response: `201 Created` con el recurso creado.

### Obtener paquete

```
GET /packages/{id}
```

Response: `200 OK` con el recurso completo.

### Actualizar paquete

```
PUT /packages/{id}
```

Request: Mismos campos que POST (parcial permitido).

Response: `200 OK` con el recurso actualizado.

### Eliminar paquete

```
DELETE /packages/{id}
```

Response: `204 No Content`.

## Routes

### Listar rutas

```
GET /routes
```

### Crear ruta

```
POST /routes
```

Request:
```json
{
  "name": "Ruta Mañana 01",
  "route_date": "2026-06-11",
  "notes": "Priorizar sector norte"
}
```

Response: `201 Created`.

### Obtener ruta

```
GET /routes/{id}
```

### Actualizar ruta

```
PUT /routes/{id}
```

### Eliminar ruta

```
DELETE /routes/{id}
```

Response: `204 No Content`. Los RoutePackage asociados se eliminan en cascada.

## Asignación

### Asignar paquete a ruta

```
POST /routes/{id}/assign
```

Request:
```json
{
  "package_id": 1,
  "sequence": 1
}
```

Response: `201 Created` con el RoutePackage creado.
Errores: `409 Conflict` si el paquete ya está asignado a otra ruta.

### Desasignar paquete de ruta

```
POST /routes/{id}/unassign
```

Request:
```json
{
  "package_id": 1
}
```

Response: `200 OK`.

## Metrics

### Obtener métricas

```
GET /metrics
```

Response:
```json
{
  "total_packages": 50,
  "total_routes": 5,
  "packages_per_route": {
    "average": 10,
    "min": 3,
    "max": 18
  },
  "unassigned_packages": 12
}
```
