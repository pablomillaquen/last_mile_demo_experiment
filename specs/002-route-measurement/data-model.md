# Data Model: Route Measurement

Extensión del modelo de datos de la Fase 1. Se añade una entidad `Setting`
para configuración global y se extiende `Route` con métricas calculadas.

## Setting

Representa un valor de configuración global del sistema.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | bigint (PK) | Identificador único autoincremental |
| key | string(100) (UNIQUE) | Nombre de la configuración |
| value | text | Valor de la configuración |
| created_at | timestamp | Fecha de creación del registro |
| updated_at | timestamp | Fecha de última modificación |

**Validación**:
- `key` es obligatorio, único, máximo 100 caracteres
- `value` puede ser texto (el parseo depende del tipo esperado)

**Validación**:
- `warehouse_lat` debe estar entre -90 y 90
- `warehouse_lng` debe estar entre -180 y 180
- `average_speed_kmh` debe ser un número entero positivo (> 0)

**Claves predefinidas**:

| Key | Tipo esperado | Valor inicial | Rango válido | Descripción |
|-----|---------------|---------------|--------------|-------------|
| `warehouse_lat` | decimal | -33.0450000 | [-90, 90] | Latitud de la bodega |
| `warehouse_lng` | decimal | -71.6200000 | [-180, 180] | Longitud de la bodega |
| `average_speed_kmh` | integer | 30 | > 0 | Velocidad promedio global (km/h) |

## Route (extensión)

La entidad Route existente se extiende con **métricas calculadas** (no
persistidas, computadas en el momento por `RouteMetricsService`).

| Campo | Tipo | Origen |
|-------|------|--------|
| total_distance_km | decimal(10,2) | Calculado: suma Haversine entre puntos consecutivos + bodega |
| avg_distance_per_delivery_km | decimal(10,2) | Calculado: total_distance / N (solo si N > 0) |
| estimated_time_minutes | integer | Calculado: (total_distance / average_speed_kmh) * 60 |

## RoutePackage (extensión)

La tabla existente `route_packages` mantiene su estructura. El campo `sequence`
sigue siendo el determinante del orden de entrega. No se requieren cambios.

## Relaciones

```
Setting (1) — (no relations, tabla de config)

Route (1) ──── (N) RoutePackage (N) ──── (1) Package
                                    |
                              (sequence define orden)
```

Una ruta ahora tiene:
- 0 o N RoutePackage (asignaciones)
- Métricas calculadas (distancia, tiempo)
- Un punto de inicio/fin compartido: la bodega global

## Migraciones

```sql
-- create_settings_table
CREATE TABLE settings (
    id BIGSERIAL PRIMARY KEY,
    key VARCHAR(100) NOT NULL UNIQUE,
    value TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

-- seed inicial
INSERT INTO settings (key, value) VALUES
    ('warehouse_lat', '-33.0450000'),
    ('warehouse_lng', '-71.6200000'),
    ('average_speed_kmh', '30');
```
