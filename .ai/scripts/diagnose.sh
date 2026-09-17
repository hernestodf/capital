#!/usr/bin/env bash
# diagnose.sh - Diagnóstico rápido para cPanel
# Uso: bash diagnose.sh > /tmp/diagnose_output.txt

echo "=== DIAGNÓSTICO DO SISTEMA ==="
echo "Data: $(date)"
echo "Host: $(hostname)"
echo ""

echo "=== PHP ==="
php -v 2>/dev/null | head -1 || echo "PHP não encontrado"
echo ""

echo "=== MySQL ==="
mysql -u capital -pMarcelo123 -e "SELECT VERSION() as versao, 1 as ok;" 2>&1 || echo "MySQL não acessível"
echo ""

echo "=== Diretórios ==="
ls -la /var/www/html/capital/ 2>/dev/null | head -10
echo ""

echo "=== Storage ==="
ls -la /var/www/html/capital/storage/ 2>/dev/null || echo "Storage não encontrado"
echo ""

echo "=== Uploads ==="
ls -la /var/www/html/capital/uploads/ 2>/dev/null | head -5 || echo "Uploads não encontrado"
echo ""

echo "=== .env ==="
ls -la /var/www/html/capital/.env 2>/dev/null || echo ".env não encontrado"
echo ""

echo "=== Logs ==="
ls -la /var/www/html/capital/storage/logs/ 2>/dev/null | head -5 || echo "Logs não encontrados"