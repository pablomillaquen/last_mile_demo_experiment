# Feature Specification: Visualización de Red Vial y Comparación Geodésico vs OSRM

**Feature Branch**: `007-road-network-visualization`

**Created**: 2026-06-21

**Status**: Draft

## 1. Context

El sistema actual de evaluación logística incorpora rutas calculadas mediante OSRM y métricas asociadas a red vial real (EXP-002). Sin embargo, la visualización en el mapa aún se basa exclusivamente en representación geodésica (líneas rectas entre puntos de entrega), lo que impide evidenciar visualmente la diferencia entre ambos modelos de distancia.

Esta brecha entre el cálculo analítico (backend) y la representación visual (frontend) reduce la capacidad del sistema para demostrar de forma intuitiva el impacto del uso de rutas reales, limitando el análisis comparativo y la comunicación de resultados.

Dos problemas críticos registrados en research/bugs.md motivan este SPEC:
- **BUG-002**: El mapa no dibuja la geometría vial real entregada por OSRM.
- **BUG-003**: No existe un selector que permita alternar entre vista geodésica y vista vial.

---

## 2. Hipótesis

**H0 (Nula)**: La visualización de rutas viales reales no aporta información adicional significativa respecto a la representación geodésica actual.

**H1 (Alterna)**: La visualización de rutas viales reales permite identificar diferencias cualitativas significativas (trazado por calles, desvíos, barreras geográficas) que la representación geodésica no puede mostrar, mejorando la interpretabilidad del sistema.

---

## 3. Objetivo

Implementar un sistema de visualización dual que permita comparar de forma clara e intuitiva la distancia geodésica (modelo teórico) y la distancia por red vial (modelo OSRM), reforzando la capacidad del sistema para demostrar el impacto del uso de rutas reales en la optimización logística.

El objetivo es exclusivamente visual y explicativo: no se introducen nuevas métricas ni se modifican los modelos de evaluación existentes.

---

## 4. Alcance

### Backend
- Exponer geometría de rutas OSRM en la API existente desde EXP-002 evaluations.
- Incluir polylines o GeoJSON por cada ruta, ya calculados por OSRM, para permitir su renderizado en el frontend.
- El frontend no debe consumir OSRM directamente; la geometría debe ser provista exclusivamente por el backend.

### Frontend
- Modificar el componente de mapa para soportar dos modos de renderizado:
  - **Geodésico** (líneas rectas entre puntos, comportamiento actual).
  - **Vial** (geometría real OSRM, trazado por calles).
- Agregar un selector de visualización (toggle o botones de selección) que permita alternar entre ambos modos sin recargar la página.
- Renderizar polilíneas distintas según el modo seleccionado.

### Cobertura experimental
- EXP-001 (línea base geodésica) debe visualizarse correctamente en modo geodésico.
- EXP-002 (comparación geodésico vs vial) debe poder visualizarse en ambos modos.

---

## 5. Exclusiones

- No se introducen nuevas métricas de evaluación.
- No se modifica el esquema persistente de base de datos de experimentos o evaluaciones.
- No se modifican los algoritmos de cálculo de rutas (OSRM, DistanceService).
- No se implementa edición o modificación de rutas desde el mapa.
- No se implementa animación de rutas ni reproducción temporal.
- No se implementa comparación lado a lado (split screen) en esta fase.
- No se modifican los datos históricos de EXP-001 ni EXP-002.

---

## 6. Historias de Usuario

**HU1 — Visualización de ruta vial**
**Como** analista logístico
**Quiero** ver el trazado real de una ruta sobre la red de calles
**Para** evaluar visualmente si la secuencia de entregas es factible en el territorio real

**HU2 — Comparación de modos de ruta**
**Como** evaluador del sistema
**Quiero** alternar entre vista geodésica y vista vial sin recargar la página
**Para** comparar visualmente la diferencia entre ambos modelos de distancia

**HU3 — Consistencia visual con métricas**
**Como** investigador
**Quiero** que la visualización vial coincida con las métricas de distancia vial ya calculadas
**Para** validar que la representación gráfica es consistente con los resultados analíticos

**HU4 — Exploración de experimentos previos**
**Como** usuario del sistema
**Quiero** visualizar rutas de EXP-001 y EXP-002 en ambos modos
**Para** apreciar la evolución del modelo y la diferencia entre aproximaciones

---

## 7. Requisitos Funcionales

| ID | Descripción | Relación |
|---|---|---|
| RF1 | El sistema debe permitir visualizar rutas en modo geodésico (líneas rectas entre puntos de entrega) | HU1 |
| RF2 | El sistema debe permitir visualizar rutas en modo vial (trazado sobre red de calles usando geometría OSRM) | HU1 |
| RF3 | El usuario debe poder alternar entre modo geodésico y modo vial sin recargar la página | HU2 |
| RF4 | Las rutas viales deben seguir la red de calles real proporcionada por OSRM | HU1 |
| RF5 | La visualización debe mantener consistencia con las métricas ya calculadas para cada ruta | HU3 |
| RF6 | El sistema debe exponer la geometría de cada ruta (polyline/GeoJSON) a través de la API | HU1 |
| RF7 | El selector de modo debe indicar claramente qué modo está activo en cada momento | HU2 |
| RF8 | Las evaluaciones de EXP-001 deben visualizarse correctamente al menos en modo geodésico | HU4 |
| RF9 | Las evaluaciones de EXP-002 deben poder visualizarse en ambos modos | HU4 |
| RF10 | Si una ruta no contiene geometría vial disponible, el sistema debe renderizar automáticamente la vista geodésica sin error ni advertencia | HU1 |
| RF11 | La geometría vial debe respetar el orden de secuencia de entrega definido en route_packages.sequence | HU3 |
| RF12 | La geometría vial debe ser una lista ordenada de coordenadas [lat, lng] representando el trazado exacto de la ruta, sin ambigüedad de codificación | HU1 |

---

## 8. Requisitos No Funcionales

| ID | Descripción |
|---|---|
| RNF1 | El cambio de modo de visualización debe ser inmediato (<200ms) |
| RNF2 | La funcionalidad de visualización no debe afectar el cálculo de métricas existentes |
| RNF3 | No se requiere modificar los datos de EXP-001 ni EXP-002 |
| RNF4 | La geometría vial debe cargarse eficientemente (no debe degradar el rendimiento del mapa) |

---

## 9. Métricas

| Métrica | Descripción | Cómo se mide |
|---|---|---|
| M1 | Tiempo de cambio entre modos | Tiempo desde que el usuario hace clic en el selector hasta que el mapa renderiza el nuevo trazado |
| M2 | Coherencia visual vs métrica | Verificación visual de que la longitud de la polyline vial corresponde a la distancia vial reportada |
| M3 | Cobertura de rutas con geometría | Porcentaje de rutas en EXP-002 que pueden visualizarse en modo vial |

---

## 10. Criterios de Aceptación

| ID | Criterio | Tipo |
|---|---|---|
| CA1 | Un usuario puede abrir una ruta de EXP-002 en el mapa y ver el trazado por calles reales | Funcional |
| CA2 | Un usuario puede alternar entre modo geodésico y modo vial al menos 5 veces sin errores ni recarga de página | Funcional |
| CA3 | La distancia visual de la polyline vial coincide con la distancia vial registrada en la evaluación (tolerancia <1%) | Validación |
| CA4 | El selector de modo muestra claramente cuál es el modo activo | UX |
| CA5 | Las rutas de EXP-001 se visualizan correctamente en modo geodésico sin requerir geometría vial | Compatibilidad |
| CA6 | El cambio de modo toma menos de 200ms en una conexión de red local | Rendimiento |

---

## Restricciones

Toda especificación debe cumplir la Constitución del proyecto.

Especialmente:
- **Evidencia antes de solución**: La visualización vial evidencia visualmente la diferencia ya calculada analíticamente en EXP-002.
- **Decisiones medibles**: La coherencia entre métrica y visualización (M2) es verificable.
- **Complejidad incremental**: Esta fase es exclusivamente visual; no introduce nuevos modelos ni métricas.
- **Optimizaciones comparables**: La visualización dual permite comparar ambas estrategias en el mismo mapa.
- **Visualización como análisis**: Este SPEC es una aplicación directa del principio VI.

---

## Regla de Evolución

Esta nueva feature cumple las siguientes condiciones:
1. **Representa un problema operacional real**: La imposibilidad de ver rutas reales en el mapa limita el análisis visual.
2. **Permite medir una característica del sistema**: La cobertura de geometría vial y el tiempo de cambio entre modos son medibles.
3. **Introduce una mejora cuantificable**: La comparación visual directa mejora la interpretabilidad del sistema.
4. **Permite comparar dos estrategias distintas**: Visualización geodésica vs vial en un mismo contexto.

---

## Comentarios de implementación (para el agente)

### 1. Fuente de verdad de geometría vial
- La geometría OSRM debe provenir exclusivamente de EXP-002 (evaluation.route_geometry o equivalente).
- Prohibido recalcular rutas en frontend.
- Prohibido aproximar geometría con líneas rectas en modo "vial".

### 2. Modo geodésico vs vial
- Modo geodésico = cálculo visual entre coordenadas de paquetes (sistema actual).
- Modo vial = polyline OSRM entregada por backend.
- Ambos modos deben coexistir sobre el mismo dataset sin transformación de datos.

### 3. Integridad de EXP-001 y EXP-002
- EXP-001 es baseline histórico (inmutable).
- EXP-002 es comparación experimental.
- Este SPEC no puede modificar datos de evaluaciones ni experimentos.
- Solo lectura.

### 4. Evitar corrupción de estado (LECCIÓN de BUG-001)
- No modificar experiment.json automáticamente.
- No reescribir evaluation_ids.
- No ejecutar sync sobre EXP-001.

### 5. Contrato de visualización
- Cada ruta debe poder renderizarse en:
  a) geodésico (fallback)
  b) vial (si geometry existe)
- Si geometry falta en EXP-002 → fallback explícito a geodésico, no error silencioso.

### 6. UX mínima obligatoria
- El selector debe ser persistente durante navegación del mapa.
- El cambio de modo no debe recargar datos del backend.
