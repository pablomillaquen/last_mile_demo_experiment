# Quickstart: Sistema de Medición, Evaluación y Validación de Resultados

Escenarios de validación para verificar que el sistema de métricas funciona
correctamente de extremo a extremo.

## Prerrequisitos

- Docker Compose ejecutándose (`docker compose up -d`)
- Datos de demostración cargados (seeders de Fase 1 y Fase 2)
- Backend accesible en `http://localhost:8000`

## Escenario 1: Evaluación básica

Ejecutar el sistema de métricas con parámetros por defecto.

```bash
curl -s -X POST http://localhost:8000/api/evaluations \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" | json_pp
```

**Resultado esperado**: `201 Created` con:
- `total_deliveries` > 0
- `total_routes` > 0
- `route_metrics` con todas las rutas existentes
- Cada ruta con campos de métricas poblados (distancia mín, máx, promedio, centroide, radio, compactación, distancia total estimada)
- `metrics_summary` con `inter_cluster_min_distance_km` y `operational_penalty_total`
- `parameters` con `algorithm` y `algorithm_version`
- Valores en km coherentes (Valparaíso: esperar 0.5–20 km)

## Escenario 2: Verificar detección de anomalías

Ejecutar con un umbral muy bajo para forzar detecciones.

```bash
curl -s -X POST http://localhost:8000/api/evaluations \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"near_delivery_threshold_km": 3.0, "ignored_delivery_ratio": 1.5}' | json_pp
```

**Resultado esperado**:
- `anomalies` con al menos 1 entrega detectada
- Cada anomalía tiene `delivery_id`, `route_id`, `ratio` > `ignored_delivery_ratio`
- `total_anomalias_detectadas` en `metrics_summary` coincide con count de anomalies

## Escenario 3: Ranking de rutas

La respuesta debe incluir un `ranking` ordenado de menor a mayor distancia
promedio a bodega.

```bash
curl -s http://localhost:8000/api/evaluations/1 | \
  json_pp | grep -A 20 '"ranking"'
```

**Resultado esperado**:
- Array ordenado ascendentemente por `avg_distance_km`
- Cada entry tiene `rank`, `route_id`, `route_name`, `avg_distance_km`
- Sin saltos en la numeración (1, 2, 3, ...)

## Escenario 4: Indicadores globales consistentes

Verificar que los indicadores globales sean coherentes con las métricas por ruta.

```bash
# Obtener la última evaluación
LAST_ID=$(curl -s http://localhost:8000/api/evaluations | json_pp | grep '"id"' | head -1 | grep -oP '\d+')
curl -s "http://localhost:8000/api/evaluations/$LAST_ID" | json_pp
```

**Resultado esperado**:
- `coverage_territorial_km` >= todas las `max_distance_to_warehouse_km`
- `balance_index` >= 1.0
- `total_anomalias_detectadas` >= 0

## Escenario 5: Exportación de archivos

Verificar que los archivos exportados existan y sean accesibles.

```bash
# Obtener output_path de la última evaluación
LAST_ID=$(curl -s http://localhost:8000/api/evaluations | json_pp | grep '"id"' | head -1 | grep -oP '\d+')
EVAL=$(curl -s "http://localhost:8000/api/evaluations/$LAST_ID")
OUTPUT_PATH=$(echo "$EVAL" | json_pp | grep '"output_path"' | cut -d'"' -f4)

# Verificar que se pueden descargar
curl -s -o /dev/null -w "%{http_code}" "http://localhost:8000/api/evaluations/$LAST_ID/files/evaluation.json"
curl -s -o /dev/null -w "%{http_code}" "http://localhost:8000/api/evaluations/$LAST_ID/files/evaluation.csv"
curl -s -o /dev/null -w "%{http_code}" "http://localhost:8000/api/evaluations/$LAST_ID/files/deliveries.csv"
curl -s -o /dev/null -w "%{http_code}" "http://localhost:8000/api/evaluations/$LAST_ID/files/map_overview.png"
```

**Resultado esperado**: Todos los códigos HTTP = 200

## Escenario 6: Reproducibilidad

Ejecutar la misma evaluación dos veces con el mismo `random_seed`. Dado que
las entregas y rutas no cambian entre ejecuciones, los resultados deben ser
idénticos.

```bash
RES1=$(curl -s -X POST http://localhost:8000/api/evaluations \
  -H "Content-Type: application/json" \
  -d '{"random_seed": 12345}' | json_pp)

RES2=$(curl -s -X POST http://localhost:8000/api/evaluations \
  -H "Content-Type: application/json" \
  -d '{"random_seed": 12345}' | json_pp)

diff <(echo "$RES1" | jq 'del(.id, .executed_at, .output_path, .files)') \
     <(echo "$RES2" | jq 'del(.id, .executed_at, .output_path, .files)')
```

**Resultado esperado**: Sin diferencias (salvo id, executed_at, output_path).
