# Tasks: Experiment Reporting & Documentation System

**Input**: Design documents from `specs/004-experiment-reporting/`

**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/, quickstart.md

**Tests**: No se incluyen tareas de tests (no solicitados en spec).

**Organization**: Tasks grouped by functional groups (A–F) for independent implementation,
siguiendo la cadena: Evaluación → Reporte PDF → Experimento → Explorador → Artefactos.

## Format: `[ID] [P?] [Group] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Group]**: Which functional group this task belongs to (A–F)
- Include exact file paths in descriptions

## Path Conventions

- **Backend**: `backend/app/`, `backend/database/`, `backend/routes/`, `backend/resources/`
- **Frontend**: `frontend/src/`

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Verificar entorno existente e instalar dependencias nuevas.

- [X] T001 Verificar que los contenedores Docker están activos: `docker compose ps`
- [X] T002 Verificar que la API responde: `curl http://localhost:8000/api/evaluations`
- [X] T003 [P] Instalar dependencia `barryvdh/laravel-dompdf` en backend: `docker compose exec backend composer require barryvdh/laravel-dompdf`
- [X] T004 [P] Instalar dependencias `react-markdown` y `remark-gfm` en frontend: `docker compose exec frontend npm install react-markdown remark-gfm`

**Checkpoint**: Docker activo, dompdf disponible en backend, react-markdown en frontend.

---

## Phase 2: Group A — Infraestructura de Experimentos

**Purpose**: Crear la base de datos, modelo y comando sync para el repositorio de experimentos.
Fuente de verdad: filesystem. DB es caché de solo lectura.

**⚠️ CRITICAL**: Groups B–F dependen de que los experimentos estén en DB y accesibles vía API.

- [X] T005 [P] [A] Crear migración `create_experiments_table` en `backend/database/migrations/2026_06_19_000002_create_experiments_table.php` con columnas:
  - `id` (bigIncrements)
  - `identifier` (string 100, unique)
  - `name` (string 255)
  - `description` (text, nullable)
  - `objective` (text)
  - `hypothesis` (text, nullable)
  - `baseline_evaluation_id` (foreignId, nullable, constrained)
  - `evaluation_ids` (jsonb, default '[]')
  - `author` (string 255, nullable)
  - `timestamps`
  Sin cambios en la tabla `evaluations` existente (SPEC-003).

- [X] T006 [P] [A] Crear modelo `Experiment` en `backend/app/Models/Experiment.php` con:
  - `$fillable`: identifier, name, description, objective, hypothesis, baseline_evaluation_id, evaluation_ids, author
  - `$casts`: evaluation_ids (array), baseline_evaluation_id (integer)
  - Método `getEvaluations(): \Illuminate\Database\Eloquent\Collection` — `Evaluation::whereIn('id', $this->evaluation_ids ?? [])->get()`. **No es una relación Eloquent** (no hay FK en evaluations). Es un accessor que resuelve las evaluaciones por ID desde el JSON array. No usar `with()`, `load()`, ni `hasMany()`.
  - Relación `baselineEvaluation(): BelongsTo` → Evaluation (esta SÍ es real, tiene FK `baseline_evaluation_id`)

- [X] T007 [P] [A] Crear `ExperimentRepository` en `backend/app/Repositories/ExperimentRepository.php` con métodos:
  - `getReportContent(Experiment $experiment): ?string` — lee `report.md` del directorio del experimento
  - `getAssets(Experiment $experiment): array` — lista archivos en `assets/`
  - `getAssetPath(Experiment $experiment, string $filename): ?string` — retorna path completo o null si el archivo no existe o contiene `..` (path traversal). Sanitizar con `basename()`.
  - `getReportPdfPath(Experiment $experiment): ?string` — retorna path de `report.pdf` o null
  - `getPath(Experiment $experiment): string` — retorna `experiments/<identifier>/`
  - Todos los paths usan `base_path('experiments')` como raíz (ej: `base_path('experiments/' . $experiment->identifier . '/report.md')`). **No usar** `Storage::disk('local')` porque los experimentos viven en la raíz del proyecto, no en `storage/app`.

- [X] T008 [A] Crear comando `experiments:sync` en `backend/app/Console/Commands/SyncExperiments.php`:
  - Scan `experiments/` directory for entries matching `NNN-*` pattern
  - For each, read `experiment.json`
  - Create or update `experiments` DB row (matched by `identifier`)
  - Delete DB rows whose identifier no longer has a matching directory
  - Validate `evaluation_ids` — if an evaluation ID doesn't exist, log a warning
  - Output summary: `Created: N, Updated: N, Deleted: N, Warnings: N`

**Checkpoint**: `php artisan experiments:sync` ejecuta sin errores, tabla experiments poblada.

---

## Phase 3: Group A+ — API de Experimentos

**Purpose**: Endpoints para listar, ver detalle, servir reportes y assets de experimentos.

**Depends on**: Phase 2 (model + repository)

- [X] T009 [P] [A] Crear `ExperimentController` en `backend/app/Http/Controllers/ExperimentController.php` con:
  - `GET /api/experiments` — lista todos los experimentos con `evaluations_count`
  - `GET /api/experiments/{id}` — detalle del experimento. Carga evaluaciones explícitamente con `Evaluation::whereIn('id', $experiment->evaluation_ids)->get()` y las pasa al Resource.
  - `GET /api/experiments/{id}/report` — sirve `report.md` (Content-Type: text/markdown)
  - `GET /api/experiments/{id}/report.pdf` — sirve `report.pdf` si existe (Content-Type: application/pdf). **Nota**: `report.pdf` es opcional y se crea manualmente. No hay generación automática de PDF de experimento en este spec.
  - `GET /api/experiments/{id}/assets/{filename}` — sirve archivo del directorio `assets/`. **Validación**: sanitizar filename con `basename()`, bloquear `..`, verificar extensión permitida (png, json, csv, pdf, md). Usar `ExperimentRepository::getAssetPath()` que retorna null si hay path traversal.

- [X] T010 [P] [A] Crear `ExperimentResource` en `backend/app/Http/Resources/ExperimentResource.php` que estructura la respuesta JSON:
  - Incluye id, identifier, name, description, objective, hypothesis, baseline_evaluation_id, evaluation_ids, author, created_at, updated_at
  - Incluye `evaluations_count`
  - Incluye `report_url`, `report_pdf_url`
  - Método fluido `resolvedEvaluations(array $evaluations)` para inyectar evaluaciones:
    ```php
    (new ExperimentResource($experiment))->resolvedEvaluations($evaluations->all())
    ```
  - Lee evaluaciones de `$this->resolvedEvaluations` en toArray()
  - No usar `$this->whenLoaded('evaluations')` porque no es una relación Eloquent real.

- [X] T011 [A] Registrar rutas en `backend/routes/api.php`:
  ```php
  Route::get('/experiments', [ExperimentController::class, 'index']);
  Route::get('/experiments/{id}', [ExperimentController::class, 'show'])->whereNumber('id');
  Route::get('/experiments/{id}/report', [ExperimentController::class, 'report'])->whereNumber('id');
  Route::get('/experiments/{id}/report.pdf', [ExperimentController::class, 'reportPdf'])->whereNumber('id');
  Route::get('/experiments/{id}/assets/{filename}', [ExperimentController::class, 'asset'])->whereNumber('id');
  ```

- [X] T012 [A] Modificar `GET /api/evaluations/{id}` en `EvaluationController@show` para incluir experiment asociado:
  - Resolver experiment via `Experiment::whereJsonContains('evaluation_ids', $id)->first()`
  - Incluir `experiment: { id, identifier, name }` en la respuesta si existe
  - **Nota**: `whereJsonContains` funciona en PostgreSQL y MySQL 8+. Si se cambia a SQLite en el futuro, encapsular esta búsqueda en `ExperimentRepository` con una implementación específica del motor.

**Checkpoint**: `curl http://localhost:8000/api/experiments` retorna lista. Trazabilidad inversa funciona.

---

## Phase 4: Group B — Explorador de Experimentos (Frontend)

**Purpose**: Interfaz web para navegar experimentos, ver informes markdown y descargar artefactos.

**Depends on**: Phase 3 (API endpoints exist)

- [X] T013 [P] [B] Extender `frontend/src/lib/api.ts` con tipos y funciones para experimentos:
  ```typescript
  export interface Experiment { ... }
  export const experimentsApi = {
    list: () => request<{ data: Experiment[] }>('/experiments'),
    get: (id: number) => request<Experiment>(`/experiments/${id}`),
    report: (id: number) => `${API_BASE}/experiments/${id}/report`,
    reportPdf: (id: number) => `${API_BASE}/experiments/${id}/report.pdf`,
    asset: (id: number, filename: string) => `${API_BASE}/experiments/${id}/assets/${filename}`,
  };
  ```

- [X] T014 [P] [B] Crear `ExperimentCard` en `frontend/src/components/ExperimentCard.tsx`:
  - Props: experiment (Experiment), onClick
  - Muestra: identifier, name, objective, evaluation count, date
  - Diseño: tarjeta con borde, sombra, mismo estilo que las tarjetas existentes de evaluations

- [X] T015 [B] Crear página de listado de experimentos en `frontend/src/app/experiments/page.tsx`:
  - Client component, fetch `experimentsApi.list()` on mount
  - Grid de `ExperimentCard`
  - Estado loading/empty/error

- [X] T016 [B] Crear página de detalle de experimento en `frontend/src/app/experiments/[id]/page.tsx`:
  - Client component
  - Fetch experiment detail + report markdown
  - Render report.md con `<ReactMarkdown remarkPlugins={[remarkGfm]}>`
  - Lista de evaluaciones asociadas con enlaces a `/evaluations/[id]`
  - Botón "Descargar PDF" (report.pdf)
  - Sección de assets descargables (si existen)

- [X] T017 [B] Agregar enlace de navegación a "Experimentos" en el layout del frontend

**Checkpoint**: `http://localhost:3000/experiments` lista experimentos, ver detalle con markdown renderizado.

---

## Phase 5: Group C — Reporte PDF de Evaluaciones

**Purpose**: Generar PDF descargable por evaluación con métricas, tablas, mapas y metadata de reproducibilidad.

**Depends on**: Phase 1 (dompdf installed)

- [X] T018 [P] [C] Crear `PdfReportService` en `backend/app/Services/PdfReportService.php` con:
  - Método `generate(int $evaluationId): string` — genera PDF y retorna la ruta del archivo
  - Estructura del PDF según data-model.md §6:
    1. Portada: "Reporte de Evaluación #{id}", fecha, algoritmo + versión, dataset
    2. Resumen Ejecutivo: métricas globales (distancia promedio, cobertura, anomalías, penalidad)
    3. Parámetros: threshold, ratio, random seed, ubicación bodega
    4. Ranking de Rutas: tabla ordenada por cercanía a bodega
    5. Métricas por Ruta: tabla completa con todas las columnas
    6. Anomalías Detectadas: tabla (si existen)
    7. Mapas: overview.png + anomalies.png incrustados como `<img src="...">`
    8. Footer en cada página con metadata de reproducibilidad (spec_version, evaluation_id, algorithm, etc.)
  - Cache: si el PDF ya existe en `storage/app/private/evaluations/<timestamp>/report.pdf`, servirlo sin regenerar
  - CA-06: generación en menos de 10s

- [X] T019 [C] Agregar endpoint `GET /api/evaluations/{id}/pdf` en `EvaluationController`:
  - Invoca `PdfReportService::generate($id)`
  - Retorna el archivo PDF (Content-Type: application/pdf)
  - Validar que la evaluación existe

- [X] T020 [C] Registrar ruta PDF en `backend/routes/api.php`:
  ```php
  Route::get('/evaluations/{id}/pdf', [EvaluationController::class, 'pdf'])->whereNumber('id');
  ```

- [X] T021 [C] Agregar botón "Descargar PDF" en `frontend/src/app/evaluations/[id]/page.tsx`:
  - Componente inline o `PdfDownloadButton` simple
  - Link a `${API_BASE}/evaluations/${evaluation.id}/pdf` (el endpoint definido en T020, no el de files existente)
  - Estilo: botón primario, mismo diseño que los enlaces de descarga existentes

**Checkpoint**: `GET /api/evaluations/{id}/pdf` descarga PDF completo con mapas y tablas.

---

## Phase 6: Group D — Documentación

**Purpose**: Guía de interpretación de métricas y documentación de pantallas.

**Depends on**: Nothing (pure content, no code)

- [X] T022 [P] [D] Crear guía de interpretación en `backend/resources/docs/guia-de-metricas.md`:
  - Por cada métrica del sistema (15 métricas según spec §7.1):
    - Nombre, Definición, Fórmula (cuando aplique), Interpretación, Ejemplos prácticos, Valores de referencia o criterios de interpretación
  - Métricas a documentar:
    1. Entregas por ruta
    2. Distancia mínima a bodega
    3. Distancia máxima a bodega
    4. Distancia promedio a bodega
    5. Centroide del cluster
    6. Distancia centroide-bodega
    7. Radio del cluster
    8. Distancia promedio al centroide (compactación)
    9. Distancia estimada de ruta
    10. Cobertura territorial
    11. Desviación estándar de distancias
    12. Balance Index (CV)
    13. Inter Cluster Distance
    14. Operational Penalty
    15. Anomalías
  - CA-01: cualquier métrica comprensible en < 30 min
  - CA-02: al menos un ejemplo práctico por métrica
  - CA-03: criterios de interpretación cuando sea posible

- [X] T023 [P] [D] Crear documentación de pantalla de evaluaciones (lista) en `backend/resources/docs/pantallas/evaluaciones.md`:
  - Explica: qué representa cada fila, indicadores en tarjetas resumen, cómo comparar visualmente, cómo identificar resultados relevantes, cómo ejecutar nueva evaluación

- [X] T024 [P] [D] Crear documentación de pantalla de detalle de evaluación en `backend/resources/docs/pantallas/detalle-evaluacion.md`:
  - Explica: resumen ejecutivo, ranking de rutas, tabla de métricas por ruta, anomalías detectadas, mapas generados, archivos exportados

- [X] T025 [P] [D] Agregar endpoint `GET /api/docs/{path}` en `DocsController` para servir archivos markdown desde `backend/resources/docs/`:
  - Validar path: solo letras, números, guiones, slashes. Bloquear `..`.
  - Resolver: `backend/resources/docs/{path}.md`
  - Servir con Content-Type: text/markdown
  - Rutas: `/api/docs/guia-de-metricas`, `/api/docs/pantallas/evaluaciones`, `/api/docs/pantallas/detalle-evaluacion`
  - Registrar en `routes/api.php`

- [X] T026 [D] Crear página de guía de métricas en `frontend/src/app/docs/guias/page.tsx`:
  - Client component, fetch `/api/docs/guia-de-metricas`
  - Renderiza con `<ReactMarkdown remarkPlugins={[remarkGfm]}>`
  - Sidebar de navegación por métrica (opcional)

- [X] T027 [D] Crear página de documentación de pantallas en `frontend/src/app/docs/pantallas/page.tsx`:
  - Client component, fetch `/api/docs/pantallas/evaluaciones` (dos requests o concatenar)
  - Renderiza con `<ReactMarkdown remarkPlugins={[remarkGfm]}>`

- [X] T028 [D] Agregar enlace de navegación a "Documentación" en el layout del frontend

**Checkpoint**: `http://localhost:3000/docs/guias` muestra las 15 métricas documentadas.

---

## Phase 7: Group E — Migración de Experimento Existente

**Purpose**: Migrar `001-baseline-comparison` a la estructura formal.

**Depends on**: Phase 2 (sync command exists), Phase 3 (API works)

- [X] T029 [E] Crear `experiment.json` para `experiments/001-baseline-comparison/experiment.json`:
  ```json
  {
    "identifier": "001-baseline-comparison",
    "name": "Comparación de Evaluaciones (Baseline)",
    "objective": "Establecer baseline cuantitativo antes de optimización.",
    "hypothesis": null,
    "baseline_evaluation_id": 2,
    "evaluation_ids": [2, 3, 4, 5, 6, 7],
    "author": "Sistema"
  }
  ```

- [X] T030 [E] Verificar que `experiments/001-baseline-comparison/report.md` existe con contenido narrativo completo (objetivo, hipótesis, metodología, evaluaciones, resultados, conclusiones)

- [X] T031 [E] Ejecutar `docker compose exec backend php artisan experiments:sync` y verificar:
  - Experimento creado en DB
  - Evaluaciones 2–7 asociadas
  - `curl http://localhost:8000/api/experiments` retorna el experimento

**Checkpoint**: Experimento histórico visible en API y explorador.

---

## Phase 8: Group F — Integración y Polish

**Purpose**: Conectar todos los componentes: trazabilidad, navegación, descargas.

**Depends on**: Phases 3–7

- [X] T032 [F] Agregar enlace a experimento en página de detalle de evaluación (`frontend/src/app/evaluations/[id]/page.tsx`):
  - Si `evaluation.experiment` está presente, mostrar "Pertenece al experimento: [nombre]" con enlace a `/experiments/[id]`
  - Colocar cerca del encabezado

- [X] T033 [F] Agregar botón "Descargar PDF de Evaluación" en página de detalle de evaluación (si no se hizo en T021):
  - Link a `evaluationsApi.fileUrl(evaluation.id, 'report.pdf')`

- [X] T034 [F] Agregar enlace "Ver Evaluación" en página de detalle de experimento para cada evaluación listada

- [X] T035 [F] Verificar todos los escenarios de `quickstart.md`:
  - Escenario 1: Listado de experimentos
  - Escenario 2: Detalle de experimento con markdown renderizado
  - Escenario 3: Trazabilidad inversa evaluación → experimento
  - Escenario 4: PDF generation y descarga
  - Escenario 5: Guía de interpretación

**Checkpoint**: Todos los escenarios de validación funcionan. Navegación completa entre evaluaciones, experimentos y documentación.

---

## Dependencies & Execution Order

### Phase Dependencies

| Phase | Depends On | Blocks |
|-------|------------|--------|
| Phase 1 — Setup | Nothing | All |
| Phase 2 — Group A (DB + model) | Phase 1 | Phases 3, 7 |
| Phase 3 — Group A (API) | Phase 2 | Phases 4, 8 |
| Phase 4 — Group B (Explorer) | Phase 3 | Phase 8 |
| Phase 5 — Group C (PDF) | Phase 1 | Phase 8 |
| Phase 6 — Group D (Docs) | Phase 1 | Phase 8 |
| Phase 7 — Group E (Migration) | Phase 2 + Phase 3 | Phase 8 |
| Phase 8 — Group F (Integration) | Phases 3–7 | — |

### Parallel Opportunities

- **Phase 1**: T003 and T004 can run in parallel (backend + frontend deps)
- **Phase 2**: T005, T006, T007 can run in parallel (migration, model, repository)
- **Phase 3**: T009 and T010 can run in parallel (controller + resource)
- **Phase 5**: T018 (PdfReportService) independent from other code work
- **Phase 6**: T022, T023, T024 can all run in parallel (pure content creation)
- **Phase 7**: T029 is manual metadata creation

### Recommended Execution Order

1. Phase 1: Setup
2. Phase 2 + Phase 6 (parallel — infra + content)
3. Phase 3 + Phase 5 (parallel — API + PDF)
4. Phase 4 (explorer — needs API)
5. Phase 7 (migration — needs API)
6. Phase 8 (integration — needs everything)

---

## Implementation Strategy

### Incremental Delivery

1. Setup → dompdf + react-markdown disponibles
2. Group A → experimentos en DB + API (sin frontend aún)
3. Group D → documentación lista (sin frontend, archivos .md)
4. Group C → PDF descargable desde API
5. Group B → explorador web funcional
6. Group E → experimento histórico migrado
7. Group F → navegación completa entre todos los componentes

### Key Design Decisions

- **Source of truth**: Filesystem (`experiments/<identifier>/`). DB is a read cache.
- **No changes to `evaluations` table**: Relationship via `experiments.evaluation_ids` JSON + `JSON_CONTAINS`
- **PDF caching**: Generated PDFs are stored to disk; subsequent requests serve cached file
- **Explorer read-only**: Experiments created manually via filesystem edits
- **reCAPTCHA**: Not applicable (no forms)

---

## Notes

- [P] tasks = different files, no dependencies
- [Group] label maps task to specific functional group (A–F)
- Each group is independently completable and testable (except F which integrates everything)
- Commit after each phase or logical group
- Stop at any checkpoint to validate independently
- Backend changes require `docker compose exec backend php artisan migrate` to take effect
- Reutilizar `EvaluationResource` de SPEC-003 sin modificarlo
- No cambios en `backend/app/Models/Evaluation.php` (SPEC-003 permanece intacto)
- Para API routes: agregar nuevas rutas después de las existentes, no modificar las de SPEC-003
