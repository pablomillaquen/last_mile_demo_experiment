# Last Mile Demo — API (Laravel 12)

API REST del experimento de simulación logística. Proporciona endpoints para gestionar paquetes, rutas, asignaciones y métricas operativas.

## Tecnologías

- Laravel 12 (PHP 8.2)
- PostgreSQL 16
- Docker Compose

## Endpoints

| Método | Ruta | Descripción |
|--------|------|-------------|
| GET | `/api/packages` | Lista paquetes. Soporta `?assigned=true/false&page=1&per_page=15` |
| POST | `/api/packages` | Crear paquete |
| DELETE | `/api/packages/{id}` | Eliminar paquete |
| GET | `/api/routes` | Lista rutas con `route_packages_count` |
| POST | `/api/routes` | Crear ruta |
| DELETE | `/api/routes/{id}` | Eliminar ruta |
| POST | `/api/routes/{route}/assign` | Asignar paquete (`body: { package_id }`). Devuelve 409 si ya asignado |
| POST | `/api/routes/{route}/unassign` | Remover paquete (`body: { package_id }`) |
| GET | `/api/metrics` | total_packages, total_routes, packages_per_route, unassigned_packages |

## Modelos

- **Package**: `tracking_number`, `recipient_name`, `address`, `city`, `district`, `latitude`, `longitude`. Computa `assigned` via `route_packages` (sin columna física `status`).
- **Route**: `name`, `description`, `date`. No tiene campo `status`.
- **RoutePackage**: `route_id`, `package_id`, `sequence`, `assigned_at`. Tabla pivote.

## Despliegue

El servicio se ejecuta dentro de Docker Compose. No se despliega de forma independiente.

```bash
# Migraciones y seeders
docker compose exec backend php artisan migrate:fresh --seed

# Seeders adicionales
docker compose exec backend php artisan db:seed --class=RouteMeasurementDemoSeeder
```

El seeder `RouteMeasurementDemoSeeder` genera 150 paquetes con coordenadas únicas en 5 perfiles claramente diferenciados:
- **Ruta A** — Cluster compacto eficiente (espiral, Concón)
- **Ruta B** — Dispersión en las 5 comunas
- **Ruta C** — Zigzag Viña Centro ↔ Miraflores
- **Ruta D** — Barrido limpio costa Concón → Reñaca
- **Ruta E** — Cluster Concón + 4 outliers en Quilpué

Las migraciones incluyen constraints de clave foránea con `ON DELETE CASCADE` para paquetes y `RESTRICT` para rutas con asignaciones existentes.

## Licencia

MIT
