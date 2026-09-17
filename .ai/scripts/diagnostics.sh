#!/usr/bin/env bash
echo "=== GIT ==="; git log --oneline -10
echo "=== DIRS ==="; find . -maxdepth 2 -type d -not -path '*/.git*' | sort
echo "PHP:"; find . -name '*.php' | wc -l
echo "JS:";  find . -name '*.js'  | wc -l
