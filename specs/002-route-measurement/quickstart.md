# Quickstart: Route Measurement

**Validación de que la Fase 2 funciona end-to-end**.

## Prerequisitos

- Docker Compose funcionando (`docker compose up -d`)
- Base de datos migrada y seeders ejecutados
- Frontend accesible en `http://localhost:3000`
- API accesible en `http://localhost:8000/api`

## Setup inicial

```bash
# Migraciones + seeders completos
docker compose exec backend php artisan migrate:fresh --seed

# Seeders adicionales (paquetes + rutas + asignaciones aleatorias)
docker compose exec backend php artisan db:seed --class=DemoDatasetSeeder
docker compose exec backend php artisan db:seed --class=RandomAssignmentSeeder

# Verificar métricas
curl -s http://localhost:8000/api/metrics | python3 -m json.tool
```

## Escenario de validación 1: Configuración global

**Objetivo**: Verificar que bodega y velocidad se pueden configurar.

```bash
# Ver valores iniciales
curl -s http://localhost:8000/api/settings | python3 -m json.tool

# Actualizar velocidad
curl -s -X PUT http://localhost:8000/api/settings \
  -H 'Content-Type: application/json' \
  -d '{"warehouse_lat": "-33.0450000", "warehouse_lng": "-71.6200000", "average_speed_kmh": "40"}' \
  | python3 -m json.tool

# Verificar que cambió
curl -s http://localhost:8000/api/settings | python3 -m json.tool
```

**Esperado**:
- `average_speed_kmh` cambia a `"40"`
- El tiempo estimado en las rutas se recalcula con la nueva velocidad

## Escenario de validación 2: Métricas de ruta

**Objetivo**: Verificar que cada ruta expone distancia y tiempo.

```bash
# Obtener detalle de una ruta
curl -s http://localhost:8000/api/routes/1 | python3 -m json.tool
```

**Esperado**:
- `total_distance_km` > 0
- `avg_distance_per_delivery_km` > 0
- `estimated_time` en formato `Xh Ym`
- `packages` incluye `sequence` numérico

## Escenario de validación 3: Dashboard con nuevas métricas

**Objetivo**: Verificar que el dashboard muestra distancia y tiempo.

```bash
curl -s http://localhost:8000/api/metrics | python3 -m json.tool
```

**Esperado**:
- `route_metrics.longest_route` con `name`, `total_distance_km`, `estimated_time`
- `route_metrics.shortest_route` con los mismos campos
- Rutas vacías no aparecen como shortest

## Escenario de validación 4: Mapa con polylines y secuencia

**Objetivo**: Verificar visualización en el mapa.

1. Abrir `http://localhost:3000/map`
2. Confirmar que se ve un marcador de bodega (ícono distintivo)
3. Confirmar que cada ruta tiene una línea poligonal de color distinto
4. Confirmar que los paquetes muestran número de secuencia (1, 2, 3...)
5. Confirmar que las líneas conectan: bodega → P1 → P2 → ... → PN → bodega

## Escenario de validación 5: Asignación dinámica

**Objetivo**: Verificar que al asignar/desasignar se actualizan métricas y mapa.

1. Abrir `http://localhost:3000/routes/1`
2. Confirmar que la tabla de paquetes muestra números de secuencia
3. Confirmar que se muestra distancia total y tiempo estimado
4. Asignar un paquete nuevo a la ruta
5. Confirmar que la secuencia, distancia y tiempo se actualizan
6. Desasignar un paquete
7. Confirmar que los valores se recalculan

## Limpieza

```bash
docker compose exec backend php artisan migrate:fresh --seed
```
