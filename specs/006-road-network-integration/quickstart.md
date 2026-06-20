# Quickstart: Red Vial y Revalidación Experimental

Escenarios de validación para verificar que la integración OSM/OSRM y el modo vial funcionan correctamente de extremo a extremo.

## Prerrequisitos

- Docker Compose ejecutándose: `docker compose up -d`
- OSRM build completado y respondiendo: `docker compose logs osrm` debe mostrar servicio activo
- Backend accesible: `curl http://localhost:8000/api/metrics`
- Evaluaciones baseline IDs 2–7 existentes (Experimento 001)
- `experiments:sync` disponible: `docker compose exec backend php artisan experiments:sync`

## Escenario 1: OSRM responde a ruteo directo

```bash
curl -s "http://localhost:5000/route/v1/driving/-71.62,-33.045;-71.61,-33.05?overview=false&steps=false"
```

**Resultado esperado**:
```json
{"code":"Ok","routes":[{"distance":1234.5,"duration":92.3,"legs":[...]}]}
```
- `code` = "Ok"
- `distance` > 0 (metros)
- `duration` > 0 (segundos)

## Escenario 2: Evaluación vial produce resultados

```bash
curl -s -X POST http://localhost:8000/api/evaluations \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"distance_mode": "vial"}' | python3 -m json.tool
```

**Resultado esperado**:
- HTTP `201 Created`
- `mode` = `"vial"`
- `parameters.distance_mode` = `"vial"`
- `route_metrics[0].estimated_route_distance_km` ≠ `estimated_route_distance_km` de una evaluación geodésica con mismos parámetros
- `route_metrics[0].estimated_time_min` > 0 (nuevo campo)

## Escenario 3: Modo geodésico sigue funcionando idéntico (retrocompatibilidad)

```bash
# Evaluación geodésica actual
GEO=$(curl -s -X POST http://localhost:8000/api/evaluations \
  -H "Content-Type: application/json" \
  -d '{"distance_mode": "geodesic", "random_seed": 42, "algorithm": "kmeans", "algorithm_version": "1.0"}')

# Obtener evaluation ID 2 (baseline original)
BASE=$(curl -s http://localhost:8000/api/evaluations/2)

# Comparar estimated_route_distance_km para ruta 1
echo "$GEO" | python3 -c "import sys,json; d=json.load(sys.stdin); print([r['estimated_route_distance_km'] for r in d['route_metrics'] if r['route_id']==1])"
```

**Resultado esperado**: Mismas distancias que la evaluación original (retrocompatibilidad total).

## Escenario 4: Nuevas métricas M001–M006 aparecen en modo vial

```bash
# Obtener última evaluación vial
EVAL_ID=$(curl -s http://localhost:8000/api/evaluations | python3 -c "import sys,json; evals=json.load(sys.stdin)['data']; print(evals[0]['id'])")
curl -s "http://localhost:8000/api/evaluations/$EVAL_ID" | python3 -c "
import sys,json
d=json.load(sys.stdin)
ms=d['metrics_summary']
print('M001 error_geodesico_medio_km:', ms.get('error_geodesico_medio_km'))
print('M002 factor_desvio_promedio:', ms.get('factor_desvio_promedio'))
print('M003 error_maximo_trayecto_km:', ms.get('error_maximo_trayecto_km'))
print('M004 variacion_ranking:', ms.get('variacion_ranking'))
print('M005 persistencia_hallazgos_pct:', ms.get('persistencia_hallazgos_pct'))
print('M006 distorsion_territorial:', ms.get('distorsion_territorial'))
"
```

**Resultado esperado**: 6 métricas pobladas (M006 puede ser parcial si no se ha ejecutado Exp002 completo).

## Escenario 5: Reproducibilidad vial

```bash
RES1=$(curl -s -X POST http://localhost:8000/api/evaluations \
  -H "Content-Type: application/json" \
  -d '{"distance_mode": "vial", "random_seed": 12345}')
sleep 2
RES2=$(curl -s -X POST http://localhost:8000/api/evaluations \
  -H "Content-Type: application/json" \
  -d '{"distance_mode": "vial", "random_seed": 12345}')

diff <(echo "$RES1" | python3 -c "
import sys,json
d=json.load(sys.stdin)
del d['id'], d['executed_at'], d['output_path'], d['files']
print(json.dumps(d, sort_keys=True))
") <(echo "$RES2" | python3 -c "
import sys,json
d=json.load(sys.stdin)
del d['id'], d['executed_at'], d['output_path'], d['files']
print(json.dumps(d, sort_keys=True))
")
```

**Resultado esperado**: Sin diferencias (salvo id, executed_at, output_path).

## Escenario 6: Modo geodésico por defecto (config)

```bash
# Sin enviar distance_mode, debe usar geodesic
curl -s -X POST http://localhost:8000/api/evaluations \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" | python3 -c "
import sys,json
d=json.load(sys.stdin)
print('mode:', d.get('mode'))
print('distance_mode:', d['parameters'].get('distance_mode'))
"
```

**Resultado esperado**: `mode = "geodesic"`, `distance_mode = "geodesic"`.

## Escenario 7: Experiment 002 se registra

```bash
# Sincronizar experimentos
docker compose exec backend php artisan experiments:sync

# Verificar que Exp002 aparece
curl -s http://localhost:8000/api/experiments | python3 -c "
import sys,json
for exp in json.load(sys.stdin)['data']:
    if exp['identifier'] == '002-road-network':
        print('Found:', exp['name'])
        print('Evaluations:', exp.get('evaluation_ids', []))
"
```

**Resultado esperado**: Experiment 002 listado con evaluation_ids poblados.

## Escenario 8: Mapa de distorsión territorial

```bash
# Generar evaluación vial
RESP=$(curl -s -X POST http://localhost:8000/api/evaluations \
  -H "Content-Type: application/json" \
  -d '{"distance_mode": "vial"}')
EVAL_ID=$(echo "$RESP" | python3 -c "import sys,json; print(json.load(sys.stdin)['id'])")

# Verificar que se puede descargar el mapa
curl -s -o /dev/null -w "%{http_code}" "http://localhost:8000/api/evaluations/$EVAL_ID/files/map_overview.png"
```

**Resultado esperado**: HTTP 200 — el mapa se generó correctamente en modo vial.
