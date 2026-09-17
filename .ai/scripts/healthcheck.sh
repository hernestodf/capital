#!/usr/bin/env bash
echo "=== HEALTHCHECK $(date) ==="
command -v php >/dev/null && echo "PHP ok" || echo "PHP ausente"
mysqladmin ping 2>/dev/null | grep -q alive && echo "MySQL ok" || echo "MySQL ?"
curl -sf http://localhost/api/health >/dev/null && echo "API ok" || echo "API ?"
