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
