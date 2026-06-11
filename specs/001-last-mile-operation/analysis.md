# Specification Analysis: Simulación de Operación Logística

**Branch**: `001-last-mile-operation`
**Date**: 2026-06-11
**Analysis Type**: `/speckit.analyze` — pre-implementation consistency check

---

## What Was Done

A cross-artifact analysis of `spec.md`, `plan.md`, `data-model.md`, `contracts/api.md`, and `tasks.md` to identify inconsistencies, ambiguities, gaps, and constitution violations before implementation begins.

### Scope

- **Requirements inventory**: 11 requirements (9 FR, 2 NFR) + 5 user stories
- **Task mapping**: 43 tasks mapped to requirements and stories
- **Constitution alignment**: 8 principles verified
- **Coverage analysis**: FR at 100%, NFR at 0%

---

## Key Findings

### HIGH: I1 — Route.status inconsistency

| File | Status |
|------|--------|
| `spec.md:84-85` | Route still has `status: pendiente, asignado` |
| `data-model.md:39-48` | Route.status was removed (no status column) |

**Fix**: Remove `status` from Route attributes in `spec.md` to match `data-model.md`.

### MEDIUM: I2 — Missing packages detail page

`plan.md:100` lists `packages/[id]/page.tsx` in the project structure, but no task in `tasks.md` creates it. The API contract specifies `GET /packages/{id}` but there is no corresponding frontend page.

**Fix**: Add a task to create the package detail page in US1 or Phase 8.

### MEDIUM: I3 — Performance requirements uncovered

RNF-001 (map < 3s) and RNF-002 (CRUD < 2s) have zero tasks. No performance benchmarking, optimization, or even a verification step is defined.

**Fix**: Add a performance-check task in Phase 8, or note that these are aspirational for Phase 1.

### LOW: I4 — checklists/requirements.md orphan

`plan.md:66` references `checklists/requirements.md` as a quality checklist, but no task creates this file.

**Fix**: Add a task or remove from plan.md structure.

### LOW: I5 — api.ts missing from plan.md

`T006` creates `frontend/src/lib/api.ts`, but this file does not appear in the `plan.md` project structure.

**Fix**: Add `frontend/src/lib/api.ts` to the plan.md project tree.

### LOW: I6 — Route color palette unspecified

`spec.md:173` says "marcador se distingue por el color o ícono de esa ruta", and `T033` implements it, but no document defines which colors to use for 5 routes.

**Fix**: Add a 5-color palette to spec.md or as a note in tasks.md.

---

## Coverage Summary

| Category | Count | Coverage |
|----------|-------|----------|
| Functional Requirements | 9 | 100% (9/9) |
| Non-Functional Requirements | 2 | 0% (0/2) |
| User Stories | 5 | 100% (5/5) |
| Total Tasks | 43 | — |
| Constitution Violations | 0 | 0% |

---

## What Needs Evaluation

Before implementation, decide:

1. **I1 (Route.status)**: Quick fix — remove from spec.md. Should take < 2 minutes.
2. **I2 (packages/[id])**: Do we need a package detail page in Phase 1? The list page + inline edit/delete may suffice for MVP. If yes, add task.
3. **I3 (Performance)**: Do we care about < 3s / < 2s in Phase 1? These are aspirational — can be deferred to later phases or removed from NFR section.
4. **I6 (Color palette)**: Choose 5 hex colors for routes. Suggestion: `#e74c3c`, `#3498db`, `#2ecc71`, `#f39c12`, `#9b59b6`.
5. **Priorities**: I1 should be fixed now. I2 optional for MVP. I3, I4, I5 deferrable.

---

## Next Steps

1. Fix I1 — remove `status` from Route in `spec.md`
2. Resolve I2 — decide if packages/[id] is needed, add task if yes
3. Resolve I3 — confirm whether performance tasks are required for Phase 1
4. Resolve I6 — define color palette for route markers
5. Run `/speckit.implement` to begin implementation once analysis findings are resolved

**Current status**: Ready for implementation after I1 fix (1-line edit in spec.md).
