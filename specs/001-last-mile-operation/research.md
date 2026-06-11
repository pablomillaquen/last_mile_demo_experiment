# Research: Simulación de Operación Logística — Asignación Manual

**Fase**: 0 — Outline & Research

## Decisiones Técnicas

### Backend: Laravel 11 + PHP 8.2

**Decisión**: Laravel 11 sobre PHP 8.2

**Rationale**: El stack tecnológico está definido en la Constitución y
architecture.md del proyecto. Laravel proporciona ORM (Eloquent), migraciones,
validación, testing (PHPUnit) y routing REST sin configuración adicional.

**Alternativas consideradas**: Symfony, Node/Express — pero la Constitución ya
define Laravel como stack inicial.

### Frontend: NextJS 14 + TypeScript

**Decisión**: NextJS 14 con App Router y TypeScript

**Rationale**: Definido en la Constitución. NextJS proporciona routing por
archivos, Server Components y soporte nativo para TypeScript.

**Alternativas consideradas**: React standalone, Vue — la Constitución define
NextJS como stack inicial.

### Base de Datos: PostgreSQL 16

**Decisión**: PostgreSQL 16 Alpine via Docker

**Rationale**: Definido en la Constitución y architecture.md (ADR-001). PostGIS
estará disponible en Fase 2 para consultas espaciales.

**Alternativas consideradas**: MySQL, SQLite — la Constitución define
PostgreSQL.

### Visualización Geográfica: Leaflet + OpenStreetMap

**Decisión**: Leaflet (React-Leaflet) con OpenStreetMap tiles

**Rationale**: Mapa libre de costo, sin API key, reproducible en cualquier
entorno sin dependencias externas pagadas. Idóneo para el portfolio y
experimento sin restricciones de licencia.

**Alternativas consideradas**: Google Maps Platform — descartado por requerir
API key y límites de uso que rompen la reproducibilidad Docker.

### Contenedores: Docker + docker-compose

**Decisión**: Docker para todos los servicios, orquestados con docker-compose.yml

**Rationale**: Principio VIII (Docker First) de la Constitución.

## Decisiones de Dominio

### Modelo de Datos

**Decisión**: Tres entidades con tabla intermedia RoutePackage.

**Rationale**: La tabla intermedia permite que un paquete tenga sequence y
assigned_at propios por ruta, habilitando la comparación entre secuencia manual
y optimizada en fases futuras. Se rechazó `packages.route_id` porque destruiría
esa capacidad.

### Geocodificación

**Decisión**: El operador ingresa latitud y longitud manualmente. Sin
geocodificación automática.

**Rationale**: Exclusiones de la spec. Geocoding automático y Address
Autocomplete quedan fuera del alcance de Fase 1.

## APIs y Contratos

Se define API REST con Laravel. Contratos documentados en `contracts/api.md`.

## Testing

**Decisión**: PHPUnit para backend, Jest + React Testing Library para frontend.

**Rationale**: Stack estándar de Laravel y NextJS respectivamente.
