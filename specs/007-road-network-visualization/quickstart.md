# Quickstart: Visualización de Red Vial

**Branch**: `007-road-network-visualization` | **Date**: 2026-06-21 | **Spec**: [spec.md](spec.md), [plan.md](plan.md)

---

## Prerrequisitos

- Docker Compose funcionando (`docker compose up -d`)
- Backend Laravel accesible en `http://localhost:8000`
- Frontend NextJS accesible en `http://localhost:3000`
- OSRM corriendo en `http://localhost:5001`
- EXP-002 con evaluaciones viales existentes (IDs 8-13)

## Escenarios de validación

### Escenario 1: Evaluación vial existente contiene geometría

**Setup**: Asegurar que al menos una evaluación vial (EXP-002, IDs 8-13) tenga `route_legs`.

```bash
# Re-ejecutar evaluación vial que ahora incluye geometría
docker compose exec backend php artisan evaluation:run \
  --seed=42 --distance-mode=vial --store-geometry
```

**Verificación**:
```bash
# route_legs debe existir y contener geometry
docker compose exec backend php artisan evaluation:show --id=<evaluation_id>
```
O directamente:
```bash
curl http://localhost:8000/api/evaluations/<id> | jq '.route_legs | length'
```
Esperado: > 0 legs, cada uno con `geometry` no vacío.

---

### Escenario 2: Evaluación geodésica no contiene geometría vial

**Setup**: EXP-001 (IDs 2-7) o cualquier evaluación con `distance_mode=geodesic`.

**Verificación**:
```bash
curl http://localhost:8000/api/evaluations/<id> | jq '.route_legs'
```
Esperado: `null` o `undefined` (no rompe, solo no tiene el campo).

---

### Escenario 3: Mapa interactivo muestra rutas viales

**Setup**: Frontend corriendo.

**Verificación**:
1. Navegar a `http://localhost:3000/evaluations/<vial-eval-id>`
2. Desplazarse a la sección de mapa
3. Ver el toggle "Geodésico / Vial"
4. Hacer clic en "Vial"
5. Las polilíneas en el mapa deben seguir calles reales (no líneas rectas)

---

### Escenario 4: Fallback a geodésico cuando no hay geometría

**Setup**: Evaluación EXP-001 (sin route_legs).

**Verificación**:
1. Navegar a `http://localhost:3000/evaluations/<exp001-id>`
2. El mapa se renderiza sin error
3. Las rutas se muestran en modo geodésico (líneas rectas)
4. El toggle puede estar deshabilitado o en modo geodésico únicamente

---

### Escenario 5: Toggle sin recarga de datos

**Setup**: Cargar evaluación vial en frontend.

**Verificación**:
1. Estar en modo vial
2. Hacer clic en "Geodésico" → las rutas cambian a líneas rectas al instante
3. Hacer clic en "Vial" → las rutas vuelven a la geometría OSRM
4. No debe haber llamadas de red durante el cambio (solo cambio de renderizado)
5. Repetir 5 veces sin errores (CA2)

---

### Escenario 6: Consistencia visual vs métrica

**Verificación**:
1. Para una ruta en modo vial, comparar la longitud visual de la polyline
2. La distancia debe coincidir con `estimated_route_distance_km` de `route_metrics`
3. Tolerancia: <1% (CA3)

---

### Escenario 7: EXP-001 inmutable

**Verificación**:
1. Las evaluaciones EXP-001 (IDs 2-7) no deben tener `route_legs`
2. No se debe haber modificado `experiment.json` de EXP-001
3. El mapa renderiza EXP-001 sin error

---

### Escenario 8: EXP-002 con ambos modos

**Verificación**:
1. Cargar cualquier evaluación vial de EXP-002
2. Alternar entre geodésico y vial
3. Ambos modos deben verse correctamente sobre el mismo dataset
4. Las métricas en las tarjetas no deben cambiar al alternar modos (RNF2)

---

### Escenario 9: Compatibilidad histórica (regresión BUG-001)

**Objetivo**: Verificar que artefactos restaurados (EXP-001, EXP-002) siguen funcionando tras los cambios de geometría.

**Setup**: Entorno con datos restaurados (evaluaciones IDs 2-13).

**Pasos**:
1. Ejecutar `experiments:sync`:
   ```bash
   docker compose exec backend php artisan experiments:sync
   ```
2. Confirmar en la salida:
   - EXP-001 → `SKIP` (inmutable)
   - EXP-002 → `UPDATE` (sin errores)
3. Navegar a `http://localhost:3000/evaluations/2` (EXP-001 baseline)
   - El mapa debe renderizar en modo geodésico sin errores
   - No debe aparecer el toggle vial (no hay route_legs)
4. Navegar a `http://localhost:3000/evaluations/8` (EXP-002 vial)
   - El mapa debe renderizar con toggle funcional
   - Alternar a modo vial: debe mostrar geometría OSRM
   - Alternar a modo geodésico: debe mostrar líneas rectas

**Esperado**: BUG-001 no reaparece. EXP-001 sigue siendo inmutable. EXP-002 se visualiza correctamente en ambos modos.
