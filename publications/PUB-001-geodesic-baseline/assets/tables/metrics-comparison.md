# Tabla Comparativa de Métricas entre Evaluaciones (Baseline)

| ID | Threshold | Ratio | Anomalías | Penalidad | Avg Dist (km) | Cobertura (km) | StdDev | Balance Index |
|----|-----------|-------|-----------|-----------|---------------|----------------|--------|---------------|
| 2 | 1 | 2 | 10 | 232.26 | 13.47 | 24.23 | 4.68 | 1.0000 |
| 3 | 1 | 2 | 10 | 232.26 | 13.47 | 24.23 | 4.68 | 1.0000 |
| 4 | 3 | 3 | 10 | 232.26 | 13.47 | 24.23 | 4.68 | 1.0000 |
| 5 | 0.5 | 1.5 | 4 | 136.94 | 13.47 | 24.23 | 4.68 | 1.0000 |
| 6 | 0.3 | 1.2 | 0 | 0.00 | 13.47 | 24.23 | 4.68 | 1.0000 |
| 7 | 5 | 5 | 10 | 232.26 | 13.47 | 24.23 | 4.68 | 1.0000 |

**Observación**: Las métricas operacionales (Avg Dist, Cobertura, StdDev, Balance Index) son invariantes entre evaluaciones. Solo varían Anomalías y Penalidad, que dependen de threshold y ratio.
