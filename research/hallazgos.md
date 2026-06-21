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
