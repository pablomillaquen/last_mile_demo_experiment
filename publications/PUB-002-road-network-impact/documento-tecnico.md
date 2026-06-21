# Documento Técnico v2 — Impacto de Red Vial en Rutas de Última Milla

**Estado**: Planeado

## Base

- documento-tecnico-v1.md: hallazgos H001–H006 preservados
- SPEC-006: Integración de red vial OSRM
- SPEC-007: Visualización de red vial
- EXP-002: Comparación geodésico vs vial

## Hallazgos acumulados

| ID | Hallazgo | Fuente |
|----|----------|--------|
| H001–H006 | Baseline geodésico | v1 |
| H007 | Factor vial 1.62× | SPEC-006 |
| H008 | Distorsión territorial crítica (>2.0) | SPEC-006 |
| H009 | 100% rutas con TDI anormal | SPEC-006 |
| H010 | Modo vial ~330x más lento | SPEC-006 |
| H011 | Tamaño evaluation.json (+2640%) | SPEC-007 |
| H012 | Distancia vial +54.3% (339→523 km) | SPEC-007 |

## Secciones nuevas respecto a v1

- Limitaciones del modelo geodésico (H012)
- Territorial Distortion Index (M006)
- Validación V001–V006: persistencia de hallazgos baseline
- Discusión sobre impacto operacional del desvío vial
