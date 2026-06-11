# Quickstart: Simulación de Operación Logística

## Prerequisitos

- Docker y Docker Compose instalados
- Git
- Puerto 8000, 3000, 5432, 5050 disponibles

## Setup

```bash
# 1. Clonar (si aplica) y posicionarse en la raíz del proyecto
cd last-mile-demo

# 2. Iniciar todos los servicios
docker compose up -d

# 3. Verificar que los servicios están corriendo
docker compose ps

# 4. Ingresar al contenedor backend para ejecutar migraciones
docker compose exec backend bash
php artisan migrate
php artisan db:seed  # opcional, datos de ejemplo
exit

# 5. Abrir en el navegador
# Frontend: http://localhost:3000
# API:      http://localhost:8000/api/packages
# PgAdmin:  http://localhost:5050 (admin@lastmile.dev / admin)
```

## Flujo de Validación

### 1. Registrar paquetes

```bash
curl -X POST http://localhost:8000/api/packages \
  -H "Content-Type: application/json" \
  -d '{
    "tracking_number": "PKG-001",
    "recipient_name": "Juan Pérez",
    "delivery_address": "Av. Siempre Viva 123",
    "latitude": -33.456,
    "longitude": -70.648
  }'
```

Resultado esperado: `201 Created` con el paquete creado.

### 2. Crear ruta

```bash
curl -X POST http://localhost:8000/api/routes \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Ruta Mañana",
    "route_date": "2026-06-11"
  }'
```

Resultado esperado: `201 Created` con la ruta creada.

### 3. Asignar paquete a ruta

```bash
curl -X POST http://localhost:8000/api/routes/1/assign \
  -H "Content-Type: application/json" \
  -d '{"package_id": 1, "sequence": 1}'
```

Resultado esperado: `201 Created`.

### 4. Ver métricas

```bash
curl http://localhost:8000/api/metrics
```

Resultado esperado: `200 OK` con JSON de métricas.

### 5. Visualizar en mapa

Abrir http://localhost:3000/map — los paquetes deben mostrarse como
marcadores en el mapa. Los paquetes asignados deben tener un color
distintivo por ruta.

## Comandos Útiles

```bash
# Ver logs del backend
docker compose logs -f backend

# Ver logs del frontend
docker compose logs -f frontend

# Ejecutar tests del backend
docker compose exec backend bash -c "php artisan test"

# Detener servicios
docker compose down

# Detener y eliminar volúmenes (borra datos)
docker compose down -v
```

## Arquitectura

Ver `specs/001-last-mile-operation/contracts/api.md` para el detalle de
endpoints.
Ver `specs/001-last-mile-operation/data-model.md` para el modelo de datos.
Ver `.specify/memory/architecture.md` para la visión arquitectónica general.
