# Research: SPEC-005 Publication & Dissemination

## 1. Portfolio Article Best Practices

### Structure & Narrative
- **Problem-first opening**: Capturar atención con el problema logístico real antes de mencionar la solución.
- **Journey narrative**: Presentar el proyecto como una investigación progresiva, no como una colección de features.
- **Data-driven results**: Incluir métricas concretas (km recorridos, penalidades, entregas) en lugar de afirmaciones cualitativas.
- **Visual balance**: Un mapa o gráfico cada ~300 palabras para mantener engagement.

### Tone & Audience
- **Nivel técnico**: Asumir conocimiento del rubro logístico pero no de programación. Evitar jargon de desarrollo (API, endpoint, deployment) sin explicación.
- **Extensión**: 1500–2500 palabras. Suficiente para profundidad, no tanto para perder lectores no técnicos.
- **Idioma**: Español neutro, evitar modismos locales.

### Portfolio Impact
- Incluir sección de "Resultados Clave" con 3–5 métricas destacadas.
- Concluir con "Próximos Pasos" para mostrar visión de largo plazo.
- Referenciar el repositorio del proyecto para transparencia.

---

## 2. LinkedIn Post Best Practices

### Format Constraints
- Máximo 3000 caracteres (incluyendo espacios) — límite de LinkedIn para posts estándar.
- Optimal engagement: 1500–2000 caracteres (LinkedIn algorithm favorece posts concisos).

### Visual Strategy
- Un elemento visual principal (mapa comparativo o gráfico de métricas).
- LinkedIn permite hasta 4 imágenes; recomendar 1–2 para no dispersar atención.
- Imagen recomendada: 1200×627px (LinkedIn open graph ratio).

### Call-to-Action
- Enlace al artículo completo del portafolio.
- Pregunta abierta para incentivar comentarios ("¿Cómo abordan este problema en su operación?").
- 3–5 hashtags relevantes (#Logística #ÚltimaMilla #Optimización #DataScience #Innovación).

---

## 3. Technical Research Paper Structure

### Sections (in order)
1. **Introducción**: Contexto del problema de última milla, relevancia industrial, vacío identificado.
2. **Problema de Investigación**: Formulación clara del problema abordado.
3. **Hipótesis**: Enunciado formal de lo que se busca demostrar.
4. **Metodología**: Descripción del proceso de evaluación, variables controladas y medidas.
5. **Descripción de Métricas**: Definición completa de cada métrica con fórmula e interpretación.
6. **Diseño Experimental**: Experimentos realizados, parámetros, condiciones, baseline.
7. **Resultados**: Presentación objetiva de datos obtenidos (tablas, gráficos).
8. **Análisis de Resultados**: Interpretación de tendencias, patrones, anomalías.
9. **Limitaciones**: Restricciones del estudio, alcance de generalización.
10. **Conclusiones**: Síntesis de hallazgos y contribución.
11. **Trabajo Futuro**: Líneas abiertas y prioridades.

### Academic Conventions
- Referencias cruzadas entre secciones.
- Citas a experimentos formales (SPEC-003, SPEC-004).
- Notación consistente para métricas.
- Incluir tabla de acrónimos y símbolos.

---

## 4. Executive Summary Best Practices

### Format
- **Extensión**: 1–2 páginas (500–1000 palabras).
- **Audiencia**: Reclutadores, gerentes, clientes, socios potenciales.
- **Tono**: Profesional, ejecutivo, no técnico.

### Structure
1. **Problema** (3 párrafos max): Qué problema resuelve y por qué es relevante.
2. **Hipótesis** (2 oraciones): Qué se busca demostrar.
3. **Metodología** (1 párrafo): Cómo se abordó el problema.
4. **Resultados Clave** (3–5 bullet points con métricas).
5. **Conclusiones** (2–3 oraciones).
6. **Próximos Pasos** (1–2 oraciones).

### Key Principle
- El lector debe entender el valor del proyecto en ≤ 3 minutos.
- Cada oración debe poder leerse de forma independiente.
- Evitar cualquier terminología que requiera conocimiento previo.

---

## 5. Visual Resource Cataloging

### Organization
- `publications/assets/maps/` — Mapas de evaluación y experimentos.
- `publications/assets/screenshots/` — Capturas de pantallas del sistema.
- `publications/assets/diagrams/` — Diagramas de arquitectura y flujo.
- `publications/assets/tables/` — Tablas comparativas de métricas.

### Metadata per asset
- **filename**: Nombre descriptivo (e.g., `mapa-anomalias-eval-002.png`).
- **source**: Evaluación o experimento de origen (e.g., `evaluation/2`, `experiment/001`).
- **date**: Fecha de generación.
- **description**: Breve descripción del contenido (≤ 100 caracteres).
- **type**: map / screenshot / diagram / table / chart / pdf.

### Index
- Archivo `publications/index.md` que catalogue todos los recursos con metadatos en tabla.
- Permitir búsqueda por tipo, fuente y fecha.

---

## 6. Research Narrative / Roadmap Documentation

### 7-Phase Timeline (from spec RF5.1)
| Fase | Especificación | Estado |
|------|---------------|--------|
| Fase 1: Modelado Operacional | SPEC-001 | ✅ Completada |
| Fase 2: Evaluación | SPEC-002, SPEC-003 | ✅ Completada |
| Fase 3: Experimentación | SPEC-004 | ✅ Completada |
| Fase 4: Optimización Algorítmica | SPEC-006 | Pendiente |
| Fase 5: Ciencia de Datos | SPEC-007 | Pendiente |
| Fase 6: Aprendizaje de Modelos | SPEC-008 | Pendiente |
| Fase 7: White Paper Final | SPEC-009 | Pendiente |

### Best Practices
- Mantener este timeline en cada publicación futura como indicador de estado.
- La narrativa de conexión debe explicar NO SOLO qué se hizo, sino POR QUÉ cada fase sigue a la anterior.
- Las dependencias entre fases deben documentarse explícitamente (output de una → input de la siguiente).
