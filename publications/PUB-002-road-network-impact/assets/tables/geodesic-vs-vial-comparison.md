# Comparación Geodésico vs Vial por Ruta

| Ruta | Entregas | Geodésico (km) | Vial (km) | Diferencia (km) | Diferencia (%) | Factor |
|------|:--------:|:--------------:|:----------:|:----------------:|:---------------:|:------:|
| Ruta A | 30 | 37.87 | 53.78 | +15.91 | +42.0% | 1.42× |
| Ruta B | 30 | 91.46 | 126.07 | +34.61 | +37.8% | 1.38× |
| Ruta C | 30 | 107.09 | 176.05 | +68.96 | +64.4% | 1.64× |
| Ruta D | 30 | 36.35 | 72.80 | +36.45 | +100.3% | **2.00×** |
| Ruta E | 30 | 66.28 | 94.41 | +28.13 | +42.4% | 1.42× |
| **Total** | **150** | **339.06** | **523.11** | **+184.06** | **+54.3%** | **1.54×** |

**Dataset**: Valparaíso Demo, 150 entregas, 5 rutas.
**Modo vial**: OSRM sobre OpenStreetMap, `overview=full`.
**Hallazgo principal (H012)**: +54.3% distancia vial sobre geodésica, equivalente a +184 km por operación diaria.
**Fuente**: SPEC-007, EXP-002, Evaluaciones 18 y 19.
