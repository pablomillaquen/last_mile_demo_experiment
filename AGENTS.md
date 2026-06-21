<!-- SPECKIT START -->
Active spec: specs/006-road-network-integration/ (SPEC-006)
Completed specs: specs/003-results-measurement/ (SPEC-003), specs/004-experiment-reporting/ (SPEC-004), specs/005-research-publication/ (SPEC-005)
Experiments: experiments/001-baseline-comparison/ (baseline analysis), experiments/002-road-network/ (geodesic vs vial)
Research: research/ (hallazgos, preguntas, decisiones, contribuciones, evidence-matrix — cross-spec)
Current state: T001–T023 implementados (Docker OSRM, DistanceService, MeasurementService raw metrics, EvaluationController, Exp002 skeleton). Pendientes: T024–T031 (requieren ejecución real de Exp002 con OSRM funcionando), T037–T040 (verificación final).

Stack: Laravel 12 (PHP 8.2) + NextJS 14 (TypeScript) + PostgreSQL 16 + Leaflet + OpenStreetMap + OSRM
Orquestación: Docker Compose

Principios activos: Evidencia Antes de Solución, Decisiones Medibles,
Complejidad Incremental, Docker First
Active plan: specs/006-road-network-integration/plan.md (SPEC-006)
Conventions:
- experiments/<num>-<name>/ para análisis exploratorios y comparaciones
- specs/<num>-<name>/ para especificaciones formales
- research/ para conocimiento acumulativo (hallazgos, preguntas, decisiones, contribuciones)
<!-- SPECKIT END -->
