# Documento Técnico v2 — Impacto de Red Vial en Rutas de Última Milla

**Estado**: Publicado

**Versión**: 2.0

**Fecha**: 2026-06-22

## Resumen

Este documento consolida los hallazgos de SPEC-006 (integración de red vial OSRM) y SPEC-007 (visualización de red vial), extendiendo el documento técnico v1 con 6 nuevos hallazgos (H007–H012) que demuestran el impacto de reemplazar distancias geodésicas por distancias calculadas sobre una red vial real en la evaluación de rutas de última milla.

El hallazgo principal (H012) establece que la distancia vial total para 150 entregas en Valparaíso es 523.11 km, un 54.3% mayor que la distancia geodésica equivalente de 339.06 km, con una diferencia absoluta de +184 km.

---

## 1. Base

- `documento-tecnico-v1.md`: todos los hallazgos H001–H006 preservados.
- SPEC-006: Integración de red vial OSRM con patrón Strategy en DistanceService.
- SPEC-007: Visualización de red vial con toggle geodésico/vial y validación visual.
- EXP-002: Comparación geodésico vs vial — 12 evaluaciones (6 pares) sobre dataset Valparaíso Demo.

---

## 2. Metodología

### 2.1 Diseño experimental

EXP-002 siguió un diseño de pares apareados: para cada configuración de evaluación (seed, threshold, ratio), se ejecutaron dos variantes idénticas cambiando únicamente el modo de distancia (`distance_mode: geodesic` vs `distance_mode: vial`). Esto garantiza que cualquier diferencia en las métricas sea atribuible exclusivamente al modelo de distancia.

### 2.2 Motor de ruteo vial

Se integró OSRM (Open Source Routing Machine) como servicio contenerizado, expuesto en `http://localhost:5001`. El backend Laravel consume OSRM mediante GuzzleHttp con el parámetro `overview=full&geometries=geojson`, obteniendo la geometría completa de cada tramo entre entregas consecutivas.

### 2.3 Métricas nuevas

| ID | Métrica | Descripción |
|----|---------|-------------|
| M001 | Desvío vial promedio | Razón entre distancia vial y geodésica promedio por ruta (factor 1.62×) |
| M002 | Variación de desvío por ruta | Rango de factores de desvío entre rutas (1.42× a 2.49×) |
| M006 | Territorial Distortion Index | Clasificación por ruta: normal ≤1.2, elevada ≤1.5, alta ≤2.0, crítica >2.0 |

### 2.4 Validación de hallazgos previos

Todos los hallazgos H001–H006 fueron re-evaluados con modo vial mediante el protocolo V001–V006 (ver sección 5).

---

## 3. Hallazgos acumulados

| ID | Hallazgo | Fuente |
|----|----------|--------|
| H001–H006 | Baseline geodésico (ver v1) | SPEC-003/004 |
| H007 | Factor vial 1.62× promedio, variación 1.42×–2.49× por ruta | SPEC-006 |
| H008 | Distorsión territorial crítica (>2.0) en rutas que cruzan la bahía | SPEC-006 |
| H009 | 100% rutas con TDI anormal; 60% alta o crítica | SPEC-006 |
| H010 | Modo vial ~330x más lento que geodésico | SPEC-006 |
| H011 | evaluation.json: 84 KB → 2.3 MB (+2640%) con geometría vial | SPEC-007 |
| H012 | Distancia vial +54.3% sobre geodésico (339→523 km, +184 km) | SPEC-007 |

---

## 4. Análisis detallado de hallazgos

### 4.1 H007 — Factor de desvío vial sistemático

Las distancias sobre red vial son sistemáticamente mayores que las geodésicas en un factor promedio de 1.62× (M001) para Gran Valparaíso. La variación por ruta (M002) oscila entre 1.42× y 2.49×, indicando que el impacto de la red vial no es uniforme en el territorio.

**Implicancia**: El uso de distancia geodésica subestima sistemáticamente los costos operacionales reales. Para planificación operativa se debe usar modo vial. Modo geodésico es aceptable solo para prototipado rápido.

### 4.2 H008 — Distorsión territorial crítica en la bahía

Las rutas que cruzan la bahía de Valparaíso presentan distorsión territorial crítica (TDI > 2.0, M006). En línea recta son ~23 km pero por carretera deben rodear toda la bahía (~58 km), más del doble. La Ruta D en ambas réplicas muestra TDI de 2.485 y 2.475.

**Implicancia**: La geografía de Valparaíso (bahía, cerros, quebradas) genera distorsiones extremas que deben considerarse en el diseño de rutas. Las asignaciones geodésicas para sectores al otro lado de la bahía son inviables en la práctica.

### 4.3 H009 — Distribución de distorsión territorial

| Clasificación TDI | Rango | Porcentaje de rutas |
|-------------------|-------|--------------------|
| Normal | ≤1.2 | 0% |
| Elevada | ≤1.5 | 40% |
| Alta | ≤2.0 | 40% |
| Crítica | >2.0 | 20% |

El 100% de las rutas en Gran Valparaíso presentan distorsión territorial anormal. El 60% tiene distorsión alta o crítica. Ninguna ruta en el área metropolitana puede considerarse libre de distorsión vial.

### 4.4 H010 — Costo computacional del modo vial

El modo vial es ~330× más lento que el geodésico: ~82 segundos vs ~0.25 segundos por evaluación. Esto se debe a aproximadamente 1500 llamadas HTTP a OSRM (una por cada tramo entre entregas consecutivas).

**Implicancia**: Para uso interactivo se necesita optimización (caching, consultas por lote, o pre-cálculo). El modo geodésico es preferible para exploración iterativa.

### 4.5 H011 — Impacto en almacenamiento

| Modo | Tamaño evaluation.json | Diferencia |
|------|----------------------|------------|
| Baseline (sin route_legs) | 84 KB | — |
| Geodésico con route_legs | 132 KB | +57% |
| Vial con route_legs | 2.3 MB | +2640% |

El tamaño vial (2.3 MB) está dentro de lo aceptable para una respuesta de API que se carga una vez por evaluación (~2-3 segundos en 10 Mbps). No se requiere optimización inmediata.

### 4.6 H012 — Diferencia total geodésico vs vial

**Hallazgo principal**: La distancia vial total para 150 entregas en Valparaíso (5 rutas, 30 entregas/ruta) es 523.11 km, un **54.3% mayor** que la distancia geodésica equivalente de 339.06 km, con una diferencia absoluta de **+184 km**.

**Desglose por ruta**:

| Ruta | Geodésico (km) | Vial (km) | Diferencia (km) | Factor |
|------|:-:|:-:|:-:|:-:|
| Ruta A | 37.87 | 53.78 | +15.91 | 1.42× |
| Ruta B | 91.46 | 126.07 | +34.61 | 1.38× |
| Ruta C | 107.09 | 176.05 | +68.96 | 1.64× |
| Ruta D | 36.35 | 72.80 | +36.45 | **2.00×** |
| Ruta E | 66.28 | 94.41 | +28.13 | 1.42× |
| **Total** | **339.06** | **523.11** | **+184.06** | **1.54×** |

Ruta D duplica la distancia vial (factor 2.00×), consistente con H008 (rutas que cruzan la bahía). Las 30 entregas de Ruta D generan 72.80 km viales vs solo 36.35 km geodésicos.

---

## 5. Validaciones V001–V006

Todos los hallazgos H001–H006 del baseline (modo geodésico) fueron re-evaluados con modo vial. Ninguno se invalida.

| ID | Hallazgo Original | Estado | Evidencia |
|----|-------------------|--------|-----------|
| V001 | H001 (Balance — balance_index = 1.0) | Válido | Exp002, Eval 24–35 |
| V002 | H002 (CV < 0.1 en ambos modos) | Válido | Exp002, Eval 24–35 |
| V003 | H003 (Invarianza — cobertura territorial idéntica) | Válido | Exp002, Eval 24–35 |
| V004 | H004 (Threshold — detección de anomalías no afectada) | Válido | Exp002, Eval 24–35 |
| V005 | H005 (Dispersión — estructura de clusters idéntica) | Válido | Exp002, Eval 24–35 |
| V006 | H006 (Micro-ruteo — entregas cercanas en sector B) | Válido | Exp002, Eval 24–35 |

**Conclusión**: Las conclusiones cualitativas del baseline (balance, invarianza, dispersión, anomalías) son robustas al cambio de modo de distancia. La estructura de hallazgos del sistema es estable.

---

## 6. Discusión

### 6.1 Impacto operacional del desvío vial

184 km adicionales por operación diaria representan un incremento significativo en costos operacionales. En una operación de 6 días a la semana, el sobrecosto anual sería de aproximadamente 57,400 km adicionales — equivalente a 1.4 vueltas completas al mundo.

Este hallazgo tiene implicaciones directas para:
- **Planificación de flotas**: Si se usan distancias geodésicas para estimar tiempos, las rutas reales serán sistemáticamente más largas, generando presiones sobre los tiempos de conducción.
- **Costos de combustible**: Un 54.3% de incremento en distancia se traduce directamente en mayor consumo de combustible.
- **Sostenibilidad**: La huella de carbono real de la operación es significativamente mayor que la estimada geodésicamente.

### 6.2 Territorial Distortion Index como herramienta de diagnóstico

M006 (TDI) demostró ser un indicador valioso para identificar rutas donde la red vial impone restricciones severas. La Ruta D (TDI crítico > 2.0) representa un caso extremo donde cualquier planificación basada en distancia geodésica es fundamentalmente incorrecta.

### 6.3 Robustez de los hallazgos baseline

La validación V001–V006 confirma que las conclusiones del estudio baseline (SPEC-004) son robustas al cambio de modelo de distancia. Esto es relevante porque sugiere que ciertas propiedades del sistema (balance, estructura de clusters) son intrínsecas a la distribución geográfica de las entregas y no dependen del modelo de distancia utilizado.

---

## 7. Limitaciones

- El estudio se realizó exclusivamente sobre Valparaíso. Los factores de desvío pueden variar en otras ciudades con diferente topografía.
- OSRM utiliza OpenStreetMap como fuente cartográfica. La calidad de los datos viales depende de la cobertura de OSM en la zona.
- El tiempo de ejecución (~82s por evaluación vial) limita la escalabilidad del método para grandes volúmenes de evaluaciones.

---

## 8. Conclusiones

1. La red vial real incrementa las distancias en un 54.3% respecto al modelo geodésico para el dataset de Valparaíso (H012).
2. El factor de desvío no es uniforme: varía entre 1.38× y 2.00× por ruta (H007, H008).
3. El 100% de las rutas presentan distorsión territorial anormal (H009), siendo la Ruta D la más afectada (TDI crítico).
4. Las validaciones V001–V006 confirman que los hallazgos del baseline son robustos al cambio de modelo de distancia.
5. Se recomienda usar distancia vial para planificación operativa y distancia geodésica solo para prototipado rápido (H010).

---

## 9. Próximos pasos

- **SPEC-008**: Visual Analytics para Comparación de Rutas — herramienta de análisis visual comparativo que permita explorar visualmente los hallazgos de este documento.
- **documento-tecnico-v3**: Extensión con hallazgos de visualización comparativa y análisis visual avanzado.
- **PUB-003**: Publicación derivada centrada en la comparación visual de modelos de distancia.

---

## Trazabilidad

- Última actualización: 2026-06-22
- SPEC origen: SPEC-006, SPEC-007
- Experimentos: EXP-002
- Versión documento técnico: 2.0
- Hallazgos: H001–H012
- Validaciones: V001–V006
