# Documentación: Pantalla de Evaluaciones (Lista)

## Vista General

La página de evaluaciones muestra el listado completo de todas las ejecuciones del sistema de métricas, ordenadas de la más reciente a la más antigua.

## Elementos de la Pantalla

### Encabezado
- **Título**: "Evaluaciones"
- **Botón "Nueva Evaluación"**: Permite ejecutar una nueva evaluación con parámetros personalizados.

### Tarjetas de Evaluación
Cada evaluación se muestra como una tarjeta con la siguiente información:

- **ID**: Identificador numérico único de la evaluación.
- **Fecha**: Fecha y hora de ejecución.
- **Entregas**: Número total de entregas evaluadas.
- **Rutas**: Número de rutas en la evaluación.
- **Métricas Clave**:
  - **Distancia Promedio (km)**: Distancia promedio de todas las entregas a la bodega.
  - **Cobertura (km)**: Distancia máxima de cualquier entrega a la bodega.
  - **Anomalías**: Número de entregas detectadas como anómalas.
  - **Penalidad**: Penalización operacional total calculada.

### Comparación Visual
Para comparar evaluaciones, observe los valores de las tarjetas. Una evaluación con menor penalidad y menos anomalías sugiere una mejor asignación de rutas, aunque esto depende del contexto y los parámetros utilizados.

### Identificación de Resultados Relevantes
- **Mejor métrica**: Busque la evaluación con menor penalidad operacional y menor distancia promedio.
- **Peor métrica**: Identifique evaluaciones con alta penalidad o muchas anomalías.
- **Parámetros inusuales**: Compare los parámetros (threshold, ratio) entre evaluaciones para entender diferencias en los resultados.

### Ejecutar Nueva Evaluación
1. Haga clic en "Nueva Evaluación".
2. Configure los parámetros:
   - **Umbral de Cercanía (km)**: Distancia máxima desde la bodega para considerar una entrega como "cercana".
   - **Ratio Ignorado**: Proporción mínima entre distancia al centroide y distancia a la bodega para clasificar como anomalía.
   - **Semilla Aleatoria**: Para reproducibilidad (opcional).
3. Haga clic en "Ejecutar".
4. Espere la confirmación. La nueva evaluación aparecerá al inicio del listado.
