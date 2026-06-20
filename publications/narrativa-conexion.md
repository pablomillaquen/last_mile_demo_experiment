---
title: "Narrativa de Conexión del Proyecto"
type: "connection-narrative"
author: "Sistema"
date: "2026-06-19"
status: "draft"
source_specs: ["SPEC-001", "SPEC-002", "SPEC-003", "SPEC-004", "SPEC-005"]
word_count: 0
target_audience: "general"
---

## Visión General

Este proyecto aborda el problema de la logística de última milla desde una perspectiva incremental: primero construir instrumentos de medición, luego ejecutar experimentos controlados, después documentar hallazgos, y finalmente optimizar algoritmos. La siguiente narrativa conecta las 7 fases de investigación en una línea temporal coherente.

## Tabla Resumen del Proyecto

| Fase | Nombre | Estado | SPEC | Objetivo |
|------|--------|--------|------|----------|
| 1 | Modelado Operacional | Completada | SPEC-001 | Definir entidades, restricciones y dominio del problema logístico |
| 2 | Evaluación | Completada | SPEC-002/003 | Construir motor de evaluación determinista y 15 métricas |
| 3 | Experimentación | Completada | SPEC-004 | Ejecutar experimentos de baseline y generar reportes |
| 4 | Optimización Algorítmica | Pendiente | SPEC-006 | Implementar algoritmos de asignación óptima de paquetes |
| 5 | Ciencia de Datos | Pendiente | SPEC-007 | Analizar datos históricos con técnicas estadísticas avanzadas |
| 6 | Aprendizaje de Modelos | Pendiente | SPEC-008 | Explorar modelos predictivos de anomalías operacionales |
| 7 | White Paper | Pendiente | SPEC-009 | Sintetizar toda la investigación en un documento publicable |

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

**Dependencia → Fase 4**: Los hallazgos de la experimentación (especialmente H001, H005, H006) definen las prioridades para la optimización algorítmica.

## Fase 4 — Optimización Algorítmica (SPEC-006 — Futuro)

**Objetivo**: Implementar algoritmos de asignación de paquetes a rutas que optimicen las métricas de balance identificadas en la Fase 3. Explorar estrategias específicas por cluster geográfico.

**Preguntas abiertas que aborda**: PI-001 (balance de carga), PI-005 (redespliegue de entregas cercanas).

**Dependencia → Fase 5**: Los resultados de optimización generan nuevos datos históricos para análisis estadístico.

## Fase 5 — Ciencia de Datos (SPEC-007 — Futuro)

**Objetivo**: Aplicar técnicas de regresión y clustering sobre los datos acumulados de todas las evaluaciones para identificar correlaciones entre características de entregas y eficiencia operacional.

**Preguntas abiertas que aborda**: PI-002 (variables que explican anomalías).

**Dependencia → Fase 6**: Los patrones identificados alimentan la selección de features para modelos predictivos.

## Fase 6 — Aprendizaje de Modelos (SPEC-008 — Futuro)

**Objetivo**: Explorar modelos predictivos supervisados y no supervisados para anticipar anomalías operacionales antes de que ocurran, basados en características de entregas y asignación histórica.

**Dependencia → Fase 7**: Los resultados de modelado predictivo se integran en la síntesis final.

## Fase 7 — White Paper (SPEC-009 — Futuro)

**Objetivo**: Sintetizar toda la investigación en un white paper publicable que integre hallazgos de todas las fases: modelado conceptual, sistema de evaluación, experimentación, optimización, análisis de datos y modelos predictivos.

**Preguntas de investigación abiertas**:

- PI-001 (Respondida parcialmente): ¿Cómo afecta la distribución geográfica de paquetes al balance de carga? Se abordará en Fase 4.
- PI-005 (Abierta): ¿Es posible reducir la penalidad operacional mediante redespliegue de entregas cercanas? Se abordará en Fase 4.
- PI-002 (Respondida): Las variables que explican anomalías son la distancia a bodega y la distancia al centroide.
- PI-003 (Respondida): Las métricas invariantes son operacionales y de balance; las sensibles son de calidad.
- PI-004 (Respondida): El ratio de severidad tiene mayor impacto que el umbral de proximidad.
