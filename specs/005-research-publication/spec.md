# Feature Specification: Research Publication & Experiment Dissemination

**Feature Branch**: `005-research-publication`

**Created**: 2026-06-19

**Status**: Draft

## 1. Context

Hasta este punto, el proyecto ha construido la infraestructura necesaria para modelar operaciones logísticas de última milla, ejecutar evaluaciones reproducibles, generar métricas cuantitativas y documentar experimentos formales. El sistema actual administra paquetes georreferenciados, visualiza rutas en mapas interactivos, produce reportes PDF descargables y mantiene una base de datos de experimentos comparables.

Sin embargo, el valor generado no reside únicamente en el software desarrollado, sino también en el conocimiento obtenido a través de las evaluaciones, los experimentos y el análisis de métricas. Este conocimiento permanece disperso en archivos técnicos, reportes y documentación interna, sin una presentación unificada que permita comunicarlo a audiencias externas.

No existe actualmente un artículo de portafolio, un documento técnico de investigación, material de difusión profesional ni una biblioteca organizada de recursos visuales que capturen los hallazgos y aprendizajes del proyecto.

---

## 2. Hipótesis

La transformación de los resultados técnicos existentes en activos de divulgación estructurados (artículo de portafolio, documento de investigación, material para LinkedIn y biblioteca visual) permitirá comunicar el problema abordado, la metodología utilizada y los resultados obtenidos a audiencias técnicas y no técnicas, fortaleciendo el portafolio profesional del proyecto y preparando la base documental para futuras etapas de optimización y publicación académica.

---

## 3. Objetivo

Producir, organizar y presentar el conocimiento generado por el proyecto en un conjunto de activos de divulgación que permitan:

- Documentar formalmente el proceso de investigación.
- Respaldar futuras decisiones técnicas.
- Comunicar resultados a clientes y organizaciones interesadas.
- Fortalecer el portafolio profesional del proyecto.
- Preparar la base documental para un White Paper final.

---

## 4. Alcance

Esta especificación comprende:

1. **Artículo de portafolio**: Documento narrativo de alto nivel que explica el problema logístico, la motivación, la metodología, los resultados, los aprendizajes y los próximos pasos. Debe ser comprensible para lectores no técnicos manteniendo rigurosidad metodológica.

2. **Material para LinkedIn**: Publicación breve con elementos visuales que presente el proyecto, destaque aprendizajes clave y dirija tráfico al artículo principal.

3. **Documento técnico de investigación**: Referencia estructurada siguiendo principios de publicación científica que incluya introducción, problema, hipótesis, metodología, métricas, diseño experimental, resultados, análisis, limitaciones, conclusiones y trabajo futuro.

4. **Biblioteca de recursos visuales**: Organización y selección de mapas, capturas de pantalla, diagramas, tablas comparativas, gráficos y reportes PDF generados durante la investigación, listos para reutilización en publicaciones futuras.

5. **Narrativa de conexión**: Documento que establezca una narrativa coherente conectando las etapas de evaluación, experimentación, optimización automática, ciencia de datos, aprendizaje de modelos y el White Paper final.

6. **Resumen Ejecutivo**: Documento de 1–2 páginas dirigido a reclutadores, gerentes, clientes y potenciales socios. Debe incluir problema, hipótesis, metodología (1 párrafo), resultados clave, conclusiones y próximos pasos. Legible en menos de 3 minutos.

---

## 5. Exclusiones

Quedan explícitamente fuera del alcance de esta especificación:

- Modificaciones a algoritmos de asignación, evaluación o detección de anomalías.
- Nuevas funcionalidades en la plataforma (backend o frontend).
- Cambios en el modelo de datos o en las APIs existentes.
- Implementación de procesos de optimización automática, machine learning o inteligencia artificial.
- Desarrollo de nuevas visualizaciones o dashboards en la aplicación.
- Publicación efectiva en LinkedIn o Medium (solo se genera el contenido, no se realiza la publicación).
- Traducción de contenidos a otros idiomas.
- Recolección de nueva data o ejecución de nuevos experimentos.

---

## 6. Historias de Usuario

### H1: Artículo de Portafolio

**Como** profesional del proyecto
**Quiero** redactar un artículo narrativo de alto nivel que explique el problema logístico abordado, la metodología utilizada y los resultados obtenidos
**Para** disponer de un activo publicable en portafolio profesional que demuestre competencias en análisis logístico, modelado de datos y experimentación cuantitativa

### H2: Material para LinkedIn

**Como** profesional del proyecto
**Quiero** generar una publicación breve con elementos visuales que resuma los hallazgos clave y enlace al artículo completo
**Para** difundir el proyecto en redes profesionales y dirigir tráfico hacia el portafolio

### H3: Documento Técnico de Investigación

**Como** investigador o tomador de decisiones técnicas
**Quiero** acceder a un documento estructurado que detalle introducción, problema, hipótesis, metodología, métricas, diseño experimental, resultados, análisis, limitaciones, conclusiones y trabajo futuro
**Para** comprender a profundidad el rigor metodológico del proyecto y poder evaluar la validez de sus conclusiones

### H4: Biblioteca de Recursos Visuales

**Como** profesional del proyecto
**Quiero** organizar en un repositorio accesible todos los recursos visuales generados (mapas, capturas, diagramas, tablas, gráficos, PDFs)
**Para** reutilizarlos eficientemente en publicaciones futuras y en la documentación final del proyecto

### H5: Narrativa de Conexión

**Como** planificador del proyecto
**Quiero** disponer de un documento que conecte las etapas cumplidas (evaluación, experimentación) con las futuras (optimización, ciencia de datos, machine learning, White Paper)
**Para** mantener una visión coherente del proyecto y guiar las siguientes fases de investigación

### H6: Resumen Ejecutivo

**Como** reclutador, gerente, cliente o socio potencial
**Quiero** leer un documento de 1–2 páginas que resuma el problema, la metodología, los resultados clave y los próximos pasos
**Para** comprender el valor del proyecto en menos de 3 minutos sin necesidad de leer el documento técnico completo

---

## 7. Requisitos Funcionales

### RF1: Artículo de Portafolio

- RF1.1: El artículo debe incluir una sección de contexto operacional que describa el problema logístico de última milla abordado.
- RF1.2: El artículo debe explicar la motivación del proyecto y por qué se eligió este problema.
- RF1.3: El artículo debe describir la metodología general utilizada sin asumir conocimientos técnicos especializados.
- RF1.4: El artículo debe presentar los resultados obtenidos hasta la fecha, incluyendo métricas clave y hallazgos principales.
- RF1.5: El artículo debe documentar los aprendizajes alcanzados durante el desarrollo del proyecto.
- RF1.6: El artículo debe esbozar los próximos pasos de investigación y desarrollo.
- RF1.7: El artículo debe tener una extensión mínima de 1500 palabras.
- RF1.8: El artículo debe ser autocontenido: un lector sin conocimiento previo del proyecto debe poder comprenderlo.

### RF2: Material para LinkedIn

- RF2.1: La publicación debe tener un máximo de 3000 caracteres (incluyendo espacios).
- RF2.2: Debe incluir al menos un elemento visual representativo (mapa, gráfico o captura).
- RF2.3: Debe incluir un enlace al artículo de portafolio.
- RF2.4: Debe destacar entre 3 y 5 aprendizajes o resultados clave.
- RF2.5: Debe tener un tono profesional pero accesible.

### RF3: Documento Técnico de Investigación

- RF3.1: Debe incluir las siguientes secciones obligatorias: Introducción, Problema de Investigación, Hipótesis, Metodología, Descripción de Métricas, Diseño Experimental, Resultados, Análisis de Resultados, Limitaciones, Conclusiones, Trabajo Futuro.
- RF3.2: La metodología debe describir el proceso de evaluación, las variables controladas y las variables medidas.
- RF3.3: La descripción de métricas debe definir cada métrica utilizada, su fórmula y su interpretación.
- RF3.4: El diseño experimental debe detallar los experimentos realizados, incluyendo parámetros y condiciones.
- RF3.5: El análisis de resultados debe incluir interpretación de tendencias, patrones y anomalías observadas.
- RF3.6: Las limitaciones deben reconocer explícitamente las restricciones del estudio y su impacto en la generalización de resultados.
- RF3.7: El trabajo futuro debe enumerar líneas de investigación abiertas y prioridades sugeridas.
- RF3.8: Debe incluir referencias a los experimentos formales del proyecto (SPEC-003, SPEC-004).

### RF4: Biblioteca de Recursos Visuales

- RF4.1: Debe catalogar todos los mapas generados durante las evaluaciones, organizados por evaluación y experimento.
- RF4.2: Debe incluir capturas de pantalla de las pantallas principales del sistema.
- RF4.3: Debe incluir diagramas descriptivos de la arquitectura y el flujo del sistema.
- RF4.4: Debe incluir tablas comparativas de métricas entre evaluaciones.
- RF4.5: Debe incluir gráficos derivados de los datos de evaluación (si existen).
- RF4.6: Debe incluir los reportes PDF generados como artefactos descargables.
- RF4.7: Cada recurso debe tener metadatos mínimos: nombre, fuente (evaluación/experimento), fecha, descripción breve.
- RF4.8: Los recursos deben organizarse en una estructura de directorios predecible dentro del repositorio.

### RF5: Narrativa de Conexión

- RF5.1: Debe conectar explícitamente las siguientes fases en una línea temporal estructurada:
  ```
  Fase 1: Modelado Operacional  (SPEC-001)
  Fase 2: Evaluación             (SPEC-002, SPEC-003)
  Fase 3: Experimentación        (SPEC-004)
  Fase 4: Optimización Algorítmica
  Fase 5: Ciencia de Datos
  Fase 6: Aprendizaje de Modelos
  Fase 7: White Paper Final
  ```
- RF5.2: Para cada etapa, debe describir el objetivo, los entregables producidos y la dependencia con la etapa siguiente.
- RF5.3: Debe identificar qué preguntas de investigación quedan abiertas y qué etapa las abordaría.
- RF5.4: Debe servir como hoja de ruta para las siguientes fases del proyecto.
- RF5.5: El documento técnico de investigación debe constituir la fuente primaria de evidencia y referencias para los demás activos de divulgación. El artículo de portafolio y el material de LinkedIn deben derivarse de sus resultados y conclusiones, manteniendo consistencia narrativa y metodológica.

### RF6: Resumen Ejecutivo

- RF6.1: Debe tener una extensión máxima de 2 páginas.
- RF6.2: Debe incluir una descripción del problema logístico abordado en no más de 3 párrafos.
- RF6.3: Debe incluir la hipótesis del proyecto en no más de 2 oraciones.
- RF6.4: Debe describir la metodología general en un solo párrafo.
- RF6.5: Debe presentar los resultados clave utilizando métricas cuantitativas.
- RF6.6: Debe incluir las conclusiones principales del proyecto.
- RF6.7: Debe esbozar los próximos pasos de investigación y desarrollo.
- RF6.8: Debe ser legible y comprensible sin conocimiento previo del proyecto.

---

## 8. Requisitos No Funcionales

- RNF1: Todos los documentos deben redactarse en español.
- RNF2: Todos los documentos deben almacenarse en el repositorio del proyecto bajo `publications/`.
- RNF3: Los documentos deben utilizar formato Markdown para facilitar versionamiento y revisión.
- RNF4: Los recursos visuales deben almacenarse en `publications/assets/` con nombres descriptivos.
- RNF5: El documento técnico debe poder exportarse a PDF manteniendo formato y estructura.

---

## 9. Métricas

| Métrica | Descripción |
|---------|-------------|
| Activismo de difusión | Número de activos de divulgación producidos (artículo, post, doc técnico, biblioteca, narrativa, resumen ejecutivo) |
| Cobertura de hallazgos | Porcentaje de resultados de experimentos (SPEC-003/004) cubiertos en al menos un activo |
| Trazabilidad metodológica | Cantidad de referencias explícitas entre activos de divulgación y experimentos formales |
| Completitud del documento técnico | Porcentaje de secciones obligatorias (RF3.1) completadas con contenido sustantivo |
| Preparación para White Paper | Existencia de narrativa de conexión que cubra las 7 fases definidas (RF5.1) |

---

## 10. Criterios de Aceptación

- CA1: Los 6 activos de divulgación (artículo, post LinkedIn, documento técnico, biblioteca visual, narrativa de conexión, resumen ejecutivo) existen en el repositorio bajo `publications/`.
- CA2: El artículo de portafolio es legible y comprensible para un profesional no técnico del rubro logístico.
- CA3: El post de LinkedIn no excede 3000 caracteres e incluye al menos un elemento visual y un enlace al artículo.
- CA4: El documento técnico contiene las 11 secciones obligatorias definidas en RF3.1 con contenido sustantivo en cada una.
- CA5: La biblioteca visual contiene al menos 10 recursos catalogados con metadatos completos (nombre, fuente, fecha, descripción).
- CA6: La narrativa de conexión cubre las 7 fases definidas en RF5.1 con objetivos, entregables y dependencias.
- CA7: Todos los documentos están en formato Markdown dentro del repositorio.
- CA8: Los resultados de los experimentos SPEC-003 y SPEC-004 están referenciados en al menos un activo de divulgación.
- CA9: El resumen ejecutivo no excede 2 páginas y es comprensible sin conocimiento previo del proyecto.

---

## Clarifications

### Session 2026-06-19

- Q: ¿Debe el documento técnico servir como fuente primaria para los demás activos? → A: Sí, RF5.5 agregado: el documento técnico es la fuente primaria de evidencia; artículo y LinkedIn se derivan de él.
- Q: ¿Se debe agregar un sexto activo (Resumen Ejecutivo)? → A: Sí, RF6 agregado con H6, requisitos y CA9.
- Q: ¿Debe la narrativa de conexión usar una línea temporal explícita de 7 fases? → A: Sí, RF5.1 actualizado con formato de 7 fases (Modelado → Evaluación → Experimentación → Optimización → Data Science → ML → White Paper).
- Q: CA6 quedó desactualizado (decía "6 etapas", RF5.1 ahora tiene 7 fases) → A: CA6 corregido a "7 fases".

---

## Restricciones

Toda especificación debe cumplir la Constitución del proyecto.

Especialmente:
- **Evidencia antes de solución**: Los activos de divulgación deben basarse en resultados reales obtenidos de experimentos ejecutados, no en supuestos.
- **Decisiones medibles**: Las métricas de la sección 9 permiten medir el éxito de esta fase.
- **Complejidad incremental**: Esta fase no introduce nuevas capacidades técnicas; solo organiza y presenta conocimiento existente.
- **Conocimiento reutilizable (Principio VII)**: Esta especificación es una aplicación directa de este principio constitucional.

Las features que contradigan estos principios deben ser rechazadas o modificadas.

---

## Regla de Evolución

Una nueva feature debe cumplir al menos una de las siguientes condiciones:
1. Representa un problema operacional real.
2. Permite medir una característica del sistema.
3. Introduce una mejora cuantificable.
4. Permite comparar dos estrategias distintas.

**Cumplimiento**: Esta especificación cumple la condición 2 (permite medir la capacidad de divulgación del proyecto) y la condición 3 (introduce una mejora cuantificable en la organización y presentación del conocimiento generado). Adicionalmente, respeta el Principio VII (Conocimiento Reutilizable) de la Constitución.

Las features que no aporten evidencia o capacidad de medición no deben incorporarse al proyecto.
