# Feature Specification: Sistema de Documentación, Interpretación y Comunicación de Resultados Experimentales

**Feature Branch**: `004-experiment-reporting`

**Created**: 2026-06-19

**Status**: Draft

---

## 1. Context

El proyecto ha evolucionado desde una herramienta de simulación logística hacia un sistema formal de medición y evaluación de rutas. Actualmente permite:

- Crear agrupamientos manuales de rutas.
- Ejecutar evaluaciones cuantitativas.
- Calcular 17 métricas operacionales.
- Detectar anomalías en asignaciones.
- Exportar resultados en JSON, CSV y mapas PNG.
- Comparar ejecuciones históricas.
- Documentar experimentos exploratorios (`experiments/`).

Sin embargo, los datos generados son difíciles de interpretar para personas que no participaron en el desarrollo del proyecto. Las métricas carecen de contexto explicativo, las pantallas del sistema no tienen documentación asociada, no existe un formato de reporte descargable apto para presentaciones o portafolio, y los experimentos se gestionan manualmente sin un explorador que permita navegarlos.

El sistema necesita evolucionar desde una herramienta de cálculo hacia una plataforma de análisis, documentación y comunicación de resultados.

---

## 2. Hipótesis

Es posible diseñar una capa de documentación y reporting que permita:

1. Explicar el significado de cada métrica de forma comprensible para una audiencia no técnica.
2. Documentar las pantallas del sistema para que cualquier usuario pueda navegarlas y entender los resultados.
3. Generar reportes PDF descargables a partir de cualquier evaluación ejecutada.
4. Gestionar experimentos históricos con una estructura formal y un explorador web.
5. Utilizar los resultados como evidencia reutilizable para artículos, documentación técnica y portafolio profesional.
6. Incorporar el principio **"Toda métrica debe poder explicarse"** como guía de diseño.

---

## 3. Objetivo

Crear un sistema de documentación, interpretación y comunicación de resultados que permita a cualquier persona —haya o no participado en el desarrollo— comprender, comparar y utilizar los resultados experimentales como evidencia cuantitativa.

---

## 4. Alcance

### 4.1 Incluye

- Guía de interpretación de métricas (documentación oficial).
- Documentación de pantallas del sistema (evaluaciones, detalle de evaluación).
- Generación de reportes PDF por evaluación (descargables, aptos para presentaciones y portafolio).
- Informes experimentales narrativos por experimento (objetivo, hipótesis, metodología, resultados, conclusiones).
- Repositorio formal de experimentos con estructura estandarizada y trazabilidad explícita entre experimento y evaluaciones.
- Explorador web de experimentos (listar, ver informe, descargar PDF y archivos asociados).
- Artefactos reutilizables para portafolio profesional (PDF, PNG, Markdown, CSV, JSON).
- Principio rector "Toda métrica debe poder explicarse".

### 4.2 Excluye

- Modificación del motor de cálculo de métricas (SPEC-003 permanece intacto).
- Nuevos algoritmos de optimización de rutas.
- Nuevos tipos de visualización (Leaflet, gráficos interactivos).
- Autenticación o permisos de usuario.
- Despliegue multi-tenant o compartición remota de experimentos.
- Exportación a formatos adicionales (DOCX, XLSX, HTML) más allá de PDF y los ya existentes (JSON/CSV/PNG).
- Comparación consolidada entre experimentos (se define en Futuro).

---

## 5. Definiciones

| Término | Definición |
|---------|-----------|
| **Métrica** | Indicador cuantitativo calculado sobre una ruta o conjunto de rutas (ej: distancia promedio a bodega, radio del cluster). |
| **Evaluación** | Ejecución completa del sistema de métricas sobre los datos actuales, con un conjunto de parámetros específico. Equivale a una fila en la tabla `evaluations`. |
| **Experimento** | Unidad principal de investigación. Agrupa una o más evaluaciones destinadas a responder una hipótesis específica. Es una entidad de primer nivel del dominio, no solo un directorio. |
| **Evaluación de referencia (baseline)** | Evaluación dentro de un experimento que sirve como línea base para comparar el resto de las evaluaciones del mismo experimento. |
| **Reporte PDF de Evaluación** | Documento descargable que resume los resultados de una evaluación individual con contexto interpretativo. |
| **Informe Experimental** | Documento narrativo por experimento que explica objetivo, hipótesis, metodología, evaluaciones utilizadas, resultados y conclusiones. Es el documento principal de cada experimento. |
| **Guía de Interpretación** | Documento oficial que explica cada métrica con definición, fórmula, interpretación y ejemplos. |
| **Repositorio de Experimentos** | Estructura de directorios y metadatos que organiza y almacena experimentos históricos como entidades navegables. |
| **Explorador de Experimentos** | Interfaz web para navegar, visualizar y descargar experimentos y sus informes. |
| **Artefacto de Portafolio** | Cualquier archivo generado por el sistema (PDF, PNG, Markdown, CSV, JSON) apto para su uso en artículos técnicos, documentación académica o portafolio profesional. |

---

## 6. Usuarios

| Usuario | Descripción |
|---------|-------------|
| **Investigador / Analista** | Ejecuta evaluaciones, interpreta resultados y documenta experimentos. |
| **Revisor externo** | Evalúa la calidad del trabajo sin conocer los detalles técnicos del proyecto. |
| **Autor / Documentador** | Prepara material para artículos técnicos, portafolio o presentaciones. |

---

## 7. Funcionalidades

### 7.1 Guía de Interpretación de Métricas

El sistema debe incluir documentación oficial que explique cada métrica del sistema. Para cada métrica se debe incluir:

- **Nombre**: Nombre oficial de la métrica.
- **Definición**: Descripción conceptual de qué mide.
- **Fórmula**: Expresión matemática utilizada para su cálculo (cuando aplique).
- **Interpretación**: Qué significa un valor alto, bajo o intermedio en términos operativos.
- **Ejemplos prácticos**: Casos concretos que ilustren el significado de la métrica.
- **Valores de referencia o criterios de interpretación**: Rangos, umbrales o criterios cualitativos que ayuden a interpretar la métrica, cuando sea posible definirlos de forma independiente del dataset.

Métricas a documentar:

1. Entregas por ruta
2. Distancia mínima a bodega
3. Distancia máxima a bodega
4. Distancia promedio a bodega
5. Centroide del cluster
6. Distancia centroide-bodega
7. Radio del cluster
8. Distancia promedio al centroide (compactación)
9. Distancia estimada de ruta
10. Cobertura territorial
11. Desviación estándar de distancias
12. Balance Index (CV)
13. Inter Cluster Distance
14. Operational Penalty
15. Anomalías

### 7.2 Documentación de Pantallas

El sistema debe incluir documentación de usuario para cada pantalla del subsistema de evaluaciones:

#### Página de Evaluaciones (lista)

Debe explicar:

- Qué representa cada fila (identificador, fecha, métricas resumidas).
- Qué representa cada indicador mostrado en las tarjetas resumen.
- Cómo comparar evaluaciones visualmente.
- Cómo identificar resultados relevantes (mejor/peor métrica).
- Cómo ejecutar una nueva evaluación.

#### Página de Detalle de Evaluación

Debe explicar:

- Resumen ejecutivo: qué muestra el encabezado y las tarjetas de métricas globales.
- Ranking de rutas: cómo interpretar la posición y el valor.
- Tabla de métricas por ruta: significado de cada columna.
- Anomalías detectadas: qué representa cada anomalía y cómo interpretar el ratio.
- Mapas generados: qué muestra cada tipo de mapa (vista general, por ruta, anomalías).
- Archivos exportados: qué contiene cada archivo y cómo utilizarlos.

### 7.3 Reporte PDF por Evaluación

Cada evaluación debe poder generar un reporte PDF descargable con el siguiente contenido mínimo:

- **Identificación**: ID de evaluación, fecha de ejecución, algoritmo y versión.
- **Parámetros utilizados**: threshold, ratio, random_seed, dataset.
- **Metadata de reproducibilidad**: `algorithm`, `algorithm_version`, `dataset`, `evaluation_id`, `generated_at`, `spec_version` (versión del spec con que fue generado).
- **Resumen ejecutivo** con los indicadores globales más relevantes.
- **Ranking de rutas** ordenado por cercanía a bodega.
- **Tabla de métricas detalladas** por ruta.
- **Anomalías detectadas** (tabla con delivery_id, ruta, distancias y ratio).
- **Mapas generados** (vista general, anomalías).
- **Conclusiones automáticas** basadas en reglas sobre los valores de las métricas (ej: "la ruta con mayor distancia promedio a bodega es X", "se detectaron Y anomalías en la ruta Z"). No incluye IA generativa.

El reporte debe ser apto para:

- Presentaciones ejecutivas y técnicas.
- Evidencia experimental en documentación.
- Material de portafolio profesional.
- Comparaciones futuras entre evaluaciones.

### 7.4 Repositorio de Experimentos

Los experimentos deben seguir una estructura estandarizada:

```
experiments/
├── NNN-nombre-experimento/
│   ├── report.md          # Informe experimental (narrativo)
│   ├── report.pdf         # Informe experimental en PDF (generado)
│   └── assets/            # Archivos adjuntos (imágenes, datos, etc.)
│       └── ...
```

Cada experimento debe declarar explícitamente:

- **Identificador** numérico secuencial.
- **Nombre** descriptivo.
- **Fecha** de creación.
- **Objetivo**: Qué pregunta busca responder el experimento.
- **Hipótesis**: Qué se espera observar (cuando aplique).
- **Evaluaciones incluidas**: Lista de IDs de evaluaciones que componen el experimento.
- **Evaluación de referencia (baseline)**: ID de la evaluación dentro del experimento que sirve como línea base para comparación.
- **Descripción**: Contexto adicional sobre la metodología o condiciones del experimento.

### 7.6 Informe Experimental por Experimento

Cada experimento debe incluir un documento narrativo que documente la investigación completa. Este documento es distinto del reporte PDF de evaluación individual.

Contenido mínimo del informe experimental:

- **Título y autor**: Nombre del experimento, fecha, autor.
- **Objetivo**: Pregunta de investigación que motiva el experimento.
- **Hipótesis**: Predicción sobre los resultados esperados.
- **Metodología**: Descripción de cómo se diseñó el experimento (parámetros, evaluaciones ejecutadas, criterios de comparación).
- **Evaluaciones utilizadas**: Lista de evaluaciones asociadas con enlaces a cada una.
- **Resultados**: Tablas, gráficos y métricas obtenidas.
- **Análisis**: Interpretación de los resultados en relación con la hipótesis.
- **Conclusiones**: Hallazgos principales, limitaciones y trabajo futuro.
- **Artefactos generados**: Referencias a archivos exportados (PDF, PNG, CSV, JSON).

Este informe es el documento principal que se publicará en artículos técnicos, documentación académica y portafolio profesional.

### 7.7 Artefactos de Portafolio

El sistema debe permitir generar y descargar artefactos reutilizables para:

- Artículos técnicos y blogs.
- Documentación académica (tesis, papers, informes).
- Portafolio profesional (demostraciones de capacidad técnica).

Artefactos incluidos:

- **PDF**: Reporte de evaluación e informe experimental completos.
- **PNG**: Mapas generados (vista general, por ruta, anomalías).
- **Markdown**: Informe experimental en formato editable.
- **CSV**: Datos exportados por ruta y por entrega.
- **JSON**: Datos completos de la evaluación.

### 7.8 Explorador de Experimentos

El sistema debe incorporar una sección web para visualizar experimentos existentes con las siguientes capacidades:

- Listar todos los experimentos registrados con su información principal.
- Ver el reporte asociado a cada experimento (markdown renderizado).
- Descargar el reporte en PDF.
- Descargar archivos relacionados (JSON, CSV, PNG).
- Navegar entre experimentos históricos.
- Acceder directamente a las evaluaciones asociadas desde cada experimento.

Además, debe existir trazabilidad inversa: desde el detalle de una evaluación individual debe ser posible identificar el experimento al que pertenece y navegar hacia su informe experimental.

**Nota**: El explorador es de solo lectura. Los experimentos se crean y gestionan manualmente editando la estructura de directorios y archivos en el repositorio. Esta decisión evita introducir un sistema de gestión de contenido y mantiene la responsabilidad del contenido narrativo en el investigador, no en el software.

---

## 8. Criterios de Aceptación

### 8.1 Guía de Interpretación

- CA-01: Un usuario sin conocimiento previo del proyecto puede leer la guía y comprender qué mide cada métrica en menos de 30 minutos.
- CA-02: La guía incluye al menos un ejemplo práctico por métrica.
- CA-03: Cada métrica incluye criterios de interpretación o valores de referencia cuando sea posible definirlos de forma independiente del dataset.

### 8.2 Documentación de Pantallas

- CA-04: Cada pantalla del subsistema de evaluaciones tiene su documentación asociada.
- CA-05: La documentación explica todos los elementos visuales presentes en cada pantalla.

### 8.3 Reporte PDF

- CA-06: Cualquier evaluación existente puede generar un reporte PDF en menos de 10 segundos.
- CA-07: El reporte incluye todos los contenidos mínimos especificados.
- CA-08: El reporte es visualmente apto para presentación profesional (portada, tablas formateadas, mapas incrustados).

### 8.4 Repositorio de Experimentos

- CA-09: Los experimentos se almacenan con una estructura de directorios consistente y predecible.
- CA-10: Cada experimento referencia explícitamente las evaluaciones que lo componen.

### 8.5 Informe Experimental

- CA-11: Cada experimento tiene un informe narrativo que incluye objetivo, hipótesis, metodología, evaluaciones, resultados y conclusiones.
- CA-12: El informe experimental es accesible desde el explorador de experimentos.
- CA-13: El informe experimental puede descargarse en PDF y Markdown.

### 8.6 Artefactos de Portafolio

- CA-14: Todos los artefactos generados (PDF, PNG, Markdown, CSV, JSON) son descargables individualmente.
- CA-15: Los artefactos no requieren herramientas adicionales más allá de un navegador o lector de PDF estándar.

### 8.7 Explorador de Experimentos

- CA-16: Un usuario puede listar todos los experimentos en menos de 2 segundos.
- CA-17: Un usuario puede navegar desde un experimento a cualquiera de sus evaluaciones asociadas en un clic.
- CA-18: Los archivos descargables (PDF, JSON, CSV, PNG) se sirven con el tipo de contenido correcto.

---

## 9. Escenarios de Uso

### Escenario 1: Nuevo miembro del equipo revisa resultados

1. El usuario accede a la guía de interpretación de métricas.
2. Lee la definición de "Operational Penalty" y entiende qué representa.
3. Abre la página de evaluaciones y selecciona la más reciente.
4. Revisa las tarjetas de métricas globales apoyándose en la guía.
5. Identifica las anomalías detectadas y comprende su significado.
6. Descarga el reporte PDF para compartirlo con su equipo.

### Escenario 2: Autor prepara material para portafolio

1. El usuario ejecuta una nueva evaluación con parámetros específicos.
2. Revisa el detalle de la evaluación en pantalla.
3. Descarga el reporte PDF con todos los indicadores y mapas.
4. Crea un experimento asociando las evaluaciones ejecutadas, declarando objetivo e hipótesis.
5. El sistema genera automáticamente la metadata de reproducibilidad en el reporte PDF.
6. Desde el explorador de experimentos, descarga el informe experimental completo (PDF + Markdown) y los artefactos asociados (PNG, CSV, JSON).
7. Utiliza los artefactos descargados en su portafolio profesional o artículo técnico.

### Escenario 3: Investigador revisa un experimento

1. El usuario navega al explorador de experimentos.
2. Visualiza la lista de todos los experimentos registrados.
3. Selecciona un experimento y revisa su información principal (objetivo, hipótesis, fecha).
4. Navega a las evaluaciones asociadas al experimento para análisis detallado.
5. Descarga el informe experimental completo y los reportes PDF de cada evaluación.

---

## 10. Criterios de Éxito

- **10.1**: Un revisor externo puede comprender el estado de un experimento sin necesidad de conocer la implementación técnica ni el contexto previo del proyecto.
- **10.2**: El 100% de las métricas del sistema cuentan con entrada en la guía de interpretación (definición, fórmula, interpretación, ejemplos).
- **10.3**: El 100% de las pantallas del subsistema de evaluaciones cuentan con documentación asociada.
- **10.4**: Cualquier evaluación ejecutada puede generar un reporte PDF descargable.
- **10.5**: Cada evaluación está asociada a un experimento que declara explícitamente su objetivo, hipótesis y evaluaciones incluidas.
- **10.6**: Todo reporte PDF incluye metadata de reproducibilidad (algorithm, algorithm_version, dataset, evaluation_id, generated_at, spec_version).
- **10.7**: El explorador de experimentos permite listar, ver, descargar y navegar experimentos en menos de 3 clics desde la página principal del sistema.
- **10.8**: Los artefactos generados (PDF, PNG, Markdown, CSV, JSON) son descargables individualmente desde el explorador de experimentos.

---

## 11. Supuestos y Dependencias

### Supuestos

- La generación de PDF se realizará mediante una librería del lado del servidor que pueda incrustar texto, tablas e imágenes PNG.
- La guía de interpretación se publicará como documentación estática accesible desde el sistema.
- Los experimentos existentes (`001-baseline-comparison`) se migrarán a la estructura formal definida en esta especificación, incluyendo la metadata de trazabilidad.
- El explorador de experimentos leerá la estructura de directorios del repositorio, no requerirá una base de datos separada.
- La metadata de reproducibilidad (`spec_version`) se definirá como un valor fijo para esta versión del spec e identificará la versión SPEC-004 en los PDF generados.

### Dependencias

- SPEC-003: El sistema de evaluación y métricas debe estar operativo (completado).
- Las evaluaciones deben incluir información de `files` en la respuesta de la API (implementado en SPEC-003).
- El frontend debe tener acceso a los archivos generados a través del endpoint de descarga existente.

---

## 12. Preguntas Abiertas

No hay preguntas abiertas. El alcance, las funcionalidades y los criterios de aceptación están definidos en las secciones anteriores.

---

## 13. Futuro (Fuera de Alcance)

- **Comparación consolidada entre experimentos**: Vista que permita seleccionar múltiples experimentos y comparar sus métricas globales lado a lado (ej: `001-baseline` vs `002-kmeans` vs `003-hybrid`), con gráficos de evolución y detección de tendencias.
- Exportación a formatos adicionales (DOCX, XLSX, HTML interactivo).
- Comparación visual lado a lado de dos evaluaciones en una misma pantalla.
- Generación automatizada de conclusiones basadas en IA (esta especificación solo contempla conclusiones basadas en reglas deterministas).
- Publicación remota de experimentos (compartir vía enlace).
- Integración con sistemas externos de documentación (Notion, Confluence).
- Panel de evolución temporal de métricas a través de múltiples experimentos.
