# Implementation Plan: Route Measurement — Distancia, Tiempo y Secuencia

**Branch**: `002-route-measurement` | **Date**: 2026-06-12 | **Spec**: [spec.md](spec.md)

**Input**: Feature specification from `/specs/002-route-measurement/spec.md`

## Summary

Añadir capacidad de medición objetiva de rutas: cálculo de distancia (Haversine),
tiempo estimado (distancia / velocidad promedio global), visualización de secuencia
de entregas y líneas de recorrido en el mapa, y configuración de centro de
distribución (bodega). Todo el cómputo vive en el backend (Laravel Services).

## Technical Context

**Language/Version**: PHP 8.2 (Laravel 11), TypeScript (Next.js 14)

**Primary Dependencies**: phpunit, Pest (testing), Leaflet + react-leaflet (mapa)

**Storage**: PostgreSQL 16 — settings table para config global (bodega, velocidad)

**Testing**: PHPUnit (backend), no testing planeado para frontend en esta fase

**Target Platform**: Docker Compose (Linux containers), navegador web moderno

**Project Type**: Web application (backend API + frontend SPA)

**Performance Goals**: Cálculo de Haversine para rutas de hasta 100 paquetes
(< 100ms en backend)

**Constraints**: Sin APIs externas de mapas/direcciones, sin dependencias de
terceros no contempladas en Docker

**Scale/Scope**: 1 bodega, métricas globales, 5-10 rutas simultáneas

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Principio | Estado | Justificación |
|-----------|--------|---------------|
| Evidencia Antes de Solución | ✅ Pasa | Fase solo mide, no optimiza |
| Decisiones Medibles | ✅ Pasa | Distancia, tiempo, secuencia son métricas objetivas |
| Complejidad Incremental | ✅ Pasa | Haversine sin APIs externas; settings planos sin modelo complejo |
| Optimizaciones Comparables | ✅ Pasa | Las métricas creadas aquí son la línea base para comparaciones futuras |
| Visualización como Análisis | ✅ Pasa | Polylines y secuencia revelan patrones visualmente |
| Conocimiento Reutilizable | ✅ Pasa | Métricas exportables, documentables |
| Docker First | ✅ Pasa | Todo dentro de contenedores existentes |

**Resultado**: GATE PASS — sin violaciones constitucionales.

## Project Structure

### Documentation (this feature)

```text
specs/002-route-measurement/
├── plan.md              # This file
├── research.md          # Phase 0 output
├── data-model.md        # Phase 1 output
├── quickstart.md        # Phase 1 output
├── contracts/
│   └── api.md           # API contract
└── tasks.md             # Phase 2 output (not created here)
```

### Source Code (repository root)

```text
backend/
├── app/
│   ├── Models/
│   │   ├── Package.php          # Existente
│   │   ├── Route.php            # Existente
│   │   ├── RoutePackage.php     # Existente
│   │   └── Setting.php          # NUEVO — configuración global
│   ├── Services/
│   │   ├── HaversineService.php       # NUEVO — fórmula Haversine
│   │   └── RouteMetricsService.php    # NUEVO — distancia, tiempo por ruta
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── PackageController.php       # Existente
│   │   │   ├── RouteController.php         # Existente (+ métricas en show)
│   │   │   ├── RouteAssignmentController.php # Existente
│   │   │   ├── MetricsController.php       # Existente (+ distancia/tiempo)
│   │   │   └── SettingsController.php      # NUEVO — CRUD settings
│   │   └── Resources/
│   │       └── RouteResource.php           # NUEVO o extendido
│   └── Providers/
│       └── AppServiceProvider.php  # Existente (+ binding settings)
├── database/
│   ├── migrations/
│   │   ├── [timestamp]_create_settings_table.php  # NUEVO
│   │   └── [timestamp]_add_sequence_index.php     # NUEVO
│   └── seeders/
│       └── SettingsSeeder.php        # NUEVO — valores iniciales
└── routes/
    └── api.php            # Existente (+ /api/settings)

frontend/
├── src/
│   ├── lib/
│   │   └── api.ts         # Existente (+ SettingsApi, RouteMetrics)
│   ├── components/
│   │   ├── MapView.tsx          # Existente (+ polylines, bodega, secuencia)
│   │   ├── MetricsCards.tsx     # Existente (+ distancia, tiempo)
│   │   ├── RouteSequenceTable.tsx # NUEVO — tabla con números de secuencia
│   │   └── SettingsForm.tsx     # NUEVO — config bodega + velocidad
│   └── app/
│       ├── routes/[id]/page.tsx  # Existente (+ secuencia, métricas)
│       └── settings/page.tsx     # NUEVO
```

**Structure Decision**: Option 2 (Web application) — backend Laravel + frontend Next.js,
misma estructura que la Fase 1.

## Complexity Tracking

Sin violaciones constitucionales detectadas.

---

## Phases

### Phase 0: Research

**Tasks**:
1. Documentar decisión Haversine vs alternativas (no necesita investigación externa)
2. Documentar esquema de settings (bodega + velocidad)
3. Documentar cómo se expondrán las métricas de ruta
4. Verificar stack actual: Docker, Laravel, Next.js disponibles

**Output**: `research.md`

### Phase 1: Design & Contracts

**Tasks**:
1. Definir `data-model.md` con entidad Setting y extensión de Route con métricas
2. Definir contrato API en `contracts/api.md` (nuevos endpoints y cambios)
3. Crear `quickstart.md` con escenarios de validación
4. Actualizar `AGENTS.md`

**Outputs**: `data-model.md`, `contracts/api.md`, `quickstart.md`, AGENTS.md actualizado

### Phase 2: Tasks (future — `/speckit.tasks`)

**Nota**: La descomposición en tareas concretas se generará con el comando
`/speckit.tasks`.
