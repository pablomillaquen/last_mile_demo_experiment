---
title: "Resumen Ejecutivo — Impacto de Red Vial en la Estimación de Distancias de Última Milla"
type: "executive-summary"
author: "Sistema"
date: "2026-06-22"
status: "published"
source_specs: ["SPEC-006", "SPEC-007"]
word_count: 0
target_audience: "executive"
---

## Problema

La mayoría de los sistemas de planificación logística utilizan distancia geodésica (línea recta entre puntos) para estimar recorridos operacionales. Este método, aunque rápido y simple, contiene un supuesto no verificado: que la diferencia con la distancia real por carretera es marginal y no afecta las decisiones operacionales.

Este estudio somete ese supuesto a prueba empírica, reemplazando la distancia geodésica por distancia calculada sobre una red vial real (OpenStreetMap + OSRM) en un sistema de evaluación de rutas de última milla.

## Hipótesis

Las distancias viales reales son sistemáticamente mayores que las geodésicas, y la magnitud de la diferencia varía significativamente según la ubicación geográfica de cada ruta.

## Metodología

Se integró OSRM como servicio de ruteo sobre OpenStreetMap. Se ejecutaron 12 evaluaciones (6 pares geodésico/vial) sobre un dataset de 150 entregas en 5 rutas de Valparaíso, variando únicamente el modo de distancia para aislar su efecto.

## Resultados Clave

- **Diferencia total**: 523 km viales vs 339 km geodésicos (+184 km, +54.3%).
- **184 km adicionales por operación diaria** que no estaban siendo considerados.
- **Factor de desvío no uniforme**: varía entre 1.38× y 2.00× por ruta.
- **Ruta D (cruce de bahía) duplica su distancia**: 73 km viales vs 36 km geodésicos.
- **100% de las rutas afectadas**: ninguna ruta en Valparaíso tiene una representación geodésica precisa.
- **Hallazgos previos validados**: las conclusiones cualitativas del estudio baseline (balance, anomalías, clusters) se mantienen en modo vial.

## Impacto Operacional

- **57,400 km adicionales por año** (6 días/semana) — equivalente a 1.4 vueltas al mundo.
- **3 a 4 horas de conducción extra por día** no presupuestadas.
- **Incremento directo** en combustible, desgaste de vehículos, emisiones y tiempo operativo.
- **Planificación basada en geodésico** subestima sistemáticamente los costos reales.

## Conclusiones

1. El modelo geodésico subestima sistemáticamente el esfuerzo operacional real. Para planificación operativa se recomienda usar distancia vial.
2. No existe un factor de corrección único: el desvío varía por ruta según su geografía.
3. Los hallazgos cualitativos del proyecto son robustos al cambio de modelo de distancia.
4. El modo geodésico sigue siendo útil para prototipado rápido por su ventaja computacional.

## Próximos Pasos

Las fases siguientes desarrollarán herramientas de visualización comparativa (SPEC-008) que permitan a cualquier analista explorar estas diferencias sin necesidad de ejecutar experimentos complejos, y una nueva publicación (PUB-003) centrada en la comparación visual de modelos de distancia.
