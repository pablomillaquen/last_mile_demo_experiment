---
title: "54.3% Más: Cuando la Distancia Real Duplica la Estimación Geodésica en Rutas de Última Milla"
type: "portfolio-article"
author: "Sistema"
date: "2026-06-22"
status: "published"
source_specs: ["SPEC-006", "SPEC-007"]
word_count: 0
target_audience: "non-technical"
---

## El Problema que Nadie Ve

Cada mañana, cientos de camiones de reparto salen de sus centros de distribución con una ruta planificada. Esa ruta fue diseñada usando un mapa digital donde las distancias se miden en línea recta: desde el punto A hasta el punto B, como si las calles no existieran, como si los cerros no estuvieran, como si la bahía de Valparaíso pudiera cruzarse en línea recta.

La mayoría de los sistemas logísticos del mundo funcionan así. Usan distancia geodésica —la distancia entre dos puntos en un mapa— porque es rápida de calcular y porque "la diferencia con la distancia real es pequeña". Ese "pequeña" es el problema que este proyecto decidió medir con precisión.

## Lo que Descubrimos

Construimos un sistema que reemplaza las distancias geodésicas por distancias reales calculadas sobre OpenStreetMap usando OSRM (Open Source Routing Machine). Luego ejecutamos el mismo conjunto de 150 entregas en Valparaíso dos veces: una con distancia geodésica y otra con distancia vial real. Todo lo demás permaneció idéntico.

El resultado fue contundente:

- Distancia geodésica total: **339 km**
- Distancia vial real total: **523 km**
- Diferencia: **+184 km (+54.3%)**

Una operación logística que creía recorrer 339 kilómetros diarios está recorriendo en realidad 523 kilómetros. Eso es un 54.3% más. Cada día.

## Por Qué Importa

Ciento ochenta y cuatro kilómetros adicionales por operación diaria no son solo un número. Son:

- **3 a 4 horas adicionales de conducción** por día que no estaban presupuestadas.
- **57,400 kilómetros extras al año** — equivalente a darle la vuelta al mundo una vez y media.
- **Combustible, desgaste de vehículos y emisiones** que ningún planificador había considerado.

Y lo más revelador: este impacto no es uniforme. Al desglosar los resultados por ruta individual, descubrimos que una de ellas —la Ruta D, que cruza la bahía de Valparaíso— duplica su distancia al usar la red vial real: 72.8 km viales contra solo 36.4 km geodésicos.

La razón es simple pero poderosa: en línea recta, la bahía de Valparaíso tiene unos 23 kilómetros de ancho. Por carretera, hay que rodearla completamente: 58 kilómetros. No hay atajo. No hay optimización que sortee la geografía.

## La Estructura del Problema

Analizamos cada ruta con una métrica que llamamos Territorial Distortion Index (TDI), que clasifica la distorsión vial en cuatro niveles: normal, elevada, alta y crítica. Los resultados fueron preocupantes:

- **0% de las rutas** tienen distorsión normal.
- **40%** tienen distorsión elevada.
- **40%** tienen distorsión alta.
- **20%** tienen distorsión crítica.

En otras palabras, ninguna ruta en Valparaíso escapa del efecto de la red vial. No es un problema de una ruta específica o de un sector mal diseñado: es una característica intrínseca de la geografía de la ciudad.

## Lo Que no Cambió

Tan importante como lo que descubrimos es lo que confirmamos. Todos los hallazgos de nuestra fase anterior de investigación —sobre balance de carga, detección de anomalías, distribución geográfica de ineficiencias— se mantienen válidos cuando usamos distancia vial. La estructura de los problemas logísticos de Valparaíso no depende del modelo de distancia; lo que cambia es la magnitud.

Esto es relevante porque significa que las conclusiones cualitativas del proyecto son robustas: sabemos que las entregas cercanas a la bodega están mal asignadas, que la heterogeneidad geográfica es dramática y que el balance entre rutas es estable, independientemente de si medimos en línea recta o por carretera.

## Lo Que Viene

Este hallazgo abre una pregunta incómoda para la industria logística: ¿cuántas operaciones en el mundo están planificadas con distancias geodésicas y, por lo tanto, subestiman sistemáticamente sus costos reales?

La respuesta no la tenemos, pero ahora tenemos las herramientas para medirla. El sistema que construimos —basado en OSRM, Docker y código abierto— puede reproducirse en cualquier ciudad del mundo.

El siguiente paso es construir herramientas de visualización que permitan a cualquier analista explorar estas diferencias sin necesidad de ejecutar experimentos complejos. Un visor que muestre simultáneamente la ruta planificada (geodésica) y la ruta real (vial), que permita aislar rutas problemáticas y que genere evidencia visual para la toma de decisiones.

Porque si algo nos enseñó este proyecto, es que la diferencia entre un mapa y la realidad puede ser del 54.3%. Y en logística de última milla, esa diferencia tiene nombre: son kilómetros, combustible, tiempo y dinero que nadie estaba contando.
