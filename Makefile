.PHONY: prepare-osrm up down logs

# ──────────────────────────────────────────────
# OSRM — Preparación del grafo de Gran Valparaíso
# ──────────────────────────────────────────────
# Requisitos:
#   - Docker Compose
#   - ~1 GB RAM libre para el preprocesamiento
#   - ~250 MB de disco para el grafo resultante
# Tiempo estimado: ~7 minutos
# ──────────────────────────────────────────────

prepare-osrm:
	@echo "=== Paso 1/2: Descargar datos OSM y extraer bounding box Gran Valparaíso ==="
	@echo "    Bounding box: -71.70,-33.15,-71.20,-32.90"
	@echo "    Esto puede tomar unos minutos (dependiendo de la conexión)..."
	docker compose run --rm osrm-prepare
	@echo ""
	@echo "=== Paso 2/2: Preprocesar grafo OSRM (osrm-extract, osrm-contract, osrm-partition, osrm-customize) ==="
	@echo "    Tiempo estimado: ~7 minutos. RAM peak: ~1 GB."
	docker compose run --rm osrm-prepare /bin/bash /scripts/preprocess.sh
	@echo ""
	@echo "=== OSRM listo! Inicia el servidor con: make up ==="
	@echo "    docker compose up -d osrm"

up:
	docker compose up -d

down:
	docker compose down

logs:
	docker compose logs -f
