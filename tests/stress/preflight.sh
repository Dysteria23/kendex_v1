#!/usr/bin/env bash
# Prepara el entorno de PRUEBAS (nunca producción) para el test de estrés.
set -euo pipefail

echo "1) Datos de carga"
php artisan migrate --force
php artisan db:seed --force || true
php artisan kendex:load-data --users="${USERS:-200}" --expenses="${EXPENSES:-5000}"

echo "2) Optimizaciones iguales a producción"
php artisan config:cache && php artisan route:cache && php artisan view:cache && php artisan event:cache
npm run build

echo "3) Verifica que el honeypot esté desactivado en este entorno"
grep -q '^KENDEX_HONEYPOT=false' .env || echo "AVISO: pon KENDEX_HONEYPOT=false en .env y vuelve a ejecutar php artisan config:cache"

echo "Listo. Ahora ejecuta:  k6 run -e BASE_URL=http://localhost:8000 tests/stress/k6-kendex.js"
