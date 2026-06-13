# Research: Route Measurement

**Phase**: 0 — Research
**Date**: 2026-06-12

## 1. Algoritmo de Distancia: Haversine

### Decisión

Usar **fórmula de Haversine** para calcular distancia en línea recta entre
puntos geográficos.

### Rationale

- Sin dependencias externas (no OSRM, GraphHopper, Google Directions).
- Sin costos operacionales ni API keys.
- Suficiente para el objetivo de comparación relativa entre rutas.
- Consistente: mismo algoritmo siempre, mismos resultados.

### Fórmula

```
a = sin²(Δlat/2) + cos(lat1)·cos(lat2)·sin²(Δlng/2)
c = 2 · atan2(√a, √(1-a))
d = R · c   (R = 6.371 km)
```

### Alternativas Consideradas

| Opción | Rechazada Por |
|--------|---------------|
| Distancia por red vial (OSRM) | Dependencia externa, complejidad, no necesaria para línea base |
| Google Directions API | Costo, API key, no reproducible en Docker |
| Distancia euclidiana (ignorar curvatura) | Imprecisa para distancias relevantes |

## 2. Recorrido de Ruta

### Decisión

El recorrido incluye **retorno a bodega**:

```
Bodega → P1 → P2 → ... → PN → Bodega
```

### Rationale

- Representa el costo operacional real (el vehículo debe volver).
- Consistente con operaciones logísticas reales de última milla.

## 3. Velocidad Promedio

### Decisión

Configuración **global**, no por ruta:
- Almacenada en tabla `settings` con clave `average_speed_kmh`.
- Valor inicial: 30 km/h.
- Misma velocidad para todas las rutas → comparaciones consistentes.

## 4. Almacenamiento de Bodega

### Decisión

Tabla `settings` con claves `warehouse_lat` y `warehouse_lng`.
No se crea modelo Warehouse independiente (1 bodega, config global).

### Rationale

- Una sola bodega no justifica una tabla con FK.
- Extensible a futuro si se necesitan múltiples bodegas.

## 5. Capa de Servicios

### Decisión

Crear `app/Services/HaversineService.php` y
`app/Services/RouteMetricsService.php`.

### Rationale

- Separa la lógica matemática de los controladores.
- Reutilizable desde cualquier parte del sistema.
- Preparado para fases futuras (optimización, comparación).
