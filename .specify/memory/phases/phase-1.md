# Fase 1: Modelado de Operación Logística Básica

**Estado**: Pendiente

## Objetivo

Modelar una operación logística básica de última milla con capacidades
mínimas: registro de paquetes, rutas, asignación manual, visualización
geográfica y métricas básicas.

## Hipótesis

Es posible representar un problema real de distribución de última milla
mediante un sistema digital que permita medir métricas operativas objetivas
sin necesidad de algoritmos de optimización.

## Entidades Iniciales

### Package

Representa un paquete a entregar.

Atributos clave:
- Dirección de destino
- Coordenadas geográficas (latitud, longitud)
- Identificador único

### Route

Representa una ruta de distribución.

Atributos clave:
- Nombre o identificador
- Fecha de operación
- Estado (pendiente, en progreso, completada)

### RoutePackage

Representa la asignación de un paquete a una ruta.

Atributos clave:
- Referencia al paquete asignado
- Referencia a la ruta
- Orden de entrega (posición en la secuencia de la ruta)

## Stack Docker

```
Docker
├── Laravel API (backend/)
├── PostgreSQL
├── NextJS (frontend/)
└── PgAdmin (opcional)
```

## Entregables

- Laravel API con CRUD de paquetes y rutas
- NextJS con dashboard y mapas
- Asignación manual de paquetes a rutas
- Métricas básicas (cantidad de paquetes, cantidad de rutas, paquetes por
  ruta, paquetes sin asignar)
- `docker-compose.yml` funcional
