# Data Model: Sistema de Medición, Evaluación y Validación de Resultados

Se añade una entidad `Evaluation` para metadatos de ejecuciones y se definen
las estructuras de datos de las métricas calculadas (no persistidas en BD,
solo exportadas a archivos).

## Evaluation

Representa una ejecución del sistema de métricas. Almacena metadatos; los datos
detallados (métricas por ruta, mapas) se exportan como archivos.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | bigint (PK) | Identificador único autoincremental |
| executed_at | timestamp | Fecha y hora de la ejecución |
| parameters | jsonb | Parámetros de la ejecución (ver sección Parámetros) |
| total_deliveries | integer | Total de entregas procesadas |
| total_routes | integer | Total de rutas evaluadas |
| metrics_summary | jsonb | Resumen de indicadores globales |
| output_path | string(255) | Ruta relativa a los archivos exportados |
| created_at | timestamp | Fecha de creación del registro |

**Validación**:
- `total_deliveries` >= 0
- `total_routes` >= 1 (se requieren al menos entregas asignadas)
- `output_path` debe ser una ruta válida dentro de `storage/app/evaluations/`

### Parámetros (JSONB)

```json
{
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

### Metrics Summary (JSONB)

```json
{
  "coverage_territorial_km": 15.3,
  "distancia_promedio_general_km": 7.2,
  "desviacion_estandar_distancias_km": 3.1,
  "balance_general_cv": 0.45,
  "balance_index": 1.8,
  "total_anomalias_detectadas": 2,
  "inter_cluster_min_distance_km": 2.1,
  "operational_penalty_total": 12.5
}
```

## Estructuras de Datos de Métricas (no persistidas)

Estas estructuras se calculan en memoria y se exportan a archivos JSON/CSV.

### RouteMetrics

Métricas calculadas por cada ruta:

| Campo | Tipo | Unidad | Descripción |
|-------|------|--------|-------------|
| route_id | integer | — | Identificador de la ruta |
| route_name | string | — | Nombre de la ruta |
| total_deliveries | integer | conteo | Cantidad de entregas asignadas |
| min_distance_to_warehouse_km | decimal | km | Distancia bodega → punto más cercano |
| max_distance_to_warehouse_km | decimal | km | Distancia bodega → punto más lejano |
| avg_distance_to_warehouse_km | decimal | km | Distancia promedio a la bodega |
| centroid_lat | decimal | — | Latitud del centroide |
| centroid_lng | decimal | — | Longitud del centroide |
| centroid_to_warehouse_km | decimal | km | Distancia centroide → bodega |
| cluster_radius_km | decimal | km | Distancia centroide → punto más lejano |
| avg_distance_to_centroid_km | decimal | km | Distancia promedio al centroide |
| estimated_route_distance_km | decimal | km | Distancia total bodega → última entrega |

### AnomalyReport

Casos de entregas cercanas ignoradas:

| Campo | Tipo | Descripción |
|-------|------|-------------|
| delivery_id | integer | ID de la entrega |
| route_id | integer | ID de la ruta asignada |
| distance_to_warehouse_km | decimal | Distancia entrega → bodega |
| centroid_distance_km | decimal | Distancia centroide ruta → bodega |
| ratio | decimal | centroid_distance / distance_to_warehouse |

### GlobalIndicators

Indicadores consolidados del experimento:

| Campo | Tipo | Unidad | Descripción |
|-------|------|--------|-------------|
| coverage_territorial_km | decimal | km | Max distancia entrega-bodega |
| distancia_promedio_general_km | decimal | km | Promedio de todas las distancias |
| desviacion_estandar_distancias_km | decimal | km | Desviación estándar |
| balance_general_cv | decimal | — | Coeficiente de variación (std/mean) |
| balance_index | decimal | — | max_entregas / min_entregas |
| inter_cluster_min_distance_km | decimal | km | Distancia mínima entre centroides de rutas distintas |
| operational_penalty_total | decimal | — | Suma de penalizaciones por anomalías (centroid_distance / delivery_distance), adimensional |

### DeliveryMetrics

Datos individuales por entrega, exportados en `deliveries.csv`:

| Campo | Tipo | Unidad | Descripción |
|-------|------|--------|-------------|
| delivery_id | integer | — | ID de la entrega |
| route_id | integer | — | ID de la ruta asignada |
| latitude | decimal | — | Latitud de la entrega |
| longitude | decimal | — | Longitud de la entrega |
| distance_to_warehouse_km | decimal | km | Distancia de la entrega a la bodega |
| distance_to_centroid_km | decimal | km | Distancia de la entrega al centroide de su ruta |

## Relaciones

```
Evaluation (1) — (no relations directas, solo metadatos)

Settings (1 per key) — lectura de parámetros de bodega y umbrales
Route (1) ──── (N) RoutePackage (N) ──── (1) Package
                                     |
                               (sequence define orden)
```

Las métricas se calculan a partir de los datos existentes:
- `Package`: lat, lng, route_id (vía RoutePackage)
- `Route`: id, name, packages asignados
- `Setting`: warehouse_lat, warehouse_lng (bodega)

## Migraciones

```sql
CREATE TABLE evaluations (
    id BIGSERIAL PRIMARY KEY,
    executed_at TIMESTAMP NOT NULL DEFAULT NOW(),
    parameters JSONB NOT NULL DEFAULT '{}',
    total_deliveries INTEGER NOT NULL DEFAULT 0,
    total_routes INTEGER NOT NULL DEFAULT 0,
    metrics_summary JSONB NOT NULL DEFAULT '{}',
    output_path VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT NOW()
);

CREATE INDEX idx_evaluations_executed_at ON evaluations(executed_at);
```
