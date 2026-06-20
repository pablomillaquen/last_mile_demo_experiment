# Data Model: Publication Assets

## Directory Structure

```
research/
├── hallazgos.md                   # Hallazgos formales (H001+)
├── preguntas-investigacion.md     # Preguntas de investigación (PI-001+)
├── decisiones.md                  # Registro de decisiones (D001+)
├── contribuciones.md              # Contribuciones del proyecto (C001+)
└── evidence-matrix.md             # Matriz central de trazabilidad IDs → evidencia

publications/
├── index.md                       # Catálogo de biblioteca visual
├── articulo-portafolio.md         # Asset 1: Artículo de portafolio
├── linkedin-post.md               # Asset 2: Post para LinkedIn
├── documento-tecnico.md           # Asset 3: Documento técnico de investigación
├── resumen-ejecutivo.md           # Asset 4: Resumen ejecutivo
├── narrativa-conexion.md          # Asset 5: Narrativa de conexión
└── assets/                        # Asset 6: Biblioteca de recursos visuales
    ├── maps/
    ├── screenshots/
    ├── diagrams/
    └── tables/
```

## Asset Metadata

Each publication asset (1–5) includes frontmatter YAML:

```yaml
---
title: "Título del Documento"
type: "portfolio-article | linkedin-post | technical-paper | executive-summary | connection-narrative"
author: "Sistema"
date: "YYYY-MM-DD"
status: "draft | review | published"
source_specs: ["SPEC-003", "SPEC-004"]
word_count: 0
target_audience: "technical | non-technical | executive | general"
---
```

## Asset 1: Artículo de Portafolio

| Field | Type | Description |
|-------|------|-------------|
| title | string | Título atractivo del artículo |
| subtitle | string | Subtítulo explicativo |
| date | date | Fecha de publicación |
| sections[] | array | Lista de secciones del artículo |
| section.title | string | Título de la sección |
| section.content | text | Contenido narrativo |
| metrics_highlighted[] | array | Métricas clave destacadas |
| images[] | array | Referencias a assets visuales |

**Required sections**: Contexto, Motivación, Metodología, Resultados, Aprendizajes, Próximos Pasos

**Constraints**: Min 1500 palabras, tono no técnico pero riguroso.

## Asset 2: Post LinkedIn

| Field | Type | Description |
|-------|------|-------------|
| content | text (≤3000 chars) | Cuerpo del post |
| visual | string | Ruta al asset visual principal |
| cta_url | string | URL del artículo de portafolio |
| hashtags[] | array | 3–5 hashtags |
| key_learnings[] | array | 3–5 aprendizajes destacados |

**Constraints**: ≤ 3000 caracteres, incluir al menos 1 elemento visual.

## Asset 3: Documento Técnico de Investigación

| Field | Type | Description |
|-------|------|-------------|
| title | string | Título formal |
| sections[] | array | 11 secciones obligatorias |
| sections[].title | string | Nombre de la sección |
| sections[].content | text | Contenido técnico detallado |
| references[] | array | Referencias a experimentos SPEC |
| acronyms | object | Tabla de acrónimos y definiciones |
| metrics_definition | object | Definición formal de cada métrica |

**Required sections**: Introducción, Problema de Investigación, Hipótesis, Metodología, Descripción de Métricas, Diseño Experimental, Resultados, Análisis de Resultados, Limitaciones, Conclusiones, Trabajo Futuro.

## Asset 4: Resumen Ejecutivo

| Field | Type | Description |
|-------|------|-------------|
| title | string | "Resumen Ejecutivo del Proyecto" |
| problem | text (≤3 párrafos) | Descripción del problema |
| hypothesis | text (≤2 oraciones) | Hipótesis del proyecto |
| methodology | text (1 párrafo) | Resumen metodológico |
| key_results[] | array (3–5 items) | Resultados con métricas |
| conclusions | text (2–3 oraciones) | Conclusiones principales |
| next_steps | text (1–2 oraciones) | Próximos pasos |

**Constraints**: ≤ 2 páginas, comprensible sin conocimiento previo.

## Asset 5: Narrativa de Conexión

| Field | Type | Description |
|-------|------|-------------|
| title | string | "Narrativa de Conexión del Proyecto" |
| phases[] | array | 7 fases de la línea temporal |
| phases[].number | int | Número de fase (1–7) |
| phases[].name | string | Nombre de la fase |
| phases[].status | enum | completed | in_progress | pending |
| phases[].spec_ref | string | Referencia a SPEC (si aplica) |
| phases[].objective | text | Objetivo de la fase |
| phases[].deliverables[] | array | Entregables producidos |
| phases[].next_dependency | text | Cómo alimenta a la siguiente fase |
| open_questions[] | array | Preguntas de investigación abiertas |
| overall_roadmap | text | Tabla resumen del estado del proyecto |

**Required**: Cubrir 7 fases con objetivos, entregables y dependencias.

## Asset 6: Biblioteca de Recursos Visuales

### Catalog entry (in `publications/index.md`)

| Field | Type | Description |
|-------|------|-------------|
| filename | string | Nombre del archivo |
| type | enum | map | screenshot | diagram | table | chart | pdf |
| source | string | Origen: evaluación o experimento |
| date | date | Fecha de generación |
| description | string (≤100 chars) | Descripción breve |
| path | string | Ruta relativa desde `publications/assets/` |

### Constraints
- Mínimo 10 recursos catalogados.
- Todos los recursos deben tener metadatos completos.

## Research Entities

### Hallazgo Formal

| Field | Type | Description |
|-------|------|-------------|
| id | string | H001, H002, ... |
| enunciado | text | Conclusión respaldada por evidencia |
| evidencia[] | array | Experimentos, evaluaciones o SPECs que lo sustentan |
| impacto | text | Consecuencia para diseño o investigación futura |
| preguntas_relacionadas[] | array | Referencias a PI-XXX |

### Pregunta de Investigación

| Field | Type | Description |
|-------|------|-------------|
| id | string | PI-001, PI-002, ... |
| pregunta | text | Formulación clara y acotada |
| estado | enum | abierta / respondida / en_investigacion |
| hallazgos_relacionados[] | array | Referencias a H-XXX |
| fase_objetivo | string | Fase del roadmap donde se aborda |

### Decisión

| Field | Type | Description |
|-------|------|-------------|
| id | string | D001, D002, ... |
| decision | text | Qué se decidió |
| contexto | text | Situación que motivó la decisión |
| razon | text | Por qué se tomó esa decisión |
| impacto | text | Consecuencias de la decisión |
| fecha | date | Fecha de la decisión |

### Contribución

| Field | Type | Description |
|-------|------|-------------|
| id | string | C001, C002, ... |
| contribucion | text | Aporte original del proyecto |
| descripcion | text | Explicación detallada |
| evidencia[] | array | SPECs que la respaldan |

## Relationships

```
Documento Técnico (fuente primaria)
        │
        ├──→ Artículo de Portafolio (derivado, simplificado)
        ├──→ Resumen Ejecutivo (derivado, ultra-simplificado)
        └──→ Post LinkedIn (derivado, resumen viral)

Narrativa de Conexión (independiente, referencia todas las fases)

Biblioteca Visual (alimenta todos los assets con imágenes/datos)

research/evidence-matrix.md ────→ Referenciado por Documento Técnico secciones 10, 12 (checklist de consistencia)
research/hallazgos.md ────────→ Referenciado por Documento Técnico sección 10
research/preguntas-investigacion.md ──→ Referenciado por Documento Técnico sección 3
research/decisiones.md ──────→ Independiente, consulta futura
research/contribuciones.md ──→ Referenciado por Documento Técnico sección 12
```
