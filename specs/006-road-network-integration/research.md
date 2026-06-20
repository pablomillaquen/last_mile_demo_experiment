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

Origen: GeoFabrik — extracto Chile completo (~200MB PBF).
```
https://download.geofabrik.de/south-america/chile-latest.osm.pbf
```

Procesar con `osrm-extract` sobre Chile completo (sin subset por bounding box). Esto permite:
- Rutas que abarcan toda la Región de Valparaíso (incluyendo Viña del Mar, Concón, Quilpué, Villa Alemana, Limache).
- Posibilidad futura de evaluar en Santiago, Concepción y otras ciudades (reproducibilidad).
- OSRM funciona de manera óptima con el extracto completo de Chile (~200MB PBF).

```
osrm-extract -p /opt/car.lua chile-latest.osm.pbf
```

### Pipeline de Preprocesado

```
chile-latest.osm.pbf
    │ osrm-extract
    ▼
chile-latest.osrm
    │ osrm-contract
    ▼
chile-latest.osrm (contracted)
    │ osrm-partition + osrm-customize
    ▼
osrm-routed (servicio HTTP)
```

### Estimación de Recursos

| Etapa | Tiempo Estimado | Disco | RAM Peak |
|-------|-----------------|-------|----------|
| osrm-extract | ~15 min | ~600MB | 2GB |
| osrm-contract | ~20 min | ~400MB | 4GB |
| osrm-partition | ~5 min | ~100MB | 1GB |
| osrm-customize | ~5 min | ~100MB | 1GB |
| Total | ~45 min | ~1.2GB | 4GB |

Aceptable para desarrollo en máquina moderna. El volumen Docker persistente evita reprocesar en cada inicio.

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

La infraestructura de ruteo utilizará el extracto nacional de Chile como fuente base de red vial. Los experimentos individuales definen su propio dataset (coordenadas de entregas), pero el grafo de OSRM cubre todo el territorio nacional.

### Tres niveles diferenciados

| Nivel | Ámbito | Qué es | Responsabilidad |
|-------|--------|--------|-----------------|
| 1 | Dataset experimental | Gran Valparaíso (coordenadas de entregas actuales) | Experimentos (Exp001, Exp002) |
| 2 | Cobertura de infraestructura | Chile completo (grafo OSRM) | Docker / OSRM build |
| 3 | Generalización futura | Santiago, Concepción, Antofagasta, Temuco | Investigación (SPEC-008+) |

### Rationale

- **Reproducibilidad**: El mismo contenedor OSRM sirve para cualquier experimento futuro, sin reconstruir el grafo.
- **Reutilización del conocimiento**: Un experimento en Santiago (SPEC-008) usaría exactamente el mismo stack, cambiando solo las coordenadas del dataset.
- **Conexión con preguntas de investigación**: Esta decisión habilita una futura PI-013: *¿Varía el factor de desvío geodésico-vial según la morfología urbana?* Comparando Valparaíso (topografía compleja), Santiago (trama regular) y Concepción (estructura policéntrica) con idéntica infraestructura.
- **Evolución del proyecto**: Consistente con los principios de Complejidad Incremental y Conocimiento Reutilizable de la Constitución.

### Alternativa Rechazada

**Subset regional por bounding box**: Rechazada porque acopla la infraestructura al experimento actual. Cualquier expansión geográfica futura requeriría reconstruir OSRM y modificar el stack Docker, rompiendo la reproducibilidad histórica.

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
