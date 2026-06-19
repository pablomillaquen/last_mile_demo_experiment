# Guía de Interpretación de Métricas

## 1. Entregas por Ruta

- **Definición**: Número total de entregas (paquetes) asignados a una ruta específica.
- **Fórmula**: Conteo simple de paquetes cuyo `route_id` corresponde a la ruta.
- **Interpretación**:
  - **Alto** (>40): La ruta tiene muchas entregas, lo que puede aumentar el tiempo de recorrido y la probabilidad de errores.
  - **Bajo** (<15): La ruta tiene pocas entregas, posiblemente subutilizada.
  - **Valores de referencia**: 25–35 entregas por ruta es un rango típico para operaciones urbanas.
- **Ejemplo**: Si la Ruta A tiene 30 entregas y la Ruta B tiene 10, la Ruta B está subutilizada y podría consolidarse.

## 2. Distancia Mínima a Bodega

- **Definición**: Distancia más corta desde cualquier entrega de la ruta hasta la bodega.
- **Fórmula**: `min(Haversine(bodega, entrega_i))` para toda entrega `i` en la ruta.
- **Interpretación**:
  - **Cercano a 0**: Hay al menos una entrega muy cerca de la bodega en esta ruta.
  - **Alto** (>5 km): Todas las entregas de la ruta están lejos de la bodega.
- **Ejemplo**: Si la distancia mínima es 0.3 km, significa que hay una entrega prácticamente al lado de la bodega en esa ruta.

## 3. Distancia Máxima a Bodega

- **Definición**: Distancia más larga desde cualquier entrega de la ruta hasta la bodega.
- **Fórmula**: `max(Haversine(bodega, entrega_i))` para toda entrega `i` en la ruta.
- **Interpretación**:
  - **Alto** (>20 km): La ruta incluye entregas en zonas periféricas o lejanas.
- **Ejemplo**: Distancia máxima de 24 km indica que la ruta cubre desde zonas cercanas hasta zonas muy alejadas de la bodega.

## 4. Distancia Promedio a Bodega

- **Definición**: Promedio de las distancias de todas las entregas de la ruta a la bodega.
- **Fórmula**: `(1/N) * Σ Haversine(bodega, entrega_i)`.
- **Interpretación**:
  - **Bajo** (<10 km): La ruta opera cerca de la bodega, lo que sugiere menor costo operacional.
  - **Alto** (>20 km): La ruta opera lejos de la bodega, mayor costo de transporte.
- **Ejemplo**: Ruta C con 7.89 km promedio está mejor ubicada que Ruta E con 16.34 km promedio.

## 5. Centroide del Cluster

- **Definición**: Punto geográfico central del conjunto de entregas de una ruta, calculado como el promedio de las coordenadas.
- **Fórmula**: `lat_centroide = (1/N) * Σ lat_i`, `lng_centroide = (1/N) * Σ lng_i`.
- **Interpretación**: Representa la "ubicación típica" de las entregas de la ruta. Útil para comparar dónde opera cada ruta en relación con la bodega y con otras rutas.
- **Ejemplo**: Si el centroide de la Ruta A está en (-33.05, -71.62) y la bodega en (-33.045, -71.62), la ruta opera muy cerca de la bodega.

## 6. Distancia Centroide-Bodega

- **Definición**: Distancia desde el centroide del cluster hasta la bodega.
- **Fórmula**: `Haversine(bodega, centroide)`.
- **Interpretación**:
  - **Bajo** (<5 km): El centro de operaciones de la ruta está cerca de la bodega.
  - **Alto** (>15 km): La ruta opera principalmente lejos de la bodega.
- **Ejemplo**: Si la distancia centroide-bodega es 7.87 km, la ruta opera en un área que está a unos 8 km de la bodega en promedio.

## 7. Radio del Cluster

- **Definición**: Distancia máxima desde cualquier entrega de la ruta hasta el centroide del cluster.
- **Fórmula**: `max(Haversine(centroide, entrega_i))`.
- **Interpretación**:
  - **Pequeño** (<1 km): Las entregas están muy concentradas geográficamente (cluster compacto).
  - **Grande** (>10 km): Las entregas están dispersas en un área extensa.
- **Ejemplo**: Ruta A con radio 0.30 km es extremadamente compacta. Ruta B con radio 13.58 km es muy dispersa.

## 8. Distancia Promedio al Centroide (Compactación)

- **Definición**: Promedio de las distancias de todas las entregas al centroide del cluster.
- **Fórmula**: `(1/N) * Σ Haversine(centroide, entrega_i)`.
- **Interpretación**:
  - **Bajo** (<1 km): Cluster muy compacto, todas las entregas están cerca unas de otras.
  - **Alto** (>5 km): Cluster disperso, las entregas están repartidas en un área amplia.
- **Ejemplo**: Ruta A con 0.17 km de compactación es óptima. Ruta B con 9.17 km sugiere que las entregas están muy dispersas.

## 9. Distancia Estimada de Ruta

- **Definición**: Distancia total estimada del recorrido de la ruta, calculada como la suma de distancias Haversine desde la bodega a la primera entrega, entre entregas consecutivas, y de la última entrega de vuelta a la bodega.
- **Fórmula**: `Hav(bodega, P1) + Σ Hav(Pi, Pi+1) + Hav(PN, bodega)`.
- **Interpretación**:
  - **Alta** (>80 km): Ruta larga, probablemente con muchas entregas o muy dispersas.
  - **Baja** (<30 km): Ruta corta y eficiente.
- **Ejemplo**: Ruta C con 99.68 km es la más larga. Ruta A con 22.04 km es la más corta y eficiente.

## 10. Cobertura Territorial

- **Definición**: Distancia máxima desde cualquier entrega del sistema hasta la bodega. Representa el alcance geográfico total de la operación.
- **Fórmula**: `max(Haversine(bodega, entrega_i))` para TODAS las entregas del sistema.
- **Interpretación**:
  - **Alta**: La operación cubre un área geográfica extensa.
  - **Baja**: La operación está concentrada cerca de la bodega.
- **Ejemplo**: Cobertura de 24.23 km significa que la entrega más lejana está a 24.23 km de la bodega.

## 11. Desviación Estándar de Distancias

- **Definición**: Desviación estándar de las distancias de todas las entregas a la bodega. Mide qué tan dispersas están las distancias alrededor del promedio.
- **Fórmula**: `σ = sqrt((1/N) * Σ (d_i - d_prom)^2)` donde `d_i` es la distancia de cada entrega a la bodega.
- **Interpretación**:
  - **Baja** (<2 km): Todas las entregas están aproximadamente a la misma distancia de la bodega.
  - **Alta** (>5 km): Hay entregas tanto muy cerca como muy lejos de la bodega.
- **Ejemplo**: Desviación de 4.68 km indica heterogeneidad significativa en las distancias de las entregas.

## 12. Balance Index (CV)

- **Definición**: Índice que mide qué tan balanceadas están las rutas en términos de distancia promedio a bodega. Se basa en el coeficiente de variación (CV) de las distancias promedio por ruta.
- **Fórmula**: `Balance Index = 1 - CV` donde `CV = σ_distancias_promedio / μ_distancias_promedio`. Un valor de 1.0 indica balance perfecto (todas las rutas tienen la misma distancia promedio a bodega).
- **Interpretación**:
  - **1.0**: Balance perfecto — todas las rutas están igualmente distantes de la bodega.
  - **< 0.8**: Desbalance significativo — algunas rutas operan mucho más cerca de la bodega que otras.
- **Ejemplo**: Balance Index de 1.0000 indica que en este conjunto de datos, todas las rutas tienen distancias promedio muy similares entre sí.

## 13. Inter Cluster Distance

- **Definición**: Distancia mínima entre los centroides de cualquier par de rutas diferentes. Mide qué tan separados están los clusters entre sí.
- **Fórmula**: `min(Haversine(centroide_i, centroide_j))` para todo par de rutas `(i, j)` con `i ≠ j`.
- **Interpretación**:
  - **Cercano a 0**: Dos o más rutas tienen centroides casi idénticos (superposición de clusters).
  - **Alto** (>5 km): Los clusters están bien separados geográficamente.
- **Ejemplo**: 0.0055 km (5.5 metros) indica que dos rutas están prácticamente superpuestas. Esto puede sugerir que deberían fusionarse o rediseñarse.

## 14. Operational Penalty

- **Definición**: Penalización operacional total que suma la severidad de todas las anomalías detectadas. Cada anomalía contribuye con el ratio entre su distancia al centroide y su distancia a la bodega.
- **Fórmula**: `Σ (dist_centroide_i / dist_bodega_i)` para cada anomalía `i`.
- **Interpretación**:
  - **0**: No se detectaron anomalías.
  - **> 100**: Se detectaron anomalías significativas que sugieren ineficiencias en la asignación de rutas.
- **Ejemplo**: Penalidad de 232.26 indica 10 anomalías con un ratio promedio de 23.23 cada una.

## 15. Anomalías

- **Definición**: Entregas que están cerca de la bodega (dentro del umbral de cercanía) pero fueron asignadas a una ruta cuyo centroide está significativamente más lejos (superando el ratio ignorado). Esto sugiere que la entrega podría reasignarse a una ruta más cercana para optimizar el recorrido.
- **Fórmula**: Una entrega `i` es anomalía si:
  1. `Haversine(bodega, entrega_i) <= threshold_km`
  2. `Haversine(centroide_ruta, entrega_i) / Haversine(bodega, entrega_i) >= ratio_ignorado`
- **Interpretación**:
  - **0 anomalías**: No hay entregas cercanas a bodega mal asignadas, o los parámetros son muy restrictivos.
  - **Muchas anomalías**: Existen oportunidades de optimización reasignando entregas a rutas más cercanas a la bodega.
- **Valores de referencia**: En un diseño de rutas eficiente, lo ideal es tener 0 anomalías. Hasta 5 anomalías puede ser aceptable en operaciones complejas.
- **Ejemplo**: Una entrega a 0.3 km de la bodega pero asignada a una ruta cuyo centroide está a 11.6 km es una anomalía clara: esa entrega debería estar en una ruta local, no en una ruta de largo recorrido.
