# API Contract: Sistema de Medición, Evaluación y Validación de Resultados

Base URL: `http://localhost:8000/api`

Se añade un nuevo controlador `EvaluationController` con endpoints para
ejecutar evaluaciones y consultar resultados históricos.

---

## Evaluations

### Ejecutar evaluación

```
POST /api/evaluations
```

Ejecuta el sistema completo de métricas sobre los datos actuales (bodega,
rutas, entregas). Genera archivos exportados y registra metadata en BD.

**Request** (opcional — si no se envía, usa defaults):
```json
{
  "near_delivery_threshold_km": 1.0,
  "ignored_delivery_ratio": 2.0,
  "random_seed": 42,
  "algorithm": "kmeans",
  "algorithm_version": "1.0"
}
```

**Response**: `201 Created`
```json
{
  "id": 1,
  "executed_at": "2026-06-19T14:30:00Z",
  "parameters": {
    "near_delivery_threshold_km": 1.0,
    "ignored_delivery_ratio": 2.0,
    "random_seed": 42,
    "algorithm": "kmeans",
    "algorithm_version": "1.0",
    "warehouse_lat": -33.045,
    "warehouse_lng": -71.62
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
    "operational_penalty_total": 12.5
  },
  "output_path": "evaluations/20260619_143000",
  "files": {
    "json": "evaluations/20260619_143000/evaluation.json",
    "csv": "evaluations/20260619_143000/evaluation.csv",
    "deliveries_csv": "evaluations/20260619_143000/deliveries.csv",
    "maps": {
      "overview": "evaluations/20260619_143000/map_overview.png",
      "routes": [
        "evaluations/20260619_143000/map_route_1.png",
        "evaluations/20260619_143000/map_route_2.png"
      ],
      "anomalies": "evaluations/20260619_143000/map_anomalies.png"
    }
  },
  "route_metrics": [
    {
      "route_id": 1,
      "route_name": "Ruta A",
      "total_deliveries": 30,
      "min_distance_to_warehouse_km": 1.2,
      "max_distance_to_warehouse_km": 8.5,
      "avg_distance_to_warehouse_km": 4.3,
      "centroid_to_warehouse_km": 4.1,
      "cluster_radius_km": 3.2,
      "avg_distance_to_centroid_km": 1.8,
      "estimated_route_distance_km": 42.5
    }
  ],
  "anomalies": [
    {
      "delivery_id": 42,
      "route_id": 3,
      "distance_to_warehouse_km": 0.8,
      "centroid_distance_km": 6.5,
      "ratio": 8.1
    }
  ],
  "ranking": [
    {"rank": 1, "route_id": 1, "route_name": "Ruta A", "avg_distance_km": 4.3},
    {"rank": 2, "route_id": 2, "route_name": "Ruta B", "avg_distance_km": 5.1}
  ]
}
```

**Validación**:
- `near_delivery_threshold_km` debe ser un número decimal positivo (> 0)
- `ignored_delivery_ratio` debe ser un número decimal positivo (> 1.0)
- `random_seed` debe ser un entero (puede ser negativo o 0)

### Listar evaluaciones

```
GET /api/evaluations
```

Returns metadata de todas las evaluaciones ejecutadas, ordenadas por fecha
descendente. No incluye métricas detalladas (usar show para eso).

**Response**: `200 OK`
```json
{
  "data": [
    {
      "id": 1,
      "executed_at": "2026-06-19T14:30:00Z",
      "total_deliveries": 150,
      "total_routes": 5,
      "metrics_summary": {
        "coverage_territorial_km": 15.3,
        "balance_index": 1.8,
        "total_anomalias_detectadas": 2
      }
    }
  ]
}
```

### Obtener evaluación por ID

```
GET /api/evaluations/{id}
```

Returns la evaluación completa, incluyendo métricas por ruta, anomalías y
ranking (mismos campos que la respuesta de creación).

**Response**: `200 OK` (misma estructura que POST, sin los campos `files`)

### Descargar archivos de evaluación

```
GET /api/evaluations/{id}/files/{filename}
```

Sirve archivos exportados (JSON, CSV, PNG) desde el almacenamiento.

**Response**: Archivo solicitado con Content-Type apropiado

---

## Errors

| Código | Escenario |
|--------|-----------|
| 422 | Parámetros inválidos (threshold <= 0, ratio <= 1) |
| 404 | Evaluation ID no encontrado |
| 500 | Error en generación de mapas o exportación |

---

## Rate Limiting

No aplica (ejecución local en Docker, bajo demanda del investigador).
