#!/usr/bin/env bash
# First-time setup: install TastyIgniter, connect MySQL, load the restaurant content.
set -euo pipefail
cd "$(dirname "$0")/.."
LOG=/tmp/setup.log; exec > >(tee -a "$LOG") 2>&1
echo "== setup start $(date -u +%T)"
if [ ! -f artisan ]; then
  composer create-project --no-interaction --prefer-dist tastyigniter/tastyigniter /tmp/ti
  cp -rn /tmp/ti/. ./ ; rm -rf /tmp/ti
fi
composer install --no-interaction --prefer-dist
[ -f .env ] || cp .env.example .env
URL="http://localhost:8000"
if [ -n "${CODESPACE_NAME:-}" ]; then URL="https://${CODESPACE_NAME}-8000.${GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN}"; fi
set_env(){ grep -q "^$1=" .env && sed -i "s|^$1=.*|$1=$2|" .env || echo "$1=$2" >> .env; }
set_env APP_URL "$URL"; set_env DB_CONNECTION mysql; set_env DB_HOST 127.0.0.1; set_env DB_PORT 3306
set_env DB_DATABASE tastyigniter; set_env DB_USERNAME ti; set_env DB_PASSWORD ti-local-only
set_env APP_ENV local; set_env APP_DEBUG true
# Codespaces serves the app through an https proxy: trust it so links and photos use https
sed -i 's/protected \$proxies;/protected $proxies = "*";/' app/Http/Middleware/TrustProxies.php
grep -q '^APP_KEY=base64' .env || php artisan key:generate --force
for i in $(seq 1 60); do mysqladmin ping -h127.0.0.1 -uti -pti-local-only --silent && break; sleep 2; done
php artisan igniter:install --no-interaction || true
php artisan restaurant:import content/menu.json
php artisan restaurant:admin
echo "== setup done $(date -u +%T)"
