# Documento Técnico v3 — Impacto de Red Vial y Analítica Visual en Rutas de Última Milla

**Estado**: Esbozo (sección 8 — pendiente de expandir)

**Versión**: 3.0 (planeada)

**Fecha**: 2026-06-23

## Resumen

Esta versión extiende el documento técnico v2 con los hallazgos de SPEC-008 (analítica visual comparativa), incorporando 4 nuevos hallazgos (H014–H017) sobre la interpretación visual de diferencias entre modelos geodésico y vial.

La contribución principal de esta versión es la transición de evidencia numérica (H012: +54.3% vial sobre geodésico) a evidencia visual: el split view, el filtrado de rutas y el aislamiento individual convierten el mapa en una herramienta analítica. Adicionalmente, se identifica una nueva limitación del instrumento visual (H017: ausencia de dirección de recorrido) que abre una línea de investigación futura (PI-018).

**Documento base**: `documento-tecnico-v2.md`. Todos los hallazgos previos (H001–H012) y validaciones (V001–V006) se preservan sin cambios.

---

## 8. Analítica Visual Comparativa (SPEC-008)

### 8.1 Motivación

SPEC-007 demostró que la diferencia entre rutas geodésicas y viales es visualmente detectable. SPEC-008 formaliza esta capacidad mediante un instrumento de investigación visual que permite:

- Comparación simultánea lado a lado (split view).
- Filtrado selectivo de rutas por visibilidad.
- Aislamiento individual con atenuación contextual.

### 8.2 Diseño de SplitView

Dos instancias sincronizadas de Leaflet MapContainer:

- **Mapa izquierdo**: modo geodésico (construido desde `from_lat/lng → to_lat/lng`).
- **Mapa derecho**: modo vial (construido desde `geometry` OSRM).

Ambos mapas comparten centro/zoom mediante eventos `moveend`/`zoomend` con flag `isSyncing` para evitar bucles infinitos. El color de cada ruta es consistente entre ambos paneles.

Ver captura `specs/008-visual-analytics-comparacion/assets/captures/02-split-view.png`.

### 8.3 RoutePanel e Interacción

El RoutePanel proporciona tres capacidades analíticas:

1. **Toggle on/off** (checkbox): oculta la ruta en ambos mapas simultáneamente. El estado se mantiene al alternar entre modo simple y split (CA10).
2. **Aislamiento** (clic en fila): atenúa las rutas no seleccionadas (opacity 0.2) en lugar de ocultarlas (RF10), preservando el contexto geográfico.
3. **Selección masiva**: botones "Seleccionar todas" / "Deseleccionar todas".

El panel es colapsable para no obstruir el mapa.

### 8.4 Resultados Observados

#### H014 — La comparación visual reduce el esfuerzo de interpretación

El split view permite identificar rápidamente la ruta con mayor divergencia (Ruta D, factor 2.00×) sin alternancia manual de modos. El observador reportó: "La diferencia entre un modo y otro se demuestra increíblemente bien."

#### H015 — El aislamiento de rutas aumenta la capacidad de análisis

La atenuación (vs ocultación completa) permite inspeccionar una ruta individual dentro de su contexto operacional completo. Fue la función más utilizada durante la validación. El observador reportó: "Permitió identificar una ruta correctamente, sin que las demás escondan el trazo."

Ver captura `specs/008-visual-analytics-comparacion/assets/captures/03-ruta-aislada.png`.

#### H016 — RoutePanel aporta control sin carga cognitiva

Contrario al riesgo de diseño identificado (PI-017: "más controles → más complejidad visual"), el panel se percibe como un complemento necesario. El observador reportó: "Estos controles no añaden ruido. Por el contrario, son muy útiles."

Ver captura `specs/008-visual-analytics-comparacion/assets/captures/04-filtrado-activo.png`.

#### H017 — La ausencia de dirección de recorrido limita la interpretación operacional

La principal dificultad de uso no está en los controles ni en la visualización comparativa, sino en la comunicación de dirección y secuencia de las rutas. El observador reportó: "No logro determinar qué dirección tomó la ruta" y sugirió puntos numerados o flechas de dirección como posibles soluciones.

Ver captura `specs/008-visual-analytics-comparacion/assets/captures/05-direccion-ambigua.png`.

### 8.5 Amenazas a la Validez

| Amenaza | Impacto | Mitigación |
|---------|---------|------------|
| n=1 observador | Generalización limitada | Documentar como estudio exploratorio (D016) |
| Respuesta conocida | M4 no medible cuantitativamente | M4 documentado como exploratorio |
| Conocimiento del sistema | Sesgo de confirmación | Hallazgos cualitativos como evidencia principal |
| Sin dirección de recorrido | Limitación no resuelta | Abre PI-018 para futura SPEC |

### 8.6 Preguntas Abiertas

#### PI-018 — Mecanismos visuales para secuencia y dirección

> ¿Qué mecanismos visuales permiten comunicar la secuencia operacional y dirección de una ruta logística (puntos numerados, flechas de movimiento, navegación textual) sin incrementar la carga cognitiva del analista?

Esta pregunta emerge directamente de H017 y constituye la línea de investigación más prometedora post-SPEC-008.

---

## Trazabilidad v3

- **Documento base**: v2 (secciones 1–7, hallazgos H001–H012, validaciones V001–V006)
- **SPEC origen**: SPEC-008
- **Hallazgos nuevos**: H014–H017
- **Preguntas nuevas**: PI-018
- **Decisiones**: D015, D016
- **Capturas**: `specs/008-visual-analytics-comparacion/assets/captures/`
- **Última actualización**: 2026-06-23
