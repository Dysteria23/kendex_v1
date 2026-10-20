#!/usr/bin/env bash
# Respaldo diario de base de datos y comprobantes con retención de 30 días (RNF-09, RNF-10).
# Cron sugerido:  15 2 * * * /var/www/kendex/deploy/backup.sh >> /var/log/kendex-backup.log 2>&1
set -euo pipefail

BACKUP_DIR="${BACKUP_DIR:-/var/backups/kendex}"
APP_DIR="${APP_DIR:-/var/www/kendex}"
STAMP="$(date +%Y%m%d-%H%M%S)"

mkdir -p "$BACKUP_DIR"

# Lee DB_* del .env
set -a; source <(grep -E '^(DB_HOST|DB_PORT|DB_DATABASE|DB_USERNAME|DB_PASSWORD)=' "$APP_DIR/.env"); set +a

PGPASSWORD="$DB_PASSWORD" pg_dump -h "$DB_HOST" -p "${DB_PORT:-5432}" -U "$DB_USERNAME" -Fc "$DB_DATABASE" \
  > "$BACKUP_DIR/db-$STAMP.dump"

# Comprobantes (si usas disco local; con S3/Spaces activa el versionado del bucket)
if [ -d "$APP_DIR/storage/app/private/receipts" ]; then
  tar -czf "$BACKUP_DIR/receipts-$STAMP.tar.gz" -C "$APP_DIR/storage/app/private" receipts
fi

# Retención de 30 días
find "$BACKUP_DIR" -type f -mtime +30 -delete
echo "Respaldo $STAMP listo"
