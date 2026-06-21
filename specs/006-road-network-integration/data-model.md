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

Se agrega `execution_time_sec` al JSONB existente. M001–M006 no se incluyen por evaluación — se calculan exclusivamente en Exp002 a partir de pares experimentales.

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
  "execution_time_sec": 3.42
}
```

### Campo nuevo

| Campo | Tipo | Unidad | Descripción |
|-------|------|--------|-------------|
| execution_time_sec | float | s | Tiempo total de ejecución del pipeline (microtime) |

**Nota sobre M001–M006**: Estas métricas comparativas no están en `metrics_summary`. Se calculan en Exp002 a partir de pares de evaluaciones (geodésica + vial). M005 (Persistencia de Hallazgos) se calcula en el reporte de Exp002 como métrica de revalidación experimental.

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
  "baseline_reference": {
    "experiment_id": 1,
    "description": "Evaluaciones originales del experimento baseline (modo geodésico)"
  },
  "evaluation_pairs": [
    {
      "geodesic_id": 8,
      "vial_id": 14,
      "parameters_hash": "a1b2c3d4e5f6a7b8c9d0e1f2a3b4c5d6"
    }
  ],
  "author": "Sistema"
}
```

### parameters_hash — Definición

El `parameters_hash` garantiza que cada par geodésica↔vial comparte exactamente los mismos parámetros de entrada. Permite la vinculación sin depender de IDs fijos de evaluación.

**Campos incluidos (orden de normalización)**:
1. `random_seed` (int)
2. `algorithm` (string)
3. `algorithm_version` (string)
4. `near_delivery_threshold_km` (float)
5. `ignored_delivery_ratio` (float)
6. `dataset` (string)
7. `warehouse_lat` (float, 6 decimales)
8. `warehouse_lng` (float, 6 decimales)

**Campos excluidos**: `distance_mode`, timestamps, IDs internos, nombres de ruta, cualquier campo de salida o metadata de ejecución.

**Normalización**:
1. Serializar a JSON compacto (sin espacios, keys en orden alfabético del listado anterior).
2. Aplicar `md5()` sobre el string JSON resultante.
3. Representación hexadecimal (32 caracteres, lowercase).

**Ejemplo**:
```json
{"algorithm":"kmeans","algorithm_version":"1.0","dataset":"Valparaíso Demo","ignored_delivery_ratio":2.0,"near_delivery_threshold_km":1.0,"random_seed":42,"warehouse_lat":-33.045,"warehouse_lng":-71.62}
```
→ `md5` → `a1b2c3d4e5f6a7b8c9d0e1f2a3b4c5d6`

**Regla de igualdad**: Dos evaluaciones tienen los mismos parámetros si y solo si sus `parameters_hash` son idénticos. Esto permite emparejar evaluaciones geodésica↔vial sin depender de IDs de baseline.

### baseline_reference — Definición

El `baseline_reference` establece el origen histórico de los parámetros replicados. No es un enlace funcional (no se usan IDs para pairing), sino un registro de trazabilidad.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| experiment_id | int | ID del experimento que originó los parámetros baseline (Exp001) |
| description | string | Descripción textual del origen |

**Reglas**:
- Exp001 es fuente histórica: no se recalcula, no se modifica.
- Los parámetros se extraen de las evaluaciones de Exp001 para replicarlos en Exp002.
- `baseline_reference` es informativo; el pairing real se hace mediante `parameters_hash`.
- Si en el futuro se agrega un Exp003 con otros parámetros, su `baseline_reference` apuntaría a Exp002 (o al experimento que corresponda).

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
