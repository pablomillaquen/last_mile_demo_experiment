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

---

## D006

**Decisión**: La infraestructura OSRM de SPEC-006 utilizará exclusivamente un grafo del Gran Valparaíso generado mediante extracción regional desde OSM Chile, priorizando eficiencia experimental sobre cobertura nacional.

**Contexto**: Se necesita una red vial real para revalidar hallazgos previos. El área experimental es el Gran Valparaíso (Valparaíso, Viña del Mar, Concón, Quilpué, Villa Alemana, Belloto, Limache).

**Razón**:
- Alineación directa con el área geográfica de Exp001 y Exp002.
- Reducción de ~45 min a ~7 min de preprocesamiento y de 4 GB a 1 GB RAM.
- La cobertura nacional es una línea de investigación separada (PI-013, PI-014).
- El proceso es completamente reproducible (Makefile + bounding box script).

**Impacto**: Experimentos futuros en otras ciudades requerirán construir nuevos grafos OSRM. La decisión queda documentada para evitar reproducir el debate en SPEC-008+.

**Fecha**: 2026-06-20

---

## D007

**Decisión**: Implementar DistanceService con patrón Strategy (modo geodésico ↔ vial intercambiable en tiempo de ejecución).

**Contexto**: SPEC-006 requiere que el sistema pueda calcular distancias tanto geodésicas como sobre red vial, y que todos los servicios downstream (MetricsCalculatorService, MeasurementService, buildDeliveriesFlat) funcionen con ambos modos sin duplicación de código.

**Razón**:
- Strategy pattern permitió reemplazar 13 llamadas estáticas a `HaversineService::calculate()` por un único `DistanceService::calculate()` inyectado.
- `setMode()` permite cambiar entre modos sin reconstruir el container.
- `OsrmClient` se aísla tras la interfaz de DistanceService; los servicios no saben si están usando Haversine u OSRM.

**Impacto**: Cero cambios en la API pública de MeasurementService. Retrofitting completo con inyección de dependencias. Modo vial agrega ~2–5 s por evaluación local vs modo geodésico.

**Fecha**: 2026-06-20

---

## D008

**Decisión**: Vincular pares de evaluaciones geodésica↔vial mediante `parameters_hash` (md5 de 8 campos normalizados), no mediante IDs fijos.

**Contexto**: SPEC-006 requiere comparar métricas entre modo geodésico y vial. El approach inicial usaba IDs fijos 2–7 del Exp001, lo que es frágil y no reproducible en otros entornos.

**Razón**:
- `parameters_hash` incluye 8 campos deterministas: random_seed, algorithm, algorithm_version, near_delivery_threshold_km, ignored_delivery_ratio, dataset, warehouse_lat, warehouse_lng.
- Excluye distance_mode, timestamps, IDs — campos que varían entre la evaluación geodesic y vial.
- md5 de JSON compacto (orden alfabético) es reproducible en cualquier lenguaje/entorno.

**Impacto**: Las evaluaciones ya no dependen de IDs específicos de Exp001. Cualquier par de evaluaciones con mismos parámetros (excepto distance_mode) se emparejará automáticamente. La trazabilidad hacia Exp001 es informativa mediante `baseline_reference`, no funcional.

**Fecha**: 2026-06-20
