#!/usr/bin/env bash
set -e
PREV=${1:-HEAD~1}
git checkout "$PREV" -- .
echo "{\"event\":\"rollback\",\"to\":\"$PREV\",\"at\":\"$(date -Iseconds)\"}" >> .ai/monitoring/incidents.jsonl
