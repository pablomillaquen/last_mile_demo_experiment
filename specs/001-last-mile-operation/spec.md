# Feature Specification: Simulación de Operación Logística — Asignación Manual

**Feature Branch**: `001-last-mile-operation`

**Created**: 2026-06-10

**Status**: Draft

## 1. Contexto

Actualmente no existe una representación digital de la operación logística.
Los operadores asignan paquetes a rutas sin herramientas visuales que
permitan evaluar la eficiencia de las decisiones.

Sin un mapa ni métricas objetivas, las ineficiencias —como rutas que cruzan
la ciudad varias veces, paquetes cercanos asignados a rutas distintas o
concentraciones geográficas desbalanceadas— pasan desapercibidas.

**Impacto operacional**: Las decisiones de asignación no pueden evaluarse
objetivamente, por lo que no existe una línea base para medir si una
optimización futura representa una mejora real.

---

## 2. Hipótesis

La visualización geográfica y las métricas operativas permitirán identificar
patrones de asignación ineficientes y establecer una línea base cuantificable
para futuras estrategias de optimización.

---

## 3. Objetivo

Permitir que un operador logístico registre paquetes con ubicación geográfica,
cree rutas de distribución, asigne paquetes manualmente y visualice la
distribución en un mapa para identificar patrones ineficientes.

---

## 4. Alcance

- Registro, edición y eliminación de paquetes con dirección y coordenadas
  geográficas
- Creación, edición y eliminación de rutas de distribución
- Asignación manual de paquetes a rutas
- Visualización geográfica de paquetes y rutas en un mapa interactivo
- Métricas operativas básicas

---

## 5. Modelo de Dominio Inicial

### Package

Representa un paquete a entregar.

Atributos:
- `id`: Identificador único
- `received_at`: Fecha y hora de recepción en el centro de distribución
- `tracking_number`: Número de seguimiento externo
- `recipient_name`: Nombre del destinatario
- `delivery_address`: Dirección de entrega
- `district`: Distrito o comuna
- `city`: Ciudad
- `latitude`: Coordenada de latitud
- `longitude`: Coordenada de longitud
- `created_at`: Fecha de creación del registro
- `updated_at`: Fecha de última modificación

### Route

Representa una ruta de distribución.

Atributos:
- `id`: Identificador único
- `name`: Nombre descriptivo de la ruta
- `route_date`: Fecha de operación
- `notes`: Notas u observaciones de la ruta
- `created_at`: Fecha de creación del registro
- `updated_at`: Fecha de última modificación

### RoutePackage

Representa la asignación de un paquete a una ruta.

Atributos:
- `id`: Identificador único
- `route_id`: Referencia a la ruta
- `package_id`: Referencia al paquete
- `sequence`: Orden de entrega dentro de la ruta
- `assigned_at`: Fecha y hora de asignación
- `created_at`: Fecha de creación del registro
- `updated_at`: Fecha de última modificación

---

## 6. Exclusiones

- Asignación automática o algoritmos de optimización
- Cálculo de distancias reales o tiempos de viaje
- Geocodificación automática de direcciones (el operador ingresa coordenadas
  manualmente)
- Restricciones de capacidad
- Vehículos
- Roles, permisos y autenticación de usuarios
- Importación o exportación masiva de datos

---

## 7. Historias de Usuario

### Historia 1 — Registro de paquetes (P1)

**Como** operador logístico
**Quiero** registrar paquetes con su dirección y coordenadas geográficas
**Para** mantener un inventario digital de los paquetes a entregar

**Criterios de aceptación**:
1. **Dado** que tengo los datos de un paquete,
   **cuando** completo el formulario de registro,
   **entonces** el paquete queda almacenado y visible en el listado.
2. **Dado** que un paquete existe,
   **cuando** lo selecciono para editar,
   **entonces** puedo modificar sus datos y guardar los cambios.
3. **Dado** que un paquete existe,
   **cuando** lo elimino,
   **entonces** desaparece del listado y de cualquier ruta a la que estuviera
   asignado.

### Historia 2 — Creación de rutas (P1)

**Como** operador logístico
**Quiero** crear rutas de distribución
**Para** organizar los paquetes en grupos de entrega

**Criterios de aceptación**:
1. **Dado** que necesito una nueva ruta,
   **cuando** la creo con un nombre y fecha,
   **entonces** la ruta aparece en el listado de rutas disponible.

### Historia 3 — Asignación manual (P1)

**Como** operador logístico
**Quiero** asignar paquetes a rutas manualmente
**Para** decidir qué paquetes forman parte de cada ruta

**Criterios de aceptación**:
1. **Dado** que existen paquetes sin asignar y rutas disponibles,
   **cuando** selecciono un paquete y lo asigno a una ruta,
   **entonces** el paquete aparece como parte de esa ruta.
2. **Dado** que un paquete está asignado a una ruta,
   **cuando** lo desasigno,
   **entonces** el paquete vuelve al pool de paquetes sin asignar.

### Historia 4 — Visualización geográfica (P2)

**Como** operador logístico
**Quiero** ver los paquetes y rutas en un mapa interactivo
**Para** detectar agrupaciones ineficientes o desbalance geográfico

**Criterios de aceptación**:
1. **Dado** que existen paquetes registrados,
   **cuando** accedo a la vista de mapa,
   **entonces** cada paquete se muestra como un marcador en su ubicación.
2. **Dado** que un paquete está asignado a una ruta,
   **cuando** lo visualizo en el mapa,
   **entonces** el marcador se distingue por el color o ícono de esa ruta.

### Historia 5 — Métricas operativas (P2)

**Como** operador logístico
**Quiero** visualizar métricas básicas de la operación
**Para** comprender el estado actual y detectar desbalances en la asignación

**Criterios de aceptación**:
1. **Dado** que existen paquetes y rutas registrados,
   **cuando** accedo al panel de métricas,
   **entonces** veo la cantidad total de paquetes, cantidad de rutas,
   paquetes por ruta y paquetes sin asignar.
2. **Dado** que realizo una asignación o desasignación,
   **cuando** reviso las métricas,
   **entonces** los valores reflejan el cambio inmediatamente.

---

## 8. Requisitos Funcionales

- **RF-001**: El sistema debe permitir registrar paquetes con dirección y
  coordenadas geográficas.
- **RF-002**: El sistema debe permitir listar, editar y eliminar paquetes.
- **RF-003**: El sistema debe permitir crear rutas con nombre y fecha de
  operación.
- **RF-004**: El sistema debe permitir listar, editar y eliminar rutas.
- **RF-005**: El sistema debe permitir asignar paquetes a rutas manualmente.
- **RF-006**: El sistema debe permitir desasignar paquetes de una ruta.
- **RF-007**: El sistema debe mostrar los paquetes sobre un mapa geográfico
  interactivo.
- **RF-008**: El sistema debe distinguir visualmente los paquetes según la
  ruta a la que pertenecen.
- **RF-009**: El sistema debe mostrar métricas: cantidad total de paquetes,
  cantidad de rutas, paquetes por ruta y paquetes sin asignar.

---

## 9. Requisitos No Funcionales

- **RNF-001**: El mapa debe cargarse en menos de 3 segundos en una conexión
  de banda ancha estándar.
- **RNF-002**: Las operaciones de registro, edición y asignación deben
  completarse en menos de 2 segundos desde la confirmación del usuario.

---

## 10. Métricas

- Cantidad de paquetes registrados
- Cantidad de rutas creadas
- Paquetes por ruta (promedio, mínimo y máximo)
- Paquetes sin asignar

---

## 11. Criterios de Aceptación

- Un operador puede registrar, listar, editar y eliminar paquetes.
- Un operador puede crear, listar, editar y eliminar rutas.
- Un operador puede asignar y desasignar paquetes de rutas.
- Los paquetes se visualizan como marcadores en un mapa geográfico.
- Los paquetes asignados se distinguen visualmente por ruta en el mapa.
- Las métricas (paquetes totales, rutas, paquetes por ruta, paquetes sin
  asignar) se actualizan después de cada operación.

---

## Restricciones

Esta especificación cumple la Constitución del proyecto:

- **Evidencia antes de solución**: La simulación establece la línea base del
  problema de asignación manual antes de introducir cualquier optimización.
- **Decisiones medibles**: Las cuatro métricas definidas son observables y
  cuantificables.
- **Complejidad incremental**: La feature se limita a capacidades mínimas sin
  algoritmos de optimización, vehículos ni restricciones de capacidad.
- **Optimizaciones comparables**: La asignación manual documentada servirá
  como baseline para comparar estrategias futuras.
- **Visualización como análisis**: El mapa interactivo es el medio principal
  para evidenciar las ineficiencias.
- **Docker First**: La implementación debe ejecutarse íntegramente en
  contenedores Docker.

---

## Regla de Evolución

Esta feature cumple:
1. **Condición 1**: Representa un problema operacional real (asignación manual
   sin herramientas visuales).
2. **Condición 2**: Permite medir características del sistema (métricas
   operativas).
3. **Condición 4**: Permite comparar estrategias (la asignación manual es la
   baseline para optimizaciones futuras).
