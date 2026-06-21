#!/bin/bash
set -euo pipefail

SOURCE_URL="https://download.geofabrik.de/south-america/chile-latest.osm.pbf"
SOURCE_FILE="/data/chile-latest.osm.pbf"
OUTPUT_FILE="/data/valparaiso.osm.pbf"
BBOX="-71.70,-33.15,-71.20,-32.90"

echo "[download-osm] Obteniendo datos OSM para Gran Valparaíso (bounding box: $BBOX)"

# Cachear la fuente OSM para evitar redescargas
if [ -f "$SOURCE_FILE" ]; then
    echo "[download-osm] Fuente OSM cacheada encontrada: $SOURCE_FILE"
else
    echo "[download-osm] Descargando fuente OSM desde $SOURCE_URL ..."
    wget -q --show-progress "$SOURCE_URL" -O "$SOURCE_FILE"
    echo "[download-osm] Descarga completada: $SOURCE_FILE"
fi

echo "[download-osm] Extrayendo bounding box Gran Valparaíso ..."
osmium extract -b "$BBOX" "$SOURCE_FILE" -o "$OUTPUT_FILE"

echo "[download-osm] Extracción completada: $OUTPUT_FILE"
ls -lh "$OUTPUT_FILE"
