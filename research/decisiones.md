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

---

## D009

**Decisión**: Preservar artefactos experimentales como fuente primaria de evidencia, versionados en git junto al experimento.

**Contexto**: BUG-001 evidenció que los experimentos pueden quedar huérfanos si solo existen en BD y esta se reconstruye. Las evaluaciones históricas de Exp001 sobrevivieron como JSON en el contenedor Docker, pero no estaban protegidas contra pérdida.

**Razón**:
- Los artefactos (evaluation.json, deliveries.csv, route_metrics) se almacenan ahora en `experiments/<experimento>/artifacts/eval-<id>/`.
- El comando `evaluations:import` puede reconstruir registros en BD desde estos JSON.
- El flag `"immutable": true` en experiment.json evita que `experiments:sync` modifique experimentos históricos.
- Los experimentos históricos pueden reconstruirse incluso después de migraciones o pérdida de BD.

**Impacto**: Los experimentos son ahora auto-contenidos (JSON + CSV + reporte + experiment.json en un mismo directorio). La BD es un caché de lectura; el filesystem es la fuente de verdad. Cada evaluación ocupa ~100 KB en el repositorio (aceptable para el volumen actual).

**Fecha**: 2026-06-21

---

## D010

**Decisión**: Versionado acumulativo de publicaciones — los hallazgos nuevos no sobrescriben publicaciones anteriores.

**Contexto**: Con H012 se ha generado evidencia cuantitativa sobre la diferencia entre modelo geodésico y vial (+54.3%, 339→523 km). Esta evidencia tiene valor para publicaciones futuras. Sin embargo, las publicaciones existentes (basadas en Exp001, modelo geodésico) representan el estado del conocimiento al momento de su creación y no deben ser modificadas retroactivamente.

**Razón**:
- Las publicaciones son inmutables por definición — modificarlas con datos nuevos rompe la trazabilidad histórica del proyecto.
- Nuevos hallazgos (H012, hallazgos futuros de SPEC-008) deben generar nuevas versiones o nuevas publicaciones.
- Las capturas, métricas y resultados deben mantener trazabilidad hacia el SPEC y EXP que los originó.
- Este principio extiende D009 (artefactos inmutables) al plano de las publicaciones académicas/técnicas.

**Impacto**:
- Publicaciones históricas (basadas en Exp001) se mantienen intactas.
- Los resultados de EXP-002 y SPEC-008 se publicarán como nuevas versiones o documentos independientes.
- Las capturas comparativas generadas por SPEC-008 deberán poder incorporarse a nuevas publicaciones sin alterar las previas.
- La trazabilidad se mantiene: cada publicación referencia el SPEC y EXP que originó sus datos.

**Fecha**: 2026-06-21

---

## D011

**Decisión**: `documento-tecnico.md` es el artefacto documental principal del proyecto; toda publicación externa se deriva de una versión específica de este documento.

**Contexto**: Hasta SPEC-005, el proyecto generó `documento-tecnico.md` como fuente de verdad documental, con publicaciones derivadas (resumen ejecutivo, narrativa de conexión, artículo de portafolio, LinkedIn). SPEC-007 y EXP-002 generaron nuevos hallazgos (H007–H012) que no existían al momento de redactar la v1. Para mantener la trazabilidad y permitir que nuevas publicaciones coexistan con las históricas sin conflicto, se requiere un versionado explícito del documento técnico.

**Razón**:
- `documento-tecnico.md` (v1) captura el estado del conocimiento hasta SPEC-005 (hallazgos H001–H006, modelo geodésico).
- Los hallazgos de SPEC-006 y SPEC-007 (H007–H012, modelo vial) requieren una v2 que preserve todo el contenido de v1 y agregue las nuevas secciones.
- Las publicaciones derivadas (PUB-001, PUB-002, etc.) referencian una versión específica del documento técnico como fuente.
- El pipeline completo es: Experimentos → Hallazgos → Documento técnico → Publicaciones derivadas.

**Impacto**:
- `documento-tecnico-v1.md` permanece inmutable como snapshot histórico.
- `documento-tecnico-v2.md` consolida hallazgos acumulados (H001–H012).
- Cada PUB tiene `source-version.txt` que indica qué versión del documento técnico utilizó.
- Las publicaciones derivadas históricas (resumen-ejecutivo.md, narrativa-conexion.md, articulo-portafolio.md, linkedin-post.md) se mantienen intactas, referenciando documento-tecnico v1.
- Las nuevas publicaciones derivadas (PUB-001, PUB-002, PUB-003) referenciarán la versión que corresponda.

**Fecha**: 2026-06-21

---

## D012

**Decisión**: Separación entre conocimiento acumulado (`documentacion/`) y comunicación publicada (`PUB-*/`). Los documentos técnicos representan el estado acumulado del conocimiento del proyecto en un momento determinado. Las publicaciones representan adaptaciones de dicho conocimiento para audiencias específicas.

**Contexto**: La reorganización de `publications/` estableció dos líneas paralelas: `documentacion/` con versionado secuencial del documento técnico (v1, v2, v3...) y `PUB-*/` con publicaciones derivadas para audiencias específicas (LinkedIn, portafolio, resumen ejecutivo, artículo). Esta distinción no estaba formalizada como decisión de diseño, lo que podría generar ambigüedad en futuras iteraciones.

**Razón**:
- `documento-tecnico-v{N}.md` es la fuente de verdad del conocimiento acumulado hasta ese momento.
- `PUB-{N}` es una adaptación para una audiencia específica, basada en una versión concreta del documento técnico.
- Una publicación nunca modifica retrospectivamente el contenido de una publicación anterior.
- Un nuevo hallazgo no cambia publicaciones pasadas, pero sí genera una nueva versión del documento técnico.
- Esta separación permite que el documento técnico evolucione como línea de investigación mientras las publicaciones se mantienen como piezas de comunicación externa.

**Impacto**:
- `documentacion/` crece secuencialmente con cada nueva versión del conocimiento acumulado.
- `PUB-*/` crece con cada nueva publicación, referenciando siempre una versión específica vía `source-version.txt`.
- El white paper final (SPEC-009) seguirá el mismo patrón.
- La trazabilidad se mantiene explícita entre conocimiento y comunicación.

**Fecha**: 2026-06-21
