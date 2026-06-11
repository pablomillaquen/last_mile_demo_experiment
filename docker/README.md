# Docker

Archivos auxiliares para la infraestructura Docker del proyecto.

- `docker-compose.yml` en la raíz del proyecto orquesta todos los servicios.
- Cada servicio tiene su propio `Dockerfile` en su directorio correspondiente.

## Uso

```bash
# Iniciar todos los servicios
docker compose up -d

# Solo servicios principales (sin PgAdmin)
docker compose up -d postgres backend frontend

# Incluir herramientas opcionales
docker compose --profile tools up -d

# Ver logs
docker compose logs -f

# Detener todo
docker compose down
```
