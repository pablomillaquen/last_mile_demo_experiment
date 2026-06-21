# Last Mile Demo — Simulación de Operación Logística

## Experimento

**Hipótesis**: La visualización geográfica y las métricas operativas permiten identificar patrones de asignación ineficientes y establecer una línea base cuantificable para futuras estrategias de optimización.

**Problema**: Los operadores asignan paquetes a rutas sin herramientas visuales. Las ineficiencias —rutas que cruzan la ciudad varias veces, paquetes cercanos en rutas distintas, concentraciones desbalanceadas— pasan desapercibidas.

## Arquitectura

| Capa | Tecnología |
|------|-----------|
| Frontend | Next.js 14 (TypeScript), Leaflet + OpenStreetMap |
| API | Laravel 12 (PHP 8.2) |
| Base de datos | PostgreSQL 16 |
| Ruteo vial | OSRM v5.27 (OpenStreetMap) |
| Infraestructura | Docker Compose |

## Despliegue rápido

```bash
# 1. Iniciar servicios
docker compose up -d

# 2. Ejecutar migraciones y seeders
docker compose exec backend php artisan migrate:fresh --seed

# 3. Abrir en el navegador
# Frontend: http://localhost:3000
# API:      http://localhost:8000/api
```

Para incluir herramientas opcionales (PgAdmin):

```bash
docker compose --profile tools up -d
```

## Reproducción del Experimento 002 (Red Vial)

Este experimento compara el cálculo de distancias geodésicas (línea recta) vs. viales (red de calles real de OpenStreetMap para Gran Valparaíso, Chile).

### Prerrequisitos

- Docker Compose instalado
- ~1 GB RAM libre para preprocesamiento OSRM
- ~250 MB de disco para el grafo vial
- Puerto 5001 libre (OSRM — se usa 5001 en lugar de 5000 para evitar conflicto con macOS AirPlay)

### Pipeline completo

```bash
# 1. Construir imágenes
docker compose build

# 2. Preprocesar grafo vial OSRM (descarga Chile OSM + extrae Valparaíso + compila)
make prepare-osrm

# 3. Iniciar todos los servicios (PostgreSQL, backend, frontend, OSRM)
docker compose up -d

# 4. Migrar y seedear datos
docker compose exec backend php artisan migrate:fresh --seed

# 5a. Verificar que OSRM responde (healthcheck automático, ~15s startup)
curl -s "http://localhost:5001/route/v1/driving/-71.62,-33.045;-71.61,-33.05?overview=false&steps=false"

# 5b. Verificar healthcheck en logs
docker compose logs osrm | grep -i health

# 6. Ejecutar evaluaciones de pares (geodesic + vial)
#    6a. Primero crear evaluaciones baseline (geodesic, semillas fijas)
curl -s -X POST http://localhost:8000/api/evaluations \
  -H "Content-Type: application/json" \
  -d '{"distance_mode": "geodesic", "random_seed": 42, "algorithm": "kmeans", "algorithm_version": "1.0"}'

curl -s -X POST http://localhost:8000/api/evaluations \
  -H "Content-Type: application/json" \
  -d '{"distance_mode": "geodesic", "random_seed": 123, "algorithm": "kmeans", "algorithm_version": "1.0"}'

#    6b. Luego evaluaciones viales con mismas semillas
curl -s -X POST http://localhost:8000/api/evaluations \
  -H "Content-Type: application/json" \
  -d '{"distance_mode": "vial", "random_seed": 42, "algorithm": "kmeans", "algorithm_version": "1.0"}'

curl -s -X POST http://localhost:8000/api/evaluations \
  -H "Content-Type: application/json" \
  -d '{"distance_mode": "vial", "random_seed": 123, "algorithm": "kmeans", "algorithm_version": "1.0"}'

# Nota: Repetir para cada par de semillas del experimento.
# La evaluación genera automáticamente:
#   - route_metrics: distancia y tiempo estimado por ruta
#   - metrics_summary: tiempo de ejecución
#   - output_path: mapa de overview y archivos CSV

# 7. Sincronizar experimentos (registra pares geodesic↔vial en Exp002)
docker compose exec backend php artisan experiments:sync

# 8. Verificar resultados
curl -s http://localhost:8000/api/experiments | python3 -c "
import sys,json
for exp in json.load(sys.stdin)['data']:
    if exp['identifier'] == '002-road-network':
        print('Exp002:', exp['name'])
        print('  Pares:', len(exp.get('evaluation_pairs', [])))
        print('  Estado:', exp.get('status', 'N/A'))
"
```

### Escenarios de validación

Ver `specs/006-road-network-integration/quickstart.md` para 8 escenarios de validación exhaustivos que cubren ruteo directo, métricas viales, retrocompatibilidad geodésica, reproducibilidad, y generación de mapas.

### Notas importantes

- **Puerto OSRM**: El servicio expone en `localhost:5001` (mapeado al puerto interno 5000). macOS reserva el 5000 para AirPlay.
- **Healthcheck**: El contenedor OSRM verifica cada 10s que puede rutearen local. Si falla 5 veces seguidas, Docker reinicia el contenedor.
- **Cache de descarga**: `download-osm.sh` cachea el archivo PBF de Chile (~200 MB) en el volumen `osrm-data`. Para forzar redescarga: `docker compose run --rm osrm-prepare rm -f /data/chile-latest.osm.pbf && make prepare-osrm`.
- **Tiempo de preprocesamiento**: ~7 minutos (extract + contract + partition + customize para algoritmo MLD).

## Estructura del proyecto

```
├── backend/               # API Laravel (PHP)
│   └── docker/osrm/       # Imágenes Docker OSRM (prepare + server)
│       ├── Dockerfile.prepare   # Herramientas de preprocesamiento
│       ├── Dockerfile           # Servidor OSRM runtime
│       ├── scripts/             # download-osm.sh, preprocess.sh
│       └── profiles/car.lua     # Perfil vehicular personalizado
├── frontend/              # App Next.js (TypeScript)
├── specs/                 # Especificaciones formales
│   ├── 001-last-mile-operation/
│   ├── 002-evaluation-metrics/
│   ├── 003-results-measurement/
│   ├── 004-experiment-reporting/
│   ├── 005-research-publication/
│   └── 006-road-network-integration/
├── experiments/           # Experimentos ejecutados
│   ├── 001-baseline-comparison/
│   └── 002-road-network/  # Exp002: geodesic vs vial (M001–M006)
├── research/              # Conocimiento acumulativo
├── publications/          # Activos de divulgación
├── Makefile               # prepare-osrm, up, down, logs
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
| POST | `/api/evaluations` | Crear evaluación (distance_mode: geodesic/vial) |
| GET | `/api/evaluations/{id}` | Resultados de una evaluación |
| GET | `/api/experiments` | Experimentos sincronizados con pares geodesic↔vial |

## Comandos Artisan disponibles

| Comando | Descripción |
|---------|-------------|
| `php artisan experiments:sync` | Sincroniza evaluaciones → experimentos (pares geodesic/vial) |

## Licencia

MIT
