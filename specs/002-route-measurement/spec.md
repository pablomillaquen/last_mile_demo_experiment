# Feature Specification: Route Measurement — Distancia, Tiempo y Secuencia

**Feature Branch**: `002-route-measurement`

**Created**: 2026-06-12

**Status**: Draft

## 1. Contexto

En la fase anterior (001 — Last Mile Operation) se construyó la capacidad de
registrar paquetes, crear rutas, asignar paquetes manualmente y visualizar la
distribución geográfica en un mapa.

Sin embargo, un operador no puede responder preguntas fundamentales:

- ¿Cuál es la distancia total que recorre una ruta?
- ¿Cuánto tiempo estimado toma completarla?
- ¿Qué ruta es más eficiente entre dos con la misma cantidad de paquetes?
- ¿El recorrido tiene cruces o zigzags innecesarios?

Actualmente la única métrica disponible es la cantidad de paquetes por ruta, lo
que no permite distinguir entre una ruta eficiente y una ineficiente si ambas
tienen la misma carga.

**Impacto operacional**: Sin métricas de distancia y tiempo, las decisiones de
asignación son cualitativas y no comparables. No existe línea base objetiva para
medir si una optimización futura representa una mejora real.

---

## 2. Hipótesis

> Visualizar la secuencia de entrega y calcular métricas de distancia y tiempo
> permitirá identificar objetivamente rutas ineficientes que no son evidentes
> observando únicamente la ubicación de los paquetes.

Las palabras clave de esta fase son: **visualizar**, **medir** y **comparar**.

---

## 3. Objetivo

Que un operador logístico pueda, al finalizar esta fase, afirmar con evidencia
cuantificable:

```
Ruta A
- 30 paquetes
- 87 km
- 3h 05m estimadas

Ruta B
- 30 paquetes
- 42 km
- 1h 35m estimadas
```

Y concluir que, aunque ambas rutas tienen la misma carga de trabajo, la Ruta A
es significativamente más costosa.

---

## 4. Alcance

- Configuración de un centro de distribución (bodega) con ubicación geográfica
  fija, que sirve como punto de inicio y fin de todas las rutas.
- Visualización del orden de entrega (secuencia numérica) de los paquetes
  asignados a una ruta, tanto en tabla como en mapa.
- Visualización de líneas que conectan las entregas en el mapa mostrando el
  recorrido completo: bodega → P1 → P2 → ... → PN → bodega.
- Cálculo de distancia total de ruta usando distancia en línea recta entre
  puntos consecutivos (fórmula de Haversine).
- Cálculo de tiempo estimado de recorrido basado en la distancia total y una
  velocidad promedio configurable por el operador.
- Exposición de nuevas métricas operativas:
  - Distancia total por ruta.
  - Distancia promedio por entrega.
  - Tiempo estimado por ruta.
  - Ruta más larga y ruta más corta (por distancia).
- Velocidad promedio configurable desde la interfaz (valor inicial 30 km/h).

---

## 5. Exclusiones

Quedan explícitamente fuera de esta fase:

- Tráfico real o histórico.
- API de direcciones (Google Directions, OSRM, GraphHopper, Valhalla).
- Algoritmos de optimización automática de rutas.
- Clustering geográfico automático.
- Inteligencia artificial o machine learning.
- Cálculo de distancia por red vial (solo Haversine).
- Tiempo de servicio por entrega (descarga, firma, etc.).
- Ventanas horarias o restricciones de tiempo.

---

## 6. Historias de Usuario

### US6 — Configuración del Centro de Distribución

**Como** operador logístico

**Quiero** definir la ubicación de la bodega

**Para** que todas las rutas tengan un punto de inicio y fin común.

**Evidencia**:
- La bodega aparece en el mapa.
- Todas las rutas comienzan desde la bodega y retornan a ella.

### US7 — Visualización de Secuencia

**Como** operador logístico

**Quiero** visualizar el orden de entrega de los paquetes en una ruta

**Para** entender el recorrido completo que debe realizar el vehículo.

**Evidencia**:
- Los paquetes muestran su número de secuencia (1, 2, 3, 4…).
- La secuencia es visible tanto en la tabla de detalle de ruta como en el mapa.

### US8 — Visualización de Recorrido

**Como** operador logístico

**Quiero** visualizar líneas que conecten las entregas en el mapa

**Para** identificar cruces, zigzags y recorridos innecesarios.

**Evidencia**:
- El mapa muestra una línea poligonal que conecta los puntos en orden:
  `Bodega → 1 → 2 → 3 → ... → N → Bodega`.
- Inicialmente las líneas son rectas (distancia Haversine entre puntos).

### US9 — Métricas de Distancia

**Como** operador logístico

**Quiero** conocer la distancia total estimada de una ruta

**Para** comparar rutas entre sí y evaluar su eficiencia relativa.

**Evidencia**:
- Cada ruta muestra en su detalle:
  - Distancia total (en km).
  - Distancia promedio por entrega (en km).

### US10 — Métricas de Tiempo

**Como** operador logístico

**Quiero** conocer el tiempo estimado de recorrido

**Para** evaluar la carga operacional de cada ruta.

**Evidencia**:
- Cada ruta muestra el tiempo estimado total.
- El tiempo se basa en la distancia total y una velocidad promedio configurable
  (valor inicial 30 km/h).

---

## 7. Requisitos Funcionales

### RF1 — Centro de Distribución

1.1. El sistema debe permitir configurar una ubicación geográfica (latitud,
longitud) para el centro de distribución (bodega).

1.2. Debe existir un valor por defecto para la bodega en caso de que el
operador no lo configure.

1.3. La bodega debe ser un punto único compartido por todas las rutas.

1.4. La bodega debe aparecer en el mapa con un marcador distintivo.

### RF2 — Secuencia de Entrega

2.1. La tabla de detalle de ruta debe mostrar el número de secuencia de cada
paquete asignado.

2.2. El mapa debe mostrar el número de secuencia en el marcador o tooltip de
cada paquete asignado a la ruta.

2.3. La secuencia se determina por el orden de asignación (campo `sequence` en
la tabla `route_packages`).

### RF3 — Visualización de Recorrido

3.1. El mapa debe dibujar una línea poligonal que conecte:
`Bodega → Paquete 1 → Paquete 2 → ... → Paquete N → Bodega`.

3.2. La línea debe ser específica para cada ruta (color distintivo).

3.3. Las líneas deben actualizarse dinámicamente al asignar o desasignar
paquetes.

### RF4 — Cálculo de Distancia

4.1. La distancia entre dos puntos debe calcularse usando la fórmula de
Haversine (distancia en línea recta sobre la superficie terrestre).

4.2. La distancia total de una ruta debe ser la suma de:
`d(Bodega, P1) + d(P1, P2) + ... + d(PN, Bodega)`.

4.3. La distancia promedio por entrega debe ser: `distancia_total / N`.

4.4. Las distancias deben expresarse en kilómetros con precisión de dos
decimales.

### RF5 — Cálculo de Tiempo

5.1. El tiempo estimado de una ruta debe calcularse como:
`tiempo_estimado = distancia_total / velocidad_promedio`.

5.2. El tiempo debe mostrarse en formato `Xh Ym` (horas y minutos).

5.3. La velocidad promedio debe ser configurable por el operador (en km/h).

5.4. El valor inicial de velocidad promedio debe ser 30 km/h.

### RF6 — Métricas Operativas

6.1. El endpoint `/api/metrics` debe incluir:
- Distancia total de cada ruta.
- Distancia promedio por entrega.
- Tiempo estimado por ruta.
- Ruta más larga (mayor distancia).
- Ruta más corta (menor distancia).

---

## 8. Requisitos No Funcionales

- **Rendimiento**: El cálculo de distancia debe ejecutarse en el backend y ser
  eficiente para rutas de hasta 100 paquetes.
- **Precisión**: La fórmula de Haversine debe implementarse correctamente (radio
  terrestre: 6.371 km).
- **Consistencia**: Todas las rutas deben usar la misma velocidad promedio para
  el cálculo de tiempo, a menos que el operador la modifique.
- **Sin dependencias externas**: El cálculo de distancia no debe depender de
  APIs externas, servicios de geocodificación, ni librerías de terceros.

---

## 9. Métricas

| Métrica | Estado |
|---------|--------|
| Total paquetes | Existente |
| Total rutas | Existente |
| Paquetes por ruta | Existente |
| Sin asignar | Existente |
| Distancia total de ruta | **Nueva** |
| Distancia promedio por entrega | **Nueva** |
| Tiempo estimado | **Nueva** |
| Ruta más larga | **Nueva** |
| Ruta más corta | **Nueva** |

---

## 10. Criterios de Aceptación

1. Un operador puede configurar la ubicación de la bodega desde la interfaz.
2. El mapa muestra la bodega como un punto de inicio distinto.
3. La tabla de detalle de ruta muestra el número de secuencia de cada paquete.
4. El mapa muestra el número de secuencia en los marcadores de paquetes
   asignados.
5. El mapa dibuja una línea poligonal conectando bodega → paquetes → bodega
   para cada ruta.
6. Al asignar un nuevo paquete a una ruta, la línea y la secuencia se
   actualizan.
7. Al desasignar un paquete, la línea y la secuencia se actualizan.
8. La distancia total de una ruta se calcula y muestra correctamente.
9. La distancia promedio por entrega se calcula y muestra correctamente.
10. El tiempo estimado se calcula usando la velocidad promedio configurada y se
    muestra en formato `Xh Ym`.
11. El operador puede cambiar la velocidad promedio y las métricas se
    recalculan.
12. El endpoint `/api/metrics` incluye las nuevas métricas de distancia y
    tiempo.
13. Todas las métricas son consistentes (misma velocidad promedio para todas las
    rutas mientras no se modifique).

---

## Restricciones

Toda especificación debe cumplir la Constitución del proyecto.

Especialmente:
- **Evidencia antes de solución**: Esta fase no implementa optimización, solo
  medición. La optimización queda para fases futuras cuando existan datos
  comparables.
- **Decisiones medibles**: Cada nueva métrica permite comparar rutas
  objetivamente.
- **Complejidad incremental**: Se usa Haversine (sin dependencias externas) en
  lugar de APIs de direcciones o motores de ruteo.
- **Visualización como análisis**: Las líneas de recorrido y la secuencia
  numérica permiten identificar patrones visualmente.
- **Docker First**: Todas las funcionalidades, incluido el cálculo de
  Haversine, deben ejecutarse dentro de los contenedores existentes sin
  dependencias externas.

---

## Regla de Evolución

Esta feature cumple múltiples condiciones:

1. **Permite medir una característica del sistema**: Distancia y tiempo por
   ruta.
2. **Introduce una mejora cuantificable**: Capacidad de comparar rutas por
   métricas objetivas.
3. **Representa un problema operacional real**: Operadores necesitan saber si
   una ruta es eficiente.
