# Hallazgos Formales

*Registro acumulativo de conocimiento validado experimentalmente.*

Cada hallazgo tiene:
- **ID único**: H001, H002, ...
- **Enunciado**: Conclusión respaldada por evidencia.
- **Evidencia**: Experimentos, evaluaciones o SPECs que lo sustentan.
- **Impacto**: Consecuencia para el diseño del sistema o futuras investigaciones.
- **Preguntas que responde**: Referencias a PI-XXX.

---

## H001

**Enunciado**: La minimización de distancia recorrida no garantiza una distribución equilibrada de carga entre rutas.

**Evidencia**: SPEC-003, Evaluaciones 2, 4 y 6.

**Impacto**: Justifica incorporar métricas de balance operacional como criterio independiente de optimización.

**Preguntas que responde**: PI-001.

---

## H002

**Enunciado**: La selección del punto inicial de una ruta tiene un impacto significativo en la distancia total recorrida.

**Evidencia**: SPEC-004, Experimento 001.

**Impacto**: Motiva futuras estrategias de optimización geográfica del punto de partida.

**Preguntas que responde**: PI-002.

---

## H003

**Enunciado**: Las métricas globales de distancia promedio, cobertura territorial y desviación estándar son invariantes ante cambios en parámetros de detección de anomalías (threshold, ratio). Solo varían con la asignación de paquetes a rutas.

**Evidencia**: SPEC-004, Experimento 001 (Evaluaciones 2–7 comparten mismas métricas de ruta).

**Impacto**: Valida que el diseño experimental puede aislar el efecto de parámetros de anomalía sin necesidad de re-ejecutar asignaciones.

**Preguntas que responde**: PI-003.

---

## H004

**Enunciado**: El parámetro `ignored_delivery_ratio` es más discriminatorio que `near_delivery_threshold_km` para controlar la detección de anomalías. Threshold define el perímetro de búsqueda; ratio filtra la severidad.

**Evidencia**: SPEC-004, Experimento 001 (Evaluación 5 con ratio 1.5 detecta solo 4 anomalías vs 10 con ratio 2).

**Impacto**: Guía la calibración de sistemas de detección en producción: priorizar ajuste de ratio sobre threshold.

**Preguntas que responde**: PI-004.

---

## H005

**Enunciado**: La distribución de paquetes en el territorio de Valparaíso genera clusters de rutas con dispersión radicalmente distinta (Ruta A: radio 0.3 km vs Ruta E: radio 15.3 km), indicando heterogeneidad geográfica significativa.

**Evidencia**: SPEC-004, Experimento 001, sección de Rutas Destacadas.

**Impacto**: Sugiere que estrategias de optimización deben ser específicas por cluster, no uniformes para todas las rutas.

**Preguntas que responde**: PI-001, PI-005.

---

## H006

**Enunciado**: Las 10 entregas más cercanas a bodega (< 1 km) pertenecen exclusivamente a rutas del sector B, lo que sugiere una oportunidad de rediseño mediante rutas exprés locales para reducir penalidad operacional.

**Evidencia**: SPEC-004, Experimento 001 (Anomalías Detectadas, Evaluación #2).

**Impacto**: Abre línea de investigación en micro-ruteo local como estrategia de optimización.

**Preguntas que responde**: PI-005.

---

## H007

**Enunciado**: Las distancias sobre red vial son sistemáticamente mayores que las geodésicas en un factor de 1.62 (M001) para Gran Valparaíso, con variación por ruta entre 1.42 y 2.49 (M002).

**Evidencia**: SPEC-006, Exp002. 6 pares de evaluaciones (12 evaluaciones) comparando modo geodésico vs vial con OSRM.

**Impacto**: El uso de distancia geodésica subestima sistemáticamente los costos operacionales reales. Para planificación operativa se debe usar modo vial. Modo geodésico es aceptable solo para prototipado rápido.

**Preguntas que responde**: PI-006, PI-007, PI-008.

---

## H008

**Enunciado**: Las rutas que cruzan la bahía de Valparaíso presentan distorsión territorial crítica (TDI > 2.0, M006). En línea recta son ~23 km pero por carretera deben rodear toda la bahía (~58 km), más del doble.

**Evidencia**: SPEC-006, Exp002. Ruta D en ambas réplicas muestra TDI de 2.485 y 2.475.

**Impacto**: La geografía de Valparaíso (bahía, cerros, quebradas) genera distorsiones extremas que deben considerarse en el diseño de rutas. Las asignaciones geodésicas para sectores al otro lado de la bahía son inviables en la práctica.

**Preguntas que responde**: PI-007, PI-011.

---

## H009

**Enunciado**: En Gran Valparaíso, el 100% de las rutas tienen distorsión territorial anormal (TDI > 1.2). El 60% presenta distorsión alta o crítica.

**Evidencia**: SPEC-006, Exp002. M006: 0% normal, 40% elevada, 40% alta, 20% crítica.

**Impacto**: Ninguna ruta en Valparaíso puede considerarse libre de distorsión vial. La red vial afecta significativamente a todas las zonas del área metropolitana.

**Preguntas que responde**: PI-011.

---

## H010

**Enunciado**: El modo vial es ~330x más lento que el geodésico (~82s vs ~0.25s por evaluación) debido a ~1500 llamadas HTTP a OSRM.

**Evidencia**: SPEC-006, Exp002. Tiempos de ejecución registrados en evaluaciones 24–35.

**Impacto**: Para uso interactivo, se necesita optimización (caching, consultas por lote, o pre-cálculo). El modo geodésico es preferible para exploración iterativa.

**Preguntas que responde**: PI-012.

---

## H011

**Enunciado**: El almacenamiento de geometría vial OSRM incrementa el tamaño de `evaluation.json` de ~84 KB (sin route_legs) a ~132 KB (geodésico con route_legs, +57%) o ~2.3 MB (vial con route_legs, +2640%), debido a ~22,000 puntos de geometría para 155 legs.

**Evidencia**: SPEC-006A. Mediciones directas: baseline (84 KB), geodésico con route_legs (132 KB, 155 legs, 2 pts/leg), vial con route_legs (2.3 MB, 155 legs, 22,177 pts totales, promedio 143 pts/leg).

**Impacto**: 
1. El tamaño vial (2.3 MB) está dentro de lo aceptable para una respuesta de API que se carga una vez por evaluación (2-3s en 10 Mbps). No impacta el endpoint de listado (no incluye route_legs).
2. La estimación inicial de research.md (~200-300 KB) era precisa para modo geodésico pero subestima por ~10x el tamaño vial.
3. Para experimentos con datasets más grandes, el tamaño podría escalar linealmente (~15 KB por ruta por 10 entregas). Monitorear si supera 10 MB en producción.
4. No se requiere optimización inmediata, pero se registra para SPEC-008 (vista comparativa simultánea) donde podrían necesitarse ambas geometrías en una misma respuesta.

**Preguntas que responde**: PI-013.

## H012

**Enunciado**: La distancia vial total para 150 entregas en Valparaíso (5 rutas, 30 entregas/ruta) es 523.11 km, un 54.3% mayor que la distancia geodésica equivalente de 339.06 km, con una diferencia absoluta de +184 km.

**Evidencia**: SPEC-006A. Comparación directa Eval #18 (modo geodésico, 339.06 km) vs Eval #19 (mismo dataset, modo vial, 523.11 km). Mismo `parameters_hash`, único cambio `distance_mode`. Dataset Valparaíso Demo, 150 entregas, 5 rutas (A–E), 30 entregas/ruta.

**Desglose por ruta**:

| Ruta | Geodésico (km) | Vial (km) | Diferencia (km) | Factor |
|------|:-:|:-:|:-:|:-:|
| Ruta A | 37.87 | 53.78 | +15.91 | 1.42× |
| Ruta B | 91.46 | 126.07 | +34.61 | 1.38× |
| Ruta C | 107.09 | 176.05 | +68.96 | 1.64× |
| Ruta D | 36.35 | 72.80 | +36.45 | **2.00×** |
| Ruta E | 66.28 | 94.41 | +28.13 | 1.42× |
| **Total** | **339.06** | **523.11** | **+184.06** | **1.54×** |

**Impacto**:
1. La hipótesis de SPEC-006 (distancias viales sistemáticamente mayores que geodésicas) se confirma cuantitativamente con datos del SPEC-006A.
2. Ruta D duplica la distancia vial (factor 2.00×), consistente con el hallazgo H008 (Rutas que cruzan la bahía de Valparaíso tienen TDI crítico >2.0). Las 30 entregas de Ruta D generan 72.80 km viales vs solo 36.35 km geodésicos.
3. El factor promedio 1.54× para este dataset específico difiere del factor M001=1.62× reportado en H007/SPEC-006, sugiriendo que el factor varía con la distribución geográfica y asignación de entregas, no solo con la zona geográfica.
4. El impacto operacional es significativo: 184 km adicionales equivalen a ~3-4 horas de conducción extra por operación diaria.

**Preguntas que responde**: PI-006, PI-007, PI-008.

---

## V001–V006: Validaciones de Hallazgos Baseline con Red Vial

Todos los hallazgos H001–H006 del baseline (modo geodésico) fueron re-evaluados con modo vial. Ninguno se invalida.

| ID | Hallazgo Original | Estado | Evidencia |
|----|-------------------|--------|-----------|
| V001 | H001 (Balance) | **Válido** — balance_index = 1.0 en ambos modos | Exp002, Evaluaciones 24–35 |
| V002 | H002 (CV) | **Válido** — CV < 0.1 en modo vial | Exp002, Evaluaciones 24–35 |
| V003 | H003 (Invarianza) | **Válido** — cobertura territorial idéntica | Exp002, Evaluaciones 24–35 |
| V004 | H004 (Threshold) | **Válido** — detección de anomalías no afectada | Exp002, Evaluaciones 24–35 |
| V005 | H005 (Dispersión) | **Válido** — estructura de clusters idéntica | Exp002, Evaluaciones 24–35 |
| V006 | H006 (Micro-ruteo) | **Válido** — entregas cercanas en sector B | Exp002, Evaluaciones 24–35 |

**Implicancia**: Las conclusiones cualitativas del baseline (balance, invarianza, dispersión, anomalías) son robustas al cambio de modo de distancia. La estructura de hallazgos del sistema es estable.

**Preguntas que responde**: PI-010.

---

## H013

**Enunciado**: La validación de H1 (SplitView, SPEC-008) demostró que una evaluación vial contiene información suficiente para representar simultáneamente trayectorias geodésicas y viales mediante distintas interpretaciones visuales de los mismos `route_legs`. Sin embargo, las métricas cuantitativas (route_metrics, metrics_summary) continúan representando únicamente el modo utilizado durante la ejecución de la evaluación. Por lo tanto, la comparación visual de trayectorias y la comparación cuantitativa entre modelos constituyen problemas distintos.

**Evidencia**: SPEC-008 H1, Evaluation #14 (vial, 155 legs). `geodesicPolylines` se construyen desde `from_lat/lng → to_lat/lng` (disponible en todo leg), `vialPolylines` desde `geometry` OSRM (disponible en legs viales). Ambos paneles del SplitView muestran representaciones diferentes de los mismos datos. Las métricas de la tabla corresponden exclusivamente al modo vial (route_metrics de la evaluación, no del panel izquierdo).

**Impacto**:
1. SPEC-008 no necesita modificar su modelo de datos — el instrumento visual es válido con una sola evaluación vial.
2. La comparación cuantitativa entre modelos geodésico y vial requiere cargar dos evaluaciones pares (vía `evaluation_pairs` de EXP-002) y es un problema de diseño separado.
3. Las métricas mostradas bajo el mapa en modo split corresponden al modo de la evaluación, no al modo del panel. Esto debe documentarse explícitamente en el protocolo M4 para no confundir al observador.

**Preguntas que responde**: PI-016, PI-017.

---

## H014

**Enunciado**: La visualización comparativa simultánea (split view) reduce el esfuerzo de interpretación de diferencias entre modelos geodésico y vial, permitiendo al analista identificar rápidamente la ruta con mayor divergencia sin alternancia manual de modos.

**Evidencia**: SPEC-008, validación visual H2–H4. Observación del investigador: "La diferencia entre un modo y otro se demuestra increíblemente bien" y "Ahora resulta más fácil encontrar qué ruta presenta la mayor diferencia visual."

**Impacto**:
1. HYP-008-01 recibe respaldo cualitativo preliminar: el split view facilita la identificación de divergencias.
2. El instrumento visual cumple su función analítica, no solo decorativa.
3. La observación justifica proceder con M4 (medición cuantitativa de tiempo de identificación).

**Preguntas que responde**: PI-016.

---

## H015

**Enunciado**: El aislamiento de rutas (atenuación de no seleccionadas vs ocultación completa) aumenta la capacidad de análisis al reducir interferencia visual sin perder contexto geográfico, permitiendo inspeccionar una ruta individual dentro del entorno operacional completo.

**Evidencia**: SPEC-008, validación visual H3. Observación del investigador: "La función que más utilicé fue aislar una ruta" y "Permitió identificar una ruta correctamente, sin que las demás escondan el trazo en el mapa."

**Impacto**:
1. RF10 (atenuación en lugar de ocultación) se valida desde una perspectiva analítica: mantener las rutas atenuadas preserva el contexto geoespacial.
2. El aislamiento no es un adorno visual — cumple una función cognitiva concreta: reduce interferencia, mantiene contexto, permite inspección individual.
3. La función de aislamiento resultó ser la más utilizada durante la validación, sugiriendo que es la intervención de mayor valor analítico en SPEC-008.

**Preguntas que responde**: PI-016, PI-017.

---

## H016

**Enunciado**: El RoutePanel (listado interactivo con toggle on/off y aislamiento) aporta control analítico sin aumentar la carga cognitiva percibida, contradiciendo el riesgo de diseño identificado en la fase de planificación (PI-017: "más controles → más complejidad visual").

**Evidencia**: SPEC-008, validación visual H2–H4. Observación del investigador: "Estos controles no añaden ruido. Por el contrario, son muy útiles" y "Resultó muy útil. Creo que fue una buena decisión."

**Impacto**:
1. PI-017 recibe respuesta preliminar: el nivel de detalle visual implementado (panel + toggle + aislamiento) no introduce sobrecarga cognitiva.
2. La hipótesis de riesgo "más controles → más complejidad visual" no se cumple en este contexto experimental.
3. El panel se percibe como un complemento necesario, no como ruido visual, lo que valida la decisión de diseño D015.

**Preguntas que responde**: PI-017.

---

## H017

**Enunciado**: La ausencia de indicadores de dirección de recorrido (flechas, puntos numerados, secuencia de navegación) limita la interpretación operacional de las rutas, incluso cuando la comparación visual entre modelos geodésico y vial es efectiva.

**Evidencia**: SPEC-008, validación visual H2–H4. Observación del investigador: "No logro determinar qué dirección tomó la ruta" y sugerencias de solución: "Si existieran los puntos numerados o si la ruta tuviese forma de flechas, mostrando la dirección del movimiento."

**Impacto**:
1. La principal dificultad de uso no está en los controles ni en la visualización comparativa, sino en la comunicación de dirección y secuencia.
2. Se abre una nueva línea de investigación: ¿qué elementos visuales adicionales son necesarios para comunicar secuencia y dirección de una operación logística?
3. Potencial SPEC-009 enfocada en dirección de rutas, puntos numerados y secuencia de entregas.
4. Este hallazgo es más relevante para la evolución del proyecto que BUG-004 (defecto técnico heredado), porque revela una limitación del instrumento visual actual.

**Preguntas que responde**: PI-017 (abre futura investigación).
