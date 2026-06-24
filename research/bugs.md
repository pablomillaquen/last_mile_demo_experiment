# Registro de Bugs

*Bug tracking del proyecto. Cada bug documenta una desviación del comportamiento esperado, su impacto y la solución implementada.*

---

## BUG-001

**Título**: Exp001 modificado por procesos de SPEC-006

**Estado**: RESUELTO

**Fecha detección**: 2026-06-21

### Descripción

Durante la implementación de SPEC-006, el comando `experiments:sync` sobrescribió el contenido histórico del Experimento 001:

- `experiment.json`: redujo `evaluation_ids` de [2,3,4,5,6,7] a [1,3]
- `baseline_evaluation_id` cambió de 2 a 1
- Violó el principio de inmutabilidad de evidencia experimental

### Causa raíz

El comando `experiments:sync` hace un upsert incondicional: lee `experiment.json` del filesystem y escribe el contenido en la base de datos. No existía protección para experimentos históricos. Cualquier modificación del JSON (accidental o intencional) se refleja automáticamente en la BD, perdiendo la referencia original.

### Comportamiento esperado

Exp001 debe ser inmutable. Una vez creado, ningún proceso del sistema debe poder modificar sus metadatos (evaluation_ids, baseline_evaluation_id). Los cambios de SPEC-006 deben producir nuevos artefactos (Exp002) sin alterar evidencia previamente publicada.

### Síntomas detectados

1. `experiment.json` de Exp001 contenía evaluation_ids [1,3] en lugar de [2,3,4,5,6,7]
2. `report.md` de Exp001 no se modificó, pero quedó inconsistente con `experiment.json`
3. La BD registraba evaluation_ids incorrectos tras ejecutar `experiments:sync`

### Solución aplicada

1. **Restauración**: `git checkout HEAD -- experiments/001-baseline-comparison/experiment.json`
2. **Prevención**: Se agregó campo `immutable` al JSON de experimentos (commit XXX)
3. **Protección en código**: `SyncExperiments.php` ahora detecta `immutable: true` y salta el update si el registro ya existe en BD
4. **Exp001 marcado como inmutable**: `"immutable": true` en `experiment.json`

### Archivos afectados

- `experiments/001-baseline-comparison/experiment.json` — modificado accidentalmente, restaurado
- `backend/app/Console/Commands/SyncExperiments.php` — añadida lógica immutable
- `backend/app/Models/Experiment.php` — sin cambios (soporta evaluation_ids como array)

### Verificación

```bash
$ docker compose exec backend php artisan experiments:sync
Created: 0, Updated: 1, Deleted: 0, Warnings: 1
  Warning: Experiment '001-baseline-comparison' is immutable — skipping update
```

Exp001 queda con evaluation_ids [2,3,4,5,6,7] y baseline_evaluation_id 2, preservando su valor histórico.

---

## BUG-002

**Título**: Visualización del mapa sigue usando segmentos geodésicos en modo vial

**Estado**: RESUELTO (SPEC-007)

**Prioridad**: ALTA

**Fecha detección**: 2026-06-21

**Fecha cierre**: 2026-06-21

### Descripción

El frontend renderiza las rutas en el mapa usando únicamente coordenadas de paquetes unidas con líneas rectas (geodésicas). Incluso cuando la evaluación se ejecuta en modo vial, el mapa no muestra las rutas reales sobre la red de calles.

### Impacto

1. **Evidencia visual inexistente**: No hay forma de verificar visualmente que OSRM está funcionando
2. **Portafolio incompleto**: La diferencia entre geodesia y red vial no es visible para el usuario
3. **Documentación técnica**: Los mapas generados en reportes/evaluaciones no reflejan el modo vial
4. **H007–H010**: Los hallazgos de SPEC-006 dependen de métricas, pero no tienen respaldo visual

### Síntomas

- El mapa en la UI dibuja líneas rectas entre paquetes (código: Leaflet polyline con coordinates de packages)
- El endpoint de evaluaciones retorna `route_metrics` con distancias viales correctas, pero no entrega `route_geometry` ni `geojson`
- Los mapas de evaluación (`output_path/map_overview.png`) también se generan con segmentos geodésicos

### Causa raíz

SPEC-006 implementó la integración OSRM solo a nivel de cálculo de distancias (backend consume `/route` de OSRM para obtener distancia y duración, descartando la geometría). No se almacena ni expone la geometría vial vía API, y el frontend nunca la solicita.

### Requerimientos

1. El backend debe obtener la geometría de la ruta desde OSRM (`/route` retorna `geometry` en formato polyline o GeoJSON)
2. La geometría debe almacenarse o estar disponible vía API
3. El frontend debe dibujar la geometría vial usando Leaflet
4. El mapa de evaluación (reporte) debe incluir ambas geometrías (geodésica como baseline, vial como capa)

### Código relevante

- Frontend: `frontend/app/components/` (buscar Leaflet polyline, coordinates)
- API: `backend/app/Services/DistanceService.php` (llamada OSRM, ver si parsea geometry)
- Evaluación: `backend/app/Services/` (generación de mapas, ver qué geometría usa)

### Dependencias

Requiere cambios en:
- Backend (exponer geometría OSRM)
- Frontend (renderizar geometría vial)
- Generación de reportes (mapas con overlay vial)

### Solución aplicada (SPEC-006A)

1. **Backend** (`OsrmClient`): `overview=full&geometries=geojson` — OSRM devuelve geometría GeoJSON completa
2. **Backend** (`MeasurementService`): nuevo método `buildRouteLegs()` genera 155 legs (5 rutas, ~30 legs/ruta, 22,177 pts totales) con geometría vial OSRM
3. **Backend** (`EvaluationController`): expone `route_legs` en POST y GET `/api/evaluations/{id}`
4. **Frontend** (`MapView`): modo vial renderiza polilíneas desde `route_legs.geometry` agrupadas por `route_id`
5. **Fallback RF10**: si no hay `route_legs`, renderiza geodésico sin error

### Validación

- ✅ API retorna 155 route_legs para evaluación vial (Eval #19)
- ✅ Primera leg con 826 puntos de geometría OSRM
- ✅ CA3 consistencia: 0.000% diferencia entre route_legs sum y estimated_route_distance_km
- ✅ MapView renderiza geometría vial correctamente
- ✅ Fallback a geodésico para evaluaciones históricas (sin route_legs)

### Verificación final

---

## BUG-004

**Título**: MapRendererService genera mapas PNG con líneas rectas (geodésicas) incluso en evaluaciones viales

**Estado**: ABIERTO (no bloquea SPEC-008)

**Prioridad**: BAJA

**Fecha detección**: 2026-06-23

### Descripción

`MapRendererService::drawPolylines()` (L200–218) dibuja conexiones entre entregas usando `imageline()` con coordenadas `latitude/longitude`, generando siempre líneas rectas independientemente del `distance_mode` de la evaluación. Las imágenes PNG estáticas (`map_overview.png`, `map_route_*.png`, `map_anomalies.png`) no reflejan la geometría vial OSRM aunque esta exista en `route_legs`.

### Impacto

1. Las capturas PNG generadas durante evaluaciones viales muestran trayectorias geodésicas (rectas), no las rutas reales sobre calles.
2. Esto fue documentado originalmente como limitación conocida en BUG-002 (L89, L144–148), pero nunca se corrigió para el renderizado PNG.
3. El mapa interactivo Leaflet sí renderiza geometría vial correctamente (vía `route_legs.geometry`).

### Causa raíz

`MapRendererService` fue implementado antes de que existiera el concepto de `route_legs` con geometría vial. Usa `Route` → `routePackages` → coordinates para dibujar polilíneas con segmentos rectos entre puntos de entrega. No lee `route_legs.geometry` y no tiene lógica condicional por `distance_mode`.

### Código relevante

- `backend/app/Services/MapRendererService.php` L200–218 (`drawPolylines`)
- `backend/app/Services/MapRendererService.php` L60–64 (`renderRouteMap`, mismo patrón)
- `backend/app/Services/MeasurementService.php` L112–137 (llamadas a MapRendererService)

### Comportamiento esperado

- Si la evaluación tiene `distance_mode: vial` y `route_legs` con `geometry`, los mapas PNG deberían dibujar la geometría vial.
- Si no hay `route_legs` (evaluaciones históricas), fallback a geodésico (líneas rectas).

### Dependencias

- El fix requeriría pasar `route_legs` a `MapRendererService` y modificar `drawPolylines` para usar `geometry` cuando esté disponible.
- Alternativamente, generar las imágenes PNG desde el frontend (captura de Leaflet) sería un enfoque radicalmente distinto.

### Nota

Este bug no bloquea SPEC-008. El instrumento visual interactivo (SplitView, RoutePanel, aislamiento) funciona correctamente usando Leaflet. Las imágenes PNG estáticas son un artefacto secundario que puede corregirse en una iteración posterior.

✅ **Validación visual completada 2026-06-21**:
- Capturas de pantalla en modo geodésico y vial (Eval #19, 150 entregas, 5 rutas)
- Toggle Geodésico ↔ Vial funciona sin recarga de página
- Polylines geodésicos (líneas rectas entre paquetes) visibles en modo geodésico
- Polylines viales (geometría OSRM con 22,177 puntos) visibles en modo vial
- Diferencia visual clara entre ambos modos en todas las rutas
- Ruta D (factor 2.00× vial) muestra desvío más pronunciado — consistente con bahía de Valparaíso
- Fallback RF10 verificado: históricos sin route_legs renderizan geodésico sin error
- CA3 consistency 0.000% previamente validado
- 9/9 quickstart escenarios PASS

**Limitaciones documentadas** (no son bugs, son candidatos SPEC-008):
- No existe comparación simultánea geodésico/vial en una misma vista
- No existe filtrado de rutas individuales para reducir solapamiento
- No existe toggle por ruta individual

---

## BUG-003

**Título**: Falta selector visual de modo de distancia (geodésico / vial)

**Estado**: RESUELTO (SPEC-007)

**Prioridad**: MEDIA

**Fecha detección**: 2026-06-21

**Fecha cierre**: 2026-06-21

### Descripción

No existe un control en la UI que permita alternar entre visualización geodésica y vial para comparar ambas métricas directamente en el mapa.

### Comportamiento esperado

El usuario debe poder seleccionar entre:
- **Geodésico (Baseline)**: líneas rectas entre paquetes
- **Red vial OSRM**: rutas sobre calles reales

El cambio debe afectar:
- Mapa principal de rutas
- Mapas de evaluación (reportes)
- Activos generados por experimentos

### Impacto

Sin este selector, la comparación experimental no es visible para el usuario final, limitando el valor del portafolio.

### Dependencias

- BUG-002 (geometría vial disponible)

### Solución aplicada (SPEC-006A)

1. Nuevo componente `RouteModeToggle` con botones "Geodésico" / "Vial"
2. `vialAvailable` prop deshabilita "Vial" cuando no hay route_legs
3. Modo manejado como estado React puro — sin recarga de API al alternar
4. Integrado en:
   - `/map` (página principal con última evaluación)
   - `/evaluations/[id]` (detalle de evaluación, debajo de tarjetas de métricas)

### Validación

- ✅ Toggle visible y funcional en ambas páginas
- ✅ Cambio instantáneo sin llamadas de red (CA6)
- ✅ 5 cambios consecutivos sin errores (CA2)
- ✅ Vial deshabilitado si no hay route_legs (RF10)
- ✅ Fallback message visible cuando vial no disponible

### Verificación final

✅ **Validación visual completada 2026-06-21**:
- RouteModeToggle visible y funcional en `/map` y `/evaluations/[id]`
- Cambio instantáneo entre modos sin recarga de API (CA6)
- 5 cambios consecutivos sin errores (CA2)
- `vialAvailable` deshabilita "Vial" en evaluaciones históricas sin route_legs (RF10)
- Fallback message "Rutas viales no disponibles" visible cuando vial no está disponible
- Toggle presente debajo de tarjetas de métricas en página de detalle
