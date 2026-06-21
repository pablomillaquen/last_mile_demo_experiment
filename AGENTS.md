<!-- SPECKIT START -->
Active spec: (ninguna — SPEC-007 cerrada, SPEC-008 en planeación)
Completed specs: specs/003-results-measurement/ (SPEC-003), specs/004-experiment-reporting/ (SPEC-004), specs/005-research-publication/ (SPEC-005), specs/006-road-network-integration/ (SPEC-006), specs/007-road-network-visualization/ (SPEC-007)
Experiments: experiments/001-baseline-comparison/ (baseline analysis), experiments/002-road-network/ (geodesic vs vial)
Research: research/ (hallazgos, preguntas, decisiones, contribuciones, evidence-matrix — cross-spec)
Current state: SPEC-007 completada, validada y cerrada. Bug vialAvailable corregido. BUG-002/BUG-003 → RESUELTOS con validación visual y experimental. H012: distancia vial +54.3% sobre geodésico (339→523 km, +184 km). D010 registrado: versionado acumulativo de publicaciones. D011 registrado: documento técnico como fuente de verdad. D012 registrado: separación conocimiento acumulado vs comunicación publicada. publications/ reorganizada con versionado explícito (documento-tecnico-v1/v2) y publicaciones derivadas (PUB-001/002/003 con linkedin, portfolio, article). 12 hallazgos (H001–H012), 16 preguntas (PI-001–PI-016), 12 decisiones (D001–D012), 5 contribuciones (C001–C005), 6 validaciones (V001–V006), 3 bugs cerrados. SPEC-008 en planeación (comparación simultánea, filtrado de rutas).

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
