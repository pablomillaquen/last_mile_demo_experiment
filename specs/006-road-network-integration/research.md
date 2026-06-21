# Research: Incorporación de Red Vial Real

**Phase**: 0 — Research
**Date**: 2026-06-20

## 1. Motor de Ruteo: OSRM vs GraphHopper

### Decisión

**OpenStreetMap + OSRM** (confirmado del spec). Perfil `car.lua` con adaptaciones para última milla urbana.

### Rationale

- OSRM es el estándar en investigación de rutas (10K+ GitHub stars)
- Docker image oficial mantenida: `ghcr.io/project-osrm/osrm-backend`
- API HTTP simple: `GET /route/v1/{profile}/{lng},{lat};{lng},{lat}`
- Responde en <100ms para rutas de <50 waypoints (suficiente para 300 entregas en 10 rutas)
- Sin dependencia externa en runtime (Docker First)
- Perfil `car.lua` personalizable para calles angostas de Valparaíso

### Alternativas Consideradas

| Opción | Rechazada Por |
|--------|---------------|
| GraphHopper | Mayor complejidad de configuración inicial. OSRM tiene Docker más simple y documentación más extensa. |
| HERE Routing API | Dependencia externa, viola Docker First, límites de cuota. |
| Google Maps Roads API | Costoso, no reproducible, requiere API key. |
| Valhalla | Buena alternativa futura (perfiles modales), pero OSRM tiene mejor adopción y documentación. |

## 2. Perfil de Routing (car.lua)

### Decisión

Usar el perfil `car.lua` por defecto de OSRM con adaptaciones para calles angostas y zonas residenciales de Valparaíso.

| Tipo de Vía | Velocidad Máxima (km/h) | Prioridad |
|-------------|------------------------|-----------|
| motorway / trunk | 90 | 0.8 |
| primary / secondary | 60 | 0.9 |
| tertiary | 40 | 1.0 |
| residential | 30 | 0.9 |
| unclassified | 30 | 0.8 |
| living_street / service | 15 | 0.7 |
| pedestrian / footway / path | 0 (excluido) | — |
| track | 20 (solo si es accesible) | 0.5 |

Calles sin salida (`dead_end`) se incluyen sin penalización adicional (OSRM las maneja correctamente por defecto).

## 3. API de OSRM

### Endpoint principal

```
GET /route/v1/driving/{lng1},{lat1};{lng2},{lat2}
  ?overview=false
  &steps=false
  &alternatives=false
```

### Schema de respuesta

```json
{
  "code": "Ok",
  "routes": [
    {
      "distance": 1234.5,
      "duration": 92.3,
      "legs": [{"distance": 1234.5, "duration": 92.3}]
    }
  ]
}
```

Campos relevantes:
- `distance`: en metros (dividir por 1000 para km)
- `duration`: en segundos (dividir por 60 para minutos)
- `code`: `"Ok"` si la ruta es válida

### Manejo de errores

| Código | Significado | Manejo |
|--------|-------------|--------|
| "Ok" | Ruta encontrada | Procesar distancia/duración |
| "NoRoute" | No hay ruta entre puntos | Registrar como null, continuar |
| "NoSegment" | Coordenadas fuera del graph | Registrar como null, continuar |
| HTTP 500 | Error interno OSRM | Reintentar 1 vez, luego fallar con error claro |

## 4. Estrategia de Almacenamiento OSRM

### Decisión

Volumen Docker persistente para el graph de OSRM. Recrear solo cuando se actualicen los datos OSM.

```yaml
volumes:
  osrm-data:

services:
  osrm:
    image: ghcr.io/project-osrm/osrm-backend
    volumes:
      - osrm-data:/data
```

### Descarga de Datos

Origen: GeoFabrik — extracto Chile completo (~200MB PBF). Se descarga completo y luego se extrae el bounding box del Gran Valparaíso con `osmium extract`.
```
https://download.geofabrik.de/south-america/chile-latest.osm.pbf
```

Extraer bounding box del Gran Valparaíso (Valparaíso, Viña del Mar, Concón, Quilpué, Villa Alemana, Belloto, Limache):
```
osmium extract -b -71.70,-33.15,-71.20,-32.90 chile-latest.osm.pbf -o valparaiso.osm.pbf
```

Luego procesar con `osrm-extract` sobre el subconjunto regional. Esto reduce el grafo a ~30 MB PBF y ~7 min de preprocesamiento, enfocando los recursos en el área geográfica cubierta por Exp001 y Exp002.

```
osrm-extract -p /opt/car.lua valparaiso.osm.pbf
```

### Pipeline de Preprocesado

```
chile-latest.osm.pbf  (~200 MB, descarga única)
    │ osmium extract -b (bounding box Gran Valparaíso)
    ▼
valparaiso.osm.pbf    (~30 MB)
    │ osrm-extract
    ▼
valparaiso.osrm
    │ osrm-contract
    ▼
valparaiso.osrm (contracted)
    │ osrm-partition + osrm-customize
    ▼
osrm-routed (servicio HTTP)
```

### Estimación de Recursos (Grafo Gran Valparaíso)

| Etapa | Tiempo Estimado | Disco | RAM Peak |
|-------|-----------------|-------|----------|
| osmium extract | ~30 seg | ~30MB | <500MB |
| osrm-extract | ~2 min | ~100MB | 500MB |
| osrm-contract | ~3 min | ~80MB | 1GB |
| osrm-partition | ~1 min | ~20MB | <500MB |
| osrm-customize | ~1 min | ~20MB | <500MB |
| Total | ~7 min | ~250MB | 1GB |

El volumen Docker persistente evita reprocesar en cada inicio. La descarga del PBF de Chile completo se hace una sola vez y se reutiliza.

## 5. Estrategia de Distancias

### Decisión

Crear `DistanceService` como fachada (patrón Strategy) que encapsula HaversineService (geodésico) y OsrmClient (vial), seleccionable por configuración o por evaluación.

### Flujo

```
MeasurementService
  └─> DistanceService
        ├─> [mode=geodesic] HaversineService::calculate()
        └─> [mode=vial]     OsrmClient::route() → DistanceResult
```

### Justificación

- HaversineService no se modifica (permanece como referencia)
- OsrmClient encapsula toda la complejidad HTTP
- DistanceService permite pruebas unitarias mockeando cualquiera de los dos backend
- El modo se configura por evaluación (parámetro POST), permitiendo Exp002

## 6. Decisión Arquitectónica: Cobertura Geográfica

### Decisión

La infraestructura de ruteo utilizará un subconjunto del extracto de Chile limitado al Gran Valparaíso, extraído mediante bounding box. Esto alinea la cobertura del grafo OSRM con el área geográfica de los experimentos actuales (Exp001, Exp002), reduciendo tiempos de preprocesamiento y requisitos de recursos.

### Tres niveles diferenciados

| Nivel | Ámbito | Qué es | Responsabilidad |
|-------|--------|--------|-----------------|
| 1 | Dataset experimental | Gran Valparaíso (coordenadas de entregas actuales) | Experimentos (Exp001, Exp002) |
| 2 | Cobertura de infraestructura | Gran Valparaíso (grafo OSRM) | Docker / OSRM build |
| 3 | Generalización futura | Santiago, Concepción, Antofagasta, Temuco | Investigación (SPEC-008+) |

### Rationale

- **Alineación experimental**: El grafo cubre exactamente el área donde se realizan las evaluaciones. No tiene sentido procesar Chile completo si las entregas están en Valparaíso, Viña, Concón, Quilpué, Villa Alemana, Belloto y Limache.
- **Eficiencia**: ~7 min y ~1 GB RAM vs ~45 min y ~4 GB RAM del Chile completo. Iteraciones más rápidas durante el desarrollo.
- **Complejidad Incremental**: SPEC-006 estudia una hipótesis específica (¿cambian los hallazgos con red vial real?). La cobertura nacional es una línea de investigación separada.
- **Conexión con preguntas de investigación**: La expansión a Santiago y Concepción (PI-013) será una futura spec, donde se abordará la generación automática de grafos multi-ciudad.
- **Reproducibilidad**: El proceso de descarga + bounding box + preprocesamiento está completamente scriptado. Cualquier persona puede reproducir el mismo grafo ejecutando `make prepare-osrm`.

### Alternativa Rechazada

**Chile completo (grafo nacional)**: Rechazada por ahora porque:
- Incrementa el tiempo de build ~6× sin beneficio para el experimento actual.
- No mejora la reproducibilidad (el proceso scriptado es igual de reproducible).
- El estudio de cobertura nacional o multi-ciudad corresponde a una spec futura (PI-013, PI-014, PI-015).

## 7. Evaluación de Dependencias PHP

| Librería | Propósito | Decisión |
|----------|-----------|----------|
| `guzzlehttp/guzzle` | HTTP client para OSRM API interna | ✅ Incluir — estándar, mantenida |
| `php-http/guzzle7-adapter` | Adapter | ❌ No necesario |
| `league/csv` | Exportación (existente) | ✅ Ya incluida |
| GD | Mapas (existente) | ✅ Nativo PHP |

## 8. Tiempo Estimado de Viaje

OSRM retorna `duration` en segundos por defecto (basado en velocidad máxima por tipo de vía, sin tráfico en tiempo real). Se convertirá a minutos:

```
tiempo_min = duration_segundos / 60
```

**Limitación conocida**: No incluye congestión vehicular, tiempos de carga/descarga, ni demoras operacionales. Documentado en Amenazas a la Validez.

## 9. Estrategia de Caché

OSRM no requiere caché adicional — las consultas son stateless y rápidas. Si el número de waypoints creciera significativamente (>1000 pares), se podría considerar una caché LRU, pero no es necesario para el alcance actual (300 entregas).
