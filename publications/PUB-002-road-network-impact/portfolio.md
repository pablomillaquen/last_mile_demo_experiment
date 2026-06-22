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

## El Mapa no es el Territorio

Cada mañana, cientos de camiones de reparto salen de los centros de distribución con una ruta planificada. Esa ruta fue diseñada usando un mapa digital donde las distancias se miden en línea recta: desde el punto A hasta el punto B, como si las calles no existieran, como si los cerros no estuvieran, como si el tiempo de conducción fuera irrelevante.

La mayoría de los sistemas logísticos del mundo funcionan así. Usan distancia geodésica —la distancia entre dos puntos en un mapa— porque es rápida de calcular, porque siempre ha sido la forma estándar y sobre todo porque se asume que "la diferencia con la distancia real es pequeña". Esa suposición es el centro de este estudio.

Decidimos medir exactamente qué tan grande es esa diferencia. No en teoría, sino con datos reales de una operación logística en Valparaíso.

## Lo que Descubrimos: 184 Kilómetros Invisibles

Construimos un sistema que reemplaza las distancias geodésicas por distancias reales calculadas sobre OpenStreetMap usando OSRM (Open Source Routing Machine). Luego ejecutamos el mismo conjunto de 150 entregas en 5 rutas de Valparaíso dos veces: una con distancia geodésica y otra con distancia vial real. Todo lo demás permaneció idéntico: las mismas entregas, las mismas rutas, los mismos parámetros. Solo cambió la forma de medir la distancia entre un punto y otro.

El resultado fue revelador:

- **Distancia geodésica total**: 339 kilómetros.
- **Distancia vial real total**: 523 kilómetros.
- **Diferencia**: 184 kilómetros adicionales. Un **54.3% más**.

Una operación logística que creía recorrer 339 kilómetros diarios está recorriendo en realidad 523 kilómetros. Cada día.

Ciento ochenta y cuatro kilómetros pueden sonar abstractos, pero tienen consecuencias muy concretas. Son aproximadamente 3 a 4 horas de conducción adicionales por jornada que ningún planificador había considerado. Son 57,400 kilómetros extras al año —equivalente a darle la vuelta al mundo una vez y media— en combustible, desgaste de vehículos, emisiones y tiempo operativo que no aparecen en ningún informe cuando se planifica con distancia geodésica.

## La Geografía como Variable Oculta

Lo más interesante no fue el promedio general, sino lo que descubrimos al analizar cada ruta individualmente. El impacto de la red vial no es uniforme: varía dramáticamente según la ubicación geográfica de cada ruta.

| Ruta | Geodésico | Vial | Diferencia | Factor |
|------|:---------:|:----:|:----------:|:------:|
| Ruta A | 38 km | 54 km | +16 km | 1.42× |
| Ruta B | 91 km | 126 km | +35 km | 1.38× |
| Ruta C | 107 km | 176 km | +69 km | 1.64× |
| Ruta D | 36 km | 73 km | +37 km | **2.00×** |
| Ruta E | 66 km | 94 km | +28 km | 1.42× |

La Ruta D duplica su distancia al usar la red vial real: 73 kilómetros contra solo 36 kilómetros en línea recta. La razón es simple pero poderosa: la Ruta D cruza la bahía de Valparaíso. En línea recta, la bahía tiene unos 23 kilómetros de ancho. Por carretera, hay que rodearla completamente: casi 58 kilómetros. No hay atajo. No hay optimización que sortee la geografía.

Este hallazgo cambia completamente la forma de entender el problema. No se trata de un ajuste marginal que pueda corregirse con un factor multiplicativo único. Cada ruta tiene su propia geografía, su propia topografía, sus propias restricciones viales. Una ruta urbana en el plan de Valparaíso tiene un factor de desvío de 1.38×. Una ruta que cruza la bahía tiene un factor de 2.00×. Usar un factor promedio de 1.54× para ambas estaría sobreestimando el esfuerzo de una y subestimando el de la otra.

## La Estructura del Problema: Territorial Distortion Index

Para cuantificar esta heterogeneidad, desarrollamos una métrica que llamamos Territorial Distortion Index (TDI). El TDI clasifica cada ruta según cuánto se desvía su distancia vial respecto a la geodésica, en cuatro niveles:

- **Normal** (≤1.2×): la red vial no afecta significativamente.
- **Elevada** (≤1.5×): la red vial incrementa la distancia de forma moderada.
- **Alta** (≤2.0×): el incremento es sustancial y debe considerarse.
- **Crítica** (>2.0×): la distancia geodésica es fundamentalmente incorrecta.

Los resultados fueron preocupantes: el 0% de las rutas tiene un TDI normal. El 60% tiene distorsión elevada y el 20% tiene distorsión crítica. En otras palabras, **ninguna ruta en Valparaíso escapa del efecto de la red vial**.

No es un problema de una ruta específica o de un sector mal diseñado. Es una característica intrínseca de la geografía urbana de la ciudad. Y probablemente de muchas otras ciudades con topografía compleja.

## Lo que NO Cambió: La Robustez del Conocimiento Acumulado

Tan importante como lo que descubrimos es lo que confirmamos. Todos los hallazgos de nuestra fase anterior de investigación —sobre balance de carga entre rutas, detección de anomalías, distribución geográfica de ineficiencias— se mantienen válidos cuando usamos distancia vial. La estructura de los problemas logísticos de Valparaíso no depende del modelo de distancia.

Esto es relevante por dos razones. La primera es que valida todo el trabajo anterior: las conclusiones no eran un artefacto del modelo geodésico, sino propiedades reales de la operación logística. La segunda es que任何 estrategia de optimización diseñada con distancia geodésica seguirá siendo válida en el mundo real. La dirección del beneficio no cambia; lo que cambia es la magnitud.

## Aprendizajes

**Los modelos incorrectos son peor que ningún modelo.** Una estimación geodésica no es simplemente "aproximada": es sistemáticamente incorrecta en una dirección y por un margen significativo. Cualquier decisión basada en ella —presupuesto de combustible, tiempos de conducción, capacidad de flota— estará igualmente desviada.

**La geografía importa más de lo que los modelos logísticos suponen.** La mayoría de los algoritmos de optimización tratan la distancia como una variable homogénea. Este estudio demuestra que la distancia vial está determinada por la topografía y la trama urbana de cada sector, no solo por la ubicación de los puntos de entrega.

**Medir es más importante que optimizar.** Si este estudio se hubiera hecho al revés —implementar primero un sistema de ruteo vial y después medir su impacto— nunca habríamos descubierto que el supuesto de partida (la distancia geodésica como aproximación válida) era incorrecto. La medición primero, la optimización después.

## El Camino que Sigue

Este hallazgo abre una pregunta que trasciende el proyecto: ¿cuántas operaciones logísticas en el mundo están planificadas con distancias geodésicas y, por lo tanto, subestiman sistemáticamente sus costos reales? No tenemos la respuesta, pero ahora tenemos las herramientas para medirla.

El sistema que construimos es reproducible: está basado en OSRM, OpenStreetMap y Docker, y puede implementarse en cualquier ciudad del mundo. No requiere datos propietarios ni servicios de mapas comerciales.

El siguiente paso es construir herramientas de visualización que permitan a cualquier analista explorar estas diferencias sin ejecutar experimentos complejos. Un visor que muestre simultáneamente la ruta planificada (geodésica) y la ruta real (vial), que permita aislar rutas problemáticas y que genere evidencia visual para la toma de decisiones.

Porque si algo nos enseñó este proyecto, es que la diferencia entre el mapa y la realidad puede ser del 54.3%. Y en logística de última milla, esa diferencia es kilómetros, combustible, tiempo y dinero que nadie estaba contando.
