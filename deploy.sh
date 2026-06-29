#!/bin/bash
set -e

PROJECT_DIR="$(cd "$(dirname "$0")/.." && pwd)"
GIT_DIR="$PROJECT_DIR/git"

cd "$PROJECT_DIR"

echo "=== Pull latest ==="
git -C "$GIT_DIR" pull

echo "=== Check vendor ==="
if git -C "$GIT_DIR" diff --name-only HEAD~1 HEAD 2>/dev/null | grep -q composer.json; then
    echo "composer.json changed → running composer install"
    docker run --rm -v "$GIT_DIR:/app" -w /app composer:2 \
        sh -c "git config --global --add safe.directory /app && \
               composer install --no-dev --optimize-autoloader --no-blocking --ignore-platform-reqs"
fi

echo "=== Build image ==="
docker compose -f docker-compose.yml build

echo "=== Deploy ==="
docker compose -f docker-compose.yml up -d --wait

echo "=== Done ==="
