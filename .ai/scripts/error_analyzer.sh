#!/usr/bin/env bash
# error_analyzer.sh - Análise de erros 500 do sistema
# Uso: bash error_analyzer.sh [dias]

DAYS=${1:-7}
OUTPUT_FILE="/var/www/html/capital/.ai/monitoring/php_errors.jsonl"

echo "=== Análise de Erros 500 — Últimos $DAYS dias ==="

analyze_logs() {
    local log_file="$1"
    local source="$2"
    
    if [ ! -f "$log_file" ]; then
        return
    fi
    
    echo "--- $source ---"
    
    grep -iE 'error|fatal|exception|500|critical' "$log_file" | tail -n 20 | while read -r line; do
        timestamp=$(date -Iseconds 2>/dev/null || date '+%Y-%m-%dT%H:%M:%S%z')
        echo "{\"timestamp\":\"$timestamp\",\"source\":\"$source\",\"error\":\"$line\"}" >> "$OUTPUT_FILE"
        echo "  $line"
    done
}

analyze_php_errors() {
    local php_error_log="/var/www/html/capital/storage/logs/php_error.log"
    
    if [ -f "$php_error_log" ]; then
        echo "--- PHP Error Log ---"
        tail -n 20 "$php_error_log" | while read -r line; do
            timestamp=$(date -Iseconds 2>/dev/null || date '+%Y-%m-%dT%H:%M:%S%z')
            echo "{\"timestamp\":\"$timestamp\",\"source\":\"php_error_log\",\"error\":\"$line\"}" >> "$OUTPUT_FILE"
            echo "  $line"
        done
    fi
}

analyze_apache() {
    if [ -f "/var/log/apache2/error.log" ]; then
        analyze_logs "/var/log/apache2/error.log" "apache"
    fi
}

analyze_nginx() {
    if [ -f "/var/log/nginx/error.log" ]; then
        analyze_logs "/var/log/nginx/error.log" "nginx"
    fi
}

echo ""
echo "Fontes de erro verificadas:"
echo "  - Apache error.log"
echo "  - Nginx error.log"
echo "  - storage/logs/*.log"
echo ""

analyze_apache
analyze_nginx
analyze_php_errors

echo ""
echo "Erros registrados em: $OUTPUT_FILE"
echo "Use 'tail -f $OUTPUT_FILE' para monitoramento em tempo real"