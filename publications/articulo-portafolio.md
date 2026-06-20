---
title: "Cómo Medir lo Invisible: Evaluación Cuantitativa de Operaciones Logísticas de Última Milla"
type: "portfolio-article"
author: "Sistema"
date: "2026-06-19"
status: "draft"
source_specs: ["SPEC-003", "SPEC-004"]
word_count: 0
target_audience: "non-technical"
---

## Contexto Operacional

Cada día, cientos de vehículos de reparto recorren las calles de ciudades como Valparaíso para entregar paquetes a miles de hogares. Detrás de esta operación aparentemente simple hay un problema de optimización complejo: ¿cómo asignar cada entrega a un vehículo de manera que se minimicen los kilómetros recorridos, se equilibre la carga entre conductores y se reduzcan las ineficiencias que nadie ve?

La mayoría de las empresas resuelve este problema con la experiencia de sus planificadores, que conocen las rutas, los tiempos y las restricciones de cada zona como la palma de su mano. Un planificador con experiencia sabe que la Ruta A es más rápida porque tiene menos semáforos, que la Ruta B hay que evitarla en hora punta, y que el sector C tiene clientes que siempre reciben después de las 14:00. Ese conocimiento tácito es valiosísimo y difícil de reemplazar.

Sin embargo, confiar únicamente en el criterio humano tiene un límite claro: cuando las operaciones crecen, las ineficiencias se vuelven invisibles. Un planificador puede saber que una ruta en particular es más larga que otra, pero no puede cuantificar si esa diferencia es aceptable ni si hay patrones sistemáticos de ineficiencia que se repiten día tras día. Sin datos objetivos que comparen cada ruta contra las demás, no hay forma de saber si una configuración es mejorable ni por dónde empezar a mejorarla.

Este problema se agrava cuando las entregas cercanas al centro de distribución quedan atrapadas en rutas que recorren distancias enormes. Un paquete que debe entregarse a solo 300 metros de la bodega puede terminar en un camión que recorre 70 kilómetros, simplemente porque la asignación se hizo pensando en la continuidad geográfica de la ruta y no en la eficiencia puntual de cada entrega.

## Motivación

Este proyecto nace de una pregunta simple pero profunda: ¿cómo sabemos si una operación logística está funcionando bien? Sin métricas, la respuesta es subjetiva y depende de la intuición del planificador. Con métricas incorrectas, la respuesta es engañosa y puede llevar a decisiones contraproducentes.

El objetivo fue construir un sistema que permitiera evaluar cuantitativamente cualquier configuración operacional de última milla, detectar anomalías y establecer una línea base medible antes de intentar mejoras. No se trataba de crear el método de ruteo más rápido ni el más inteligente, sino de construir primero los instrumentos de medición. En ingeniería existe un principio que dice que no se puede mejorar lo que no se puede medir. Este proyecto lleva ese principio al extremo: en lugar de asumir qué métricas son importantes, el sistema permite descubrirlas a través de la experimentación.

La decisión de construir primero la evaluación y después la optimización es deliberada. La mayoría de los proyectos de logística comienzan implementando un sistema de ruteo y luego tratan de medir si funciona. Este proyecto invierte el orden: primero se construyen los instrumentos de medición, se ejecutan experimentos controlados, se documentan los resultados, y solo entonces se diseña la estrategia de optimización.

## Metodología General

Para evaluar una operación logística necesitamos responder cuatro preguntas fundamentales, cada una con su propio conjunto de indicadores:

1. **¿Cuánto recorremos?** Las métricas operacionales miden distancia promedio a bodega, cobertura territorial y volumen de entregas. Responden a la pregunta más básica: ¿cuál es el esfuerzo físico de la operación?

2. **¿Estamos distribuidos de manera equilibrada?** Las métricas de balance calculan la desviación estándar entre rutas, el coeficiente de variación y el índice de equilibrio. Revelan si hay rutas que cargan con mucho más trabajo que otras, un problema común que pasa desapercibido cuando solo se mira el total.

3. **¿Hay entregas mal asignadas?** Las métricas de calidad detectan anomalías: entregas que están muy cerca de la bodega pero muy lejos del centro de su ruta asignada. Cada anomalía genera una penalidad operacional que cuantifica el costo de esa mala asignación.

4. **¿Estamos usando bien nuestros recursos?** Las métricas de utilización miden cuántos vehículos están activos, qué porcentaje de la flota se usa y cuál es la ocupación promedio por ruta. Revelan si hay capacidad ociosa o rutas sobrecargadas.

El sistema procesa estas cuatro dimensiones simultáneamente y produce 15 indicadores cuantitativos por cada evaluación. Luego ejecuta evaluaciones múltiples variando parámetros como el umbral de detección de anomalías y el factor de severidad, para entender cómo se comporta cada configuración y qué parámetros tienen mayor impacto en los resultados.

## Resultados Obtenidos

Los resultados del experimento inicial revelaron patrones que no eran evidentes a simple vista y que cambiaron nuestra comprensión del problema:

**Las métricas de distancia y balance no cambian entre evaluaciones.** Repetimos el mismo experimento seis veces con distintos valores de semilla, umbral y severidad. Las métricas de ruta —distancia promedio, cobertura territorial, desviación estándar— fueron idénticas en las seis ejecuciones. Esto puede sonar obvio, pero tiene una implicación profunda: si queremos mejorar la eficiencia operacional, no basta con ajustar parámetros de detección; hay que cambiar la asignación misma de paquetes a rutas. Las métricas globales son el resultado de la configuración, no de los parámetros de evaluación.

**El factor de severidad de anomalías es más importante que el umbral de búsqueda.** Cuando redujimos el perímetro de búsqueda de 1 kilómetro a 0.5 kilómetros y ajustamos el factor de severidad de 2 a 1.5, las anomalías detectadas cayeron un 41%. Sin embargo, aumentar el perímetro a 3 o incluso 5 kilómetros no tuvo efecto alguno. Esto significa que el umbral funciona como una compuerta: define qué entregas son candidatas a revisión. Pero es el factor de severidad el que realmente determina cuántas de esas candidatas se convierten en anomalías. Calibrar correctamente la severidad tiene un impacto mucho mayor que ajustar el perímetro.

**La distribución geográfica de las entregas es dramáticamente heterogénea.** Al analizar las rutas individualmente descubrimos que algunas tienen todas sus entregas concentradas en un radio de 300 metros, mientras que otras están dispersas en un radio de 15 kilómetros. Esto significa que no existe una estrategia de optimización única que funcione para todas las rutas. Lo que es eficiente para una ruta compacta (como Ruta A) puede ser ineficiente para una ruta dispersa (como Ruta E). Cualquier estrategia de mejora debe ser específica para cada cluster geográfico.

**Todas las entregas problemáticas están en el mismo sector.** El hallazgo más revelador fue que las 10 entregas más cercanas a la bodega —todas a menos de 1 kilómetro— pertenecen exclusivamente a rutas del sector B. Esto significa que un camión del sector B sale de la bodega, entrega un paquete a 300 metros, y luego recorre 24 kilómetros para entregar el resto de su carga. La penalidad operacional acumulada por estas 10 entregas mal asignadas es de 232 kilómetros. La solución más evidente es crear una ruta exprés local que agrupe todas las entregas cercanas a la bodega, independientemente de su sector original.

## Aprendizajes

**Primero mide, después optimiza.** Es tentador saltar directamente a la implementación de algoritmos complejos de ruteo vehicular, especialmente cuando existen bibliotecas de código abierto como OR-Tools o jsprit que resuelven el problema en segundos. Pero sin una línea base cuantitativa no hay forma de saber si la optimización realmente está funcionando o si está introduciendo nuevos problemas. La línea base establecida en este proyecto permite comparar cualquier configuración futura contra un punto de referencia objetivo.

**Las métricas incorrectas pueden ser peor que ninguna métrica.** Si hubiéramos medido solo la distancia total recorrida, habríamos concluido que todas las rutas están igualmente optimizadas (527 kilómetros acumulados en todos los casos). Fue necesario agregar métricas de balance y calidad para revelar las ineficiencias ocultas. La lección es que el conjunto de métricas importa tanto como los valores individuales. Una métrica que parece buena en aislamiento puede estar ocultando problemas graves en otras dimensiones.

**La reproducibilidad no es un lujo, es un requisito.** En muchos proyectos de logística, los resultados varían entre ejecuciones porque los algoritmos usan componentes aleatorios (semillas, muestreos, inicializaciones). El diseño determinista de este sistema garantiza que cualquier persona pueda repetir exactamente los mismos experimentos y obtener los mismos resultados. Esto no solo valida la credibilidad de las conclusiones, sino que permite que otros investigadores construyan sobre nuestros resultados sin ambigüedad.

**Las anomalías son ventanas a problemas más grandes.** Cada entrega mal asignada no es solo un error que corregir, sino una señal de que el diseño de rutas tiene una debilidad estructural. En nuestro caso, las 10 anomalías no eran errores aleatorios; todas apuntaban al mismo problema: la mezcla de entregas cercanas y lejanas en las mismas rutas del sector B. Un patrón así no se descubre mirando una métrica agregada; requiere desglosar los datos por ruta individual.

## Próximos Pasos

El sistema de evaluación está completo y funcional, pero esto es solo el comienzo. Las siguientes fases del proyecto abordarán tres líneas de trabajo paralelas.

La primera es la optimización algorítmica: utilizar las métricas de balance como función objetivo para redistribuir paquetes entre rutas de manera más equitativa, explorando estrategias de asignación basadas en proximidad geográfica y capacidad de vehículos.

La segunda es el análisis estadístico avanzado: aplicar técnicas de regresión y clustering sobre los datos históricos de evaluaciones para identificar correlaciones entre características de entregas y ocurrencia de anomalías, y construir modelos predictivos que anticipen qué entregas tienen mayor probabilidad de ser mal asignadas.

La tercera es la exploración de rutas exprés locales para entregas cercanas a bodega, siguiendo la pista del hallazgo más concreto de esta fase: si logramos agrupar las entregas a menos de 1 kilómetro de la bodega en rutas dedicadas, podríamos reducir la penalidad operacional hasta en un 100%.

El objetivo final de todo el proyecto es un marco completo que cualquier operador logístico pueda utilizar para medir, comparar y mejorar sus operaciones de última milla, independientemente de la escala o la geografía. Un conjunto de herramientas, métricas y metodologías que transformen la optimización logística de un arte basado en la intuición a una ciencia basada en datos.
