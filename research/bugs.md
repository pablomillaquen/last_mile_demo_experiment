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

**Estado**: ABIERTO

**Prioridad**: ALTA

**Fecha detección**: 2026-06-21

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

---

## BUG-003

**Título**: Falta selector visual de modo de distancia (geodésico / vial)

**Estado**: ABIERTO

**Prioridad**: MEDIA

**Fecha detección**: 2026-06-21

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
