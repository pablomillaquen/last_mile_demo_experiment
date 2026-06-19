# Documentación: Pantalla de Detalle de Evaluación

## Vista General

La página de detalle de evaluación muestra los resultados completos de una ejecución específica del sistema de métricas.

## Elementos de la Pantalla

### Encabezado
- **ID de Evaluación**: Número identificador único.
- **Fecha y Hora**: Momento exacto de la ejecución.
- **Dataset**: Nombre del conjunto de datos utilizado.
- **Total de Entregas y Rutas**: Resumen del alcance de la evaluación.

### Resumen Ejecutivo (Tarjetas de Métricas Globales)

Ocho tarjetas muestran los indicadores globales más importantes:

| Métrica | Descripción | Interpretación |
|---------|-------------|----------------|
| Dist. Promedio General | Promedio de distancias de todas las entregas a la bodega | Menor = entregas más cercanas a bodega |
| Cobertura Territorial | Distancia máxima de cualquier entrega a la bodega | Mayor = zona de cobertura más extensa |
| Desviación Estándar | Dispersión de las distancias | Mayor = rutas más heterogéneas |
| Dist. Mínima Inter-Cluster | Separación entre los dos centroides más cercanos | Menor = clusters más superpuestos |
| Balance (CV) | Coeficiente de variación de distancias | Cercano a 0 = rutas balanceadas |
| Balance Index | Índice normalizado de balance | 1.0 = balance perfecto |
| Anomalías | Número de entregas anómalas detectadas | Mayor = más entregas mal ubicadas |
| Penalidad Operacional | Suma de ratios de anomalías | Mayor = anomalías más severas |

### Parámetros de la Evaluación
Sección colapsable que muestra:
- Umbral de cercanía (km)
- Ratio ignorado
- Semilla aleatoria
- Algoritmo y versión
- Coordenadas de la bodega
- Dataset

### Ranking de Rutas
Lista ordenada de rutas por cercanía promedio a la bodega (de más cercana a más lejana).
- **#1**: La ruta mejor ubicada (más cercana a bodega).
- Los números indican la posición en el ranking.
- El valor en km es la distancia promedio de sus entregas a la bodega.

### Tabla de Métricas por Ruta
Tabla detallada con una fila por ruta y las siguientes columnas:

| Columna | Descripción |
|---------|-------------|
| Ruta | Nombre de la ruta |
| Entregas | Número de entregas asignadas |
| Dist. Ruta (km) | Distancia total estimada del recorrido |
| Prom. Bodega (km) | Distancia promedio de entregas a bodega |
| Min (km) | Distancia mínima de una entrega a bodega |
| Max (km) | Distancia máxima de una entrega a bodega |
| Radio (km) | Dispersión del cluster (max distancia al centroide) |
| Prom. Centroide (km) | Compactación del cluster |
| Centroide→Bodega (km) | Distancia del centro del cluster a bodega |

**Ordenamiento**: Haga clic en cualquier encabezado de columna para ordenar la tabla por ese criterio.

### Anomalías Detectadas
Tabla que muestra las entregas clasificadas como anómalas:
- **Delivery ID**: Identificador de la entrega.
- **Ruta**: Ruta a la que pertenece.
- **Dist. Bodega (km)**: Distancia de la entrega a la bodega.
- **Dist. Centroide (km)**: Distancia de la entrega al centroide de su ruta.
- **Ratio**: Proporción entre distancia al centroide y distancia a bodega. Un ratio alto indica una entrega muy cercana a bodega pero asignada a una ruta cuyo centroide está lejos.

### Mapas Generados
- **Vista General**: Mapa con todas las rutas y entregas del sistema.
- **Mapa de Anomalías**: Destaca las entregas anómalas detectadas.
- **Mapas por Ruta**: Un mapa individual por cada ruta.

### Archivos Exportados
Enlaces para descargar:
- **JSON**: Datos completos de la evaluación en formato estructurado.
- **CSV (rutas)**: Métricas por ruta en formato tabular.
- **CSV (entregas)**: Datos de cada entrega con distancias.
- **PDF**: Reporte profesional de la evaluación (si está disponible).
