#!/usr/bin/env bash
# health_report.sh - Gera relatório JSON de saúde do sistema
# Uso: bash health_report.sh > health.json

OUTPUT_FILE="/var/www/html/capital/.ai/monitoring/system_health.json"

timestamp=$(date -Iseconds 2>/dev/null || date '+%Y-%m-%dT%H:%M:%S%z')

php_check=$(php -v 2>/dev/null | head -1 || echo "unavailable")
mysql_check=$(mysql -u capital -pMarcelo123 -e "SELECT 1 as ok;" 2>/dev/null && echo "ok" || echo "failed")
disk_usage=$(df -h /var/www/html/capital 2>/dev/null | tail -1 | awk '{print $5}' || echo "unknown")
memory_usage=$(free -m 2>/dev/null | grep Mem | awk '{print $3 "/" $2 "MB"}' || echo "unknown")

cat > "$OUTPUT_FILE" << EOF
{
  "timestamp": "$timestamp",
  "php": "$php_check",
  "mysql": "$mysql_check",
  "disk_usage": "$disk_usage",
  "memory_usage": "$memory_usage",
  "status": "ok"
}
EOF

cat "$OUTPUT_FILE"