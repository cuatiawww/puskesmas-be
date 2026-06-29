#!/bin/bash
set -e

PROJECT_DIR="$(cd "$(dirname "$0")" && pwd)"
cd "$PROJECT_DIR"

echo "=== Pull latest ==="
git pull

echo "=== Check vendor ==="
if git diff --name-only HEAD~1 HEAD 2>/dev/null | grep -q composer.json; then
    echo "composer.json changed → running composer install"
    docker run --rm -v "$PROJECT_DIR:/app" -w /app composer:2 \
        sh -c "git config --global --add safe.directory /app && \
               composer install --no-dev --optimize-autoloader --no-blocking --ignore-platform-reqs"
fi

echo "=== Build image ==="
docker compose -f docker-compose.yml build

echo "=== Deploy ==="
docker compose -f docker-compose.yml up -d --wait

echo "=== Done ==="
