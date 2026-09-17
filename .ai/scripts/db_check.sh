#!/usr/bin/env bash
# db_check.sh - Verifica conexão e status do banco de dados
# Uso: bash db_check.sh [--json]

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ENV_FILE="$(dirname "$SCRIPT_DIR")/../.env"

JSON=0
[ "$1" = "--json" ] && JSON=1

source_env() {
    if [ -f "$ENV_FILE" ]; then
        export $(grep -v '^#' "$ENV_FILE" | xargs)
    fi
}

source_env

DB_HOST="${DB_ONLINE_HOST:-localhost}"
DB_USER="${DB_ONLINE_USER:-capital}"
DB_PASS="${DB_ONLINE_PASS:-}"
DB_NAME="${DB_ONLINE_NAME:-capital}"

check_connection() {
    mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" -D "$DB_NAME" -e "SELECT 1 as ok;" 2>/dev/null
}

check_tables() {
    mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" -D "$DB_NAME" -N -e "SHOW TABLES;" 2>/dev/null | wc -l
}

check_data() {
    mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" -D "$DB_NAME" -N -e "
        SELECT 'clientes' as tabela, COUNT(*) as total FROM clientes
        UNION ALL SELECT 'eventos', COUNT(*) FROM eventos
        UNION ALL SELECT 'colaboradores', COUNT(*) FROM colaboradores
        UNION ALL SELECT 'planilhas', COUNT(*) FROM planilhas;
    " 2>/dev/null
}

if $JSON; then
    echo "{"
    echo "  \"connection\": \"$([ \"$(check_connection)\" ] && echo 'ok' || echo 'failed')\","
    echo "  \"tables\": $(check_tables),"
    echo "  \"data\": ["
    check_data | awk -F'|' '{printf "    {\"tabela\": \"%s\", \"total\": %s},\n", $1, $2}' | sed '$ s/,$//'
    echo "  ]"
    echo "}"
else
    echo "=== Database Check ==="
    echo "Host: $DB_HOST"
    echo "Database: $DB_NAME"
    echo ""
    echo "Connection: $([ "$(check_connection)" ] && echo 'OK' || echo 'FAILED')"
    echo "Tables: $(check_tables)"
    echo ""
    echo "Data Summary:"
    check_data | column -t -s'|'
fi