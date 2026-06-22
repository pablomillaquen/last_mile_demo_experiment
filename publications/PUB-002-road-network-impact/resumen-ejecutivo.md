---
title: "Resumen Ejecutivo — Impacto de Red Vial en Rutas de Última Milla"
type: "executive-summary"
author: "Sistema"
date: "2026-06-22"
status: "published"
source_specs: ["SPEC-006", "SPEC-007"]
word_count: 0
target_audience: "executive"
---

## Problema

Las operaciones logísticas de última milla suelen planificarse utilizando distancias geodésicas (línea recta entre puntos), asumiendo que la diferencia con la distancia real por carretera es marginal. Este estudio demuestra que dicha suposición es incorrecta: la distancia vial real es 54.3% mayor que la estimación geodésica para un conjunto representativo de rutas en Valparaíso.

## Hipótesis

Reemplazar el cálculo de distancia geodésica por distancia sobre red vial real (OSRM) produce diferencias significativas y sistemáticas en las métricas operacionales de rutas de última milla.

## Metodología

Se integró OSRM (Open Source Routing Machine) como servicio de ruteo sobre OpenStreetMap. Se ejecutaron 12 evaluaciones (6 pares geodésico/vial) sobre el mismo dataset de 150 entregas en 5 rutas de Valparaíso, variando únicamente el modo de distancia para aislar su efecto.

## Resultados Clave

- **Diferencia total**: 523.11 km viales vs 339.06 km geodésicos (+184 km, +54.3%).
- **Factor de desvío promedio**: 1.54×, variando entre 1.38× y 2.00× por ruta.
- **Distorsión territorial crítica**: Rutas que cruzan la bahía de Valparaíso duplican su distancia vial (factor 2.00×).
- **100% de rutas afectadas**: Ninguna ruta en Valparaíso tiene distorsión territorial normal.
- **Hallazgos previos validados**: Los 6 hallazgos del baseline (H001–H006) se mantienen válidos en modo vial.

## Impacto Operacional

184 km adicionales por operación diaria equivalen a ~57,400 km anuales (6 días/semana), con impacto directo en costos de combustible, tiempos de conducción y huella de carbono.

## Conclusión

El modelo geodésico subestima sistemáticamente los costos operacionales reales. Para planificación operativa se recomienda usar distancia vial. El modo geodésico es aceptable solo para prototipado rápido.
