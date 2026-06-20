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
