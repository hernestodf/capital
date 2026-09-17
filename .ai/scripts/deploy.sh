#!/usr/bin/env bash
set -e
bash .ai/scripts/healthcheck.sh
git pull origin main
echo "- $(date): deploy $(git rev-parse --short HEAD)" >> .ai/monitoring/deployments.jsonl
