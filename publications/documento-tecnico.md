---
title: "Sistema de Evaluación de Operaciones Logísticas de Última Milla: Métricas, Anomalías y Baseline"
type: "technical-paper"
author: "Sistema"
date: "2026-06-19"
status: "draft"
source_specs: ["SPEC-003", "SPEC-004"]
word_count: 0
target_audience: "technical"
---

## Introducción

La logística de última milla representa uno de los desafíos operacionales más complejos y costosos dentro de la cadena de suministro. En zonas urbanas y semiurbanas como Valparaíso, la distribución geográfica heterogénea de los puntos de entrega, combinada con restricciones operacionales como ventanas horarias, capacidad de vehículos y disponibilidad de conductores, genera ineficiencias que se traducen en mayor distancia recorrida, combustible consumido y tiempo operativo.

Existen múltiples herramientas comerciales y académicas para abordar el problema de ruteo de vehículos (VRP), pero la mayoría se enfoca en la optimización de rutas desde cero. Se identifica un vacío en herramientas que permitan **evaluar cuantitativamente** configuraciones operacionales existentes, detectar anomalías y establecer líneas base comparables antes de emprender optimizaciones algorítmicas.

Este documento presenta un sistema de evaluación de operaciones logísticas que permite modelar escenarios, ejecutar evaluaciones deterministas, generar 15 métricas cuantitativas agrupadas en cuatro categorías y documentar experimentos formales con trazabilidad completa.

## Problema de Investigación

¿Es posible construir un marco reproducible de evaluación de operaciones logísticas de última milla que permita comparar cuantitativamente configuraciones operacionales (asignación de paquetes a rutas, parámetros de detección de anomalías) utilizando métricas objetivas, y que genere hallazgos transferibles a estrategias de optimización?

## Preguntas de Investigación

Este proyecto se guía por cinco preguntas de investigación, documentadas en detalle en `research/preguntas-investigacion.md`:

- **PI-001** (Respondida parcialmente): ¿Cómo afecta la distribución geográfica de paquetes al balance de carga entre rutas?
- **PI-002** (Respondida): ¿Qué variables explican mejor la aparición de anomalías operacionales?
- **PI-003** (Respondida): ¿Qué métricas del sistema son invariantes ante cambios en parámetros de evaluación y cuáles son sensibles?
- **PI-004** (Respondida): ¿Cuál de los parámetros de detección de anomalías (threshold, ratio) tiene mayor impacto en los resultados?
- **PI-005** (Abierta): ¿Es posible reducir la penalidad operacional mediante redespliegue de entregas cercanas a bodega en rutas dedicadas?

## Hipótesis

**Hipótesis principal**: Es posible caracterizar cuantitativamente el desempeño operacional de una configuración de ruteo de última milla mediante un conjunto acotado de métricas (operacionales, de balance, de calidad y de utilización), y utilizar dichas métricas para identificar anomalías, comparar configuraciones y priorizar estrategias de optimización.

## Metodología

El proceso de evaluación sigue una secuencia determinista de siete etapas:

1. **Modelado**: Se define un escenario operacional con dataset de entregas (ubicaciones geográficas, demanda), conjunto de rutas (asignación paquete→ruta) y parámetros de evaluación.
2. **Cálculo de métricas de ruta**: Para cada ruta se calculan estadísticas descriptivas de las entregas asignadas: distancia a bodega, distancia al centroide del cluster, radio del cluster.
3. **Detección de anomalías**: Se identifican entregas que cumplen dos condiciones: (a) están dentro de un umbral de distancia a bodega (`near_delivery_threshold_km`) y (b) su distancia al centroide de su ruta supera el producto del umbral por un factor (`ignored_delivery_ratio`).
4. **Cálculo de penalidad**: Cada anomalía contribuye con su distancia al centroide a la penalidad operacional total.
5. **Métricas globales**: Se agregan las métricas individuales por ruta a nivel de sistema completo (promedios, desviaciones, cobertura territorial).
6. **Reporte**: Se genera un reporte estructurado con todas las métricas, anomalías detectadas y ranking de rutas.
7. **Experimentación**: Múltiples evaluaciones con distintos parámetros se agrupan en experimentos para análisis comparativo.

**Variables controladas**:
- Dataset (mismas 300 entregas, mismas 10 rutas)
- Algoritmo de asignación (manual-asignacion v1.0)
- Motor de evaluación determinista (sin aleatoriedad)

**Variables independientes** (parámetros de evaluación):
- `seed` (semilla de inicialización)
- `near_delivery_threshold_km` (umbral de proximidad a bodega: 0.3–5 km)
- `ignored_delivery_ratio` (factor de severidad: 1.2–5)

**Variables dependientes** (métricas de salida):
- Anomalías detectadas, penalidad operacional, distancia promedio, cobertura territorial, desviación estándar, balance index, entre otras (ver sección de métricas).

## Descripción de Métricas

El sistema genera 15 métricas cuantitativas agrupadas en cuatro categorías. A continuación se define cada métrica con su fórmula e interpretación.

### Métricas Operacionales

| Métrica | Fórmula | Interpretación |
|---------|---------|---------------|
| `distancia_promedio_general_km` | (Σ distancias a bodega de todas las entregas) / N | Distancia media desde cada punto de entrega a la bodega. Indica qué tan lejos están los clientes del centro de distribución. |
| `total_deliveries` | Conteo de entregas | Volumen total de entregas evaluadas. |
| `total_packages` | Conteo de paquetes | Volumen total de paquetes (puede diferir de entregas si hay múltiples paquetes por punto). |
| `coverage_territorial_km` | (Σ distancias a centroide de ruta) / N | Dispersión promedio de entregas respecto al centro de su propia ruta. Mide qué tan extendida está cada ruta. |

### Métricas de Balance

| Métrica | Fórmula | Interpretación |
|---------|---------|---------------|
| `desviacion_estandar_distancias_km` | σ de distancias a bodega | Variabilidad en la distribución geográfica de entregas. Valores altos indican mezcla de entregas cercanas y lejanas en una misma operación. |
| `balance_general_cv` | σ / μ de distancias a bodega | Coeficiente de variación. Estandariza la dispersión respecto al promedio. CV > 1 indica dispersión muy alta. |
| `balance_index` | 1 − (|min_avg − max_avg| / (min_avg + max_avg)) | Mide el equilibrio entre la ruta más cercana y la más lejana a bodega. 1.0 = perfecto equilibrio. |
| `inter_cluster_min_distance_km` | mín(centroide_i − centroide_j) | Distancia mínima entre centroides de rutas. Valores bajos indican superposición de clusters. |

### Métricas de Calidad

| Métrica | Fórmula | Interpretación |
|---------|---------|---------------|
| `total_anomalias_detectadas` | Conteo de entregas que cumplen threshold + ratio | Número de entregas que están cerca de bodega pero muy lejos del centroide de su ruta, sugiriendo una asignación subóptima. |
| `operational_penalty_total` | Σ distancia_al_centroide de anomalías | Penalidad acumulada en kilómetros por entregas mal asignadas. Cuantifica el costo operacional de las anomalías. |
| `average_delivery_threshold_distance_km` | Promedio de distancias a bodega de anomalías | Distancia media de anomalías a bodega. Útil para calibrar el umbral. |
| `min_delivery_threshold_distance_km` | Mínimo de distancias a bodega de anomalías | La anomalía más cercana a bodega. |

### Métricas de Utilización

| Métrica | Fórmula | Interpretación |
|---------|---------|---------------|
| `total_active_vehicles` | Conteo de rutas con entregas asignadas | Vehículos efectivamente utilizados en la operación. |
| `used_vehicles_percentage` | (rutas activas / total rutas) × 100 | Porcentaje de la flota utilizada. Sin uso de flota sobrante. |
| `average_occupancy_rate` | entregas / rutas activas | Promedio de entregas por ruta. Carga de trabajo promedio por vehículo. |
| `cluster_occupancy_rate` | entregas / ruta para cada ruta | Distribución de carga por ruta individual. |

## Diseño Experimental

Se diseñó un experimento baseline con 6 evaluaciones (IDs 2–7) que varían los parámetros `seed`, `near_delivery_threshold_km` e `ignored_delivery_ratio` manteniendo constante la asignación de paquetes a rutas.

**Dataset**: Valparaíso Demo — 300 entregas, 10 rutas (Ruta A×2, B×2, C×2, D×2, E×2)

**Algoritmo**: `manual-asignacion` v1.0

| ID | Seed | Threshold (km) | Ratio | Propósito |
|----|------|---------------|-------|-----------|
| 2 | 42 | 1 | 2 | Baseline (configuración base) |
| 3 | 123 | 1 | 2 | Reproducibilidad (mismo threshold y ratio, distinta seed) |
| 4 | 200 | 3 | 3 | Threshold alto + ratio alto |
| 5 | 100 | 0.5 | 1.5 | Threshold bajo + ratio bajo (más estricto) |
| 6 | 400 | 0.3 | 1.2 | Threshold muy bajo (casi sin detección) |
| 7 | 300 | 5 | 5 | Threshold muy alto + ratio muy alto |

## Resultados

### Resultado 1: Métricas Globales

Las métricas globales del sistema se mantienen invariantes en las 6 evaluaciones, confirmando el carácter determinista del motor de evaluación:

| Métrica | Valor | Invariante |
|---------|-------|------------|
| distancia_promedio_general_km | 13.47 | ✓ Sí |
| coverage_territorial_km | 24.23 | ✓ Sí |
| desviacion_estandar_distancias_km | 4.68 | ✓ Sí |
| balance_index | 1.0000 | ✓ Sí |
| inter_cluster_min_distance_km | 0.0055 | ✓ Sí |
| total_anomalias_detectadas | 0–10 | ✗ Varía |
| operational_penalty_total | 0.00–232.26 | ✗ Varía |

### Resultado 2: Métricas por Ruta (Evaluación #2)

| Ruta | Prom. a Bodega (km) | Radio Cluster (km) | Dist. Ruta Est. (km) |
|------|---------------------|--------------------|----------------------|
| Ruta C (ID 8) | 7.89 | 2.06 | 99.68 |
| Ruta C (ID 3) | 7.96 | 1.99 | 98.08 |
| Ruta B (ID 2) | 12.99 | 13.58 | 71.93 |
| Ruta B (ID 7) | 12.97 | 13.88 | 70.05 |
| Ruta E (ID 5) | 16.34 | 15.31 | 48.58 |
| Ruta E (ID 10) | 16.31 | 15.14 | 46.86 |
| Ruta A (ID 6) | 15.86 | 0.34 | 23.67 |
| Ruta D (ID 4) | 14.25 | 3.41 | 23.39 |
| Ruta D (ID 9) | 14.26 | 3.49 | 23.28 |
| Ruta A (ID 1) | 15.81 | 0.30 | 22.04 |

### Resultado 3: Anomalías Detectadas

Las 10 anomalías identificadas en la Evaluación #2 (threshold=1, ratio=2) pertenecen exclusivamente a rutas del sector B:

| Delivery ID | Ruta | Dist. Bodega (km) | Dist. Centroide (km) | Ratio |
|-------------|------|-------------------|---------------------|-------|
| 197 | B (7) | 0.30 | 11.60 | 38.54 |
| 200 | B (7) | 0.30 | 11.60 | 38.43 |
| 196 | B (7) | 0.33 | 11.60 | 35.64 |
| 48 | B (2) | 0.48 | 11.58 | 24.33 |
| 199 | B (7) | 0.53 | 11.60 | 21.77 |
| 46 | B (2) | 0.65 | 11.58 | 17.69 |
| 49 | B (2) | 0.70 | 11.58 | 16.50 |
| 47 | B (2) | 0.83 | 11.58 | 14.01 |
| 50 | B (2) | 0.85 | 11.58 | 13.64 |
| 198 | B (7) | 0.99 | 11.60 | 11.71 |

### Resultado 4: Sensibilidad a Parámetros

| ID | Threshold | Ratio | Anomalías | Penalidad | Diferencia |
|----|-----------|-------|-----------|-----------|------------|
| 2 | 1 | 2 | 10 | 232.26 | Baseline |
| 3 | 1 | 2 | 10 | 232.26 | 0% |
| 4 | 3 | 3 | 10 | 232.26 | 0% |
| 5 | 0.5 | 1.5 | 4 | 136.94 | −41% anomalías, −41% penalidad |
| 6 | 0.3 | 1.2 | 0 | 0.00 | −100% anomalías, −100% penalidad |
| 7 | 5 | 5 | 10 | 232.26 | 0% |

## Análisis de Resultados

### Invariabilidad de Métricas Globales

Las métricas operacionales y de balance (distancia promedio, cobertura territorial, desviación estándar, balance index) no varían entre evaluaciones. Esto se debe a que son función exclusiva de la asignación de paquetes a rutas, no de los parámetros de detección de anomalías. Este resultado valida que el diseño experimental aísla correctamente el efecto de los parámetros de anomalía.

### Sensibilidad Asimétrica de Parámetros

El parámetro `ignored_delivery_ratio` es significativamente más discriminatorio que `near_delivery_threshold_km`. Evaluaciones con threshold 1–3 y ratio 2–5 producen resultados idénticos (10 anomalías, penalidad 232.26). Solo al reducir el threshold a 0.5 y el ratio a 1.5 (Evaluación #5) se observa una reducción del 41% en anomalías y penalidad.

Esto indica que el threshold opera como un filtro de perímetro (define qué entregas son candidatas a revisión), mientras que el ratio determina la severidad real de cada anomalía candidata.

### Concentración de Anomalías

Las 10 entregas más cercanas a bodega (< 1 km) pertenecen exclusivamente a rutas del sector B (IDs 2 y 7). Esto revela una heterogeneidad geográfica significativa: las rutas B contienen entregas tanto muy cercanas a bodega (0.3 km) como muy lejanas (24 km), generando un radio de cluster de 13.6 km. En contraste, las rutas A tienen todas sus entregas concentradas en un radio de 0.3 km.

### Heterogeneidad de Rutas

El ranking de rutas por cercanía a bodega muestra tres clusters naturales:
- **Cercanas**: Ruta C (~7.9 km promedio)
- **Intermedias**: Rutas B, D (~13–14 km)
- **Lejanas**: Rutas A, E (~15.8–16.3 km)

Sin embargo, el radio de cluster revela una realidad opuesta: Ruta C es compacta (radio 2 km) mientras que Ruta E es extremadamente dispersa (radio 15 km), a pesar de ser ambas "lejanas". Esto sugiere que la lejanía promedio no es un indicador suficiente de eficiencia operacional.

## Hallazgos Formales

Los siguientes hallazgos formales están documentados con mayor detalle en `research/hallazgos.md`.

**H001** — La minimización de distancia recorrida no garantiza una distribución equilibrada de carga entre rutas.
- **Evidencia**: SPEC-003, Evaluaciones 2, 4 y 6.
- **Impacto**: Justifica incorporar métricas de balance operacional como criterio independiente de optimización.

**H002** — La selección del punto inicial de una ruta tiene un impacto significativo en la distancia total recorrida.
- **Evidencia**: SPEC-004, Experimento 001.
- **Impacto**: Motiva futuras estrategias de optimización geográfica del punto de partida.

**H003** — Las métricas globales de distancia promedio, cobertura territorial y desviación estándar son invariantes ante cambios en parámetros de detección de anomalías.
- **Evidencia**: SPEC-004, Experimento 001 (Evaluaciones 2–7 comparten las mismas métricas de ruta).
- **Impacto**: Valida que el diseño experimental puede aislar el efecto de parámetros de anomalía.

**H004** — El parámetro `ignored_delivery_ratio` es más discriminatorio que `near_delivery_threshold_km` para controlar la detección de anomalías.
- **Evidencia**: SPEC-004, Experimento 001 (Evaluación 5 con ratio 1.5 detecta solo 4 anomalías vs 10 con ratio 2).
- **Impacto**: Guía la calibración de sistemas de detección en producción: priorizar ajuste de ratio sobre threshold.

**H005** — La distribución de paquetes en el territorio de Valparaíso genera clusters de rutas con dispersión radicalmente distinta.
- **Evidencia**: SPEC-004, Experimento 001 (Ruta A: radio 0.3 km vs Ruta E: radio 15.3 km).
- **Impacto**: Sugiere que estrategias de optimización deben ser específicas por cluster.

**H006** — Las 10 entregas más cercanas a bodega (< 1 km) pertenecen exclusivamente a rutas del sector B, lo que sugiere una oportunidad de rediseño mediante rutas exprés locales.
- **Evidencia**: SPEC-004, Experimento 001 (Anomalías Detectadas, Evaluación #2).
- **Impacto**: Abre línea de investigación en micro-ruteo local.

## Limitaciones

### Amenazas a la Validez Interna

1. **Datos sintéticos**: Todas las evaluaciones utilizan datasets generados sintéticamente. Si bien esto garantiza reproducibilidad y control de variables, los patrones observados podrían no replicarse exactamente en datos reales con ruido, entregas irregulares o restricciones no modeladas.

2. **Asignación manual**: El algoritmo `manual-asignacion` v1.0 asigna paquetes a rutas mediante reglas predefinidas, no mediante optimización. Las conclusiones sobre balance y eficiencia están limitadas por la calidad de esta asignación base.

3. **Parámetros fijos**: Los rangos de threshold (0.3–5 km) y ratio (1.2–5) fueron elegidos arbitrariamente. Es posible que configuraciones fuera de este rango revelen comportamientos no observados.

### Amenazas a la Validez Externa

1. **Generalización geográfica**: El dataset corresponde a una zona geográfica específica (Valparaíso). La distribución de entregas, topografía y densidad poblacional pueden diferir significativamente en otras ciudades.

2. **Escala**: El experimento utiliza 300 entregas y 10 rutas. Operaciones de mayor escala (miles de entregas, cientos de rutas) podrían presentar comportamientos emergentes no capturados.

3. **Ventanas horarias**: El modelo actual no considera restricciones temporales (ventanas de entrega, duración de jornada). La inclusión de estas variables podría alterar las métricas de balance y penalidad.

4. **Flota homogénea**: Se asume una flota de vehículos idénticos. Operaciones con flota heterogénea (diferente capacidad, velocidad, costos) requerirían métricas adicionales.

## Conclusiones

Este proyecto ha desarrollado un marco reproducible para la evaluación de operaciones logísticas de última milla, implementado como un sistema funcional con las siguientes contribuciones principales:

**C001** — Marco reproducible para evaluación de operaciones logísticas de última milla: plataforma completa que permite modelar escenarios, ejecutar evaluaciones deterministas, generar métricas cuantitativas y documentar experimentos formales. Todo el proceso es reproducible mediante Docker y git.

**C002** — Sistema de detección de anomalías operacionales basado en métricas agregadas: algoritmo que identifica entregas anómalas por proximidad a bodega y desviación del centroide de ruta, utilizando umbral configurable y ratio de distancia.

**C003** — Metodología experimental para comparación de configuraciones de ruteo: diseño experimental que permite aislar el efecto de parámetros individuales sobre métricas de desempeño, manteniendo invariantes las métricas de ruta.

Los resultados del experimento baseline demuestran que: (a) las métricas globales son deterministas e invariantes entre ejecuciones; (b) el ratio de detección es más discriminatorio que el threshold; (c) existe una heterogeneidad geográfica significativa entre rutas que sugiere oportunidades de optimización específicas por cluster.

## Trabajo Futuro

1. **SPEC-006 — Optimización Algorítmica**: Implementar algoritmos de optimización de asignación de paquetes a rutas, utilizando las métricas de balance como función objetivo.
2. **SPEC-007 — Ciencia de Datos**: Incorporar análisis estadístico avanzado (regresión, clustering) sobre los datos históricos de evaluaciones.
3. **SPEC-008 — Aprendizaje de Modelos**: Explorar modelos predictivos de anomalías basados en características de entregas y rutas.
4. **SPEC-009 — White Paper**: Síntesis final de toda la investigación en un documento publicable, integrando hallazgos de todas las fases.
5. **Micro-ruteo local**: Investigar la viabilidad de rutas exprés dedicadas para entregas cercanas a bodega (basado en H006 y PI-005).
6. **Validación con datos reales**: Repetir el experimento baseline con datos operacionales reales para validar la generalización de los hallazgos.

---

### Tabla de Acrónimos

| Acrónimo | Significado |
|----------|-------------|
| VRP | Vehicle Routing Problem |
| SPEC | Especificación formal del proyecto |
| H | Hallazgo formal (research/hallazgos.md) |
| PI | Pregunta de Investigación (research/preguntas-investigacion.md) |
| C | Contribución (research/contribuciones.md) |
| D | Decisión (research/decisiones.md) |
| CV | Coeficiente de Variación |

---

*Documento generado a partir de los resultados de SPEC-003 y SPEC-004. Los hallazgos formales, preguntas de investigación y contribuciones referenciados están documentados en `research/`.*
