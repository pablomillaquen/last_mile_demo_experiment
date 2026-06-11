<!--
SYNC IMPACT REPORT
Version change: 1.0.0 → 1.1.0
Modified principles: N/A (amendments only)
Added sections: Principle VIII (Docker First), architecture.md
Removed sections: N/A
Templates requiring updates:
  - .specify/templates/plan-template.md ✅ no changes needed
  - .specify/templates/spec-template.md ✅ no changes needed
  - .specify/templates/tasks-template.md ✅ no changes needed
  - .specify/templates/checklist-template.md ✅ no changes needed
  - All command files in extensions/ ✅ no changes needed
New artifacts:
  - .specify/memory/architecture.md ✅ created
  - .specify/memory/phases/ ✅ created
  - docker-compose.yml ✅ created
  - docker/ ✅ created
Follow-up TODOs: None
-->

# Sistema de Optimización de Rutas de Última Milla — Constitución

## Principios Fundamentales

### I. Evidencia Antes de Solución

Ninguna solución de optimización será implementada sin antes existir una
representación funcional del problema que intenta resolver. Las primeras fases
deben enfocarse en reproducir escenarios reales de operación logística mediante
procesos manuales y métricas observables.

**Justificación**: La optimización carece de valor si no existe una línea base
contra la cual comparar resultados.

### II. Decisiones Medibles

Toda funcionalidad incorporada al sistema debe permitir la obtención de métricas
objetivas. Ejemplos: cantidad de paquetes asignados, distancia recorrida, tiempo
estimado de distribución, tiempo de procesamiento, cantidad de vehículos
utilizados, capacidad utilizada.

**Justificación**: Las decisiones basadas únicamente en percepción no permiten
validar mejoras reales.

### III. Complejidad Incremental

Cada fase debe incorporar únicamente la complejidad necesaria para validar sus
objetivos. Se evitará implementar funcionalidades no relacionadas con la
hipótesis actualmente en evaluación. Quedan excluidas durante las primeras
etapas: roles, permisos, autenticación avanzada, facturación, gestión de
clientes, notificaciones e integraciones empresariales.

**Justificación**: La complejidad innecesaria dificulta la validación de los
experimentos.

### IV. Modelado de Escenarios Reales

Aunque el sistema es una simulación, las entidades y procesos deben reflejar
operaciones logísticas reales: paquetes con ubicación geográfica, asignación de
paquetes a rutas, vehículos con capacidad limitada y restricciones operativas.

**Justificación**: Los resultados obtenidos deben poder extrapolarse a
situaciones reales.

### V. Optimizaciones Comparables

Toda nueva estrategia deberá coexistir temporalmente con la estrategia anterior
para permitir comparaciones objetivas. Ejemplos: asignación manual vs
agrupación automática, ruta original vs ruta optimizada, algoritmo A vs
algoritmo B.

**Justificación**: Una optimización no puede considerarse exitosa si no existe
evidencia cuantificable de mejora.

### VI. Visualización como Análisis

Siempre que sea posible, los resultados deberán representarse visualmente
mediante mapas, diagramas, gráficos o comparaciones geográficas.

**Justificación**: Los problemas de distribución suelen ser más evidentes
visualmente que mediante tablas de datos.

### VII. Conocimiento Reutilizable

Cada etapa del proyecto deberá generar documentación técnica y material
educativo que permita explicar: el problema observado, la solución propuesta,
las decisiones tomadas y los resultados obtenidos.

**Justificación**: El proyecto tiene un doble propósito: construcción de
software y generación de contenido técnico para portafolio y blog.

### VIII. Docker First (Contenerización)

Todo componente del sistema debe ejecutarse mediante contenedores Docker.
El entorno de desarrollo, las pruebas y la eventual distribución deben
reproducirse íntegramente a través de `docker-compose.yml` sin depender de
instalaciones locales adicionales.

**Justificación**: Garantizar reproducibilidad absoluta del entorno, eliminar
conflictos de dependencias locales y permitir agregar o reemplazar componentes
(PostGIS, OSRM, Valhalla, Redis, RabbitMQ, motores Python, OR-Tools) sin
contaminar el entorno de desarrollo.

## Alcance Inicial

La primera fase del proyecto tendrá como objetivo exclusivo modelar una
operación logística básica de última milla.

Capacidades mínimas:
- Registro de paquetes
- Registro de rutas
- Asignación manual de paquetes
- Visualización geográfica
- Obtención de métricas básicas

No se implementarán algoritmos de optimización durante esta etapa.

Stack Docker inicial:
```text
Docker
├── Laravel API (backend/)
├── PostgreSQL
├── NextJS (frontend/)
└── PgAdmin (opcional)
```

Servicios externos:
- **Mapas**: Leaflet + OpenStreetMap (gratuito, sin API key, reproducible en Docker)

## Evolución Esperada

El proyecto evolucionará mediante fases sucesivas orientadas a validar hipótesis
específicas. Ejemplos de futuras etapas:
- Simulación de operación logística
- Agrupación geográfica automática
- Optimización de secuencia de entregas
- Restricciones de capacidad
- Balanceo de carga entre vehículos
- Optimización multi-ruta
- Simulación en tiempo real
- Comparación de algoritmos de optimización
- Motor de decisiones asistido por IA

La incorporación de nuevas fases dependerá de la evidencia obtenida en las
etapas anteriores.

## Governance

Esta Constitución prevalece sobre cualquier otra práctica de desarrollo,
decisión técnica o convención del proyecto. Todas las propuestas de cambio
(PRs, ramas, experimentos) deben verificar cumplimiento explícito con los
principios establecidos antes de ser aceptadas.

Las enmiendas a esta Constitución requieren:
- Documentación de la evidencia que motiva el cambio.
- Aprobación explícita del equipo.
- Plan de migración desde la regla anterior.
- Incremento de versión conforme a la política de versionado semántico.

La complejidad debe justificarse: toda desviación de los principios debe
explicarse y documentarse. Para guías de desarrollo en tiempo de ejecución,
consultar el archivo de contexto del agente (AGENTS.md).

Criterio de éxito del proyecto:
- Representar un problema real de distribución de última milla.
- Medir objetivamente dicho problema.
- Implementar soluciones progresivas.
- Demostrar mejoras cuantificables.
- Generar documentación técnica reproducible.
- Servir como caso de estudio para portafolio profesional y contenido técnico.

Principio rector: No construiremos optimizaciones porque parecen interesantes.
Construiremos optimizaciones porque los datos demuestren que son necesarias y
porque podamos medir claramente el beneficio que aportan.

**Versión**: 1.1.0 | **Ratificada**: 2026-06-10 | **Última Enmienda**: 2026-06-10
