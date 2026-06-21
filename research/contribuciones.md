# Contribuciones del Proyecto

*Declaración explícita del aporte original del proyecto.*

---

## C001

**Contribución**: Marco reproducible para evaluación de operaciones logísticas de última milla.

**Descripción**: Plataforma completa que permite modelar escenarios operacionales, ejecutar evaluaciones deterministas, generar métricas cuantitativas y documentar experimentos formales. Todo el proceso es reproducible mediante Docker y git.

**Evidencia**: SPEC-001, SPEC-002, SPEC-003, SPEC-004.

---

## C002

**Contribución**: Sistema de detección de anomalías operacionales basado en métricas agregadas.

**Descripción**: Algoritmo que identifica entregas anómalas por proximidad a bodega y desviación del centroide de ruta, utilizando umbral configurable y ratio de distancia. Produce penalidad operacional como métrica de calidad.

**Evidencia**: SPEC-003 (módulo de anomalías), SPEC-004 (experimento 001).

---

## C003

**Contribución**: Metodología experimental para comparación de configuraciones de ruteo.

**Descripción**: Diseño experimental que permite aislar el efecto de parámetros individuales (threshold, ratio, seed) sobre métricas de desempeño, manteniendo invariantes las métricas de ruta. Incluye trazabilidad inversa entre evaluaciones y experimentos.

**Evidencia**: SPEC-004, data-model.md, ExperimentRepository.

---

## C004

**Contribución**: Framework de revalidación experimental con categoría V (Validación de Hallazgos).

**Descripción**: Metodología formal para revalidar hallazgos previos al incorporar nuevas fuentes de datos o cambios en el modelo de medición. Introduce la categoría V (Válido / Válido con ajustes / Revisado / Rechazado) en la taxonomía de evidencia, permitiendo trazabilidad de la evolución del conocimiento sin descartar hallazgos previos.

**Evidencia**: SPEC-006, H007–H010, V001–V006.

---

## C005

**Contribución**: Métrica de Distorsión Territorial (M006) como indicador de desviación sistema vial vs geodésico.

**Descripción**: M006 cuantifica la diferencia entre distancia vial real y distancia geodésica por punto y por ruta, clasificándola en rangos (normal ≤1.2, elevada ≤1.5, alta ≤2.0, crítica >2.0). Permite identificar zonas geográficas donde el modelo geodésico subestima sistemáticamente el esfuerzo operacional.

**Evidencia**: SPEC-006, experiments/002-road-network/report.md.
