# Data Model: Simulación de Operación Logística

## Package

Representa un paquete a entregar.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | bigint (PK) | Identificador único autoincremental |
| received_at | datetime | Fecha de recepción en el centro de distribución |
| tracking_number | string(100) | Número de seguimiento externo |
| recipient_name | string(255) | Nombre del destinatario |
| delivery_address | text | Dirección de entrega |
| district | string(100) | Distrito o comuna |
| city | string(100) | Ciudad |
| latitude | decimal(10,7) | Coordenada de latitud |
| longitude | decimal(10,7) | Coordenada de longitud |
| created_at | timestamp | Fecha de creación del registro |
| updated_at | timestamp | Fecha de última modificación |

**Validación**:
- `tracking_number`, `recipient_name`, `delivery_address`, `latitude`,
  `longitude` son obligatorios
- `latitude` debe estar entre -90 y 90
- `longitude` debe estar entre -180 y 180

**Estado**: Se infiere de `route_packages` — si existe `route_packages.package_id`, el estado es `asignado`; si no, `pendiente`.

## Route

Representa una ruta de distribución.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | bigint (PK) | Identificador único autoincremental |
| name | string(255) | Nombre descriptivo de la ruta |
| route_date | date | Fecha de operación |
| notes | text | Notas u observaciones de la ruta |
| created_at | timestamp | Fecha de creación del registro |
| updated_at | timestamp | Fecha de última modificación |

**Validación**:
- `name` y `route_date` son obligatorios

## RoutePackage

Representa la asignación de un paquete a una ruta.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | bigint (PK) | Identificador único autoincremental |
| route_id | bigint (FK) | Referencia a la ruta |
| package_id | bigint (FK) | Referencia al paquete |
| sequence | integer | Orden de entrega dentro de la ruta |
| assigned_at | datetime | Fecha y hora de asignación |
| created_at | timestamp | Fecha de creación del registro |
| updated_at | timestamp | Fecha de última modificación |

**Restricciones**:
- Un paquete puede estar en una sola ruta a la vez
- `sequence` determina el orden de entrega
- FK: `route_id` → `routes.id`
- FK: `package_id` → `packages.id`

## Relaciones

```
Package (1) ──── (N) RoutePackage (N) ──── (1) Route
```

Un Package puede estar asignado a 0 o 1 Route (a través de RoutePackage).
Una Route puede tener 0 o N Packages (a través de RoutePackage).

## Migraciones

```sql
-- create_packages_table
CREATE TABLE packages (
    id BIGSERIAL PRIMARY KEY,
    received_at TIMESTAMP,
    tracking_number VARCHAR(100),
    recipient_name VARCHAR(255),
    delivery_address TEXT,
    district VARCHAR(100),
    city VARCHAR(100),
    latitude DECIMAL(10, 7) NOT NULL,
    longitude DECIMAL(10, 7) NOT NULL,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

-- create_routes_table
CREATE TABLE routes (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    route_date DATE NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

-- create_route_packages_table
CREATE TABLE route_packages (
    id BIGSERIAL PRIMARY KEY,
    route_id BIGINT NOT NULL REFERENCES routes(id) ON DELETE CASCADE,
    package_id BIGINT NOT NULL REFERENCES packages(id) ON DELETE CASCADE,
    sequence INTEGER,
    assigned_at TIMESTAMP DEFAULT NOW(),
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW(),
    UNIQUE(package_id)
);
```
