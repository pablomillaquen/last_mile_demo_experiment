# Mediciones — SPEC-008: Visual Analytics para Comparación de Rutas

## Estado: Exploratorio (no experimental controlado)

Por decisión metodológica D016, el protocolo M4 **no se ejecutó como medición cuantitativa formal**. Las razones se documentan en `research/decisiones.md` (D016).

### Amenazas a la validez identificadas

| Amenaza | Detalle |
|---------|---------|
| n=1 observador | Investigador principal, no ciego, con conocimiento profundo del sistema |
| Respuesta conocida | La ruta de mayor divergencia (Ruta D, factor 2.00×) era conocida antes del primer intento |
| Efecto de aprendizaje | Después del primer intento, los siguientes miden memoria, no descubrimiento |
| Evaluación única | Misma evaluación repetida 10 veces — imposible distinguir interfaz vs aprendizaje |
| Sesgo de confirmación | El observador tiene interés en que la hipótesis se cumpla |

### Conclusión

Los hallazgos principales de SPEC-008 son cualitativos (H014–H017), no cuantitativos.

**HYP-008-01** (split view reduce tiempo de identificación): **No evaluada cuantitativamente**. La hipótesis sigue abierta para futuros estudios controlados con:
- Múltiples evaluaciones con distinta ruta de mayor divergencia
- Observadores externos (n>1)
- Protocolo ciego
- Control de efecto aprendizaje

**HYP-008-02** (filtrado reduce carga cognitiva): **Respondida parcialmente** por H016. El observador reportó que los controles "no añaden ruido" y "son muy útiles", indicando que el nivel de detalle visual implementado no introduce sobrecarga cognitiva.

### Referencias
- D016: `research/decisiones.md`
- H014–H017: `research/hallazgos.md`
- PI-016, PI-017: `research/preguntas-investigacion.md`
