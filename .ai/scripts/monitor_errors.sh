#!/usr/bin/env bash
# monitor_errors.sh - Monitora erros em tempo real
# Uso: bash monitor_errors.sh [segundos]

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ENV_FILE="$(dirname "$SCRIPT_DIR")/../.env"

INTERVAL=${1:-10}
LOG_DIR="/var/www/html/capital/storage/logs"
OUTPUT_FILE="/var/www/html/capital/.ai/monitoring/php_errors.jsonl"

if [ ! -d "$LOG_DIR" ]; then
    LOG_DIR="/home/$USER/logs"
fi

source_env() {
    if [ -f "$ENV_FILE" ]; then
        export $(grep -v '^#' "$ENV_FILE" | xargs)
    fi
}

source_env

echo "=== Monitorando erros (intervalo: ${INTERVAL}s) ==="
echo "Pressione Ctrl+C para parar"
echo ""

while true; do
    timestamp=$(date -Iseconds 2>/dev/null || date '+%Y-%m-%dT%H:%M:%S%z')
    
    for log in "$LOG_DIR"/*.log /var/log/apache2/error.log /var/log/nginx/error.log 2>/dev/null; do
        [ -f "$log" ] || continue
        
        tail -n 5 "$log" 2>/dev/null | while read -r line; do
            if echo "$line" | grep -qiE 'error|fatal|exception|500'; then
                short=$(echo "$line" | cut -c1-100)
                echo "{\"timestamp\":\"$timestamp\",\"source\":\"$(basename $log)\",\"error\":\"$short\"}" >> "$OUTPUT_FILE"
                echo "[$(date '+%H:%M:%S')] $short..."
            fi
        done
    done
    
    sleep "$INTERVAL"
done