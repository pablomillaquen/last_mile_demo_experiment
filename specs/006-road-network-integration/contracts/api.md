# API Contract: Evaluations con modo de distancia

Base URL: `http://localhost:8000/api`

Se extiende el contrato existente de EvaluationController para soportar el modo de distancia (geodésico/vial) y las nuevas métricas M001–M006.

---

## POST /api/evaluations

Ejecuta el sistema completo de métricas sobre los datos actuales, ahora con soporte para dos modos de distancia.

### Request (extensión)

```json
{
  "near_delivery_threshold_km": 1.0,
  "ignored_delivery_ratio": 2.0,
  "distance_mode": "vial",
  "random_seed": 42,
  "algorithm": "kmeans",
  "algorithm_version": "1.0"
}
```

**Campo nuevo**: `distance_mode` — opcional. Valores: `"geodesic"` (default) o `"vial"`. Si no se envía, usa el valor de `config/evaluation.php` (default: `"geodesic"`).

### Response: `201 Created`

```json
{
  "id": 8,
  "executed_at": "2026-06-20T14:30:00Z",
  "mode": "vial",
  "parameters": {
    "distance_mode": "vial",
    "near_delivery_threshold_km": 1.0,
    "ignored_delivery_ratio": 2.0,
    "random_seed": 42,
    "algorithm": "kmeans",
    "algorithm_version": "1.0",
    "warehouse_lat": -33.045,
    "warehouse_lng": -71.62,
    "dataset": "Valparaíso Demo"
  },
  "total_deliveries": 150,
  "total_routes": 5,
  "metrics_summary": {
    "coverage_territorial_km": 15.3,
    "distancia_promedio_general_km": 7.2,
    "desviacion_estandar_distancias_km": 3.1,
    "balance_general_cv": 0.45,
    "balance_index": 1.8,
    "total_anomalias_detectadas": 2,
    "inter_cluster_min_distance_km": 2.1,
    "operational_penalty_total": 12.5,
    "execution_time_sec": 3.42,
    "error_geodesico_medio_km": null,
    "factor_desvio_promedio": null,
    "error_maximo_trayecto_km": null,
    "variacion_ranking": null,
    "distorsion_territorial": null
  },
  "output_path": "evaluations/20260620_143000",
  "route_metrics": [
    {
      "route_id": 1,
      "route_name": "Ruta A",
      "total_deliveries": 30,
      "min_distance_to_warehouse_km": 1.2,
      "max_distance_to_warehouse_km": 8.5,
      "avg_distance_to_warehouse_km": 4.3,
      "centroid_lat": -33.04,
      "centroid_lng": -71.61,
      "centroid_to_warehouse_km": 4.1,
      "cluster_radius_km": 3.2,
      "avg_distance_to_centroid_km": 1.8,
      "estimated_route_distance_km": 48.2,
      "estimated_time_min": 45.0
    }
  ],
  "anomalies": [],
  "ranking": [
    {"rank": 1, "route_id": 1, "route_name": "Ruta A", "avg_distance_km": 4.3},
    {"rank": 2, "route_id": 2, "route_name": "Ruta B", "avg_distance_km": 5.1}
  ],
  "files": {
    "json": "evaluations/20260620_143000/evaluation.json",
    "csv": "evaluations/20260620_143000/evaluation.csv",
    "deliveries_csv": "evaluations/20260620_143000/deliveries.csv",
    "maps": {
      "overview": "evaluations/20260620_143000/map_overview.png",
      "routes": ["evaluations/20260620_143000/map_route_ruta-a.png"],
      "anomalies": null
    }
  }
}
```

**Notas sobre la respuesta**:
- Los campos `error_geodesico_medio_km` a `distorsion_territorial` son `null` si `mode = "geodesic"` (no hay nada que comparar).
- `execution_time_sec` está presente en ambos modos y registra el tiempo total del pipeline.
- M005 (Persistencia de Hallazgos) no está en metrics_summary; se calcula en reporte de Exp002.
- `estimated_time_min` aparece solo en modo vial.
- `estimated_route_distance_km` refleja el modo seleccionado (geodésico = suma Haversine, vial = suma OSRM).

### Validación adicional

| Campo | Regla |
|-------|-------|
| `distance_mode` | Must be `"geodesic"` or `"vial"`. Default: config value. |

## GET /api/evaluations

Sin cambios. La respuesta incluye `parameters` con `distance_mode` para cada evaluación.

## GET /api/evaluations/{id}

Sin cambios estructurales. Incluye `mode` y `estimated_time_min` si aplica.

## GET /api/evaluations/{id}/files/{filename}

Sin cambios.

## GET /api/evaluations/{id}/pdf

Sin cambios. El PDF incluirá las nuevas métricas si están presentes.

## Errors

| Código | Escenario |
|--------|-----------|
| 422 | `distance_mode` inválido (no es "geodesic" ni "vial") |
| 422 | Parámetros estándar inválidos (threshold <= 0, ratio <= 1) |
| 404 | Evaluation ID no encontrado |
| 500 | Error en OSRM (caído), generación de mapas o exportación |
| 503 | OSRM no disponible (modo vial requiere OSRM corriendo) |
