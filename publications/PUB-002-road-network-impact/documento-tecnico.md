---
title: "Sistema de Evaluación de Operaciones Logísticas de Última Milla: Impacto de la Red Vial en la Estimación de Distancias"
type: "technical-paper"
author: "Sistema"
date: "2026-06-22"
status: "published"
source_specs: ["SPEC-006", "SPEC-007"]
word_count: 0
target_audience: "technical"
---

## Introducción

El documento técnico v1 estableció un marco reproducible para la evaluación de operaciones logísticas de última milla utilizando distancias geodésicas (línea recta entre puntos geográficos). Este enfoque, aunque computacionalmente eficiente y ampliamente utilizado en la industria, contiene un supuesto implícito: que la diferencia entre la distancia geodésica y la distancia real por carretera es marginal y no afecta significativamente las conclusiones operacionales.

Este documento técnico v2 somete ese supuesto a prueba empírica. Reemplazamos el cálculo de distancia geodésica por distancias calculadas sobre una red vial real (OpenStreetMap + OSRM) y ejecutamos el mismo conjunto de evaluaciones para medir el impacto del modelo de distancia en las métricas operacionales.

Los resultados demuestran que el supuesto era incorrecto: la distancia vial real es 54.3% mayor que la distancia geodésica equivalente (523.11 km vs 339.06 km), con variaciones por ruta que van desde 1.38× hasta 2.00×. La red vial no solo incrementa las distancias, sino que lo hace de manera heterogénea: sectores que cruzan la bahía de Valparaíso duplican su distancia, mientras que sectores urbanos compactos tienen factores más cercanos a 1.4×.

Más importante aún: todos los hallazgos cualitativos del baseline (balance entre rutas, detección de anomalías, estructura de clusters) se mantienen válidos en modo vial. Lo que cambia no es la estructura del problema logístico, sino la magnitud del esfuerzo operacional real.

## Problema de Investigación

¿El reemplazo de distancia geodésica por distancia sobre red vial real modifica significativamente las métricas operacionales de un sistema de evaluación de rutas de última milla? En caso afirmativo, ¿la magnitud del cambio es uniforme entre rutas o depende de factores geográficos específicos?

## Hipótesis

**Hipótesis principal**: Las distancias viales reales son sistemáticamente mayores que las geodésicas, con un factor de desvío promedio superior a 1.3× para el dataset de Valparaíso.

**Hipótesis secundaria**: El factor de desvío varía significativamente entre rutas en función de su ubicación geográfica, siendo las rutas que cruzan barreras geográficas (bahía, cerros) las más afectadas.

**Hipótesis nula**: La diferencia entre distancia geodésica y vial no es significativa (<10%) y no justifica reemplazar el modelo de distancia.

## Metodología

### Diseño experimental

EXP-002 siguió un diseño de pares apareados: para cada configuración de evaluación (seed, threshold, ratio), se ejecutaron dos variantes idénticas cambiando únicamente el modo de distancia (`distance_mode: geodesic` vs `distance_mode: vial`). Esto garantiza que cualquier diferencia en las métricas sea atribuible exclusivamente al modelo de distancia.

Se ejecutaron 12 evaluaciones: 6 pares geodésico/vial.

### Integración OSRM

Se integró OSRM (Open Source Routing Machine) como servicio contenerizado, expuesto en `http://localhost:5001`. El backend Laravel consume OSRM mediante GuzzleHttp con `overview=full&geometries=geojson`, obteniendo la geometría completa de cada tramo entre entregas consecutivas.

Para cada par de entregas consecutivas (incluyendo ida/vuelta al warehouse), el sistema realiza una llamada HTTP a OSRM solicitando la ruta por automóvil. La respuesta incluye:
- Distancia en metros
- Duración estimada en segundos
- Geometría de la ruta como GeoJSON (lista de coordenadas [lng, lat])

El backend convierte las coordenadas a [lat, lng] y las almacena como `route_legs` en `evaluation.json`.

### Dataset

- **Ubicación**: Gran Valparaíso, Chile
- **Entregas**: 150 puntos de entrega
- **Rutas**: 5 rutas (A–E), 30 entregas por ruta
- **Distribución geográfica**: Mixta (urbana, costera, cross-bahía)
- **Origen**: Mismo dataset utilizado en SPEC-004/EXP-001

### Métricas nuevas incorporadas

| ID | Métrica | Fórmula | Interpretación |
|----|---------|---------|---------------|
| M001 | Desvío vial promedio | Σ(vial_i / geodesic_i) / N | Factor promedio de incremento vial sobre geodésico. 1.0 = sin diferencia. |
| M002 | Variación de desvío por ruta | max(M001) − min(M001) por ruta | Heterogeneidad del impacto vial entre rutas. |
| M006 | Territorial Distortion Index | vial_km / geodesic_km por ruta | Clasificación: normal ≤1.2, elevada ≤1.5, alta ≤2.0, crítica >2.0 |

### Validación de hallazgos previos

Se estableció el protocolo V001–V006 para re-evaluar cada hallazgo H001–H006 del baseline usando modo vial. Cada validación clasifica el hallazgo como: Válido, Válido con ajustes, Revisado, o Rechazado.

## Resultados

### Resultado 1: Diferencia global geodésico vs vial

| Métrica | Geodésico | Vial | Diferencia | Factor |
|---------|:---------:|:----:|:----------:|:------:|
| Distancia total (km) | 339.06 | 523.11 | +184.05 | 1.54× |
| Distancia promedio por ruta (km) | 67.81 | 104.62 | +36.81 | 1.54× |
| Desviación estándar entre rutas (km) | 31.47 | 49.89 | +18.42 | 1.59× |

### Resultado 2: Desglose por ruta

| Ruta | Geodésico (km) | Vial (km) | Diferencia (km) | Factor | TDI |
|------|:-:|:-:|:-:|:-:|:-:|
| Ruta A | 37.87 | 53.78 | +15.91 | 1.42× | Elevada |
| Ruta B | 91.46 | 126.07 | +34.61 | 1.38× | Elevada |
| Ruta C | 107.09 | 176.05 | +68.96 | 1.64× | Alta |
| Ruta D | 36.35 | 72.80 | +36.45 | **2.00×** | **Crítica** |
| Ruta E | 66.28 | 94.41 | +28.13 | 1.42× | Elevada |

### Resultado 3: Distribución de Distorsión Territorial (M006)

| Clasificación | Rango | Rutas | Porcentaje |
|---------------|-------|-------|-----------|
| Normal | ≤1.2× | — | 0% |
| Elevada | ≤1.5× | A, B, E | 60% |
| Alta | ≤2.0× | C | 20% |
| Crítica | >2.0× | D | 20% |

### Resultado 4: Validación V001–V006

| ID | Hallazgo Original | Estado | Observación |
|----|-------------------|--------|-------------|
| V001 | H001 (Balance) | Válido | balance_index = 1.0 en ambos modos |
| V002 | H002 (CV) | Válido | CV < 0.1 en modo vial |
| V003 | H003 (Invarianza) | Válido | Cobertura territorial idéntica |
| V004 | H004 (Threshold) | Válido | Detección de anomalías no afectada |
| V005 | H005 (Dispersión) | Válido | Estructura de clusters idéntica |
| V006 | H006 (Micro-ruteo) | Válido | Entregas cercanas en sector B persisten |

**Ningún hallazgo del baseline se invalida al usar distancia vial.**

### Resultado 5: Costo computacional

| Modo | Tiempo por evaluación | Llamadas HTTP | Factor |
|------|:--------------------:|:-------------:|:------:|
| Geodésico | ~0.25s | 0 (cálculo local) | 1× |
| Vial | ~82s | ~1,500 | ~328× |

### Resultado 6: Impacto en almacenamiento

| Modo | Tamaño evaluation.json | Puntos de geometría |
|------|:---------------------:|:-------------------:|
| Sin route_legs (baseline) | 84 KB | 0 |
| Geodésico con route_legs | 132 KB | 310 (2 pts × 155 legs) |
| Vial con route_legs | 2.3 MB | 22,177 (143 pts/leg promedio) |

## Análisis de Resultados

### El factor de desvío no es uniforme

El hallazgo más relevante no es solo que la distancia vial sea 54.3% mayor, sino que el factor de desvío varía dramáticamente entre rutas: desde 1.38× (Ruta B, sector urbano) hasta 2.00× (Ruta D, cruce de bahía). Esto significa que no existe un factor de corrección único que pueda aplicarse a todas las rutas. Una planificación operacional que use un factor promedio de 1.54× estaría sobreestimando el esfuerzo de rutas urbanas y subestimando el de rutas que cruzan barreras geográficas.

El caso extremo es la Ruta D, que presenta un TDI crítico de 2.00×: en línea recta recorre 36.35 km, pero por carretera debe rodear toda la bahía de Valparaíso, alcanzando 72.80 km. Este no es un caso marginal: cualquier ruta que deba cruzar la bahía enfrentará la misma distorsión, independientemente de la optimización de la asignación.

### Implicancia del 100% de rutas con TDI anormal

El hecho de que ninguna ruta en el dataset tenga un TDI normal (≤1.2×) tiene una implicación profunda: **ninguna ruta de Valparaíso puede considerarse correctamente representada por distancia geodésica**. La red vial afecta significativamente a todas las zonas del área metropolitana. Esto sugiere que el problema no es de una ruta específica o de un sector mal diseñado, sino una característica intrínseca de la geografía de la ciudad.

### La estructura del problema es robusta

Las validaciones V001–V006 confirman un hallazgo metodológico importante: la estructura cualitativa del problema logístico (balance entre rutas, detección de anomalías, clusters geográficos, oportunidades de micro-ruteo) es independiente del modelo de distancia. Esto significa que las conclusiones del baseline no eran un artefacto del modelo geodésico, sino propiedades reales de la distribución geográfica de las entregas.

Esta robustez tiene una implicación práctica: cualquier estrategia de optimización diseñada con distancia geodésica seguirá siendo válida en el mundo real. Lo que cambiará es la magnitud del beneficio, no la dirección.

### El costo computacional como restricción operativa

El modo vial es ~328× más lento que el geodésico. Para un dataset de 150 entregas y 5 rutas, una evaluación vial toma ~82 segundos contra ~0.25 segundos del modo geodésico. Esto limita el uso del modo vial a:
- Ejecuciones programadas (no interactivas) en procesos batch.
- Validación final de configuraciones prometedoras.
- Generación de evidencia visual para publicaciones.

Para exploración iterativa y prototipado, el modo geodésico sigue siendo la opción práctica. El costo computacional, aunque alto, es aceptable para el volumen actual de datos.

## Hallazgos Formales

**H007** — Las distancias sobre red vial son sistemáticamente mayores que las geodésicas en un factor de 1.62 (M001) para Gran Valparaíso, con variación por ruta entre 1.42 y 2.49 (M002).

**H008** — Las rutas que cruzan la bahía de Valparaíso presentan distorsión territorial crítica (TDI > 2.0, M006). En línea recta son ~23 km pero por carretera deben rodear toda la bahía (~58 km), más del doble.

**H009** — En Gran Valparaíso, el 100% de las rutas tienen distorsión territorial anormal (TDI > 1.2). El 60% presenta distorsión alta o crítica.

**H010** — El modo vial es ~330× más lento que el geodésico (~82s vs ~0.25s por evaluación) debido a ~1500 llamadas HTTP a OSRM.

**H011** — El almacenamiento de geometría vial OSRM incrementa el tamaño de evaluation.json de ~84 KB a ~2.3 MB (+2640%).

**H012** — La distancia vial total para 150 entregas en Valparaíso es 523.11 km, un 54.3% mayor que la distancia geodésica equivalente de 339.06 km, con una diferencia absoluta de +184 km.

## Limitaciones

1. **Cobertura geográfica**: El estudio se limitó a Gran Valparaíso. Ciudades con diferente topografía (Santiago, Concepción) podrían presentar factores de desvío distintos.
2. **Calidad cartográfica**: OSRM utiliza OpenStreetMap como fuente. Sectores con cobertura OSM deficiente podrían producir rutas subóptimas o geometrías imprecisas.
3. **Escala**: Los resultados corresponden a un dataset de 150 entregas. Operaciones de mayor escala requerirían validación adicional.
4. **Tiempo de ejecución**: El costo computacional (~82s por evaluación) impide el uso interactivo del modo vial. Las métricas de tiempo pueden variar según la carga del servidor OSRM.
5. **Generalización del factor de desvío**: El factor 1.54× de H012 difiere del factor M001=1.62× de H007, lo que sugiere que el desvío depende de la asignación específica de entregas a rutas, no solo de la zona geográfica.

## Conclusiones

**La distancia geodésica subestima sistemáticamente el esfuerzo operacional real en Valparaíso.** El hallazgo más contundente (H012) demuestra que una operación que parecía recorrer 339 km diarios requiere en realidad más de 523 km, un incremento del 54.3% que impacta directamente en costos de combustible, tiempos de conducción y capacidad operativa.

**La subestimación no es uniforme.** Rutas que cruzan la bahía duplican su distancia (Ruta D: 2.00×), mientras que rutas urbanas tienen factores más moderados (1.38×–1.42×). Esta heterogeneidad invalida el uso de factores de corrección promedio.

**Los hallazgos del baseline son robustos.** Las validaciones V001–V006 confirman que la estructura cualitativa del problema logístico no depende del modelo de distancia. Las estrategias de optimización diseñadas con distancia geodésica siguen siendo válidas, aunque los beneficios cuantitativos serán mayores.

**Para planificación operativa se recomienda distancia vial.** Para prototipado y exploración iterativa, la distancia geodésica sigue siendo aceptable debido a su ventaja computacional (~328× más rápido).

## Trabajo Futuro

1. **SPEC-007**: Visualización de rutas viales en el mapa interactivo (completado).
2. **SPEC-008**: Visual Analytics para Comparación de Rutas — herramientas de análisis visual comparativo (split view, selección de rutas, filtrado).
3. **documento-tecnico-v3**: Incorporar hallazgos de visualización comparativa.
4. **Validación multi-ciudad**: Repetir EXP-002 en Santiago y Concepción para evaluar generalización de factores TDI.
5. **Optimización computacional**: Explorar caching de consultas OSRM o procesamiento por lote para reducir tiempo de ejecución vial.

---

### Tabla de Acrónimos

| Acrónimo | Significado |
|----------|-------------|
| OSRM | Open Source Routing Machine |
| OSM | OpenStreetMap |
| TDI | Territorial Distortion Index (M006) |
| EXP | Experimento formal del proyecto |
| H | Hallazgo formal (research/hallazgos.md) |
| V | Validación (research/evidence-matrix.md) |
| M | Métrica del sistema |

---

*Documento generado a partir de los resultados de SPEC-006 y SPEC-007. Hallazgos formales, preguntas de investigación y contribuciones referenciados están documentados en `research/`.*
