#!/usr/bin/env bash
# XarxesGest — deploy / update script, run on the server as the CloudPanel
# site user from the site root:
#   cd ~/htdocs/socialgest.soportic.es && bash deploy/cloudpanel/deploy.sh
# Requirements: PHP 8.4 CLI, Composer, Node.js 22 (nvm), Supervisor programs
# from deploy/cloudpanel/supervisor-socialgest.conf.
set -euo pipefail

PHP="${PHP_BIN:-php8.4}"
BRANCH="${DEPLOY_BRANCH:-main}"
cd "$(dirname "$0")/../.."

# Load nvm (Node is only needed to build the frontend).
export NVM_DIR="${NVM_DIR:-$HOME/.nvm}"
# shellcheck disable=SC1091
[ -s "$NVM_DIR/nvm.sh" ] && . "$NVM_DIR/nvm.sh"

echo "==> Maintenance mode"
$PHP artisan down --retry=60 || true
trap '$PHP artisan up || true' EXIT

echo "==> Code ($BRANCH)"
git fetch origin "$BRANCH"
git reset --hard "origin/$BRANCH"

echo "==> PHP dependencies"
$PHP "$(command -v composer)" install --no-dev --no-interaction --prefer-dist --optimize-autoloader

echo "==> Frontend build"
# The previous deploy's `optimize` cached routes and config; Wayfinder reads
# routes through artisan, so a stale cache hides every new route from the build.
$PHP artisan optimize:clear
$PHP artisan wayfinder:generate --with-form
npm ci --no-audit --no-fund
npm run build

echo "==> Database"
$PHP artisan migrate --force
$PHP artisan db:seed --class='Database\Seeders\PassportSeeder' --force
$PHP artisan changelog:sync || true
[ -L public/storage ] || $PHP artisan storage:link

echo "==> Caches"
$PHP artisan optimize

echo "==> Restart workers"
$PHP artisan horizon:terminate
$PHP artisan reverb:restart || true

echo "==> Done"
