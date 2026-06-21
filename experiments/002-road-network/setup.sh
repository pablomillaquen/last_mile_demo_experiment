#!/bin/bash
set -euo pipefail

# ──────────────────────────────────────────────────────────────
# Setup: Experiment 002 — Comparación Geodésica vs Vial
# ──────────────────────────────────────────────────────────────
# Requisitos:
#   - docker compose up -d (backend + osrm funcionando)
#   - API en http://localhost:8000/api
#   - Evaluaciones baseline existentes (Exp001, al menos una)
# ──────────────────────────────────────────────────────────────
# Uso: bash experiments/002-road-network/setup.sh
# ──────────────────────────────────────────────────────────────

API="${API_URL:-http://localhost:8000/api}"
EXP_FILE="experiments/002-road-network/experiment.json"

echo "=== Exp002 Setup: Comparación Geodésica vs Vial ==="

# ── Paso 1: Obtener evaluaciones existentes y encontrar baseline ──
echo ""
echo "--- Paso 1: Buscar evaluaciones baseline (Exp001) ---"

# Obtener ID del experimento 001
EXP1_ID=$(curl -sf "$API/experiments" | python3 -c "
import sys, json
data = json.load(sys.stdin)['data']
for exp in data:
    if exp.get('identifier') == '001-baseline-comparison':
        print(exp['id'])
        break
" 2>/dev/null || echo "")

if [ -z "$EXP1_ID" ]; then
    echo "[WARN] No se encontró Exp001. Usando evaluaciones baseline conocidas (IDs 2-7)."
    BASELINE_IDS="2,3,4,5,6,7"
else
    echo "[OK] Exp001 encontrado (ID: $EXP1_ID). Obteniendo evaluations..."
    BASELINE_IDS=$(curl -sf "$API/experiments/$EXP1_ID" | python3 -c "
import sys, json
data = json.load(sys.stdin)
eids = data.get('evaluation_ids', [])
print(','.join(str(e) for e in eids))
" 2>/dev/null || echo "2,3,4,5,6,7")
fi

echo "Evaluaciones baseline: $BASELINE_IDS"

# ── Paso 2: Para cada evaluación baseline, extraer parámetros ──
echo ""
echo "--- Paso 2: Extraer parámetros y crear pares ---"

PAIRS_JSON="[]"
IFS=',' read -ra IDS <<< "$BASELINE_IDS"

for BID in "${IDS[@]}"; do
    BID=$(echo "$BID" | xargs)
    echo ""
    echo "Procesando evaluación baseline ID: $BID"

    # Obtener parámetros de la evaluación baseline
    EVAL_DATA=$(curl -sf "$API/evaluations/$BID" | python3 -c "
import sys, json
data = json.load(sys.stdin)
params = data.get('parameters', {})
# Extraer campos relevantes excluyendo distance_mode
result = {
    'random_seed': params.get('random_seed'),
    'algorithm': params.get('algorithm', 'unknown'),
    'algorithm_version': params.get('algorithm_version', '1.0'),
    'near_delivery_threshold_km': params.get('near_delivery_threshold_km', 1.0),
    'ignored_delivery_ratio': params.get('ignored_delivery_ratio', 2.0),
    'dataset': params.get('dataset', 'Valparaíso Demo'),
}
print(json.dumps(result))
" 2>/dev/null || echo "{}")

    PARAMS=$(echo "$EVAL_DATA" | python3 -c "
import sys, json
p = json.loads(sys.stdin.read())
if not p.get('random_seed'):
    p['random_seed'] = $BID  # usar ID como fallback
print(json.dumps(p))
" 2>/dev/null)

    echo "  Parámetros: $PARAMS"

    # Crear evaluación geodésica
    GEO_BODY=$(echo "$PARAMS" | python3 -c "
import sys, json
p = json.loads(sys.stdin.read())
p['distance_mode'] = 'geodesic'
print(json.dumps(p))
")
    echo "  Creando evaluación geodésica..."
    GEO_RESP=$(curl -sf -X POST "$API/evaluations" \
        -H "Content-Type: application/json" \
        -d "$GEO_BODY" 2>/dev/null || echo "{}")
    GEO_ID=$(echo "$GEO_RESP" | python3 -c "import sys,json; print(json.load(sys.stdin).get('id',''))" 2>/dev/null)

    if [ -z "$GEO_ID" ]; then
        echo "  [ERROR] Falló creación geodésica para baseline $BID"
        continue
    fi
    echo "  [OK] Evaluation geodésica ID: $GEO_ID"

    # Crear evaluación vial
    VIAL_BODY=$(echo "$PARAMS" | python3 -c "
import sys, json
p = json.loads(sys.stdin.read())
p['distance_mode'] = 'vial'
print(json.dumps(p))
")
    echo "  Creando evaluación vial..."
    VIAL_RESP=$(curl -sf -X POST "$API/evaluations" \
        -H "Content-Type: application/json" \
        -d "$VIAL_BODY" 2>/dev/null || echo "{}")
    VIAL_ID=$(echo "$VIAL_RESP" | python3 -c "import sys,json; print(json.load(sys.stdin).get('id',''))" 2>/dev/null)

    if [ -z "$VIAL_ID" ]; then
        echo "  [ERROR] Falló creación vial para baseline $BID"
        continue
    fi
    echo "  [OK] Evaluation vial ID: $VIAL_ID"

    # Generar parameters_hash
    HASH=$(echo "$PARAMS" | python3 -c "
import sys, json, hashlib
p = json.loads(sys.stdin.read())
# Orden normalizado: random_seed, algorithm, algorithm_version, near_delivery_threshold_km, ignored_delivery_ratio, dataset, warehouse_lat, warehouse_lng
normalized = {
    'random_seed': p.get('random_seed'),
    'algorithm': p.get('algorithm'),
    'algorithm_version': p.get('algorithm_version'),
    'near_delivery_threshold_km': float(p.get('near_delivery_threshold_km', 1.0)),
    'ignored_delivery_ratio': float(p.get('ignored_delivery_ratio', 2.0)),
    'dataset': p.get('dataset'),
    # nota: warehouse_lat/lng se agregan desde el response de creación
}
# Por ahora, placeholder — los valores reales vienen del response de la evaluación
print('pending')
")

    PAIR_ITEM="{\"geodesic_id\": $GEO_ID, \"vial_id\": $VIAL_ID, \"parameters_hash\": \"$HASH\"}"
    PAIRS_JSON=$(echo "$PAIRS_JSON" | python3 -c "
import sys, json
pairs = json.loads(sys.stdin.read())
pairs.append($PAIR_ITEM)
print(json.dumps(pairs))
")
done

# ── Paso 3: Actualizar experiment.json con los pares ──
echo ""
echo "--- Paso 3: Actualizar experiment.json ---"

python3 << EOF
import json
with open('$EXP_FILE', 'r') as f:
    exp = json.load(f)
exp['evaluation_pairs'] = json.loads('''$PAIRS_JSON''')
with open('$EXP_FILE', 'w') as f:
    json.dump(exp, f, indent=4)
EOF

echo "[OK] experiment.json actualizado con ${#IDS[@]} pares"

# ── Paso 4: Sincronizar con Laravel ──
echo ""
echo "--- Paso 4: Sincronizar experimento con la base de datos ---"
docker compose exec -T backend php artisan experiments:sync 2>/dev/null && \
    echo "[OK] experiments:sync exitoso" || \
    echo "[WARN] experiments:sync falló — ejecutar manualmente"

echo ""
echo "=== Setup completo! ==="
echo "Revisar: experiments/002-road-network/experiment.json"
echo "Luego verificar con: curl -sf $API/experiments | python3 -m json.tool"
