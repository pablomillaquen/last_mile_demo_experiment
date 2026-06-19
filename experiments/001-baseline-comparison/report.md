# Experimento 001: Comparación de Evaluaciones (Baseline)

**Fecha**: 2026-06-19

**Dataset**: Valparaíso Demo — 300 entregas, 10 rutas (Ruta A×2, B×2, C×2, D×2, E×2)

**Algoritmo**: `manual-asignacion` v1.0 (asignación manual existente)

**Objetivo**: Establecer baseline cuantitativo antes de optimización.

## 1. Parámetros por Evaluación

| ID | Seed | Threshold (km) | Ratio | Anomalías | Penalidad | Avg Dist (km) | Cobertura (km) | StdDev | Balance Index | Inter-Cluster (km) |
|----|------|---------------|-------|-----------|-----------|---------------|----------------|--------|---------------|-------------------|
| 2 | 42 | 1 | 2 | 10 | 232.26 | 13.47 | 24.23 | 4.68 | 1.0000 | 0.0055 |
| 3 | 123 | 1 | 2 | 10 | 232.26 | 13.47 | 24.23 | 4.68 | 1.0000 | 0.0055 |
| 4 | 200 | 3 | 3 | 10 | 232.26 | 13.47 | 24.23 | 4.68 | 1.0000 | 0.0055 |
| 5 | 100 | 0.5 | 1.5 | 4 | 136.94 | 13.47 | 24.23 | 4.68 | 1.0000 | 0.0055 |
| 6 | 400 | 0.3 | 1.2 | 0 | 0.00 | 13.47 | 24.23 | 4.68 | 1.0000 | 0.0055 |
| 7 | 300 | 5 | 5 | 10 | 232.26 | 13.47 | 24.23 | 4.68 | 1.0000 | 0.0055 |

### Observaciones

1. **Métricas globales invariantes**: `distancia_promedio_general_km`, `coverage_territorial_km`, `desviacion_estandar_distancias_km`, `balance_general_cv`, `balance_index`, `inter_cluster_min_distance_km` **no cambian** entre ejecuciones. Son función exclusiva de la asignación de paquetes a rutas, no de los parámetros de detección de anomalías.
2. **Sensibilidad a parámetros de anomalía**: Solo `total_anomalias_detectadas` y `operational_penalty_total` varían con `threshold` y `ratio`.
3. **Umbral no discrimina**: ID 2, 3, 4 (threshold 1–3) detectan las mismas 10 anomalías. Todas las entregas cercanas están dentro de 1 km de bodega.
4. **Ratio filtra**: ID 5 (threshold=0.5, ratio=1.5) detecta solo 4 anomalías — solo 4 entregas están dentro de 0.5 km de bodega (IDs 48, 196, 197, 200), y todas superan el ratio 1.5.
5. **Umbral + Ratio**: ID 6 (threshold=0.3, ratio=1.2) no captura ninguna entrega (todas están a > 0.3 km de bodega), resultando en 0 anomalías, independiente del ratio.

## 2. Métricas por Ruta (referencia: Evaluación #2)

| Ruta | ID | Entregas | Min→Bodega | Max→Bodega | Avg→Bodega | Centroide→Bodega | Radio Cluster | Avg→Centroide | Dist. Ruta Est. |
|------|-----|----------|-----------|-----------|-----------|------------------|---------------|---------------|-----------------|
| Ruta C | 8 | 30 | 6.03 | 9.88 | 7.89 | 7.87 | 2.06 | 1.61 | 99.68 |
| Ruta C | 3 | 30 | 6.07 | 9.86 | 7.96 | 7.94 | 1.99 | 1.60 | 98.08 |
| Ruta B | 2 | 30 | 0.48 | 24.01 | 12.99 | 11.58 | 13.58 | 9.17 | 71.93 |
| Ruta B | 7 | 30 | 0.30 | 24.23 | 12.97 | 11.60 | 13.88 | 9.14 | 70.05 |
| Ruta E | 5 | 30 | 15.31 | 19.49 | 16.34 | 15.27 | 15.31 | 4.06 | 48.58 |
| Ruta E | 10 | 30 | 15.41 | 19.46 | 16.31 | 15.25 | 15.14 | 4.02 | 46.86 |
| Ruta A | 6 | 30 | 15.59 | 16.14 | 15.86 | 15.86 | 0.34 | 0.19 | 23.67 |
| Ruta D | 4 | 30 | 13.00 | 15.95 | 14.25 | 14.13 | 3.41 | 1.76 | 23.39 |
| Ruta D | 9 | 30 | 13.02 | 15.94 | 14.26 | 14.14 | 3.49 | 1.77 | 23.28 |
| Ruta A | 1 | 30 | 15.54 | 16.06 | 15.81 | 15.81 | 0.30 | 0.17 | 22.04 |

### Rutas Destacadas

- **Mejor ubicada**: Ruta C (prom. 7.89 km de bodega)
- **Peor ubicada**: Ruta E (prom. 16.34 km de bodega)
- **Más dispersa**: Ruta E (radio 15.31 km)
- **Más compacta**: Ruta A (radio 0.2982 km)
- **Más larga**: Ruta C (99.68 km)
- **Más corta**: Ruta A (22.04 km)

**Totales**: 527.57 km acumulados | Promedio 52.76 km/ruta | 300 entregas | 30 entregas/ruta promedio

## 3. Anomalías Detectadas (Evaluación #2, threshold=1, ratio=2)

| Delivery ID | Ruta | Dist. Bodega (km) | Dist. Centroide (km) | Ratio |
|-------------|------|-------------------|---------------------|-------|
| 197 | Ruta 7 | 0.3010 | 11.6016 | 38.54 |
| 200 | Ruta 7 | 0.3019 | 11.6016 | 38.43 |
| 196 | Ruta 7 | 0.3255 | 11.6016 | 35.64 |
| 48 | Ruta 2 | 0.4759 | 11.5775 | 24.33 |
| 199 | Ruta 7 | 0.5328 | 11.6016 | 21.77 |
| 46 | Ruta 2 | 0.6546 | 11.5775 | 17.69 |
| 49 | Ruta 2 | 0.7018 | 11.5775 | 16.50 |
| 47 | Ruta 2 | 0.8262 | 11.5775 | 14.01 |
| 50 | Ruta 2 | 0.8489 | 11.5775 | 13.64 |
| 198 | Ruta 7 | 0.9907 | 11.6016 | 11.71 |

- Promedio ratio: 23.23
- Ratio mínimo: 11.71
- Ratio máximo: 38.54
- Penalidad total: 232.26
- Las 10 anomalías pertenecen a rutas B (route_id 2 y 7) — las únicas con entregas cerca de bodega

## 4. Evolución entre Evaluaciones

| ID | Seed | Threshold | Ratio | Anomalías | Penalidad | Diferencia vs Eval #2 |
|----|------|-----------|-------|-----------|-----------|----------------------|
| 2 | 42 | 1 | 2 | 10 | 232.26 | Anomalías: 0, Penalidad: 0.00 |
| 3 | 123 | 1 | 2 | 10 | 232.26 | Anomalías: 0, Penalidad: 0.00 |
| 4 | 200 | 3 | 3 | 10 | 232.26 | Anomalías: 0, Penalidad: 0.00 |
| 5 | 100 | 0.5 | 1.5 | 4 | 136.94 | Anomalías: -6, Penalidad: -95.32 |
| 6 | 400 | 0.3 | 1.2 | 0 | 0.00 | Anomalías: -10, Penalidad: -232.26 |
| 7 | 300 | 5 | 5 | 10 | 232.26 | Anomalías: 0, Penalidad: 0.00 |

## 5. Ranking de Rutas por Cercanía a Bodega

| Rank | Ruta | Avg Dist Bodega (km) |
|------|------|---------------------|
| 1 | Ruta C | 7.89 |
| 2 | Ruta C | 7.96 |
| 3 | Ruta B | 12.97 |
| 4 | Ruta B | 12.99 |
| 5 | Ruta D | 14.25 |
| 6 | Ruta D | 14.26 |
| 7 | Ruta A | 15.81 |
| 8 | Ruta A | 15.86 |
| 9 | Ruta E | 16.31 |
| 10 | Ruta E | 16.34 |

## 6. Conclusiones

1. **Baseline estable**: Todas las métricas de ruta y globales son deterministas e invariantes entre ejecuciones (no hay aleatoriedad involucrada).
2. **Anomalías sensibles a ratio**: `ignored_delivery_ratio` es el parámetro más discriminatorio. El threshold solo define el perímetro de búsqueda.
3. **Distribución heterogénea**: Rutas B y E tienen clusters muy dispersos (radio > 13 km) vs Rutas A (radio < 0.3 km). Oportunidad de optimización evidente.
4. **Oportunidad identificada**: Las 10 entregas cercanas a bodega (< 1 km) están todas en rutas B. Podrían rediseñarse rutas exprés locales para eliminarlas de rutas largas.
5. **Penalidad operacional**: 232.26 es la penalidad base (threshold=1, ratio=2). Con threshold=0.5 baja a 136.94 (−41%). Con threshold=0.3 desaparece.
6. **Superposición de clusters**: La distancia inter-cluster mínima es 5.5 m (entre las dos Rutas A), indicando que los pares del mismo nombre tienen centroides casi idénticos.

