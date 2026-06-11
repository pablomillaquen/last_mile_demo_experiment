# Last Mile Demo — Frontend (Next.js 14)

Aplicación web del experimento de simulación logística. Permite a un operador gestionar paquetes, crear rutas, asignar paquetes manualmente y visualizar la distribución geográfica para identificar patrones ineficientes.

## Tecnologías

- Next.js 14 (TypeScript)
- React 18
- Leaflet + OpenStreetMap (mapa interactivo, sin API key)
- Tailwind CSS 3

## Páginas

| Ruta | Descripción |
|------|-------------|
| `/packages` | Lista de paquetes con opción de eliminar |
| `/packages/create` | Formulario para registrar paquete |
| `/routes` | Lista de rutas con opción de eliminar |
| `/routes/create` | Formulario para crear ruta |
| `/routes/[id]` | Detalle de ruta con asignación/desasignación de paquetes |
| `/map` | Mapa interactivo con paquetes coloreados por ruta |
| `/dashboard` | Métricas operativas (total paquetes, rutas, asignados por ruta, no asignados) |

## API

El frontend se comunica con `http://localhost:8000/api` mediante fetch.

```typescript
// Tipos principales (src/lib/api.ts)
Package  { id, tracking_number, recipient_name, address, city, district, latitude, longitude, assigned }
Route    { id, name, description, date, route_packages_count }
RoutePackage { id, package_id, route_id, sequence, assigned_at, package: Package }
```

## Despliegue

El servicio se ejecuta dentro de Docker Compose. No se despliega de forma independiente.

```bash
docker compose up -d frontend
# Abrir http://localhost:3000
```

## Licencia

MIT
