# Last Mile Demo — Simulación de Operación Logística

## Experimento

**Hipótesis**: La visualización geográfica y las métricas operativas permiten identificar patrones de asignación ineficientes y establecer una línea base cuantificable para futuras estrategias de optimización.

**Problema**: Los operadores asignan paquetes a rutas sin herramientas visuales. Las ineficiencias —rutas que cruzan la ciudad varias veces, paquetes cercanos en rutas distintas, concentraciones desbalanceadas— pasan desapercibidas.

**Objetivo**: Registrar paquetes con ubicación geográfica, crear rutas de distribución, asignar paquetes manualmente y visualizar la distribución en un mapa para medir la ineficiencia de la asignación manual.

## Arquitectura

| Capa | Tecnología |
|------|-----------|
| Frontend | Next.js 14 (TypeScript), Leaflet + OpenStreetMap |
| API | Laravel 12 (PHP 8.2) |
| Base de datos | PostgreSQL 16 |
| Infraestructura | Docker Compose |

## Despliegue rápido

```bash
# 1. Clonar
git clone <repo-url>
cd last-mile-demo

# 2. Iniciar servicios
docker compose up -d

# 3. Ejecutar migraciones y seeders
docker compose exec backend php artisan migrate:fresh --seed

# 4. Abrir en el navegador
# Frontend: http://localhost:3000
# API:      http://localhost:8000/api
```

Para incluir herramientas opcionales (PgAdmin):

```bash
docker compose --profile tools up -d
```

## Estructura del proyecto

```
├── backend/          # API Laravel (PHP)
├── frontend/         # App Next.js (TypeScript)
├── docker/           # Infraestructura auxiliar
├── specs/            # Documentación del feature
│   └── 001-last-mile-operation/
├── docker-compose.yml
└── README.md
```

## Seeders

| Comando | Paquetes | Rutas | Asignaciones |
|---------|----------|-------|-------------|
| `DemoDataSeeder` | 50 | — | — |
| `DemoDatasetSeeder` | 100 | 5 | 20 paquetes, distribuidas deliberadamente ineficientes |

Para cargar ambos datasets:

```bash
docker compose exec backend php artisan db:seed --class=DemoDatasetSeeder
docker compose exec backend php artisan db:seed --class=DemoDataSeeder
```

## Endpoints principales

| Método | Ruta | Descripción |
|--------|------|-------------|
| GET | `/api/packages` | Lista paquetes (filtro `?assigned=true/false`) |
| POST | `/api/packages` | Crear paquete |
| DELETE | `/api/packages/{id}` | Eliminar paquete |
| GET | `/api/routes` | Lista rutas |
| POST | `/api/routes` | Crear ruta |
| DELETE | `/api/routes/{id}` | Eliminar ruta |
| POST | `/api/routes/{route}/assign` | Asignar paquete a ruta |
| POST | `/api/routes/{route}/unassign` | Remover paquete de ruta |
| GET | `/api/metrics` | Métricas operativas |

## Licencia

MIT
