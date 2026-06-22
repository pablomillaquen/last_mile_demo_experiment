# Feature Specification: Visual Analytics para Comparación de Rutas

**Feature Branch**: `008-visual-analytics-comparacion`

**Created**: 2026-06-22

**Status**: Draft

## 1. Context

SPEC-006 introdujo el cálculo de distancias mediante red vial real utilizando OSRM (EXP-002).

SPEC-007 incorporó la visualización de dichas rutas sobre el mapa, resolviendo:
- BUG-002: ausencia de geometría vial en la visualización.
- BUG-003: ausencia de selector geodésico/vial.

Como resultado, el sistema permite alternar entre representación geodésica y representación vial utilizando el mismo conjunto de entregas.

La validación visual confirmó el hallazgo H012:
- Distancia total geodésica: 339.06 km
- Distancia total vial: 523.11 km
- Diferencia: +184.05 km (+54.3%)

Las capturas generadas durante SPEC-007 demostraron que la representación visual comunica claramente diferencias que no son evidentes únicamente mediante tablas de métricas.

**Problema identificado**: Aunque actualmente es posible alternar entre modos geodésico y vial, el análisis visual presenta limitaciones:
1. Solo puede observarse un modo a la vez.
2. Todas las rutas se muestran simultáneamente.
3. Cuando varias rutas comparten calles, las polilíneas se superponen visualmente.
4. No existe una forma sencilla de aislar una ruta específica para analizarla.
5. La generación de evidencia visual para publicaciones requiere trabajo manual adicional.

Estas limitaciones reducen la capacidad del sistema para utilizar la visualización como herramienta analítica.

---

## 2. Hipótesis

**H0 (Nula)**: La comparación visual simultánea no aporta información adicional respecto a la visualización actual.

**H1 (Alterna)**: La comparación visual simultánea y la selección individual de rutas permiten identificar patrones, diferencias y efectos operacionales que son difíciles de detectar mediante métricas agregadas o mediante alternancia secuencial de modos.

---

## 3. Objetivo

Transformar el mapa desde una herramienta de visualización básica hacia una herramienta de análisis visual (Visual Analytics), permitiendo:
- Comparación simultánea geodésico vs vial.
- Selección individual de rutas.
- Filtrado de rutas visibles.
- Reducción de solapamiento visual.
- Generación de evidencia visual para documentación, publicaciones y portafolio.

---

## 4. Alcance

### Comparación visual
- Vista lado a lado (split view) geodésico vs vial.
- Sincronización de zoom y desplazamiento entre ambos mapas.

### Selección de rutas
- Mostrar listado de rutas disponibles.
- Permitir activar o desactivar rutas individuales.
- Permitir visualizar una sola ruta aislada.

### Visualización
- Mantener consistencia de colores entre modos.
- Mantener compatibilidad con EXP-001 y EXP-002.
- Preservar fallback histórico definido en SPEC-007.

### Publicaciones
- SPEC-008 alimentará documento-tecnico-v3 y PUB-003-visual-comparison.
- La evidencia visual generada debe ser exportable para su uso en documentación técnica y portafolio.

---

## 5. Exclusiones

- No se modifican algoritmos de cálculo de distancia.
- No se introducen nuevas métricas operacionales.
- No se modifican experimentos existentes (EXP-001, EXP-002).
- No se modifican artefactos históricos (evaluation.json).
- No se implementa animación de rutas ni reproducción temporal.
- No se implementa edición o modificación de rutas desde el mapa.
- No se implementa exportación automatizada de imágenes (captura manual).

---

## 6. Historias de Usuario

**HU1 — Comparación lado a lado**
**Como** analista logístico
**Quiero** ver dos mapas sincronizados (geodésico a la izquierda, vial a la derecha)
**Para** comparar visualmente la diferencia de trazado entre ambos modelos de distancia

**HU2 — Selección individual de rutas**
**Como** investigador
**Quiero** activar o desactivar rutas individuales desde un listado
**Para** analizar una ruta específica sin solapamiento visual de las demás

**HU3 — Aislamiento de ruta**
**Como** evaluador del sistema
**Quiero** hacer clic en una ruta para verla de forma aislada en el mapa
**Para** examinar su trazado completo sin distracción visual

**HU4 — Sincronización de mapas**
**Como** usuario del sistema
**Quiero** que al hacer zoom o desplazarme en un mapa, el otro se mueva igual
**Para** mantener la comparación visual en la misma zona geográfica

**HU5 — Compatibilidad con evaluaciones existentes**
**Como** investigador
**Quiero** visualizar EXP-001 y EXP-002 en el nuevo visor
**Para** mantener la trazabilidad con experimentos anteriores

**HU6 — Generación de evidencia visual**
**Como** autor de documentación técnica
**Quiero** generar capturas comparativas reproducibles desde el visor
**Para** incorporarlas en documentos técnicos, publicaciones y portafolio

---

## 7. Requisitos Funcionales

| ID | Descripción | Relación |
|---|---|---|
| RF1 | El sistema debe mostrar dos mapas sincronizados (geodésico y vial) en vista lado a lado | HU1 |
| RF2 | Los mapas deben mantener el mismo centro, zoom y nivel de desplazamiento | HU4 |
| RF3 | El usuario debe poder activar o desactivar rutas individuales desde un listado | HU2 |
| RF4 | El usuario debe poder seleccionar una ruta para verla de forma aislada | HU3 |
| RF5 | Los colores de cada ruta deben ser consistentes entre ambos mapas | HU1 |
| RF6 | El sistema debe preservar el toggle geodésico/vial de SPEC-007 como modo de visualización simple | HU5 |
| RF7 | Las evaluaciones EXP-001 deben visualizarse sin geometría vial (fallback geodésico) | HU5 |
| RF8 | Las evaluaciones EXP-002 deben visualizarse con ambas geometrías disponibles | HU5 |
| RF9 | El listado de rutas debe mostrar el identificador de cada ruta y su estado (activa/inactiva) | HU2 |
| RF10 | Al seleccionar una ruta aislada, las demás rutas deben atenuarse visualmente sin ocultarse por completo | HU3 |
| RF11 | Si una evaluación no contiene rutas, el sistema debe mostrar un mensaje indicando que no hay datos disponibles | HU5 |
| RF12 | Si route_legs está presente pero vacío para una evaluación vial, el sistema debe fallback a geodésico (mismo comportamiento que SPEC-007) | HU5 |

---

## 8. Requisitos No Funcionales

| ID | Descripción |
|---|---|
| RNF1 | La sincronización entre mapas debe ser inmediata (<200ms de retardo) |
| RNF2 | El listado de rutas debe poder cerrarse o colapsarse para no obstruir el mapa |
| RNF3 | El cambio entre modo split y modo simple no debe requerir recarga de datos del backend |
| RNF4 | El sistema debe funcionar sin degradación perceptible con datasets de hasta 15 rutas y 300 entregas |
| RNF5 | La vista split no debe duplicar llamadas a la API (un solo juego de datos para ambos mapas) |

---

## 9. Métricas

| Métrica | Descripción | Cómo se mide |
|---|---|---|
| M1 | Tiempo de sincronización entre mapas | Tiempo desde interacción en un mapa hasta actualización del otro |
| M2 | Precisión de sincronización | Diferencia de centro/zoom entre mapas después de una secuencia de interacciones |
| M3 | Cobertura de rutas seleccionables | Porcentaje de rutas que pueden activarse/desactivarse individualmente (debe ser 100%) |
| M4 | Tiempo para identificar una divergencia significativa entre modelos | Tiempo requerido por un analista para localizar la ruta con mayor divergencia porcentual usando visor SPEC-007 (toggle) vs visor SPEC-008 (split + filtros) |

---

## 10. Criterios de Aceptación

| ID | Criterio | Tipo |
|---|---|---|
| CA1 | Un usuario puede activar la vista split y ver dos mapas sincronizados con la misma área geográfica | Funcional |
| CA2 | Al hacer zoom en un mapa, el segundo mapa se actualiza en <200ms | Rendimiento |
| CA3 | Un usuario puede desactivar rutas desde el listado y las rutas se ocultan en ambos mapas | Funcional |
| CA4 | Un usuario puede seleccionar una ruta para aislarla y las demás rutas se atenúan visualmente | UX |
| CA5 | Los colores de cada ruta son idénticos en ambos mapas | Funcional |
| CA6 | Las evaluaciones EXP-001 se visualizan sin error en modo geodésico (split deshabilitado o un solo mapa activo) | Compatibilidad |
| CA7 | El toggle de SPEC-007 sigue funcionando correctamente como modo de visualización simple | Regresión |
| CA8 | Un usuario puede alternar entre split view y modo simple sin recargar la página | Funcional |
| CA9 | Si una evaluación tiene una sola ruta, la vista split muestra esa misma ruta en ambos mapas sin error | Compatibilidad |
| CA10 | Al alternar de split a modo simple y viceversa, el estado de selección de rutas activas se preserva | UX |

---

## Restricciones

Toda especificación debe cumplir la Constitución del proyecto.

Especialmente:
- **Evidencia antes de solución**: SPEC-007 ya evidenció la diferencia visual; SPEC-008 profundiza el análisis.
- **Decisiones medibles**: La capacidad de aislar rutas y comparar trazados es directamente verificable.
- **Complejidad incremental**: SPEC-008 extiende SPEC-007 sin modificar modelos subyacentes.
- **Visualización como análisis**: Aplicación directa del principio VI (múltiples modos de visualización analítica).

Las preguntas de investigación que guían este SPEC son:
- **PI-016**: ¿Cómo influye la visualización selectiva y comparativa de rutas en la capacidad de interpretar diferencias entre métricas geodésicas y viales?
- **PI-017**: ¿Qué nivel de detalle visual es necesario para comunicar eficazmente diferencias operacionales entre modelos geodésicos y viales sin introducir sobrecarga cognitiva?

---

## Regla de Evolución

Esta nueva feature cumple las siguientes condiciones:
1. **Representa un problema operacional real**: La visualización monomodo y sin filtros actual limita el análisis comparativo.
2. **Permite medir una característica del sistema**: La precisión de sincronización y la cobertura de selección son medibles.
3. **Introduce una mejora cuantificable**: La capacidad de aislar rutas reduce el tiempo para identificar patrones de divergencia.
4. **Permite comparar dos estrategias distintas**: Split view vs toggle simple como estrategias de visualización analítica.

---

## Supuestos

- Los datos de evaluación ya contienen route_legs con geometría vial (heredado de SPEC-007).
- Si route_legs está presente pero vacío para una evaluación marcada como vial, el comportamiento de fallback es el mismo que SPEC-007 (geodésico).
- Las evaluaciones EXP-001 (geodésicas) no tienen route_legs y se visualizan únicamente en modo geodésico.
- El rendimiento de sincronización entre mapas asume una conexión de red local sin latencia significativa.
- No se requiere autenticación ni permisos de usuario para acceder al visor comparativo.

---

## Relación con publicaciones

SPEC-008 alimenta los siguientes artefactos de publicación:
- **documento-tecnico-v3**: Nueva sección de análisis visual comparativo.
- **PUB-003-visual-comparison**: Artículo derivado centrado en la comparación visual de modelos de distancia.

### Evidencia esperada

SPEC-008 debería producir como mínimo el siguiente material visual reutilizable:

- Captura de mapa en modo geodésico (vista completa).
- Captura de mapa en modo vial (vista completa).
- Captura de split view con ambos modos sincronizados.
- Captura de ruta aislada (efecto de atención visual).
- Captura de superposición reducida mediante filtrado de rutas.

Este material alimenta directamente documento-tecnico-v3, PUB-003 portfolio, PUB-003 LinkedIn y PUB-003 article.

---

## Amenazas a la validez

### TV-002: Sesgo de interpretación visual

La identificación de patrones mediante inspección visual puede depender de la experiencia del observador y producir conclusiones distintas entre evaluadores. A diferencia de SPEC anteriores (basados en métricas cuantitativas), SPEC-008 se apoya en interpretación humana de visualizaciones, lo que introduce subjetividad en el análisis.

**Mitigación**: Las conclusiones visuales deben estar respaldadas por las métricas cuantitativas existentes (distancia geodésica, distancia vial, divergencia porcentual). El visor comparativo no reemplaza el análisis métrico: lo complementa.
