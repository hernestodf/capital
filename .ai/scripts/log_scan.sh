#!/usr/bin/env bash
for f in /var/log/apache2/error.log /var/log/nginx/error.log ./storage/logs/*.log; do
  [ -f "$f" ] || continue
  echo "=== $f ==="; grep -iE 'error|fatal|exception|warning' "$f" | tail -n 30
done
