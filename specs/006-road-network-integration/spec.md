# Feature Specification: Incorporación de Red Vial Real y Revalidación Experimental

**Feature Branch**: `006-road-network-integration`

**Created**: 2026-06-20

**Status**: Draft

## 1. Context

Las fases anteriores del proyecto (SPEC-001 a SPEC-005) permitieron construir un modelo operacional reproducible para analizar rutas logísticas de última milla, generar métricas cuantitativas y ejecutar experimentos controlados sobre distintos parámetros de evaluación.

Sin embargo, las mediciones realizadas hasta la fecha utilizan distancias geodésicas entre coordenadas geográficas, asumiendo desplazamientos directos entre puntos. Este enfoque simplificado ignora restricciones reales del territorio: calles y avenidas existentes, calles sin salida, pasajes peatonales, sentidos de tránsito, restricciones de circulación, barreras geográficas naturales, costas y cuerpos de agua, y topografía urbana compleja.

En ciudades como Valparaíso estas simplificaciones pueden producir trayectorias imposibles desde el punto de vista operacional, incluyendo desplazamientos que atraviesan cerros, quebradas o incluso la bahía. Como consecuencia, las métricas obtenidas hasta ahora deben considerarse una aproximación geométrica y no una representación fiel del costo real de desplazamiento. Antes de continuar con fases de optimización algorítmica resulta necesario aumentar la fidelidad del modelo operacional.

**Problema**: El sistema actual evalúa rutas utilizando distancia geodésica entre puntos. No existe actualmente una representación explícita de la red vial ni un mecanismo para calcular trayectos factibles sobre infraestructura real. Esto genera diferencias potencialmente significativas entre distancia teórica, tiempo real de desplazamiento y secuencia factible de visitas. La magnitud de estas diferencias aún no ha sido cuantificada.

---

## 2. Hipótesis

**H0 (Nula)**: La incorporación de una red vial real no produce diferencias significativas en las métricas operacionales obtenidas previamente.

**H1 (Alterna)**: La incorporación de una red vial real modifica significativamente las métricas operacionales y puede alterar conclusiones obtenidas bajo el modelo geométrico simplificado.

---

## 3. Objetivo

### Objetivo General

Incrementar el realismo operacional del sistema incorporando cálculo de rutas sobre infraestructura vial real y revalidar experimentalmente los resultados obtenidos en fases anteriores.

### Objetivos Específicos

1. Integrar una fuente de datos de red vial basada en OpenStreetMap.
2. Incorporar un motor de cálculo de trayectorias reales.
3. Permitir calcular distancias entre entregas utilizando la red vial.
4. Mantener compatibilidad con el modelo geodésico existente.
5. Comparar ambos enfoques bajo las mismas evaluaciones.
6. Cuantificar diferencias métricas entre ambos modelos.
7. Determinar qué hallazgos previos siguen siendo válidos tras la revalidación.

---

## 4. Alcance

### Modelado Vial
- Integración con OpenStreetMap como fuente de red vial.
- Obtención de rutas reales sobre la red vial.
- Cálculo de distancia vial.
- Cálculo de tiempo estimado de viaje.
- Soporte para calles sin salida y restricciones geográficas reales.

### Evaluación
- Reejecución de evaluaciones existentes (IDs 2–7) sobre la red vial.
- Comparación contra baseline actual (Experimento 001).
- Nuevas métricas de error entre modelos.

### Investigación
- Nuevos hallazgos formales (H007+).
- Nuevas preguntas de investigación (PI-006 a PI-010).
- Revalidación de hallazgos previos (H001–H006).
- Nuevas validaciones de hallazgos previos (V001+) con estructura formal.
- Identificación de zonas de distorsión territorial (M006).
- PI-011 registrada como pregunta abierta.
- PI-012 registrada como pregunta abierta (costo computacional).
- Nuevas decisiones arquitectónicas (D006+).
- Nuevas contribuciones (C004+).

---

## 5. Exclusiones

- Optimización de asignación de paquetes.
- Optimización de clustering geográfico.
- Aprendizaje automático o modelos predictivos.
- Predicción de demanda.
- Tráfico en tiempo real.
- Optimización multiobjetivo.

Estas capacidades pertenecen a fases posteriores (SPEC-007+).

---

## 6. Preguntas de Investigación

### PI-006 (Principal)

¿Cuál es el impacto de reemplazar distancias geodésicas por distancias calculadas sobre una red vial real en las métricas operacionales del sistema?

### PI-007

¿Qué métricas son más sensibles al cambio de modelo de distancia?

### PI-008

¿Cuál es la diferencia promedio entre distancia geodésica y distancia vial para las evaluaciones existentes?

### PI-009

¿Se mantienen los rankings relativos de calidad entre rutas después de incorporar una red vial real?

### PI-010

¿Qué hallazgos obtenidos previamente (H001–H006) permanecen válidos y cuáles deben revisarse?

### Criterios de Validez para Hallazgos

Para responder PI-010 objetivamente, cada hallazgo previo recibe uno de estos estados tras la revalidación:

| Estado | Criterio |
|--------|----------|
| Válido | La conclusión permanece igual tras incorporar red vial |
| Válido con ajustes | La conclusión se mantiene pero las magnitudes cambian significativamente |
| Revisado | La nueva evidencia contradice parcialmente la conclusión original |
| Rechazado | El hallazgo deja de sostenerse con el nuevo modelo |

### Estructura de Validaciones (V)

Cada revalidación de un hallazgo previo se documenta como una entidad V con el siguiente esquema:

```text
V{id}
Hallazgo validado: H{id}
Estado: {Válido | Válido con ajustes | Revisado | Rechazado}
Evidencia: Exp002
Observaciones: {detalles}
```

Donde:
- **H** = conocimiento descubierto (hallazgo original)
- **V** = conocimiento revalidado (validación tras nueva evidencia)

### PI-011

¿Existen zonas geográficas cuya configuración vial produce sistemáticamente mayores costos operacionales que los estimados por distancia geodésica?

### PI-012

¿Cuál es el impacto computacional de reemplazar cálculo geodésico local por consultas a red vial (OSRM) en el tiempo de ejecución de una evaluación completa?

### PI-013 (Futura — habilitada por infraestructura nacional)

¿Varía el factor de desvío geodésico-vial (M002) según la morfología urbana de una ciudad? (Valparaíso topografía compleja vs Santiago trama regular vs Concepción estructura policéntrica)

---

## 7. Historias de Usuario

**US1**: Como investigador, quiero que el motor de evaluación pueda calcular distancias sobre una red vial real, para que las métricas reflejen el costo operacional real de desplazamiento.

**US2**: Como investigador, quiero mantener la compatibilidad con el modelo geodésico existente, para poder comparar ambos enfoques bajo exactamente las mismas condiciones.

**US3**: Como investigador, quiero reejecutar evaluaciones existentes (IDs 2–7) sobre la red vial, para cuantificar las diferencias métricas entre ambos modelos.

**US4**: Como investigador, quiero que el sistema calcule tiempo estimado de viaje sobre la red vial, para incorporar una dimensión temporal al análisis operacional.

**US5**: Como investigador, quiero que el sistema registre nuevas métricas de error entre modelo geodésico y modelo vial, para documentar formalmente el impacto del cambio.

**US6**: Como investigador, quiero revalidar los hallazgos previos (H001–H006) a la luz de los nuevos resultados, para determinar cuáles siguen siendo válidos y cuáles deben revisarse.

---

## 8. Requisitos Funcionales

**RF1**: El sistema debe integrar OpenStreetMap como fuente de red vial para el cálculo de rutas.

**RF2**: El sistema debe proporcionar un mecanismo para obtener la distancia vial entre dos coordenadas geográficas sobre la red integrada.

**RF3**: El sistema debe proporcionar un mecanismo para obtener el tiempo estimado de viaje entre dos coordenadas sobre la red vial.

**RF4**: El motor de evaluación debe soportar dos modos de operación: geodésico (actual) y vial (nuevo), seleccionables por el usuario al ejecutar una evaluación.

**RF5**: El sistema debe reejecutar las evaluaciones IDs 2–7 sobre la red vial, manteniendo idénticos los parámetros de evaluación originales (seed, threshold, ratio).

**RF6**: El sistema debe calcular para cada evaluación las nuevas métricas de error (M001–M004, M006) entre el modelo geodésico y el modelo vial.

**RF7**: El sistema debe generar un reporte comparativo que muestre, para cada evaluación, las diferencias en distancia total, distancia promedio, penalidad, cobertura y balance entre ambos modelos.

**RF8**: El sistema debe calcular el tiempo estimado de viaje acumulado para cada ruta y para la operación completa.

**RF9**: El sistema debe permitir que el modo de operación (geodésico/vial) sea configurable globalmente y por evaluación.

**RF10**: Los resultados del modelo vial deben persistirse de manera que puedan compararse directamente con los resultados del modelo geodésico existentes.

---

## 9. Requisitos No Funcionales

**RNF1 (Reproducibilidad)**: El entorno de red vial debe ser reproducible mediante Docker, permitiendo que cualquier persona ejecute exactamente las mismas evaluaciones sin depender de servicios externos.

**RNF2 (Medición de Rendimiento)**: El sistema debe registrar el tiempo de ejecución de cada evaluación en modo vial y documentar el factor de degradación observado respecto al modo geodésico. No se impone un límite máximo a priori; la medición es el primer paso para establecer líneas base.

**RNF3 (Compatibilidad)**: El nuevo modelo vial no debe romper las evaluaciones existentes. El modo geodésico debe seguir funcionando exactamente como antes.

**RNF4 (Configuración)**: La selección entre modo geodésico y vial debe ser posible mediante archivo de configuración, sin modificar código.

---

## 10. Métricas

### Nuevas Métricas de Error

| ID | Nombre | Fórmula | Interpretación |
|----|--------|---------|---------------|
| M001 | Error Geodésico Medio | avg(d_vial − d_geodésica) | Diferencia promedio entre ambos modelos |
| M002 | Factor de Desvío | d_vial / d_geodésica | 1.0 = idéntico, 1.2 = ruta vial 20% más larga |
| M003 | Error Máximo de Trayecto | max(d_vial − d_geodésica) | Mayor diferencia observada entre modelos |
| M004 | Variación de Ranking | cambios en orden relativo de rutas | Cuánto cambia la clasificación de rutas |
| M005 | Persistencia de Hallazgos | hallazgos_válidos / hallazgos_totales × 100 | Métrica de revalidación experimental (reporte Exp002, no por evaluación individual) |
| M006 | Índice de Distorsión Territorial | d_vial / d_geodésica (por punto o ruta) | Identifica zonas donde la red vial infla significativamente la distancia real |

### Métricas del Sistema (Actualizadas)

Las 15 métricas existentes (operacionales, balance, calidad, utilización) se calcularán también sobre el modelo vial, permitiendo la comparación directa.

---

## 11. Criterios de Aceptación

**CA1**: Las evaluaciones existentes (IDs 2–7) pueden ejecutarse utilizando red vial real, produciendo resultados diferentes a los del modelo geodésico.

**CA2**: Existe una comparación reproducible entre ambos modelos, documentada en un reporte estructurado.

**CA3**: El sistema puede alternar entre modo geodésico y modo vial mediante configuración.

**CA4**: Las métricas M001–M004 y M006 se calculan automáticamente y se incluyen en el reporte de cada evaluación. M005 se calcula a nivel de experimento (Exp002).

**CA5**: Se completa el Experimento 002 — Comparación Geodésica vs Vial, con baseline en SPEC-003/004/Experimento 001.

**CA6**: Se documentan nuevos hallazgos formales (H007+) derivados de la comparación.

**CA7**: Se determina explícitamente, para cada hallazgo previo (H001–H006), si permanece válido o requiere revisión.

**CA8**: La integración de OSM + OSRM funciona correctamente en Docker, sin depender de servicios externos.

**CA9**: El reporte de Exp002 incluye M005 (Persistencia de Hallazgos) con el estado de revalidación de cada hallazgo H001–H006.

---

## Clarifications

### Session 2026-06-20

- Q: Add explicit "Amenazas a la Validez" section? → A: Yes, add with sub-sections for validez interna, externa y constructiva.
- Q: Create new "Validaciones (V)" evidence category for revalidation tracking? → A: Yes, add as new evidence type alongside H, PI, D, C.
- Q: Adjust M002 formula? → A: Flip to d_vial / d_geodésica (1.0 = idéntico, >1 = ruta real más larga).
- Q: Define explicit criteria for "hallazgo válido"? → A: Yes, add table with statuses: Válido, Válido con ajustes, Revisado, Rechazado.

---

## Alternativas Tecnológicas

### Opción A — OpenStreetMap + OSRM (Recomendada)

**Ventajas**: Open source, sin costo de licencias, muy utilizado en investigación, buen rendimiento.
**Desventajas**: Requiere infraestructura propia (contenedor Docker).

### Opción B — OpenStreetMap + GraphHopper

**Ventajas**: Open source, soporta perfiles de transporte, fácil integración.
**Desventajas**: Mayor complejidad inicial de configuración.

### Opción C — HERE Routing API

**Ventajas**: Datos de alta calidad, API sencilla.
**Desventajas**: Dependencia externa, límites de cuota, viola el principio de reproducibilidad.

### Decisión Inicial

**OpenStreetMap + OSRM**. Justificación: consistente con el principio de reproducibilidad (Docker First), permite versionar el entorno experimental, evita dependencia de servicios comerciales, y facilita futuras publicaciones técnicas.

---

## Diseño Experimental

### Experimento 002 — Comparación Geodésica vs Vial

**Baseline**: Resultados de SPEC-003, SPEC-004 y Experimento 001 (evaluaciones IDs 2–7).

**Tratamiento**: Reejecutar exactamente las mismas evaluaciones (mismos parámetros: seed, threshold, ratio) utilizando distancias calculadas sobre red vial real.

**Comparación**: Para cada evaluación se comparará:
- Distancia total por ruta.
- Distancia promedio general.
- Penalidad operacional.
- Cobertura territorial.
- Balance (CV, balance index).
- Ranking de rutas por cercanía a bodega.
- Tiempo estimado de viaje acumulado.

**Resultado Esperado**: Generar evidencia suficiente para aceptar o rechazar H0, documentar el factor de desvío promedio entre ambos modelos, y determinar qué hallazgos previos requieren revisión.

---

## Amenazas a la Validez

### Validez Interna

- **Calidad de datos OSM**: La red vial de OpenStreetMap puede tener omisiones, errores de geometría o calles faltantes en zonas periféricas.
- **Errores de geocodificación**: Las coordenadas de entregas y bodega pueden no corresponder exactamente con la entrada vial más cercana.
- **Restricciones no modeladas**: Sentidos de tránsito, giros prohibidos y restricciones por horario no están necesariamente completos en OSM.
- **Calles sin salida**: Pueden generar trayectorias infladas si el motor de ruteo no las maneja correctamente.

### Validez Externa

- **Generalización geográfica**: Los resultados obtenidos para Valparaíso pueden no generalizarse a ciudades con distinta topografía, trama urbana o densidad vial.
- **Escala del dataset**: 300 entregas en 10 rutas puede no capturar la variabilidad de operaciones de mayor escala.

### Validez Constructiva

- **Distancia vial sigue siendo una aproximación**: No incluye congestión vehicular, tiempos de carga/descarga, ni demoras operacionales.
- **Tiempo estimado de viaje**: Se calcula con velocidad promedio por tipo de vía, no con condiciones de tráfico real.
- **Costo operacional real**: La distancia vial se aproxima mejor al costo real que la distancia geodésica, pero sigue siendo una simplificación.

---

## Entregables

### Técnicos
- Integración OpenStreetMap como fuente de red vial.
- Servicio de cálculo de rutas (OSRM en Docker).
- Adaptación del motor de evaluación para soportar modo geodésico/vial.
- Configuración geodésica/vial intercambiable.
- Experimento 002 completo.

### Investigación
- Nuevas preguntas de investigación PI-006 a PI-010 (actualizar `research/preguntas-investigacion.md`).
- Nuevos hallazgos formales H007+ (actualizar `research/hallazgos.md`).
- Nuevas validaciones V001+ — registrar qué hallazgos previos sobreviven a la revalidación (actualizar `research/evidence-matrix.md`).
- Nuevas decisiones D006+ (actualizar `research/decisiones.md`).
- Nuevas contribuciones C004+ (actualizar `research/contribuciones.md`).
- Actualización de `research/evidence-matrix.md` con nuevos IDs.
- Nuevas métricas M001–M004, M006 documentadas (por evaluación); M005 documentada en reporte de Exp002.
- PI-011 (distorsión territorial) y PI-012 (costo computacional) registradas en `research/preguntas-investigacion.md`.
- Documentar la nueva categoría de evidencia "Validaciones (V)" con su estructura formal en `research/evidence-matrix.md` y `research/hallazgos.md`.
- Mapa de distorsión territorial (zonas con alto índice M006).
- Tabla de tiempos de ejecución comparativos (geodésico vs vial) para medir degradación computacional.

---

## Restricciones

Toda especificación debe cumplir la Constitución del proyecto.

Especialmente:
- **Evidencia antes de solución**: Este spec no propone optimización; propone aumentar la fidelidad del modelo de evaluación antes de optimizar.
- **Decisiones medibles**: Las nuevas métricas (M001–M004, M006) y la reejecución de evaluaciones existentes garantizan medición objetiva. M005 se evalúa a nivel de experimento.
- **Complejidad incremental**: Se excluyen explícitamente optimización, ML y clustering.
- **Optimizaciones comparables**: Ambos modelos (geodésico y vial) coexisten y son comparables.
- **Visualización como análisis**: Los mapas de rutas sobre red vial constituyen una mejora visual significativa.
- **Docker First**: OSRM se ejecutará como contenedor Docker en `docker-compose.yml`.
- **Conocimiento reutilizable**: Los hallazgos sobre el impacto de la fidelidad espacial tienen valor investigativo publicable.

---

## Regla de Evolución

Esta feature cumple las siguientes condiciones:
1. **Representa un problema operacional real**: Las distancias geodésicas no reflejan el costo real de desplazamiento en ciudades con geografía compleja.
2. **Permite medir una característica del sistema**: M001–M004 y M006 cuantifican el error del modelo actual; M005 mide la revalidación experimental.
3. **Introduce una mejora cuantificable**: Aumenta la fidelidad operacional del sistema y permite revalidar hallazgos previos.
4. **Permite comparar dos estrategias distintas**: Comparación directa entre modelo geodésico y modelo vial bajo idénticas condiciones.
