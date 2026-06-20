# Contract: Documento Técnico de Investigación

**Source**: `publications/documento-tecnico.md`

## Sections (mandatory)

1. **Introducción** — Contexto del problema, relevancia industrial, vacío identificado.
2. **Problema de Investigación** — Formulación clara y acotada del problema.
3. **Preguntas de Investigación** — Lista de PI activas que guían el proyecto (ver `research/preguntas-investigacion.md`).
4. **Hipótesis** — Enunciado formal de lo que se busca demostrar.
5. **Metodología** — Proceso de evaluación, variables controladas y medidas.
6. **Descripción de Métricas** — Cada métrica con fórmula, interpretación y taxonomía (operacionales, balance, calidad, utilización).
7. **Diseño Experimental** — Experimentos realizados, parámetros, baseline.
8. **Resultados** — Presentación **objetiva de datos** (tablas, gráficos). Sin interpretación.
9. **Análisis de Resultados** — Interpretaciones, tendencias, patrones y anomalías observadas. Separado explícitamente de Resultados.
10. **Hallazgos Formales** — Lista de H001, H002... con enunciado, evidencia, impacto (ver `research/hallazgos.md`).
11. **Limitaciones** — Restricciones del estudio.
    - **Amenazas a la validez interna**: Limitaciones del diseño experimental que pueden afectar la relación causal (ej: datos sintéticos).
    - **Amenazas a la validez externa**: Limitaciones para generalizar resultados a otros contextos (ej: operaciones con ventanas horarias estrictas).
12. **Conclusiones** — Síntesis de hallazgos y contribución del proyecto.
13. **Trabajo Futuro** — Líneas abiertas, prioridades sugeridas.

## Taxonomía de Métricas

Las métricas del sistema (15 total) se agrupan en:

- **Métricas Operacionales**: distancia, entregas, paquetes — miden volumen y alcance.
- **Métricas de Balance**: desviación estándar, ratio de carga, balance index — miden distribución.
- **Métricas de Calidad**: anomalías, penalidades — miden eficiencia operacional.
- **Métricas de Utilización**: vehículos activos, ocupación — miden uso de recursos.

## Constraints

- Incluir referencias explícitas a experimentos SPEC-003 y SPEC-004.
- Las secciones Resultados y Análisis de Resultados deben estar separadas. No mezclar datos con interpretaciones.
- Máximo 25 páginas (recomendado).
- Incluir tabla de acrónimos.

## Role

Este documento es la **fuente primaria de evidencia**. Todos los demás activos de divulgación (artículo de portafolio, post LinkedIn, resumen ejecutivo) deben derivarse de sus resultados y conclusiones.

## Validation

- [ ] Documento completo en `publications/documento-tecnico.md`
- [ ] 13 secciones obligatorias presentes con contenido sustantivo
- [ ] Secciones Resultados y Análisis claramente separadas
- [ ] Hallazgos formales (H001+) incluidos con evidencia e impacto
- [ ] Amenazas a validez interna y externa documentadas
- [ ] Taxonomía de métricas (4 grupos) incluida en Descripción de Métricas
- [ ] Referencias a SPEC-003 y SPEC-004 incluidas
- [ ] Tabla de acrónimos incluida
