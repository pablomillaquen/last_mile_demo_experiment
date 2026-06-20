# Quickstart: SPEC-005 — Research Publication & Experiment Dissemination

## Prerequisites

- Repositorio clonado en `005-research-publication` branch.
- Experimentos SPEC-003 y SPEC-004 ejecutados con resultados disponibles.
- Acceso a `experiments/001-baseline-comparison/` (report.md + experiment.json).

## Validation Scenarios

### Escenario 1: Documento Técnico Completo

```bash
# Verificar que el documento técnico existe con las 11 secciones obligatorias
grep "^##" publications/documento-tecnico.md
```

**Esperado**: 11 secciones: Introducción, Problema de Investigación, Hipótesis, Metodología, Descripción de Métricas, Diseño Experimental, Resultados, Análisis de Resultados, Limitaciones, Conclusiones, Trabajo Futuro.

### Escenario 2: Artículo de Portafolio Derivado

```bash
# Verificar extensión mínima
wc -w publications/articulo-portafolio.md
# Debe ser ≥ 1500 palabras

# Verificar autocontenido — sin referencias a código o APIs
grep -ci "api\|endpoint\|controller\|migration\|docker" publications/articulo-portafolio.md || echo "✅ Sin jargon técnico"
```

**Esperado**: ≥ 1500 palabras, sin terminología de desarrollo de software.

### Escenario 3: Post LinkedIn

```bash
# Verificar longitud
wc -m publications/linkedin-post.md
# Debe ser ≤ 3000 caracteres

# Verificar enlace al artículo
grep "articulo-portafolio" publications/linkedin-post.md
```

**Esperado**: ≤ 3000 caracteres, contiene enlace al artículo de portafolio.

### Escenario 4: Resumen Ejecutivo

```bash
# Verificar extensión máxima (aproximadamente 2 páginas = ~1000 palabras)
wc -w publications/resumen-ejecutivo.md
# Debe ser ≤ 1000 palabras aproximadamente

# Verificar secciones
grep "^##" publications/resumen-ejecutivo.md
```

**Esperado**: ≤ 2 páginas, contiene Problema, Hipótesis, Metodología, Resultados, Conclusiones, Próximos Pasos.

### Escenario 5: Narrativa de Conexión

```bash
# Verificar 7 fases documentadas
grep -c "Fase [1-7]" publications/narrativa-conexion.md
```

**Esperado**: 7 fases presentes (Modelado, Evaluación, Experimentación, Optimización, Data Science, ML, White Paper).

### Escenario 6: Biblioteca Visual

```bash
# Verificar catálogo
ls publications/assets/maps/ publications/assets/screenshots/ 2>/dev/null
wc -l publications/index.md
```

**Esperado**: Directorios de assets existentes, catálogo index.md con al menos 10 recursos.

### Escenario 7: Matriz de Evidencia

```bash
# Verificar que todos los IDs del research/ están registrados
echo "Hallazgos:" && grep -c "^## H" research/hallazgos.md
echo "Preguntas:" && grep -c "^## PI" research/preguntas-investigacion.md
echo "Decisiones:" && grep -c "^## D" research/decisiones.md
echo "Contribuciones:" && grep -c "^## C" research/contribuciones.md

# Verificar que la matriz contiene todos los IDs
for id in $(grep "^## " research/hallazgos.md | sed 's/## //'); do
  grep -q "$id" research/evidence-matrix.md || echo "⚠️ $id no está en evidence-matrix.md"
done
```

**Esperado**: Todos los IDs de research/ están registrados en la matriz y tienen evidencia asociada.

### Escenario 8: Consistencia Multi-Activo

```bash
# Verificar que métricas en resumen ejecutivo coinciden con documento técnico
# (Validación manual: comparar cifras entre documentos)
```

**Esperado**: Todas las métricas en activos derivados (artículo, LinkedIn, resumen) coinciden con el documento técnico fuente.

## Hierarchy Validation

```
Documento Técnico (fuente primaria)
        ↓
Artículo de Portafolio  ←→  Resumen Ejecutivo
        ↓
Post LinkedIn
```

Cada activo derivado debe ser consistente con su fuente inmediata. Para validar:

```bash
# Extraer métricas comunes y comparar
grep -o "[0-9]\+\.[0-9]\+" publications/documento-tecnico.md | sort > /tmp/metrics-tecnicas.txt
grep -o "[0-9]\+\.[0-9]\+" publications/resumen-ejecutivo.md | sort > /tmp/metrics-resumen.txt
diff /tmp/metrics-tecnicas.txt /tmp/metrics-resumen.txt || echo "⚠️ Diferencias encontradas — revisar consistencia"
```

## References

- [Spec](spec.md) — Feature specification
- [Data Model](data-model.md) — Asset structure and metadata
- [Contracts](contracts/) — Per-asset outline contracts
