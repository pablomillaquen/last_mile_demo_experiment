# Matriz de Evidencia

*Índice centralizado de todos los IDs del proyecto (hallazgos, preguntas, decisiones, contribuciones) con sus fuentes y evidencia asociada.*

## Índice Central

| ID | Tipo | Fuente | Evidencia |
|----|------|--------|-----------|
| H001 | Hallazgo | `research/hallazgos.md` | SPEC-003, Evaluaciones 2,4,6 |
| H002 | Hallazgo | `research/hallazgos.md` | SPEC-004, Experimento 001 |
| H003 | Hallazgo | `research/hallazgos.md` | SPEC-004, Experimento 001 (Evaluaciones 2–7) |
| H004 | Hallazgo | `research/hallazgos.md` | SPEC-004, Experimento 001 (Evaluación 5) |
| H005 | Hallazgo | `research/hallazgos.md` | SPEC-004, Experimento 001 |
| H006 | Hallazgo | `research/hallazgos.md` | SPEC-004, Experimento 001 (Evaluación 2) |
| PI-001 | Pregunta | `research/preguntas-investigacion.md` | Problema inicial, H001, H005 |
| PI-002 | Pregunta | `research/preguntas-investigacion.md` | Problema inicial, H004 |
| PI-003 | Pregunta | `research/preguntas-investigacion.md` | Problema inicial, H003 |
| PI-004 | Pregunta | `research/preguntas-investigacion.md` | Problema inicial, H004 |
| PI-005 | Pregunta | `research/preguntas-investigacion.md` | Problema inicial, H006 |
| D001 | Decisión | `research/decisiones.md` | SPEC-001, Diseño metodológico |
| D002 | Decisión | `research/decisiones.md` | SPEC-002, Motor de evaluación |
| D003 | Decisión | `research/decisiones.md` | SPEC-004, ExperimentRepository |
| D004 | Decisión | `research/decisiones.md` | SPEC-004, data-model.md |
| D005 | Decisión | `research/decisiones.md` | SPEC-005, contracts/documento-tecnico.md |
| C001 | Contribución | `research/contribuciones.md` | SPEC-001, SPEC-002, SPEC-003, SPEC-004 |
| C002 | Contribución | `research/contribuciones.md` | SPEC-003, SPEC-004 |
| C003 | Contribución | `research/contribuciones.md` | SPEC-004, data-model.md |

## Matriz Hallazgo → Evidencia Específica

| Hallazgo | Evidencia |
|----------|-----------|
| H001 | SPEC-003: Evaluaciones 2,4,6 muestran que la ruta más corta (B) no es la más equilibrada |
| H002 | SPEC-004: Exp001 — rutas con punto de inicio distinto varían hasta 15% en distancia total |
| H003 | SPEC-004: Exp001 — Evaluaciones 2–7 comparten métricas de ruta pese a variar threshold/ratio/seed |
| H004 | SPEC-004: Exp001 — Eval 5 (ratio 1.5) detecta 4 anomalías; Eval 2 (ratio 2) detecta 10 |
| H005 | SPEC-004: Exp001 — Rutas A (0.3 km radio) vs E (15.3 km radio) en misma operación |
| H006 | SPEC-004: Exp001 — Anomalías: 10 entregas < 1 km de bodega, todas en sector B |

## Matriz Pregunta → Hallazgos → Contribución

| Pregunta | Hallazgos | Contribución |
|----------|-----------|-------------|
| PI-001 | H001, H005 | C001, C003 |
| PI-002 | H004 | C002 |
| PI-003 | H003 | C003 |
| PI-004 | H004 | C002 |
| PI-005 | H006 | C001, C002 |

## Estado de IDs

| Tipo | Total | Pendientes |
|------|-------|------------|
| Hallazgos | 6 (H001–H006) | H007+ abiertos para SPEC-006+ |
| Preguntas | 5 (PI-001–PI-005) | PI-006+ abiertas para SPEC-006+ |
| Decisiones | 5 (D001–D005) | D006+ abiertas para SPEC-006+ |
| Contribuciones | 3 (C001–C003) | C004+ abiertas para SPEC-006+ |
