# Resumen Ejecutivo del Proyecto

**Fecha**: 2026-06-23
**Estado**: SPEC-008 cerrada. Sin spec activa.

---

## Progresión del proyecto

```
Fase 1 (SPEC-003–005)     Fase 2 (SPEC-006–007)       Fase 3 (SPEC-008)
    SIMULAR       →           MEDIR          →          INTERPRETAR
    ─────────                ─────────                ──────────────
   Motor de eval.           Red vial OSRM              Split view
   Asignación manual        Distancias viales          RoutePanel
   Dataset sintético        TDI (M006)                 Aislamiento
   Detección anomalías      Validación V001–V006       Hallazgos H014–H017
```

**Próxima fase natural** (emergente desde PI-018):

```
    EXPLICAR
    ─────────
   Dirección de recorrido
   Puntos numerados
   Secuencia operacional
   Flechas / navegación
```

---

## Hallazgos (H001–H017)

### Baseline operacional (SPEC-003–005)

| ID | Enunciado | Fuente |
|----|-----------|--------|
| H001 | Minimizar distancia no garantiza balance de carga | SPEC-003 |
| H002 | Punto inicial impacta significativamente distancia total | SPEC-004 |
| H003 | Métricas globales son invariantes ante parámetros de anomalía | SPEC-004 |
| H004 | `ignored_delivery_ratio` es más discriminatorio que `near_delivery_threshold_km` | SPEC-004 |
| H005 | Clusters con dispersión radicalmente distinta (0.3 km vs 15.3 km radio) | SPEC-004 |
| H006 | 10 entregas < 1 km de bodega, todas en sector B — oportunidad de micro-ruteo | SPEC-004 |

### Red vial OSRM (SPEC-006–007)

| ID | Enunciado | Fuente |
|----|-----------|--------|
| H007 | Distancias viales 62.5% mayores que geodésicas (factor 1.62×) | SPEC-006 |
| H008 | Rutas que cruzan bahía de Valparaíso: distorsión crítica TDI > 2.0 | SPEC-006 |
| H009 | 100% rutas con TDI anormal; 60% alta o crítica | SPEC-006 |
| H010 | Modo vial ~330x más lento que geodésico (~82s vs ~0.25s) | SPEC-006 |
| H011 | evaluation.json: 84 KB → 2.3 MB (+2640%) con geometría vial | SPEC-006A |
| H012 | Distancia vial +54.3% sobre geodésico (339→523 km, +184 km). Ruta D: 2.00× | SPEC-006A |

### Visual analytics (SPEC-008)

| ID | Enunciado | Fuente |
|----|-----------|--------|
| H013 | geodesicPolylines y vialPolylines desde mismos route_legs | SPEC-008 |
| H014 | Split view reduce esfuerzo de interpretación visual | SPEC-008 |
| H015 | Aislamiento de rutas aumenta capacidad analítica | SPEC-008 |
| H016 | RoutePanel aporta control sin carga cognitiva | SPEC-008 |
| H017 | Ausencia de dirección de recorrido limita interpretación operacional | SPEC-008 |

---

## Preguntas de investigación (PI-001–PI-018)

| Estado | IDs |
|--------|-----|
| **Respondidas** | PI-002, PI-003, PI-004 |
| **Respondidas parcialmente** | PI-001, PI-005, PI-006, PI-007, PI-008, PI-009, PI-010, PI-011, PI-012, PI-016, PI-017 |
| **Abiertas / investigación futura** | PI-013, PI-014, PI-015, PI-018 |

### Pregunta emergente principal

> **PI-018**: ¿Qué mecanismos visuales permiten comunicar la secuencia operacional y dirección de una ruta logística sin incrementar la carga cognitiva del analista?

Esta pregunta es la candidata natural para la siguiente SPEC, ya que emerge de una limitación observada durante uso real del sistema (H017), no de una especulación teórica.

---

## Decisiones arquitectónicas (D001–D017)

| Grupo | IDs |
|-------|-----|
| Metodología experimental | D001–D004 |
| Documentación y publicaciones | D005, D010–D014 |
| Infraestructura vial | D006–D009 |
| Diseño experimental SPEC-008 | D015–D016 |
| Gestión documental | D017 |

### Decisiones destacadas

- **D001**: Datasets sintéticos controlados — reproducibilidad
- **D006**: Cobertura Gran Valparaíso para EXP-002
- **D007**: Strategy Pattern en DistanceService (geodésico/vial intercambiables)
- **D014**: PUB-001 como estándar editorial
- **D016**: M4 como exploratorio (no cuantitativo) — ver D016 para limitaciones
- **D017**: Inmutabilidad de documentos técnicos publicados

---

## Contribuciones (C001–C006)

| ID | Contribución | Especificación |
|----|-------------|----------------|
| C001 | Marco reproducible para evaluación logística última milla | SPEC-001–004 |
| C002 | Sistema de detección de anomalías operacionales | SPEC-003–004 |
| C003 | Metodología experimental para comparación de configuraciones | SPEC-004 |
| C004 | Framework de revalidación experimental (categoría V) | SPEC-006 |
| C005 | Métrica de Distorsión Territorial (M006 TDI) | SPEC-006 |
| C006 | Visual analytics para diferencias geodésico/vial | SPEC-008 |

---

## Bugs cerrados (BUG-001–BUG-004)

| ID | Problema | Estado |
|----|----------|--------|
| BUG-001 | Exp001 modificado por `experiments:sync` | RESUELTO |
| BUG-002 | Mapa sin geometría vial OSRM | RESUELTO (SPEC-007) |
| BUG-003 | Falta selector visual geodésico/vial | RESUELTO (SPEC-007) |
| BUG-004 | RoutePanel no actuaba sobre MapView en modo simple | RESUELTO (SPEC-008) |

---

## Validaciones (V001–V006)

Todos los hallazgos H001–H006 del baseline (modo geodésico) fueron re-evaluados con modo vial. **Ninguno se invalida**. Las conclusiones cualitativas del baseline son robustas al cambio de modo de distancia.

---

## Estado de artefactos documentales

| Artefacto | Estado | Contenido |
|-----------|--------|-----------|
| `documento-tecnico-v1.md` | Publicado | H001–H006 (SPEC-003–005) |
| `documento-tecnico-v2.md` | Publicado | H001–H012 + V001–V006 (SPEC-006–007) |
| `documento-tecnico-v3.md` | Esbozo | H014–H017 (SPEC-008) — sección 8, sobre v2 |
| SPEC-008 assets/ | Completado | 5 capturas, mediciones M4 exploratorias |
| Evidence matrix | Actualizada | Trazabilidad completa H001–H017, PI-001–PI-018, D001–D017 |

---

## Próximos movimientos posibles

1. **Especulación — SPEC-009 basada en PI-018**: dirección de recorrido, puntos numerados, flechas, secuencia operacional
2. **PUB-003**: Publicación derivada centrada en visual analytics (datos de SPEC-008)
3. **documento-tecnico-v3 → Publicado**: Expandir sección 8 con narrativa completa
4. **Revisión metodológica**: Decidir si HYP-008-01 merece un diseño experimental controlado con observadores externos
