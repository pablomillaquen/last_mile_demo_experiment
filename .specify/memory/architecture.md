# Arquitectura del Sistema

Este proyecto implementa una plataforma experimental para estudiar problemas de
optimización logística de última milla. La arquitectura prioriza simplicidad
inicial, evolución incremental, comparación de algoritmos y reproducibilidad.

## Principios Arquitectónicos

Derivados de la Constitución del proyecto.

### La arquitectura sigue a la hipótesis

No se incorporarán componentes cuya única justificación sea una posible
necesidad futura. Cada nuevo servicio o infraestructura debe responder a una
hipótesis activa que se esté validando en la fase actual.

### Servicios desacoplados

Los componentes de optimización deben poder reemplazarse sin modificar la
operación logística base. La API principal expone contratos estables; los
motores de optimización se conectan como servicios independientes.

### Docker First

Todo componente debe ejecutarse mediante contenedores Docker. El ecosistema
completo se orquesta con `docker-compose.yml` desde la Fase 1.

## Arquitectura Actual (Fase 1)

```
┌──────────────┐
│   NextJS     │  frontend/
└──────┬───────┘
       │ REST
       ▼
┌──────────────┐
│ Laravel API  │  backend/
└──────┬───────┘
       │
       ▼
┌──────────────┐
│ PostgreSQL   │
└──────────────┘
```

Todos los servicios se ejecutan como contenedores Docker orquestados por
`docker-compose.yml`.

## Responsabilidades

### NextJS (frontend/)

- Dashboard operativo
- Formularios de registro (paquetes, rutas)
- Mapas interactivos (Leaflet + OpenStreetMap)
- Visualización de métricas y resultados

### Laravel API (backend/)

- Reglas de negocio del dominio logístico
- CRUD de entidades (paquetes, rutas, asignaciones)
- Lógica de asignación manual de paquetes a rutas
- Cálculo de métricas básicas
- Exposición de endpoints REST

### PostgreSQL

- Persistencia de todas las entidades del dominio
- Consultas geográficas futuras (PostGIS en Fase 2+)
- Almacenamiento de resultados de experimentos

## Roadmap Arquitectónico

| Fase | Componentes Nuevos | Propósito |
|------|-------------------|-----------|
| 1 | Laravel + PostgreSQL + NextJS | Modelar operación logística básica |
| 2 | PostGIS | Consultas espaciales nativas |
| 3 | Motor de optimización (Python) | Algoritmos de agrupación y ruteo |
| 4 | Servicio de cálculo de rutas (OSRM/Valhalla) | Distancias y tiempos reales |
| 5 | Redis + RabbitMQ | Procesamiento asíncrono y caché |
| 6 | OR-Tools | Optimización combinatorial avanzada |

La hoja de ruta se ajusta según la evidencia obtenida en cada fase.

## Decisiones Arquitectónicas

### ADR-001: PostgreSQL en lugar de MySQL

**Motivo**: Las fases futuras requieren consultas espaciales mediante PostGIS.
Migrar de MySQL a PostgreSQL posteriormente sería costoso y riesgoso.

### ADR-002: Docker para todos los servicios

**Motivo**: Garantizar reproducibilidad absoluta del entorno, eliminar
conflictos de dependencias locales y permitir agregar componentes sin
contaminar el entorno de desarrollo.

**Consecuencia**: Todo desarrollador puede ejecutar el sistema completo con un
solo comando (`docker compose up`).

### ADR-003: Laravel como API monolítica inicial

**Motivo**: La Fase 1 no requiere desacoplamiento de servicios. Laravel
proporciona ORM, migraciones, validación y testing integrados. La separación
en microservicios se evaluará cuando la complejidad lo justifique (Principio
III: Complejidad Incremental).

### ADR-004: Optimización como servicio independiente

**Motivo**: Permitir reemplazar algoritmos (agrupación geográfica, ruteo,
balanceo de carga) sin modificar la API principal. Cada motor se implementa
como un contenedor separado que expone una interfaz bien definida.

### ADR-005: Leaflet + OpenStreetMap en lugar de Google Maps Platform

**Motivo**: Google Maps Platform requiere API key, registro de tarjeta de
crédito y tiene límites de uso que rompen la reproducibilidad Docker. Leaflet
con OpenStreetMap tiles es gratuito, sin API key, y funciona en cualquier
entorno sin dependencias externas pagadas.

**Consecuencia**: No hay geocodificación automática en Fase 1. Las coordenadas
deben ingresarse manualmente. Si se necesita geocodificación en fases futuras,
se evaluarán opciones libres (Nominatim, Pelias).
