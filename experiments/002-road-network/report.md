# Exp002: Comparación Geodésica vs Vial

## Objetivo
Cuantificar el impacto de reemplazar distancias geodésicas por distancias sobre red vial real (OSRM + OpenStreetMap) en las métricas operacionales del sistema de enrutamiento de última milla para Gran Valparaíso.

## Hipótesis
**H1**: La red vial modifica significativamente las métricas operacionales, con incrementos sistemáticos en distancias de rutas.

## Metodología

- **Dataset**: Gran Valparaíso (bounding box -71.70,-33.15,-71.20,-32.90), 300 deliveries, 10 rutas fijas
- **Red Vial**: OpenStreetMap → OSRM v5.27 (perfil car-lastmile), preprocesado ~45s, graph final 45 MB
- **Pares Evaluados**: 6 pares (12 evaluaciones) con parámetros idénticos variando solo `distance_mode`: geodésico vs vial
- **Servicio de Distancias**: DistanceService con Strategy Pattern (Haversine para geodésico, OsrmClient para vial)
- **Ejecución**: Las 6 evaluaciones viales comparten las mismas rutas y entregas (DB fija), por lo que las distancias de ruta son idénticas entre pares. Variación en semilla aleatoria y umbrales afecta detección de anomalías, no distancias.

## Resultados

### M001: Factor de Distorsión Global
**M001 = 1.6248**

La distancia vial total es un **62.5% mayor** que la distancia geodésica en todo el dataset.

### M002: Razón Vial/Geodésica por Ruta
| Ruta | Dist. Geodésica (km) | Dist. Vial (km) | Razón (M002) | Categoría M006 |
|------|---------------------|-----------------|-------------|----------------|
| Ruta D (bahía) | 23.39 | 58.12 | **2.485** | Crítica |
| Ruta D (bahía) | 23.28 | 57.62 | **2.475** | Crítica |
| Ruta C | 98.08 | 159.80 | 1.629 | Alta |
| Ruta C | 99.68 | 161.43 | 1.619 | Alta |
| Ruta E | 48.58 | 77.60 | 1.597 | Alta |
| Ruta A | 23.67 | 35.99 | 1.521 | Alta |
| Ruta A | 22.04 | 32.74 | 1.486 | Elevada |
| Ruta E | 46.86 | 70.07 | 1.495 | Elevada |
| Ruta B | 71.93 | 104.11 | 1.447 | Elevada |
| Ruta B | 70.05 | 99.69 | 1.423 | Elevada |

Rango de razones: **1.42 – 2.49** (mediana = 1.51)

### M003: Correlación de Pearson
**r = 0.9742**

Correlación casi perfecta entre distancias viales y geodésicas, indicando que el ordenamiento relativo de rutas se mantiene. Sin embargo, la pendiente > 1 confirma el sesgo sistemático.

### M004: Varianza Explicada por Modo
**η² = 0.1655**

El modo de distancia explica solo el 16.5% de la varianza total en distancias de ruta. La mayor parte de la varianza (83.5%) se debe a las diferencias entre rutas (Ruta C vs Ruta A), no al modo de cálculo.

### M005: Persistencia de Hallazgos

| Hallazgo | Estado | Descripción |
|----------|--------|-------------|
| H001 (Balance) | **PERSISTE (V)** | balance_index = 1.0 en ambos modos |
| H002 (CV) | **PERSISTE (V)** | CV < 0.1 en ambos modos |
| H003 (Cobertura) | **PERSISTE (V)** | coverage_territorial = 24.23 km (idéntico) |
| H004 (Anomalías) | **PERSISTE (V)** | Misma detección, umbrales no afectados por modo |
| H005 (Clusters) | **PERSISTE (V)** | 10 rutas fijas en ambos modos |
| H006 (Radio) | **PERSISTE (V)** | cluster_radius = 0.30 km en ambos modos |

Todos los hallazgos H001–H006 **persisten** (categoría V = validado) al usar red vial. La estructura de clustering no cambia porque las ubicaciones de entregas son fijas.

### M006: Territorial Distortion Index (TDI)

| Categoría | Rango | Rutas | Porcentaje |
|-----------|-------|-------|------------|
| **Normal** | ≤ 1.2 | 0 | 0% |
| **Elevada** | 1.2 – 1.5 | 4 | 40% |
| **Alta** | 1.5 – 2.0 | 4 | 40% |
| **Crítica** | > 2.0 | 2 | 20% |

Las rutas **críticas** corresponden a la Ruta D, que cruza la bahía de Valparaíso. En línea recta son ~23 km, pero por carretera deben rodear toda la bahía (~58 km).

### Tiempo de Ejecución

- **Geodésico**: ~0.25 s por evaluación (cálculo local instantáneo)
- **Vial**: ~82 s por evaluación (~1500 consultas HTTP a OSRM + renderizado de mapas)
- **Ratio**: ~330x más lento

El cuello de botella son las ~1500 llamadas HTTP a OSRM (una por cada par de coordenadas en la pipeline).

## Interpretación

1. La red vial de Gran Valparaíso **incrementa las distancias en promedio 62.5%** versus geodésico
2. Las rutas que cruzan la bahía (Ruta D) sufren **distorsión crítica (>2.0)** por la geografía
3. A pesar de la magnitud del incremento, el **ordenamiento relativo** de rutas se mantiene (r = 0.974)
4. Todos los hallazgos del baseline **se validan** con red vial — las conclusiones cualitativas son robustas
5. El TDI revela que **ninguna ruta** tiene distorsión normal en Valparaíso; el 60% de las rutas tienen distorsión alta o crítica

## Limitaciones

- Datos de OSM pueden tener cobertura incompleta en zonas rurales/de cerros
- Perfil OSRM car-lastmile usa velocidades urbanas genéricas (~30 km/h en ciudad)
- Las 10 rutas son fijas (no se reasignan con el modo vial)
- Sin medición de congestión real ni restricciones de tránsito
- Tiempo de ejecución vial (~82s) limita uso interactivo sin optimización

## Conclusiones

**H1 se confirma**: la red vial modifica significativamente las métricas de distancia (M001 = 1.62, M006 con 60% rutas alta/crítica). Sin embargo, la estructura de hallazgos del baseline es robusta (M005 = V para H001–H006). Se recomienda usar modo vial para planificación operativa real y modo geodésico solo para exploración inicial o prototipado rápido.

---

*Generado: 2026-06-21*
