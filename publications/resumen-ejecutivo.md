---
title: "Resumen Ejecutivo del Proyecto"
type: "executive-summary"
author: "Sistema"
date: "2026-06-19"
status: "draft"
source_specs: ["SPEC-003", "SPEC-004"]
word_count: 0
target_audience: "executive"
---

## Problema

La logística de última milla enfrenta un desafío fundamental: asignar recursos (vehículos, conductores) de manera eficiente cuando los puntos de entrega están distribuidos geográficamente de forma heterogénea. En operaciones como las de Valparaíso, algunas rutas recorren distancias muy superiores a otras, y ciertas entregas cercanas a la bodega quedan asignadas a rutas largas, generando ineficiencias difíciles de cuantificar sin métricas objetivas.

## Hipótesis

Es posible medir, comparar y mejorar el desempeño operacional mediante un conjunto acotado de métricas cuantitativas que revelen dónde están las ineficiencias y qué parámetros las controlan.

## Metodología

Se diseñó un motor de evaluación determinista que procesa configuraciones operacionales (asignación de paquetes a rutas, parámetros de detección) y produce 15 métricas agrupadas en cuatro categorías: operacionales, balance, calidad y utilización. Se ejecutaron 6 evaluaciones sobre un dataset de 300 entregas en 10 rutas, variando parámetros para aislar su efecto.

## Resultados Clave

- **Métricas invariantes**: Las métricas de distancia, cobertura y balance no cambian entre evaluaciones. Son función exclusiva de la asignación de paquetes.
- **Parámetros de anomalía**: El `ratio de entrega ignorada` es 4× más discriminatorio que el `umbral de proximidad` para detectar anomalías.
- **10 anomalías detectadas**: Todas pertenecen a rutas del sector B, con entregas a menos de 1 km de bodega pero asignadas a rutas de 24 km de extensión.
- **Heterogeneidad geográfica**: El radio de cluster varía entre 0.3 km (Ruta A) y 15.3 km (Ruta E), indicando que no existe una estrategia única de optimización.
- **Penalidad base**: 232.26 km de penalidad operacional acumulada en configuración base, reducible hasta 0 km ajustando parámetros de detección.

## Conclusiones

El sistema de evaluación demuestra que es posible caracterizar cuantitativamente una operación de última milla y detectar ineficiencias específicas. La concentración de anomalías en rutas del sector B sugiere que una estrategia de micro-ruteo local (rutas exprés para entregas cercanas a bodega) podría reducir significativamente la penalidad operacional.

## Próximos Pasos

Las fases siguientes abordarán optimización algorítmica de asignación (SPEC-006), análisis de datos históricos (SPEC-007) y modelos predictivos de anomalías (SPEC-008), culminando en un white paper integrador (SPEC-009).
