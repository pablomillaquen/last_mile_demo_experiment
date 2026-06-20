# Tasks: Research Publication & Experiment Dissemination

**Input**: Design documents from `/specs/005-research-publication/`

**Prerequisites**: plan.md (required), spec.md (required for user stories), research.md, data-model.md, contracts/

**Tests**: No se requieren tests automatizados. La validación es manual mediante revisión de contenido.

**Organization**: Tasks are grouped by user story to enable independent implementation and verification of each story.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to
- Include exact file paths in descriptions

## Path Conventions

- **Publications**: `publications/` at repository root
- **Assets**: `publications/assets/` with subdirectories per type
- **Design docs**: `specs/005-research-publication/`

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Create directory structure and initialize the publications framework

- [X] T001 Create `publications/` directory with subdirectories: `assets/maps/`, `assets/screenshots/`, `assets/diagrams/`, `assets/tables/`
- [X] T002 Create `publications/assets/index.md` with catalog table structure per data-model.md
- [X] T003 [P] Copy existing evaluation data into the research context: evaluate baseline metrics from evaluations 2–7, compile parameter variations (threshold, ratio, seed), and extract route-level metrics

**Checkpoint**: Directory structure ready and baseline data compiled

---

## Phase 2: Research Infrastructure (Shared)

**Purpose**: Crear registros formales de conocimiento acumulativo del proyecto (hallazgos, preguntas, decisiones, contribuciones).

**Independent Test**: Los 4 archivos existen bajo `research/` con contenido inicial.

### Implementation

- [X] T004 [P] Crear `research/hallazgos.md` con hallazgos formales H001–H006: cada uno con enunciado, evidencia, impacto y preguntas relacionadas (basado en resultados de SPEC-003/004)
- [X] T005 [P] Crear `research/preguntas-investigacion.md` con PI-001–PI-005: cada una con estado, hallazgos relacionados y fase objetivo
- [X] T006 [P] Crear `research/decisiones.md` con decisiones D001–D005: cada una con contexto, razón, impacto y fecha
- [X] T007 [P] Crear `research/contribuciones.md` con contribuciones C001–C003: cada una con descripción y evidencia

**Checkpoint**: `ls research/` retorna 4 archivos. Cada archivo tiene contenido inicial con IDs secuenciales.

---

## Phase 3: Documento Técnico de Investigación (H3 - Priority: P1) 🎯 MVP

**Goal**: Crear documento técnico de 13 secciones que constituye la fuente primaria de evidencia.

**Independent Test**: `grep "^##" publications/documento-tecnico.md` retorna exactamente 13 secciones.

### Implementation

- [X] T008 [US3] Redactar sección de Introducción en `publications/documento-tecnico.md`: contexto del problema de última milla, relevancia industrial, vacío identificado
- [X] T009 [US3] Redactar sección de Problema de Investigación en `publications/documento-tecnico.md`: formulación clara y acotada del problema
- [X] T010 [US3] Redactar sección de Preguntas de Investigación en `publications/documento-tecnico.md`: lista de PI activas, referenciando `research/preguntas-investigacion.md`
- [X] T011 [US3] Redactar sección de Hipótesis en `publications/documento-tecnico.md`: enunciado formal de lo que se busca demostrar
- [X] T012 [US3] Redactar sección de Metodología en `publications/documento-tecnico.md`: proceso de evaluación, variables controladas y medidas
- [X] T013 [US3] Redactar sección de Descripción de Métricas en `publications/documento-tecnico.md`: cada métrica con definición, fórmula e interpretación, agrupadas por taxonomía (operacionales, balance, calidad, utilización)
- [X] T014 [P] [US3] Redactar sección de Diseño Experimental en `publications/documento-tecnico.md`: experimentos realizados, parámetros (threshold, ratio, seed), baseline
- [X] T015 [US3] Redactar sección de Resultados en `publications/documento-tecnico.md`: presentación objetiva de datos (tablas, gráficos). Sin interpretación. Separada explícitamente de Análisis.
- [X] T016 [US3] Redactar sección de Análisis de Resultados en `publications/documento-tecnico.md`: interpretaciones, tendencias, patrones, anomalías observadas. Separada explícitamente de Resultados.
- [X] T017 [US3] Redactar sección de Hallazgos Formales en `publications/documento-tecnico.md`: H001–H006 con enunciado, evidencia e impacto. Referenciar `research/hallazgos.md`.
- [X] T018 [US3] Redactar sección de Limitaciones en `publications/documento-tecnico.md`: incluir subsecciones de amenazas a validez interna (datos sintéticos) y validez externa (generalización a otros contextos)
- [X] T019 [US3] Redactar sección de Conclusiones en `publications/documento-tecnico.md`: síntesis de hallazgos y contribuciones del proyecto (C001–C003)
- [X] T020 [US3] Redactar sección de Trabajo Futuro en `publications/documento-tecnico.md`: líneas abiertas, prioridades sugeridas
- [X] T021 [US3] Agregar tabla de acrónimos, referencias a SPEC-003 y SPEC-004, y enlaces a `research/` en `publications/documento-tecnico.md`

**Checkpoint**: `grep "^##" publications/documento-tecnico.md` retorna 13 secciones. Documento completo legible.

---

## Phase 4: Resumen Ejecutivo (H6 - Priority: P2)

**Goal**: Crear documento de 1–2 páginas dirigido a reclutadores, gerentes y clientes.

**Independent Test**: `wc -w publications/resumen-ejecutivo.md` ≤ 1000 palabras. Comprensible sin conocimiento previo.

### Implementation

- [X] T022 [US6] Redactar sección de Problema en `publications/resumen-ejecutivo.md`: descripción en no más de 3 párrafos
- [X] T023 [US6] Redactar sección de Hipótesis en `publications/resumen-ejecutivo.md`: enunciado en no más de 2 oraciones
- [X] T024 [US6] Redactar sección de Metodología en `publications/resumen-ejecutivo.md`: resumen en 1 párrafo
- [X] T025 [US6] Redactar sección de Resultados Clave en `publications/resumen-ejecutivo.md`: 3–5 bullet points con métricas cuantitativas (extraídas del documento técnico)
- [X] T026 [US6] Redactar sección de Conclusiones y Próximos Pasos en `publications/resumen-ejecutivo.md`: 2–3 oraciones + 1–2 oraciones respectivamente
- [X] T027 [US6] Revisar consistencia: verificar que todas las métricas del resumen coinciden con el documento técnico (`publications/documento-tecnico.md`)

**Checkpoint**: `wc -w publications/resumen-ejecutivo.md` ≤ 1000 palabras. 6 secciones presentes.

---

## Phase 5: Artículo de Portafolio (H1 - Priority: P3)

**Goal**: Crear artículo narrativo de alto nivel de ≥ 1500 palabras, comprensible para no técnicos.

**Independent Test**: `wc -w publications/articulo-portafolio.md` ≥ 1500 palabras. Sin jargon técnico.

### Implementation

- [X] T028 [P] [US1] Redactar secciones de Contexto Operacional y Motivación en `publications/articulo-portafolio.md`: problema logístico, por qué se inició el proyecto
- [X] T029 [US1] Redactar sección de Metodología General en `publications/articulo-portafolio.md`: enfoque utilizado, legible para no técnicos
- [X] T030 [US1] Redactar sección de Resultados Obtenidos en `publications/articulo-portafolio.md`: métricas clave y hallazgos principales (extraídos del documento técnico)
- [X] T031 [US1] Redactar sección de Aprendizajes en `publications/articulo-portafolio.md`: lecciones aprendidas durante el desarrollo
- [X] T032 [US1] Redactar sección de Próximos Pasos en `publications/articulo-portafolio.md`: visión de futuro y líneas siguientes
- [X] T033 [US1] Verificar extensión mínima de 1500 palabras y ausencia de jargon técnico (API, endpoint, controller, migration, docker)
- [X] T034 [US1] Revisar consistencia: verificar que todos los hallazgos del artículo tienen respaldo en el documento técnico

**Checkpoint**: `wc -w publications/articulo-portafolio.md` ≥ 1500 palabras. Sin terminología de software.

---

## Phase 6: Post LinkedIn (H2 - Priority: P4)

**Goal**: Crear publicación breve de ≤ 3000 caracteres con elemento visual y enlace al artículo.

**Independent Test**: `wc -m publications/linkedin-post.md` ≤ 3000 caracteres.

### Implementation

- [X] T035 [US2] Redactar cuerpo del post en `publications/linkedin-post.md`: máximo 3000 caracteres, tono profesional pero accesible
- [X] T036 [US2] Incluir 3–5 aprendizajes o resultados clave destacados en `publications/linkedin-post.md`
- [X] T037 [US2] Agregar enlace al artículo de portafolio (`publications/articulo-portafolio.md`) y 3–5 hashtags relevantes
- [X] T038 [US2] Seleccionar e incorporar al menos 1 elemento visual representativo desde `publications/assets/`

**Checkpoint**: `wc -m publications/linkedin-post.md` ≤ 3000 caracteres. Enlace y visual incluidos.

---

## Phase 7: Biblioteca de Recursos Visuales (H4 - Priority: P5)

**Goal**: Catalogar y organizar todos los recursos visuales generados durante la investigación.

**Independent Test**: `wc -l publications/index.md` ≥ 10 recursos catalogados con metadatos completos.

### Implementation

- [X] T039 [P] [US4] Catalogar mapas de evaluaciones en `publications/assets/maps/` y registrar en `publications/index.md` con metadatos (nombre, fuente, fecha, descripción)
- [X] T040 [P] [US4] Catalogar capturas de pantalla del sistema en `publications/assets/screenshots/` y registrar en `publications/index.md`
- [X] T041 [P] [US4] Crear diagrama de arquitectura del sistema y almacenar en `publications/assets/diagrams/`
- [X] T042 [US4] Crear tabla comparativa de métricas entre evaluaciones (2–7) y almacenar en `publications/assets/tables/`
- [X] T043 [US4] Completar catálogo en `publications/index.md` con todos los recursos: verificar mínimo 10 entradas con metadatos completos
- [X] T044 [US4] Incluir referencias a reportes PDF en el catálogo desde `experiments/001-baseline-comparison/`

**Checkpoint**: `wc -l publications/index.md` ≥ 10 recursos. Directorios `assets/` poblados.

---

## Phase 8: Narrativa de Conexión (H5 - Priority: P6)

**Goal**: Crear documento que conecte las 7 fases de investigación en una línea temporal coherente.

**Independent Test**: `grep -c "Fase [1-7]" publications/narrativa-conexion.md` = 7.

### Implementation

- [X] T045 [P] [US5] Redactar introducción y visión general en `publications/narrativa-conexion.md`: propósito de la narrativa
- [X] T046 [US5] Documentar Fase 1 (Modelado Operacional - SPEC-001) y Fase 2 (Evaluación - SPEC-002/003) con objetivos, entregables y dependencias
- [X] T047 [US5] Documentar Fase 3 (Experimentación - SPEC-004) con objetivos, entregables y dependencia → Fase 4
- [X] T048 [US5] Documentar Fases 4–7 (Optimización, Data Science, ML, White Paper) como hoja de ruta futura
- [X] T049 [US5] Agregar tabla resumen del estado del proyecto con las 7 fases y su estado (completada/en progreso/pendiente)
- [X] T050 [US5] Documentar preguntas de investigación abiertas y qué fase las abordaría

**Checkpoint**: `grep -c "Fase [1-7]" publications/narrativa-conexion.md` = 7. Tabla resumen presente.

---

## Phase 9: Polish & Cross-Cutting Concerns

**Purpose**: Revisiones finales y verificación de consistencia multi-activo

- [X] T051 Verificar que `publications/index.md` enlace correctamente todos los activos de divulgación
- [X] T052 Validar jerarquía: verificar que resumen ejecutivo y artículo no contengan afirmaciones sin respaldo en el documento técnico
- [X] T053 Ejecutar escenarios de validación de `quickstart.md` para confirmar todos los criterios de aceptación
- [X] T054 [P] Actualizar `README.md` con enlaces a los activos de divulgación en `publications/`
- [X] T055 Verificar que todos los documentos estén en formato Markdown dentro del repositorio

**Checkpoint**: Todos los criterios de aceptación del spec (CA1–CA9) verificados.

---

## Dependencies & Execution Order

### Phase Dependencies

| Phase | Depends On | Blocks |
|-------|------------|--------|
| Phase 1 — Setup | Nothing | All |
| Phase 2 — Research Infrastructure | Phase 1 | — |
| Phase 3 — Documento Técnico (H3) | Phase 1 | Phases 4, 5 |
| Phase 4 — Resumen Ejecutivo (H6) | Phase 3 | — |
| Phase 5 — Artículo Portafolio (H1) | Phase 3 | Phase 6 |
| Phase 6 — Post LinkedIn (H2) | Phase 5 | — |
| Phase 7 — Biblioteca Visual (H4) | Phase 1 | — |
| Phase 8 — Narrativa Conexión (H5) | Phase 1 | — |
| Phase 9 — Polish | All Phases | — |

### User Story Dependencies

- **H3 (Documento Técnico)**: Fundación — todos los demás activos derivados dependen de él
- **H6 (Resumen Ejecutivo)**: Depende de H3 (extrae métricas y conclusiones)
- **H1 (Artículo de Portafolio)**: Depende de H3 (deriva hallazgos)
- **H2 (Post LinkedIn)**: Depende de H1 (deriva contenido y enlaza al artículo)
- **H4 (Biblioteca Visual)**: Sin dependencias — puede ejecutarse en paralelo con H3
- **H5 (Narrativa de Conexión)**: Sin dependencias — puede ejecutarse en paralelo con H3 y H4

### Within Each User Story

- Secciones críticas (Introducción, Problema, Hipótesis) antes que secciones derivadas (Resultados, Análisis)
- Draft completo antes de revisión de consistencia
- Story complete before moving to next priority

### Parallel Opportunities

- **Phase 1**: T003 puede ejecutarse en paralelo con T001 y T002
- **Phase 2**: T004, T005, T006, T007 pueden ejecutarse en paralelo (diferentes archivos)
- **Phase 3**: T014 puede ejecutarse en paralelo con T013 (diferentes secciones del documento técnico)
- **Phase 7 (H4) y Phase 8 (H5)**: Pueden ejecutarse en paralelo con Phase 3 (H3) — no tienen dependencias
- **Phase 4 (H6) y Phase 5 (H1)**: Pueden ejecutarse en paralelo entre sí después de completar H3
- Tareas marcadas [P] dentro de una misma fase pueden ejecutarse en paralelo

---

## Parallel Example: Phase 3 (H3 - Documento Técnico)

```bash
# T013 + T014 son independientes (diferentes secciones):
Task: "Redactar Descripción de Métricas en publications/documento-tecnico.md"
Task: "Redactar Diseño Experimental en publications/documento-tecnico.md"
```

## Parallel Example: Phases 3, 7, 8

```bash
# H3, H4 y H5 no tienen dependencias entre sí:
Task: "Redactar Documento Técnico (Phase 3)"
Task: "Catalogar Biblioteca Visual (Phase 7)"
Task: "Redactar Narrativa de Conexión (Phase 8)"
```

---

## Implementation Strategy

### MVP First (Phase 3 Only)

1. Complete Phase 1: Setup
2. Complete Phase 2: Research Infrastructure
3. Complete Phase 3: Documento Técnico (H3)
4. **STOP and VALIDATE**: 13 secciones completas, referencias a SPEC-003/004 incluidas
5. Este es el entregable mínimo — el resto son derivados

### Incremental Delivery

1. **Setup** → Directorio `publications/` listo
2. **Research Infrastructure** → `research/` con hallazgos, preguntas, decisiones, contribuciones
3. **Documento Técnico** → Base de evidencia lista (MVP)
4. **Biblioteca Visual + Narrativa Conexión** → En paralelo, sin dependencias
5. **Resumen Ejecutivo + Artículo Portafolio** → En paralelo, derivados del técnico
6. **Post LinkedIn** → Derivado del artículo
7. **Polish** → Consistencia multi-activo validada

### Parallel Team Strategy

Con un solo desarrollador:

1. Setup → Research Infrastructure → Documento Técnico (secuencial, base de todo)
2. Biblioteca Visual + Narrativa de Conexión (paralelo con Documento Técnico si se desea)
3. Resumen Ejecutivo + Artículo (después del técnico, en paralelo)
4. LinkedIn (después del artículo)
5. Polish final

---

## Notes

- [P] tasks = different files, no dependencies
- [Story] label maps task to specific user story for traceability
- Cada user story debe ser completable y verificable independientemente
- No se requieren tests automatizados — la validación es revisión de contenido
- Todos los activos deben mantener consistencia con el documento técnico fuente (RF5.5)
- Commit después de cada fase o grupo lógico
- Detenerse en cualquier checkpoint para validar independientemente
