#!/usr/bin/env bash
# Despliegue de Kendex (lo ejecuta GitHub Actions por SSH al hacer push a main).
set -euo pipefail

APP_DIR="${APP_DIR:-/var/www/kendex}"
cd "$APP_DIR"

php artisan down --retry=30 || true
git fetch origin main && git reset --hard origin/main

composer install --no-dev --optimize-autoloader --no-interaction
npm ci && npm run build

php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan queue:restart

php artisan up
echo "Despliegue completado: $(git rev-parse --short HEAD)"
