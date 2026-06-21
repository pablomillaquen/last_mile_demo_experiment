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
| H007 | Hallazgo | `research/hallazgos.md` | SPEC-006, Exp002 (M001 = 1.62) |
| H008 | Hallazgo | `research/hallazgos.md` | SPEC-006, Exp002 (M006, Ruta D crítica 2.49) |
| H009 | Hallazgo | `research/hallazgos.md` | SPEC-006, Exp002 (0% rutas con TDI normal) |
| H010 | Hallazgo | `research/hallazgos.md` | SPEC-006, Exp002 (modo vial 330x más lento) |
| V001 | Validación | `research/hallazgos.md` | SPEC-006, Exp002 (H001 persiste) |
| V002 | Validación | `research/hallazgos.md` | SPEC-006, Exp002 (H002 persiste) |
| V003 | Validación | `research/hallazgos.md` | SPEC-006, Exp002 (H003 persiste) |
| V004 | Validación | `research/hallazgos.md` | SPEC-006, Exp002 (H004 persiste) |
| V005 | Validación | `research/hallazgos.md` | SPEC-006, Exp002 (H005 persiste) |
| V006 | Validación | `research/hallazgos.md` | SPEC-006, Exp002 (H006 persiste) |
| PI-001 | Pregunta | `research/preguntas-investigacion.md` | Problema inicial, H001, H005 |
| PI-002 | Pregunta | `research/preguntas-investigacion.md` | Problema inicial, H004 |
| PI-003 | Pregunta | `research/preguntas-investigacion.md` | Problema inicial, H003 |
| PI-004 | Pregunta | `research/preguntas-investigacion.md` | Problema inicial, H004 |
| PI-005 | Pregunta | `research/preguntas-investigacion.md` | Problema inicial, H006 |
| PI-006 | Pregunta | `research/preguntas-investigacion.md` | SPEC-006, M001 |
| PI-007 | Pregunta | `research/preguntas-investigacion.md` | SPEC-006, M002 |
| PI-008 | Pregunta | `research/preguntas-investigacion.md` | SPEC-006, M003 |
| PI-009 | Pregunta | `research/preguntas-investigacion.md` | SPEC-006, M004 |
| PI-010 | Pregunta | `research/preguntas-investigacion.md` | SPEC-006, V001–V006, M005 |
| PI-011 | Pregunta | `research/preguntas-investigacion.md` | SPEC-006, M006, H008 |
| PI-012 | Pregunta | `research/preguntas-investigacion.md` | SPEC-006, RNF2, execution_time_sec |
| PI-013 | Pregunta | `research/preguntas-investigacion.md` | SPEC-006, M002, investigación futura |
| PI-014 | Pregunta | `research/preguntas-investigacion.md` | SPEC-006, D006, investigación futura |
| D001 | Decisión | `research/decisiones.md` | SPEC-001, Diseño metodológico |
| D002 | Decisión | `research/decisiones.md` | SPEC-002, Motor de evaluación |
| D003 | Decisión | `research/decisiones.md` | SPEC-004, ExperimentRepository |
| D004 | Decisión | `research/decisiones.md` | SPEC-004, data-model.md |
| D005 | Decisión | `research/decisiones.md` | SPEC-005, contracts/documento-tecnico.md |
| D006 | Decisión | `research/decisiones.md` | SPEC-006 — Gran Valparaíso coverage |
| D007 | Decisión | `research/decisiones.md` | SPEC-006 — DistanceService Strategy Pattern |
| D008 | Decisión | `research/decisiones.md` | SPEC-006 — parameters_hash linking |
| D009 | Decisión | `research/decisiones.md` | SPEC-006 — Preservar artefactos experimentales (BUG-001) |
| D010 | Decisión | `research/decisiones.md` | SPEC-007 — Versionado acumulativo de publicaciones |
| BUG-001 | Bug | `research/bugs.md` | Exp001 modificado por `experiments:sync` — inmutable agregado |
| BUG-002 | Bug | `research/bugs.md` | Mapa sin geometría vial OSRM — RESUELTO (SPEC-007) |
| BUG-003 | Bug | `research/bugs.md` | Falta selector visual geodésico/vial — RESUELTO (SPEC-007) |
| C001 | Contribución | `research/contribuciones.md` | SPEC-001, SPEC-002, SPEC-003, SPEC-004 |
| C002 | Contribución | `research/contribuciones.md` | SPEC-003, SPEC-004 |
| C003 | Contribución | `research/contribuciones.md` | SPEC-004, data-model.md |
| C004 | Contribución | `research/contribuciones.md` | SPEC-006 — Revalidation framework (categoría V) |
| C005 | Contribución | `research/contribuciones.md` | SPEC-006 — M006 Territorial Distortion Index |

## Matriz Hallazgo → Evidencia Específica

| Hallazgo | Evidencia |
|----------|-----------|
| H001 | SPEC-003: Evaluaciones 2,4,6 muestran que la ruta más corta (B) no es la más equilibrada |
| H002 | SPEC-004: Exp001 — rutas con punto de inicio distinto varían hasta 15% en distancia total |
| H003 | SPEC-004: Exp001 — Evaluaciones 2–7 comparten métricas de ruta pese a variar threshold/ratio/seed |
| H004 | SPEC-004: Exp001 — Eval 5 (ratio 1.5) detecta 4 anomalías; Eval 2 (ratio 2) detecta 10 |
| H005 | SPEC-004: Exp001 — Rutas A (0.3 km radio) vs E (15.3 km radio) en misma operación |
| H006 | SPEC-004: Exp001 — Anomalías: 10 entregas < 1 km de bodega, todas en sector B |
| H007 | SPEC-006: Exp002 — M001 = 1.6248, distancias viales 62.5% mayores que geodésicas |
| H008 | SPEC-006: Exp002 — Rutas que cruzan bahía de Valparaíso (Ruta D) tienen TDI crítico > 2.0 |
| H009 | SPEC-006: Exp002 — 0% de rutas con TDI normal (≤1.2); 60% alta o crítica |
| H010 | SPEC-006: Exp002 — Modo vial ~82s vs geodésico ~0.25s (330x más lento) |
| H011 | SPEC-006A: evaluation.json 84KB→132KB (geo route_legs) → 2.3MB (vial route_legs, +2640%) |
| H012 | SPEC-006A: Eval #18 (geodésico 339 km) vs Eval #19 (vial 523 km), +54.3%. Ruta D 2.00×. |

## Matriz Pregunta → Hallazgos → Contribución

| Pregunta | Hallazgos | Contribución |
|----------|-----------|-------------|
| PI-001 | H001, H005 | C001, C003 |
| PI-002 | H004 | C002 |
| PI-003 | H003 | C003 |
| PI-004 | H004 | C002 |
| PI-005 | H006 | C001, C002 |
| PI-006 | H007, M001 | C004 |
| PI-007 | H007, H008, M002 | C005 |
| PI-008 | H007, M003 | C004 |
| PI-009 | M004 | C004 |
| PI-010 | V001–V006 | C004 |
| PI-011 | H008, H009, M006 | C005 |
| PI-012 | H010 | C004 |
| PI-013 | H012 | C005 |
| PI-014 | pendiente | C004 |
| PI-015 | H011 | C005 |
| PI-016 | H012 | — |

## Estado de IDs

| Tipo | Total | Pendientes |
|------|-------|------------|
| Hallazgos | 12 (H001–H012) | — |
| Preguntas | 16 (PI-001–PI-016) | PI-013, PI-014, PI-016 investigación futura |
| Decisiones | 10 (D001–D010) | — |
| Contribuciones | 5 (C001–C005) | — |
| Validaciones | 6 (V001–V006) | — |
| Bugs | 3 (BUG-001—BUG-003) | Todos resueltos |
