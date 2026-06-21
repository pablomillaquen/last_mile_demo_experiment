# Preguntas de Investigación

*Líneas de investigación activas que guían el desarrollo del proyecto.*

Cada pregunta tiene:
- **ID único**: PI-001, PI-002, ...
- **Pregunta**: Formulación clara y acotada.
- **Estado**: Abierta / Respondida / En investigación.
- **Hallazgos relacionados**: Referencias a H-XXX.
- **Fase objetivo**: En qué fase del roadmap se abordará.

---

## PI-001

**Pregunta**: ¿Cómo afecta la distribución geográfica de paquetes al balance de carga entre rutas?

**Estado**: Respondida parcialmente.

**Hallazgos relacionados**: H001, H005.

**Fase objetivo**: Fase 4 — Optimización Algorítmica.

---

## PI-002

**Pregunta**: ¿Qué variables explican mejor la aparición de anomalías operacionales?

**Estado**: Respondida.

**Hallazgos relacionados**: H004.

**Fase objetivo**: Fase 3 — Experimentación (completada).

---

## PI-003

**Pregunta**: ¿Qué métricas del sistema son invariantes ante cambios en parámetros de evaluación y cuáles son sensibles?

**Estado**: Respondida.

**Hallazgos relacionados**: H003.

**Fase objetivo**: Fase 3 — Experimentación (completada).

---

## PI-004

**Pregunta**: ¿Cuál de los parámetros de detección de anomalías (threshold, ratio) tiene mayor impacto en los resultados?

**Estado**: Respondida.

**Hallazgos relacionados**: H004.

**Fase objetivo**: Fase 3 — Experimentación (completada).

---

## PI-005

**Pregunta**: ¿Es posible reducir la penalidad operacional mediante redespliegue de entregas cercanas a bodega en rutas dedicadas?

**Estado**: Abierta.

**Hallazgos relacionados**: H006.

**Fase objetivo**: Fase 4 — Optimización Algorítmica.

---

## PI-006

**Pregunta**: ¿Cuál es el impacto de reemplazar distancias geodésicas por distancias calculadas sobre una red vial real en las métricas operacionales del sistema?

**Estado**: En investigación.

**Hallazgos relacionados**: H001–H006, V001–V006.

**Fase objetivo**: SPEC-006.

---

## PI-007

**Pregunta**: ¿Qué métricas son más sensibles al cambio de modelo de distancia?

**Estado**: En investigación.

**Hallazgos relacionados**: H001–H006, V001–V006.

**Fase objetivo**: SPEC-006.

---

## PI-008

**Pregunta**: ¿Cuál es la diferencia promedio entre distancia geodésica y distancia vial para las evaluaciones existentes?

**Estado**: En investigación.

**Hallazgos relacionados**: M001, M002.

**Fase objetivo**: SPEC-006.

---

## PI-009

**Pregunta**: ¿Se mantienen los rankings relativos de calidad entre rutas después de incorporar una red vial real?

**Estado**: En investigación.

**Hallazgos relacionados**: M004.

**Fase objetivo**: SPEC-006.

---

## PI-010

**Pregunta**: ¿Qué hallazgos obtenidos previamente (H001–H006) permanecen válidos y cuáles deben revisarse?

**Estado**: En investigación.

**Hallazgos relacionados**: V001–V006, M005.

**Fase objetivo**: SPEC-006.

---

## PI-011

**Pregunta**: ¿Existen zonas geográficas cuya configuración vial produce sistemáticamente mayores costos operacionales que los estimados por distancia geodésica?

**Estado**: En investigación.

**Hallazgos relacionados**: M006, H008.

**Fase objetivo**: SPEC-006.

---

## PI-012

**Pregunta**: ¿Cuál es el impacto computacional de reemplazar cálculo geodésico local por consultas a red vial (OSRM) en el tiempo de ejecución de una evaluación completa?

**Estado**: En investigación.

**Hallazgos relacionados**: RNF2, execution_time_sec.

**Fase objetivo**: SPEC-006.

---

## PI-013

**Pregunta**: ¿Varía el factor de desvío geodésico-vial (M002) según la morfología urbana de una ciudad? (Valparaíso topografía compleja vs Santiago trama regular vs Concepción estructura policéntrica)

**Estado**: Abierta (futura).

**Hallazgos relacionados**: M002.

**Fase objetivo**: SPEC-008+.

---

## PI-014

**Pregunta**: ¿Cuál es el impacto de la extensión geográfica del grafo OSRM (ciudad, región o país completo) sobre el costo computacional y la estabilidad de las métricas de evaluación?

**Estado**: Abierta.

**Hallazgos relacionados**: D006.

**Fase objetivo**: SPEC-008+.
