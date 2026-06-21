---
title: "Narrativa de Conexión del Proyecto"
type: "connection-narrative"
author: "Sistema"
date: "2026-06-21"
status: "draft"
source_specs: ["SPEC-001", "SPEC-002", "SPEC-003", "SPEC-004", "SPEC-005", "SPEC-006", "SPEC-007"]
word_count: 0
target_audience: "general"
---

## Visión General

Este proyecto aborda el problema de la logística de última milla desde una perspectiva incremental: primero construir instrumentos de medición, luego ejecutar experimentos controlados, después documentar hallazgos, y finalmente optimizar algoritmos. La siguiente narrativa conecta las fases de investigación en una línea temporal coherente.

## Tabla Resumen del Proyecto

| Fase | Nombre | Estado | SPEC | Objetivo |
|------|--------|--------|------|----------|
| 1 | Modelado Operacional | Completada | SPEC-001 | Definir entidades, restricciones y dominio del problema logístico |
| 2 | Evaluación | Completada | SPEC-002/003 | Construir motor de evaluación determinista y 15 métricas |
| 3 | Experimentación | Completada | SPEC-004 | Ejecutar experimentos de baseline y generar reportes |
| 4 | Documentación | Completada | SPEC-005 | Consolidar hallazgos en documento técnico y publicaciones derivadas |
| 5 | Red Vial | Completada | SPEC-006 | Integrar OSRM y comparar distancia geodésica vs vial real |
| 6 | Visualización Vial | Completada | SPEC-007 | Visualizar rutas viales con toggle geodésico/vial + validación |
| 7 | Comparación Visual | Planeación | SPEC-008 | Visualización comparativa avanzada y filtrado de rutas |

## Fase 1 — Modelado Operacional (SPEC-001)

**Objetivo**: Definir el dominio del problema logístico de última milla, incluyendo entidades (entregas, rutas, vehículos, bodega), restricciones operacionales y el modelo de datos subyacente.

**Entregables**: Modelo conceptual del dominio, definición de escenarios operacionales, estructura de datos para evaluaciones.

**Dependencia → Fase 2**: El modelo conceptual define las variables que el motor de evaluación debe procesar (entregas, rutas, parámetros).

## Fase 2 — Evaluación (SPEC-002/003)

**Objetivo**: Construir un motor de evaluación determinista que procese configuraciones operacionales y genere 15 métricas cuantitativas agrupadas en operacionales, balance, calidad y utilización. Implementar detección de anomalías basada en umbral de proximidad y factor de severidad.

**Entregables**: Sistema de evaluación funcional, 15 métricas implementadas, algoritmo de detección de anomalías.

**Dependencia → Fase 3**: El motor de evaluación permite ejecutar experimentos que varían parámetros de manera controlada.

## Fase 3 — Experimentación (SPEC-004)

**Objetivo**: Ejecutar el experimento baseline con 6 evaluaciones (IDs 2–7) variando threshold, ratio y seed. Documentar resultados en reportes estructurados con trazabilidad completa. Generar reportes PDF con mapas incorporados.

**Entregables**: Experimento baseline completado, 6 evaluaciones, reporte en markdown + PDF, métricas de ruta detalladas, 6 hallazgos formales (H001–H006).

**Hallazgos clave**:
- Las métricas globales son invariantes entre evaluaciones (H003)
- El ratio de severidad es más discriminatorio que el umbral de proximidad (H004)
- La heterogeneidad geográfica entre rutas es significativa (H005)
- Las anomalías se concentran en rutas del sector B (H006)

**Dependencia → Fase 4**: Los hallazgos de la experimentación se consolidan en un documento técnico formal.

## Fase 4 — Documentación (SPEC-005)

**Objetivo**: Consolidar todos los hallazgos (H001–H006) en un documento técnico estructurado y generar publicaciones derivadas (portafolio, LinkedIn, resumen ejecutivo). Establecer el pipeline de investigación → documentación → publicación.

**Entregables**: documento-tecnico.md (v1), resumen-ejecutivo.md, articulo-portafolio.md, linkedin-post.md, index.md (catálogo visual).

**Dependencia → Fase 5**: El documento técnico v1 sirve como línea base para medir el impacto de la red vial.

## Fase 5 — Distancia Geodésica vs Red Vial (SPEC-006)

**Objetivo**: Integrar OSRM como servicio de cálculo de distancias sobre red vial real. Comparar métricas geodésicas vs viales para el mismo conjunto de evaluaciones. Implementar patrón Strategy en DistanceService para alternar entre modos.

**Resultados**:
- EXP-002 ejecutado: 12 evaluaciones (6 pares geodésico↔vial)
- H007: Factor de desvío M001 = 1.62×; distancias viales 62.5% mayores
- H008: Distorsión territorial crítica (TDI > 2.0) en rutas que cruzan la bahía
- H009: 100% de rutas con TDI anormal (>1.2); 60% alta o crítica
- H010: Modo vial ~330x más lento que geodésico (~82s vs ~0.25s)
- V001–V006: Validación de hallazgos baseline — todos persisten en modo vial

**Dependencia → Fase 6**: La evidencia vial requiere visualización para su comunicación efectiva.

## Fase 6 — Visualización de Red Vial (SPEC-007)

**Objetivo**: Visualizar rutas viales OSRM en el mapa interactivo. Implementar toggle geodésico/vial. Validar consistencia visual con métricas calculadas.

**Resultados**:
- H011: Tamaño de evaluation.json — 84 KB (baseline) → 2.3 MB (vial, +2640%)
- H012: Distancia vial +54.3% sobre geodésico (339→523 km, +184 km)
- route_legs persistidos en evaluation.json con geometría OSRM completa
- Toggle funcional en /map y /evaluations/[id]
- Fallback geodésico para evaluaciones históricas (RF10)
- CA3 consistency: 0.000% diferencia entre ruta calculada y route_legs
- BUG-002/BUG-003 resueltos con validación visual y experimental

**Dependencia → Fase 7**: La visualización actual revela limitaciones de solapamiento que motiva la fase de comparación avanzada.

## Fase 7 — Comparación Visual Avanzada (SPEC-008 — Próxima)

**Objetivo**: Mejorar la capacidad analítica del visor de rutas mediante herramientas de comparación y filtrado visual.

**Objetivos específicos**:
- Comparación simultánea geodésico/vial
- Selección y filtrado individual de rutas
- Reducción de solapamiento visual
- Generación de evidencia visual reutilizable para publicaciones

**Motivación**: H012 demostró que el modelo geodésico subestima en 54.3% la distancia real. SPEC-008 no busca calcular más métricas, sino mejorar la capacidad de interpretar y comunicar los resultados ya obtenidos.

## Preguntas de investigación activas

- PI-001 (Respondida parcialmente): ¿Cómo afecta la distribución geográfica de paquetes al balance de carga?
- PI-005 (Abierta): ¿Es posible reducir la penalidad operacional mediante redespliegue de entregas cercanas?
- PI-002 (Respondida): Las variables que explican anomalías son la distancia a bodega y la distancia al centroide.
- PI-003 (Respondida): Las métricas invariantes son operacionales y de balance; las sensibles son de calidad.
- PI-004 (Respondida): El ratio de severidad tiene mayor impacto que el umbral de proximidad.
- PI-016 (Abierta): ¿Qué mecanismos de visualización permiten comunicar con mayor claridad las diferencias entre modelos geodésicos y viales?

## Cadena evolutiva del conocimiento

```text
SPEC-003 → EXP-001 → H001–H006 → PUB-001 (baseline geodésico)
SPEC-006 → EXP-002 → H007–H010 → PUB-002 (impacto red vial)
SPEC-007 → EXP-002 → H011–H012 → PUB-002 (evidencia visual)
SPEC-008 → ?       → H013+     → PUB-003 (visualización avanzada)
```
