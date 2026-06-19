# Research: Sistema de Medición, Evaluación y Validación de Resultados

**Phase**: 0 — Research
**Date**: 2026-06-19

## 1. Generación de Mapas Server-Side

### Decisión

Usar **GD library** nativa de PHP con proyección Mercator simplificada para
generar mapas en formato PNG. La implementación se encapsulará en un servicio
(MapRendererService) que toma coordenadas, las proyecta a píxeles y dibuja
marcadores, polilíneas y etiquetas.

### Rationale

- Sin dependencias externas (GD viene incluido en PHP).
- Sin APIs externas de mapas (reproducible en Docker).
- Suficiente para el objetivo: documentación visual de rutas y clusters.
- Consistente: mismo algoritmo siempre, mismos resultados visuales.

### Alternativas Consideradas

| Opción | Rechazada Por |
|--------|---------------|
| Intervention Image | Es wrapper de GD, no añade capacidad de mapas |
| Puppeteer/BrowserShot | Dependencia Node.js pesada en contenedor PHP, overkill |
| Image-Charts / Static Maps API | Dependencia externa, no reproducible en Docker |
| SVG generado con coordenadas proyectadas | Alternativa viable como complemento (formato vectorial para artículos) |

### Proyección

Se usará una proyección Mercator simplificada escalada al bounding box de los
datos más un margen del 10%. Esto asegura que todas las entregas y la bodega
sean visibles sin depender de tiles externos.

## 2. Estrategia de Exportación

### Decisión

Exportar resultados como archivos JSON y CSV en el directorio
`storage/app/evaluations/`, organizados por timestamp de ejecución.

### Estructura de archivos

```text
storage/app/evaluations/
├── YYYYMMDD_HHMMSS/
│   ├── evaluation.json          # Métricas completas en JSON
│   ├── evaluation.csv           # Métricas por ruta en CSV
│   ├── map_overview.png         # Mapa general
│   ├── map_route_{id}.png       # Mapa por ruta
│   └── map_anomalies.png        # Mapa de casos relevantes
```

### Nomenclatura de archivos

- `evaluation_{timestamp}.json` — métricas completas
- `evaluation_{timestamp}.csv` — métricas por ruta (una fila por ruta)
- `map_overview_{timestamp}.png` — mapa general
- `map_route_{route_id}_{timestamp}.png` — mapa por ruta
- `map_anomalies_{timestamp}.png` — mapa de anomalías

## 3. Evaluación de Dependencias PHP

| Librería | Propósito | Decisión |
|----------|-----------|----------|
| `league/csv` | Exportación CSV | ✅ Incluir — estándar, mantenida, sin dependencias pesadas |
| GD | Generación de mapas | ✅ Nativo PHP, sin `composer require` |
| `spatie/geography` | Cálculos geográficos | ❌ HaversineService existente es suficiente |

## 4. Esquema de Tabla Evaluations

### Decisión

Crear tabla `evaluations` para persistir metadatos de cada ejecución del sistema
de métricas. Los datos detallados (métricas por ruta) se almacenan en los
archivos exportados, no en la BD.

### Columnas

| Columna | Tipo | Descripción |
|---------|------|-------------|
| id | bigint (PK) | Identificador único |
| executed_at | timestamp | Fecha y hora de la ejecución |
| parameters | jsonb | Parámetros usados (algorithm, algorithm_version, random_seed, near_delivery_threshold, ignored_delivery_ratio) |
| total_deliveries | integer | Total de entregas procesadas |
| total_routes | integer | Total de rutas evaluadas |
| metrics_summary | jsonb | Resumen de indicadores globales (cobertura, promedio, stddev, balance, separación entre clusters, penalización operacional) |
| output_path | string | Ruta relativa a los archivos exportados |
| created_at | timestamp | Fecha de creación del registro |

## 5. capa de Servicios

### Decisión

Crear nuevos servicios en `app/Services/`:

- `MeasurementService.php` — Orquestador principal: orquesta el cálculo de todas
  las métricas, detección de anomalías, generación de mapas y exportación.
- `MetricsCalculatorService.php` — Cálculos matemáticos puros (centroide, radio,
  distancias, balance), sin efectos secundarios.
- `AnomalyDetector.php` — Lógica de detección de entregas cercanas ignoradas.
- `MapRendererService.php` — Generación de imágenes de mapas con GD.
- `MetricsExporter.php` — Exportación a JSON y CSV (en `app/Exports/`).

### Rationale

- Separación clara de responsabilidades.
- `MetricsCalculatorService` es puro y testeable sin base de datos ni archivos.
- `AnomalyDetector` encapsula la lógica de umbrales configurables.
- `MapRendererService` aísla la complejidad de GD.
- `MeasurementService` es el orquestador que usará el controlador.
