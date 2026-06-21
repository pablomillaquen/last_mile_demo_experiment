# Research: Visualización de Red Vial

**Branch**: `007-road-network-visualization` | **Date**: 2026-06-21 | **Spec**: [spec.md](spec.md)

---

## Decisiones de Diseño

### D1: Almacenamiento de geometría vial

**Contexto**: La geometría OSRM (polyline de cada tramo entre entregas) debe persistirse para ser servida al frontend. Se evaluaron tres opciones.

| Opción | Descripción | Ventajas | Desventajas |
|---|---|---|---|
| A | Columna `geometry` en tabla `routes` | Consulta directa desde API | Migración BD, inconsistencia si se re-evalúa, rompe D009 (BD como caché) |
| B | Array `route_legs` dentro de `evaluation.json` en disco | Sigue D009 (filesystem = fuente de verdad), sin migración | `GET /evaluations/{id}` ya lo sirve automáticamente |
| C | Archivo separado por ruta (e.g., `geometry-{route_id}.json`) | Aislamiento | Mayor complejidad de carga, más requests |

**Decisión**: **Opción B** — Almacenar geometría como array `route_legs` dentro de `evaluation.json`.

**Rationale**: D009 establece que el filesystem es la fuente de verdad y la BD es caché. evaluation.json ya es el artefacto canónico y `GET /evaluations/{id}` lo sirve directamente. No se requieren migraciones, nuevas tablas ni endpoints.

---

### D2: Modificación de OsrmClient

**Contexto**: Actualmente `OsrmClient::route()` llama a OSRM con `overview=false&steps=false`, descartando la geometría.

**Decisión**: Cambiar a `overview=full` en la llamada OSRM y devolver `geometry` (encoded polyline) en el array de respuesta.

**Rationale**: `overview=full` retorna la polyline completa de la ruta. No se necesitan `steps` (geometry por step) porque solo renderizamos la ruta completa. El overhead de ancho de banda es mínimo (~1-2KB por ruta de 30 entregas en Valparaíso).

**Formato de retorno de OsrmClient::route()**:
```php
[
  'distance_km' => float,
  'duration_min' => float,
  'geometry' => [[lat, lng], ...],  // NUEVO — GeoJSON [[lat, lng], ...]
  'code' => 'Ok',
]
```

**Nota de implementación**: Se usa `geometries=geojson` en lugar de `overview=full` (sin geometrías) + decodificación. OSRM soporta `geometries=geojson` que retorna coordenadas `[lng, lat]` directamente. El backend convierte a `[lat, lng]` inline. Esto elimina la necesidad de `pcrov/polyline`.

---

### D3: Obtención de geometría OSRM

**Contexto**: OSRM retorna geometría en formato [encoded polyline](https://developers.google.com/maps/documentation/utilities/polylinealgorithm) (algoritmo de Google) por defecto. El frontend Leaflet espera `[lat, lng][]`.

**Opciones**:

| Opción | Descripción |
|---|---|
| A | Usar polyline encoded + `pcrov/polyline` en PHP para decodificar |
| B | Usar `geometries=geojson` — OSRM retorna `[lng, lat]` directamente, backend convierte a `[lat, lng]` |
| C | Pasar el string encoded al frontend y decodificar con `@mapbox/polyline` |

**Decisión**: **Opción B** — Usar `geometries=geojson` y convertir inline en `OsrmClient::route()`.

**Rationale**: OSRM soporta nativamente `geometries=geojson` que retorna coordenadas en formato GeoJSON estándar `[lng, lat]`. Esto elimina la necesidad de cualquier librería de decodificación (PHP o frontend). El backend solo invierte el orden de las coordenadas con `array_map`. Menos dependencias, menos código, misma especificación RF12.

**Dependencia PHP**: Ninguna — OSRM nativo.

**Trade-off**: Sin la dependencia explícita `pcrov/polyline`, el pipeline es ligeramente menos autocontenido (asume formato GeoJSON de OSRM). Sin embargo, GeoJSON es un estándar W3C/IETF, no un formato propietario. Si en el futuro se cambia de motor de ruteo, mientras soporte GeoJSON el cambio es trivial.

---

### D4: Formato de route_legs en evaluation.json

**Contexto**: Cada evaluation.json contiene `route_metrics[]` (métricas agregadas por ruta) y `deliveries_flat[]` (entregas individuales). Falta la geometría entre pares de entregas consecutivas.

**Decisión**: Agregar `route_legs` como array de objetos obligatorio en TODA evaluación generada desde SPEC-007 en adelante. Cada objeto representa un tramo entre dos entregas consecutivas (incluyendo ida/vuelta al warehouse).

```json
{
  "route_legs": [
    {
      "route_id": 1,
      "from_delivery_id": null,
      "to_delivery_id": 1,
      "from_lat": -33.045,
      "from_lng": -71.62,
      "to_lat": -32.929,
      "to_lng": -71.521,
      "distance_km": 15.5,
      "duration_min": 18.2,
      "geometry": [[-33.045, -71.62], [-33.044, -71.619], ...],
      "mode": "vial"
    }
  ]
}
```

**Geodésica**: misma estructura, `geometry` = 2 puntos (origen, destino), `mode = 'geodesic'`.
**Vial**: `geometry` = polyline decodificada OSRM, `mode = 'vial'`.

**Beneficios de hacerlo obligatorio**:
- Contrato único frontend: sin condicionales por modo o edad de la evaluación.
- Consistencia con D009: todo artefacto nuevo contiene la misma estructura.
- Trazabilidad: cualquier evaluación futura tendrá geometría, incluso si se cambia el motor de ruteo.
- Evaluaciones históricas (EXP-001, EXP-002 pre-SPEC-007) mantienen compatibilidad hacia atrás: `route_legs` ausente → fallback geodésico.

**Consideración de tamaño**: Valparaíso demo: ~300 deliveries, ~10 rutas, ~30 legs por ruta, ~50 puntos por leg (OSRM overview=full) → ~15,000 puntos total. Como array JSON plano ~200-300KB. Aceptable para una respuesta de API que se carga una vez.

---

### D5: API contract — GET /evaluations/{id}

**Contexto**: Actualmente `GET /evaluations/{id}` ejecuta `EvaluationController::show()` que busca el registro en BD y luego lee `evaluation.json` del disco (`output_path`).

**Decisión**: Sin cambios en el endpoint. Al almacenar `route_legs` dentro de `evaluation.json`, este endpoint lo servirá automáticamente. El frontend recibe la geometría como parte de la respuesta existente.

**Contrato**:
```typescript
interface RouteLeg {
  route_id: number;
  from_delivery_id: number | null;
  to_delivery_id: number;
  from_lat: number;
  from_lng: number;
  to_lat: number;
  to_lng: number;
  distance_km: number;
  duration_min: number | null;
  geometry: [number, number][];       // [[lat, lng], ...]
  mode: 'geodesic' | 'vial';
}
```

---

### D6: Estrategia de carga en frontend

**Contexto**: El mapa interactivo actual (`/map/page.tsx`) carga routes + packages y construye polilíneas geodésicas. El detalle de evaluación (`/evaluations/[id]/page.tsx`) muestra imágenes estáticas.

**Decisión**: 

1. **`/map/page.tsx`**: Modificar para que, cuando haya una evaluación activa (EXP-002), cargue `GET /evaluations/{id}` y use `route_legs` para renderizar modo vial. Si no hay evaluación, mantiene el comportamiento actual (geodésico desde routes/packages).

2. **`/evaluations/[id]/page.tsx`**: Agregar un `MapView` interactivo debajo de las tarjetas de métricas, usando la geometría ya incluida en la respuesta de la evaluación. Agregar toggle geodésico/vial.

---

### D7: Componente de toggle

**Contexto**: No existe selector de modo en el frontend.

**Decisión**: Agregar un `RouteModeToggle` componente UI con dos botones: "Geodésico" y "Vial". El estado se mantiene en el padre (MapView o página) y se pasa como prop al MapView.

**Estados**:
- `'geodesic'`: renderiza polilíneas rectas entre puntos (comportamiento actual)
- `'vial'`: renderiza geometry desde `route_legs` si existe; si no, fallback silencioso a geodésico (RF10)

---

## TV-001: Amenaza a la validez

**Descripción**: La visualización vial depende de la calidad de OpenStreetMap y OSRM. Si existen errores cartográficos (calles faltantes, geometría imprecisa, sentidos incorrectos), la geometría visualizada puede diferir de la ruta real que un conductor experimentaría en terreno.

**Impacto**: Bajo. El objetivo del sistema es comparativo entre modelos de distancia (geodésico vs vial), no navegación operativa en tiempo real. Ambos modelos se ven afectados por la misma fuente cartográfica.

**Mitigación**:
- El propósito declarado del sistema es análisis y visualización, no guiado operativo.
- La comparación siempre es contra el modelo geodésico, que es el mismo baseline usado en EXP-001.
- OSM en zonas urbanas consolidadas (Valparaíso) tiene cobertura adecuada para rutas de última milla.

---

## SPEC-008: Evolución futura (registrada, no implementada)

**Idea**: Vista comparativa simultánea que muestre ambas geometrías (geodésico + vial) al mismo tiempo, ya sea como overlay (línea gris semitransparente para geodésico + línea de color para vial) o en pantalla dividida (split view before/after).

**Relación**: BUG-003 se resuelve en SPEC-006A con el toggle. SPEC-008 llevaría la comparación al siguiente nivel con visualización simultánea.

**Decisión**: No implementar en este SPEC. Se registra aquí para mantener trazabilidad y evitar perder la idea. No se agrega a issues activos.

---

## Resumen de cambios por archivo

| Archivo | Cambio |
|---|---|
| `backend/app/Services/OsrmClient.php` | `overview=full&geometries=geojson`, devolver `geometry` como `[lat, lng][]` |
| `backend/app/Services/DistanceService.php` | Pasar `geometry` en array de retorno |
| `backend/app/Services/MeasurementService.php` | Construir `route_legs[]` con geometry, escribirlo en evaluation.json |
| `backend/composer.json` | ~~Agregar `pcrov/polyline`~~ No necesario — OSRM devuelve GeoJSON nativo |
| `backend/app/Http/Resources/EvaluationResource.php` | Agregar `route_legs` a respuesta API (estaba omitido) |
| `frontend/src/lib/api.ts` | Agregar interfaces `RouteLeg`, actualizar `Evaluation` |
| `frontend/src/components/MapView.tsx` | Agregar prop `routeLegs`, modo activo, toggle interno |
| `frontend/src/components/RouteModeToggle.tsx` | (NUEVO) Componente toggle geodésico/vial |
| `frontend/src/app/map/page.tsx` | Cargar evaluation si existe, pasar routeLegs a MapView |
| `frontend/src/app/evaluations/[id]/page.tsx` | Agregar MapView con routeLegs + toggle |
