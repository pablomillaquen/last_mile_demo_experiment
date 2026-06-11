# Implementation Plan: Simulación de Operación Logística — Asignación Manual

**Branch**: `001-last-mile-operation` | **Date**: 2026-06-10 | **Spec**: [spec.md](spec.md)

**Input**: Feature specification from `specs/001-last-mile-operation/spec.md`

## Summary

Simulación de operación logística de última milla con registro de paquetes,
creación de rutas, asignación manual y visualización geográfica en mapa para
evidenciar ineficiencias de la asignación manual.

## Technical Context

**Language/Version**: PHP 8.2 (Laravel 11), Node.js 20 + TypeScript (NextJS 14)

**Primary Dependencies**: Laravel (backend), NextJS (frontend),
PostgreSQL (base de datos), Leaflet + OpenStreetMap (visualización)

**Storage**: PostgreSQL 16 via Docker

**Testing**: PHPUnit (backend API), Jest + React Testing Library (frontend)

**Target Platform**: Docker containers (Linux), desarrollo local

**Project Type**: web-service / web-application

**Performance Goals**: Operaciones CRUD < 2s, carga de mapa < 3s

**Constraints**: Todo en Docker, sin geocodificación automática,
sin autenticación en Fase 1

**Scale/Scope**: Operador único, simulación sin concurrencia

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Principio | Estado | Justificación |
|-----------|--------|---------------|
| I. Evidencia Antes de Solución | ✅ Pasa | La simulación establece línea base del problema manual |
| II. Decisiones Medibles | ✅ Pasa | 4 métricas definidas: paquetes, rutas, paquetes/ruta, no asignados |
| III. Complejidad Incremental | ✅ Pasa | Sin optimización, sin vehículos, sin geocodificación |
| IV. Modelado de Escenarios Reales | ✅ Pasa | Entidades Package/Route/RoutePackage reflejan operación real |
| V. Optimizaciones Comparables | ✅ Pasa | Asignación manual será baseline para fases futuras |
| VI. Visualización como Análisis | ✅ Pasa | Mapa interactivo como medio principal de análisis |
| VII. Conocimiento Reutilizable | ✅ Pasa | Documentación técnica servirá para artículos de blog |
| VIII. Docker First | ✅ Pasa | Todo el stack se ejecuta en contenedores |

Sin violaciones. No se requiere Complexity Tracking.

## Project Structure

### Documentation (this feature)

```text
specs/001-last-mile-operation/
├── plan.md              # This file
├── research.md          # Phase 0 output
├── spec.md              # Feature specification
├── data-model.md        # Phase 1 output
├── quickstart.md        # Phase 1 output
├── contracts/
│   └── api.md           # API contract
└── tasks.md             # Phase 2 output (/speckit.tasks)
```

### Source Code

```text
backend/                        # Laravel API
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       ├── PackageController.php
│   │       ├── RouteController.php
│   │       ├── RouteAssignmentController.php
│   │       └── MetricsController.php
│   └── Models/
│       ├── Package.php
│       ├── Route.php
│       └── RoutePackage.php
├── database/
│   └── migrations/
│       ├── create_packages_table.php
│       ├── create_routes_table.php
│       └── create_route_packages_table.php
├── routes/
│   └── api.php
└── Dockerfile

frontend/                       # NextJS
├── src/
│   ├── lib/
│   │   └── api.ts
│   ├── app/
│   │   ├── packages/
│   │   │   ├── page.tsx
│   │   │   └── create/page.tsx
│   │   ├── routes/
│   │   │   ├── page.tsx
│   │   │   ├── create/page.tsx
│   │   │   └── [id]/page.tsx
│   │   ├── map/page.tsx
│   │   └── dashboard/page.tsx
│   └── components/
│       ├── PackageForm.tsx
│       ├── PackageTable.tsx
│       ├── RouteForm.tsx
│       ├── RouteTable.tsx
│       ├── AssignmentPanel.tsx
│       ├── MapView.tsx
│       └── MetricsCards.tsx
├── Dockerfile
└── package.json

docker-compose.yml              # Orquestación de servicios
```

**Structure Decision**: Web application con frontend (NextJS) y backend (Laravel API)
separados. Cada uno con su propio Dockerfile. Orquestados via docker-compose.yml.
Corresponde a la Opción 2 del template y a la architecture.md del proyecto.

## Complexity Tracking

> Sin violaciones constitucionales. No se requiere.
