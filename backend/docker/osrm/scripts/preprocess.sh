#!/bin/bash
set -euo pipefail

PBF_FILE="/data/valparaiso.osm.pbf"
PROFILE="${1:-/scripts/profiles/car.lua}"
DATA_DIR="/data"

echo "[preprocess] Iniciando preprocesamiento de $PBF_FILE"

cd "$DATA_DIR"

echo "[preprocess] 1/4 osrm-extract ..."
osrm-extract -p "$PROFILE" "$PBF_FILE"

echo "[preprocess] 2/4 osrm-contract ..."
osrm-contract "valparaiso.osrm"

echo "[preprocess] 3/4 osrm-partition ..."
osrm-partition "valparaiso.osrm"

echo "[preprocess] 4/4 osrm-customize ..."
osrm-customize "valparaiso.osrm"

echo "[preprocess] Preprocesamiento completado."
ls -lh /data/valparaiso.osrm*
