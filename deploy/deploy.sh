#!/usr/bin/env bash
#
# Clusterfiy production deploy. Single-server, brief maintenance window.
# Usage: sudo -u www-data ./deploy/deploy.sh
#    or: ssh prod 'cd /var/www/clusterfiy && ./deploy/deploy.sh'
#
set -euo pipefail

APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$APP_DIR"

echo "==> Deploying Clusterfiy in $APP_DIR"

echo "==> PHP dependencies (no dev, while live)"
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

echo "==> Frontend build (while live)"
if [ ! -d node_modules ]; then npm ci; else npm ci; fi
npm run build

echo "==> Maintenance mode ON (downtime starts here)"
php artisan down --render="errors.503" || true

echo "==> App key (only if empty)"
if grep -q '^APP_KEY=$' .env 2>/dev/null || ! grep -q '^APP_KEY=.\+' .env 2>/dev/null; then
  php artisan key:generate --force
fi

echo "==> Database backup (before migrate)"
mkdir -p storage/backups
if [ "${DB_CONNECTION:-mysql}" = "sqlite" ]; then
  cp database/database.sqlite "storage/backups/database-$(date +%F-%H%M).sqlite" || true
else
  mysqldump --single-transaction -h "${DB_HOST:-127.0.0.1}" -u "${DB_USERNAME}" -p"${DB_PASSWORD}" "${DB_DATABASE}" \
    | gzip > "storage/backups/db-$(date +%F-%H%M).sql.gz"
fi

echo "==> Migrate"
php artisan migrate --force

echo "==> Caches"
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Restart queue workers (graceful: finishes current job)"
php artisan queue:restart

echo "==> Maintenance mode OFF"
php artisan up

echo "==> Health check"
php artisan up --no-interaction >/dev/null 2>&1 || true
curl -fsS -o /dev/null -w "HTTP %{http_code}\n" "${APP_URL:-http://localhost}/up"

echo "==> Done"
