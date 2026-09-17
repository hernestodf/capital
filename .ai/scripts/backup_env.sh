#!/usr/bin/env bash
# backup_env.sh - Faz backup seguro do .env
# Uso: bash backup_env.sh

ENV_FILE="/var/www/html/capital/.env"
BACKUP_DIR="/var/www/html/capital/.ai/backups"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)

mkdir -p "$BACKUP_DIR"

if [ -f "$ENV_FILE" ]; then
    cp "$ENV_FILE" "$BACKUP_DIR/.env.backup.$TIMESTAMP"
    echo "Backup criado: $BACKUP_DIR/.env.backup.$TIMESTAMP"
    
    # Remove backups antigos (mantém últimos 5)
    ls -t "$BACKUP_DIR"/.env.backup.* 2>/dev/null | tail -n +6 | xargs rm -f 2>/dev/null
    echo "Backups antigos limpos"
else
    echo "ERRO: $ENV_FILE não encontrado"
    exit 1
fi