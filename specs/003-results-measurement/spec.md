# Feature Specification: Sistema de Medición, Evaluación y Validación de Resultados

**Feature Branch**: `003-results-measurement`

**Created**: 2026-06-19

**Status**: Draft

## 1. Context

Las fases previas del proyecto (001: operación básica, 002: agrupamiento geográfico) permitieron generar datos simulados y aplicar técnicas de clustering para distribuir entregas en múltiples rutas.

Durante el análisis de la Fase 2 se observó que la percepción visual de una ruta no siempre coincide con su eficiencia operativa. Algunos clusters presentaban comportamientos aparentemente ineficientes:

- Rutas cuyo punto de inicio se encontraba significativamente alejado de la bodega.
- Entregas cercanas a la bodega asignadas a rutas distantes.
- Agrupamientos geográficamente correctos pero operativamente cuestionables.

Actualmente no existe un marco formal de métricas que permita evaluar de manera objetiva y reproducible la calidad de los agrupamientos generados. Las decisiones se basan principalmente en observación visual, lo que introduce subjetividad y dificulta la comparación entre estrategias.

---

## 2. Hipótesis

Es posible diseñar un conjunto de indicadores cuantitativos que permita:

1. Evaluar objetivamente la calidad de los agrupamientos generados por cualquier algoritmo.
2. Detectar automáticamente anomalías operativas que no son evidentes mediante inspección visual.
3. Comparar distintas estrategias de optimización bajo los mismos criterios de evaluación.
4. Transformar observaciones visuales en evidencia cuantificable para la documentación del proyecto.

---

## 3. Objetivo

Diseñar e implementar un sistema formal de métricas capaz de medir la calidad de los agrupamientos generados, evaluar la relación entre las rutas y la bodega, detectar anomalías operativas, comparar distintas estrategias de optimización y generar evidencia cuantitativa reproducible para la documentación del proyecto y la construcción de artículos técnicos.

---

## 4. Alcance

El sistema deberá:

- Calcular métricas individuales por ruta (distancia mínima, máxima, promedio, centroide, radio, compactación, distancia total estimada).
- Calcular métricas globales del experimento (cobertura territorial, distancia promedio general, desviación estándar, balance de carga, separación entre clusters, penalización operacional total).
- Detectar automáticamente entregas cercanas a la bodega asignadas a rutas lejanas.
- Generar ranking de rutas por cercanía operativa.
- Medir separación mínima entre clusters para cuantificar calidad del agrupamiento.
- Calcular penalización operacional total como indicador consolidado de anomalías.
- Producir evidencia visual obligatoria (vista general, vista por ruta, casos relevantes).
- Exportar resultados en formato estructurado (JSON y CSV), incluyendo datos por entrega.
- Almacenar cada ejecución con fecha, parámetros (incluyendo algoritmo y versión), dataset, semilla aleatoria y métricas calculadas.
- Capturar el estado actual como línea base para comparaciones futuras con estrategias optimizadas.

---

## 5. Exclusiones

Esta especificación no incluye:

- Modificación de los algoritmos de clustering existentes.
- Implementación de nuevos algoritmos de optimización.
- Interfaz de usuario para visualización de métricas (la generación de imágenes es el mecanismo de salida).
- Alertas en tiempo real ni sistemas de notificación.
- Integración con servicios externos de mapas distintos a los ya existentes en el proyecto.
- Métricas temporales (duración estimada, tiempo de entrega, ventanas horarias). Los indicadores exclusivamente espaciales son el foco de esta fase; los temporales se abordarán en fases posteriores.

---

## 6. Historias de Usuario

**HU-01: Evaluación de agrupamientos**

**Como** investigador de operaciones logísticas
**Quiero** obtener métricas cuantitativas de cada ruta generada
**Para** evaluar objetivamente si el agrupamiento es eficiente sin depender de inspección visual

---

**HU-02: Detección de anomalías**

**Como** analista de rutas
**Quiero** identificar automáticamente entregas cercanas a la bodega que fueron asignadas a rutas lejanas
**Para** corregir asignaciones ineficientes antes de la ejecución operativa

---

**HU-03: Comparación de estrategias**

**Como** responsable de optimización
**Quiero** comparar los indicadores de distintas ejecuciones con diferentes parámetros o algoritmos
**Para** seleccionar la estrategia que produce los mejores resultados según criterios objetivos

---

**HU-04: Generación de evidencia visual**

**Como** documentador técnico
**Quiero** generar mapas que muestren la distribución de rutas, clusters y casos relevantes
**Para** respaldar las conclusiones del proyecto con evidencia visual en artículos y documentación

---

**HU-05: Exportación de resultados**

**Como** investigador
**Quiero** exportar las métricas calculadas en formato estructurado
**Para** realizar análisis posteriores y comparaciones históricas

---

## 7. Requisitos Funcionales

**RF-01: Cálculo de entregas por ruta**

El sistema debe calcular la cantidad total de entregas asignadas a cada ruta y presentarla en forma de tabla.

**RF-02: Distancia bodega a punto más cercano**

El sistema debe calcular la distancia mínima entre la bodega y las entregas de cada ruta.

**RF-03: Distancia bodega a punto más lejano**

El sistema debe calcular la distancia máxima entre la bodega y las entregas de cada ruta.

**RF-04: Distancia promedio a la bodega**

El sistema debe calcular el promedio de distancias entre la bodega y todas las entregas de cada ruta.

**RF-05: Distancia del centroide a la bodega**

El sistema debe calcular el centroide geográfico de cada ruta y medir su distancia a la bodega.

**RF-06: Radio del cluster**

El sistema debe calcular la distancia entre el centroide de cada ruta y su entrega más alejada.

**RF-07: Distancia promedio al centroide**

El sistema debe calcular el promedio de distancias entre el centroide y todas las entregas de cada ruta.

**RF-08: Índice de balance de carga**

El sistema debe calcular el cociente entre la ruta con más entregas y la ruta con menos entregas.

**RF-09: Detección de entregas cercanas ignoradas**

El sistema debe identificar entregas cuya distancia a la bodega se encuentre dentro de un umbral configurable (`near_delivery_threshold`, default: 1 km) y que hayan sido asignadas a rutas cuyo centroide está al menos `ignored_delivery_ratio` veces más lejos de la bodega (default: 2.0). Ambos parámetros deben ser modificables sin alterar el código del sistema de métricas.

**RF-10: Ranking de rutas por cercanía operativa**

El sistema debe ordenar las rutas de menor a mayor distancia promedio a la bodega.

**RF-11: Indicadores globales**

El sistema debe calcular:
- Cobertura territorial (distancia máxima entre cualquier entrega y la bodega).
- Distancia promedio general (promedio de todas las distancias entrega-bodega).
- Desviación estándar de distancias entrega-bodega.
- Balance general (coeficiente de variación de entregas por ruta).

**RF-12: Generación de mapa general**

El sistema debe generar un mapa que muestre la bodega, todas las entregas y sus clusters asignados.

**RF-13: Generación de mapas por ruta**

El sistema debe generar un mapa individual para cada ruta mostrando sus entregas asignadas.

**RF-14: Generación de mapa de casos relevantes**

El sistema debe generar mapas destacando entregas cercanas ignoradas, rutas alejadas de la bodega y clusters con alta dispersión.

**RF-15: Exportación de resultados**

El sistema debe exportar todas las métricas calculadas en formato JSON y CSV, incluyendo: fecha de ejecución, parámetros utilizados (incluyendo `random_seed`, `algorithm`, `algorithm_version`), dataset, métricas por ruta, indicadores globales y referencias a imágenes generadas. Además debe exportar un archivo `deliveries.csv` con datos individuales por entrega (delivery_id, route_id, lat, lng, distance_to_warehouse, distance_to_centroid).

**RF-16: Distancia total estimada de ruta**

El sistema debe calcular una estimación de distancia recorrida para cada ruta considerando el trayecto desde la bodega hasta la última entrega. Se utilizará la secuencia almacenada en la base de datos (`route_packages.sequence`); si la ruta no posee secuencia explícita, se usará el orden de asignación registrado. La secuencia utilizada debe exportarse junto con los resultados para garantizar reproducibilidad. El retorno a la bodega no se incluye en esta fase. Esta métrica será la línea base para futuras comparaciones con rutas optimizadas.

**RF-17: Separación mínima entre clusters**

El sistema debe calcular la distancia mínima entre los centroides de cada par de rutas. Esto permite cuantificar qué tan separados están los agrupamientos entre sí. Resultado esperado: tabla de distancias entre pares de rutas y valor mínimo global.

**RF-18: Anomalía operacional total**

El sistema debe calcular una penalización acumulada por entregas cercanas ignoradas. Para cada anomalía detectada, se calcula: `centroid_distance / delivery_distance` (adimensional). La métrica final es la suma de todas las penalizaciones. Un valor de 0 indica clustering perfecto (sin anomalías); valores altos indican asignaciones ineficientes. Esta métrica permite afirmaciones cuantitativas del tipo: "la estrategia A redujo la penalización operacional total de 43.8 a 12.1".

---

## 8. Requisitos No Funcionales

- **Reproducibilidad**: Dada la misma entrada y parámetros, el sistema debe producir resultados idénticos.
- **Rendimiento**: El cálculo de métricas para una ejecución típica (hasta 500 entregas, 10 rutas) debe completarse en menos de 30 segundos.
- **Portabilidad**: El sistema debe ejecutarse en el entorno Docker existente sin dependencias externas adicionales.
- **Extensibilidad**: Debe ser posible agregar nuevas métricas sin modificar las existentes.

---

## 9. Métricas

Esta especificación define las siguientes métricas como entregables del sistema:

| # | Métrica | Tipo | Unidad |
|---|---------|------|--------|
| 1 | Entregas por ruta | Por ruta | Conteo |
| 2 | Distancia bodega → punto más cercano | Por ruta | km |
| 3 | Distancia bodega → punto más lejano | Por ruta | km |
| 4 | Distancia promedio a la bodega | Por ruta | km |
| 5 | Distancia centroide → bodega | Por ruta | km |
| 6 | Radio del cluster | Por ruta | km |
| 7 | Distancia promedio al centroide | Por ruta | km |
| 8 | Índice de balance de carga | Global | Adimensional |
| 9 | Entregas cercanas ignoradas | Por caso | Listado |
| 10 | Ranking de rutas por cercanía | Global | Ordenado |
| 11 | Cobertura territorial | Global | km |
| 12 | Distancia promedio general | Global | km |
| 13 | Desviación estándar de distancias | Global | km |
| 14 | Balance general | Global | Adimensional |
| 15 | Distancia total estimada de ruta | Por ruta | km |
| 16 | Separación mínima entre clusters | Global | km |
| 17 | Anomalía operacional total | Global | Adimensional |

---

## 10. Criterios de Aceptación

1. El sistema calcula y reporta las métricas definidas para cada ruta y a nivel global.
2. El sistema detecta automáticamente entregas cercanas ignoradas según los umbrales configurados y las reporta en un listado.
3. El sistema genera al menos un mapa general, un mapa por ruta y mapas de casos relevantes por ejecución.
4. Los resultados se exportan en JSON y CSV incluyendo `random_seed`, `algorithm`, `algorithm_version`, fecha, parámetros, dataset, métricas por ruta, indicadores globales, datos por entrega (`deliveries.csv`) y referencias a imágenes.
5. Dada la misma entrada y semilla, dos ejecuciones producen resultados idénticos (reproducibilidad).
6. El sistema permite comparar los resultados de dos ejecuciones distintas lado a lado.
7. Todas las métricas se expresan en unidades claras (km, conteo, adimensional).
8. Los umbrales `near_delivery_threshold` e `ignored_delivery_ratio` son configurables sin modificar el código del sistema de métricas.
9. El sistema calcula la distancia total estimada por ruta considerando la secuencia almacenada o el orden de asignación registrado.
10. El sistema calcula la separación mínima entre centroides de rutas y la reporta como indicador global.
11. El sistema calcula la penalización operacional total como sumatoria de `centroid_distance / delivery_distance` para todas las anomalías detectadas.

---

## Restricciones

Toda especificación debe cumplir la Constitución del proyecto.

Especialmente:
- **Evidencia antes de solución**: Este sistema de métricas debe implementarse antes de cualquier nuevo algoritmo de optimización.
- **Decisiones medibles**: Todas las métricas definidas son cuantificables y reproducibles.
- **Complejidad incremental**: No se implementan visualizaciones interactivas ni dashboards; solo la generación de imágenes estática.
- **Optimizaciones comparables**: El sistema debe permitir comparar ejecuciones con diferentes parámetros.
- **Visualización como análisis**: Los mapas generados son parte obligatoria del análisis.

Las features que contradigan estos principios deben ser rechazadas o modificadas.

---

## Regla de Evolución

Esta nueva feature cumple las siguientes condiciones:

1. **Representa un problema operacional real**: La evaluación subjetiva de rutas es una limitación identificada en fases previas.
2. **Permite medir una característica del sistema**: Mide calidad de agrupamiento, balance de carga y anomalías operativas.
3. **Introduce una mejora cuantificable**: Transforma observaciones visuales en métricas objetivas.
4. **Permite comparar dos estrategias distintas**: Facilita la comparación directa entre ejecuciones.

Las features que no aporten evidencia o capacidad de medición no deben incorporarse al proyecto.
