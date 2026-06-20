# Data Model: Incorporación de Red Vial Real y Revalidación Experimental

## DistanceResult

DTO que encapsula el resultado de un cálculo de distancia, usado por `DistanceService` y `MetricsCalculatorService`.

| Campo | Tipo | Unidad | Descripción |
|-------|------|--------|-------------|
| distance_km | float\|null | km | Distancia entre los dos puntos. `null` si no se pudo calcular. |
| duration_min | float\|null | min | Tiempo estimado de viaje. `null` si no se pudo calcular. |
| mode | string | — | `"geodesic"` o `"vial"` |
| metadata | array\|null | — | Información adicional (error details, waypoints opcional) |

**Validación**: `distance_km` >= 0, `duration_min` >= 0. Si OSRM está caído o las coordenadas están fuera de rango, ambos son `null` y `metadata.error` contiene el mensaje.

## DistanceMode

Configuración del modo de operación del motor de evaluación.

| Valor | Comportamiento |
|-------|---------------|
| `"geodesic"` | Usa HaversineService (comportamiento actual) |
| `"vial"` | Usa OsrmClient sobre OSM/OSRM |

Se almacena en `Evaluation.parameters.distance_mode`. Default: `"geodesic"`.

## Evaluation.parameters (extensión)

Se agrega `distance_mode` al JSONB existente:

```json
{
  "distance_mode": "geodesic",
  "random_seed": 42,
  "algorithm": "kmeans",
  "algorithm_version": "1.0",
  "near_delivery_threshold_km": 1.0,
  "ignored_delivery_ratio": 2.0,
  "dataset": "Valparaíso Demo",
  "warehouse_lat": -33.045,
  "warehouse_lng": -71.62
}
```

**Default**: `"geodesic"` — retrocompatible con evaluaciones existentes (RNF3).

## metrics_summary (extensión)

Se agregan campos M001–M006 al JSONB existente:

```json
{
  "coverage_territorial_km": 15.3,
  "distancia_promedio_general_km": 7.2,
  "desviacion_estandar_distancias_km": 3.1,
  "balance_general_cv": 0.45,
  "balance_index": 1.8,
  "total_anomalias_detectadas": 2,
  "inter_cluster_min_distance_km": 2.1,
  "operational_penalty_total": 12.5,
  "error_geodesico_medio_km": 2.1,
  "factor_desvio_promedio": 1.25,
  "error_maximo_trayecto_km": 5.8,
  "variacion_ranking": 3,
  "persistencia_hallazgos_pct": 83.3,
  "distorsion_territorial": {
    "por_ruta": [
      {"route_id": 1, "route_name": "Ruta A", "indice": 1.15, "zona": "eficiente", "d_geodesica_km": 8.5, "d_vial_km": 9.8},
      {"route_id": 2, "route_name": "Ruta B", "indice": 2.40, "zona": "critica", "d_geodesica_km": 5.2, "d_vial_km": 12.5}
    ],
    "por_punto": [
      {"delivery_id": 101, "route_id": 2, "indice": 4.1, "zona": "critica"}
    ],
    "zonas_criticas": ["sector_cerro_alegre", "sector_placeres"]
  }
}
```

### Descripción de nuevos campos

| Campo | Tipo | Unidad | Descripción |
|-------|------|--------|-------------|
| error_geodesico_medio_km | float\|null | km | M001 — avg(d_vial − d_geodésica) |
| factor_desvio_promedio | float\|null | — | M002 — avg(d_vial / d_geodésica) |
| error_maximo_trayecto_km | float\|null | km | M003 — max(d_vial − d_geodésica) |
| variacion_ranking | int\|null | cambios | M004 — cuántas posiciones cambiaron en el ranking |
| persistencia_hallazgos_pct | float\|null | % | M005 — hallazgos_válidos / hallazgos_totales × 100 |
| distorsion_territorial | object\|null | — | M006 — índice por ruta y por punto |

`null` cuando la evaluación es geodésica (no hay comparación posible).

## M006 — Índice de Distorsión Territorial

### Cálculo

```
M006_punto = d_vial / d_geodésica
M006_ruta  = avg(M006_punto para todos los puntos de la ruta)
```

### Interpretación

| Rango | Clasificación | Acción |
|-------|---------------|--------|
| 1.0 – 1.2 | Normal | Diferencia esperada por trazado vial |
| 1.2 – 1.5 | Elevada | Topografía urbana afectando |
| 1.5 – 2.0 | Alta | Barrera geográfica presente |
| > 2.0 | Crítica | Distorsión severa — investigar |

## RouteMetrics (extensión)

Se agregan campos al DTO existente:

| Campo | Tipo | Unidad | Descripción |
|-------|------|--------|-------------|
| estimated_route_distance_km | float | km | Distancia total según modo actual |
| estimated_time_min | float\|null | min | Tiempo estimado (solo modo vial) |
| distortion_index | float\|null | — | M006 por ruta (solo comparación) |

El resto de los campos (`route_id`, `route_name`, `total_deliveries`, `min_distance_to_warehouse_km`, etc.) permanecen idénticos pero sus valores reflejan el modo de distancia seleccionado.

## OsrmClient — Schema interno

### Request

```
GET /route/v1/driving/{lng1},{lat1};{lng2},{lat2}
  ?overview=false
  &steps=false
  &alternatives=false
```

### Response parseada

```json
{
  "code": "Ok",
  "routes": [
    {
      "distance": 1234.5,
      "duration": 92.3
    }
  ]
}
```

### Error Response

```json
{
  "code": "NoRoute",
  "message": "No route found between coordinates"
}
```

## Experiment 002

Extensión de la entidad Experiment existente (estructura `experiment.json`):

```json
{
  "identifier": "002-road-network",
  "name": "Comparación Geodésica vs Vial",
  "objective": "Cuantificar el impacto de reemplazar distancias geodésicas por distancias sobre red vial real en las métricas operacionales del sistema.",
  "hypothesis": "H1: La red vial modifica significativamente las métricas operacionales.",
  "baseline_evaluation_id": null,
  "evaluation_ids": [8, 9, 10, 11, 12, 13],
  "author": "Sistema"
}
```

## Relaciones

```
DistanceService (1)
    ├─ impl -> HaversineService  (mode=geodesic)
    └─ impl -> OsrmClient        (mode=vial)

MeasurementService (1) ── injects ──> DistanceService (1)
                                             |
                                      MetricsCalculatorService
                                      (recibe DistanceService en lugar de HaversineService directo)

Routes (1) ──> Waypoints (N) ──> OsrmClient.route(lng,lat;lng,lat)
```

No hay nuevas migraciones de base de datos. Evaluation usa `parameters` JSONB existente. No se requieren nuevas tablas ni columnas.
