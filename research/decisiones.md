# Registro de Decisiones

*Decisiones arquitectónicas, metodológicas y de diseño documentadas para trazabilidad futura.*

Formato basado en ADR (Architecture Decision Records).

---

## D001

**Decisión**: Utilizar datasets sintéticos controlados para todas las evaluaciones.

**Contexto**: Se requiere reproducibilidad total de experimentos y capacidad de generar escenarios específicos.

**Razón**: Los datos sintéticos permiten aislar variables, replicar resultados exactos y eliminar dependencias de fuentes externas.

**Impacto**: Los resultados son comparables entre experimentos, pero la generalización a operaciones reales debe validarse por separado.

**Fecha**: 2026-06-10

---

## D002

**Decisión**: Implementar motor de evaluación como proceso determinista (sin aleatoriedad).

**Contexto**: Las evaluaciones deben producir resultados idénticos al re-ejecutarse con los mismos parámetros.

**Razón**: Eliminar varianza no controlada que dificulte la comparación entre experimentos.

**Impacto**: Simplifica el diseño experimental. Cualquier diferencia entre evaluaciones es atribuible exclusivamente a cambios en parámetros o asignación.

**Fecha**: 2026-06-12

---

## D003

**Decisión**: Filesystem como fuente de verdad para experimentos; base de datos como caché de lectura.

**Contexto**: Los experimentos deben versionarse con git y ser portables entre entornos.

**Razón**: Git proporciona trazabilidad, diff, branching y recuperación sin depender de la base de datos.

**Impacto**: Los experimentos se crean y modifican como archivos; sync command actualiza la DB. La DB nunca es la fuente primaria.

**Fecha**: 2026-06-19

---

## D004

**Decisión**: No incluir foreign key `experiment_id` en la tabla de evaluaciones. Usar `JSON_CONTAINS` sobre `evaluation_ids` en experimentos.

**Contexto**: Una evaluación puede pertenecer a múltiples experimentos; forzar FK crearía dependencias unidireccionales.

**Razón**: Mantener evaluaciones independientes de experimentos permite reutilizarlas en múltiples análisis sin migraciones.

**Impacto**: Las consultas de trazabilidad inversa requieren `JSON_CONTAINS` en lugar de JOIN directo. Aceptable para el volumen actual (< 100 evaluaciones).

**Fecha**: 2026-06-19

---

## D005

**Decisión**: Separar Resultados (datos) de Análisis (interpretaciones) en el documento técnico.

**Contexto**: La mezcla de datos objetivos con interpretaciones subjetivas reduce la credibilidad metodológica.

**Razón**: Permite al lector examinar los datos sin interferencia interpretativa, y evaluar las conclusiones con transparencia.

**Impacto**: Documento técnico más extenso pero metodológicamente más sólido.

**Fecha**: 2026-06-19
