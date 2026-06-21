<!-- SPECKIT START -->
Active spec: specs/007-road-network-visualization/ (SPEC-006A)
Completed specs: specs/003-results-measurement/ (SPEC-003), specs/004-experiment-reporting/ (SPEC-004), specs/005-research-publication/ (SPEC-005), specs/006-road-network-integration/ (SPEC-006)
Experiments: experiments/001-baseline-comparison/ (baseline analysis), experiments/002-road-network/ (geodesic vs vial)
Research: research/ (hallazgos, preguntas, decisiones, contribuciones, evidence-matrix — cross-spec)
Current state: SPEC-006A completada y cerrada. BUG-002/BUG-003 → RESUELTOS (SPEC-007) con validación visual (screenshots geodesic/vial, toggle funcional, CA3 0.00%). H012 registrado: distancia vial +54.3% sobre geodésico (339→523 km, +184 km). 12 hallazgos (H001–H012), 15 preguntas (PI-001—PI-015), 9 decisiones (D001–D009), 5 contribuciones (C001–C005), 6 validaciones (V001–V006), 3 bugs cerrados. Limitaciones documentadas como candidatos SPEC-008 (comparación simultánea, filtrado de rutas).

Stack: Laravel 12 (PHP 8.2) + NextJS 14 (TypeScript) + PostgreSQL 16 + Leaflet + OpenStreetMap + OSRM
Orquestación: Docker Compose

Principios activos: Evidencia Antes de Solución, Decisiones Medibles,
Complejidad Incremental, Docker First
Active plan: specs/007-road-network-visualization/plan.md (SPEC-006A)
Conventions:
- experiments/<num>-<name>/ para análisis exploratorios y comparaciones
- specs/<num>-<name>/ para especificaciones formales
- research/ para conocimiento acumulativo (hallazgos, preguntas, decisiones, contribuciones)
<!-- SPECKIT END -->
