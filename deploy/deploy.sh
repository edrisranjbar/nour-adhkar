#!/usr/bin/env bash
# Deploys the checked-out commit: backend (Laravel) and website (built Vue app).
# Run by CI as the deploy user; CI uploads backend-vendor.tgz and web-dist.tgz next to APP_DIR.
set -euo pipefail
APP_DIR=${APP_DIR:?APP_DIR must point at the git checkout}
UPLOADS=$(dirname "$APP_DIR")
cd "$APP_DIR"
git fetch --quiet origin main
git reset --hard "${1:-origin/main}"

# Backend. vendor/ is built by CI because Composer cannot download packages on this server.
cd backend
if [ -f "$UPLOADS/backend-vendor.tgz" ]; then
  rm -rf vendor.new && mkdir vendor.new && tar xzf "$UPLOADS/backend-vendor.tgz" -C vendor.new
  rm -rf vendor && mv vendor.new/vendor vendor && rm -rf vendor.new
fi
php artisan down --retry=15 || true
trap 'php artisan up || true' EXIT
php artisan migrate --force
# Uploaded lecture audio is served from public/storage.
[ -e public/storage ] || php artisan storage:link
php artisan optimize:clear
php artisan optimize
php artisan up
trap - EXIT

# PHP-FPM keeps OPcache and resolver state; reload it so new code and settings take effect.
# Needs a sudoers rule for the deploy user (see deploy/README.md).
FPM=$(systemctl list-units --type=service --no-legend 'php*-fpm.service' | awk '{print $1}' | head -1)
if [ -n "$FPM" ]; then
  sudo -n systemctl reload "$FPM" || echo "warning: could not reload $FPM (check sudoers)"
fi
cd ..

# Website. The build is made by CI; swap it in so visitors never see a half-copied site.
# WEB_ROOT is the directory the web server serves (default: the Vue app's dist/ in the checkout).
WEB_ROOT=${WEB_ROOT:-"$APP_DIR/Nour Adhkar/dist"}
if [ -f "$UPLOADS/web-dist.tgz" ]; then
  rm -rf "$WEB_ROOT.new" && mkdir -p "$WEB_ROOT.new"
  tar xzf "$UPLOADS/web-dist.tgz" -C "$WEB_ROOT.new"
  rm -rf "$WEB_ROOT.old"
  if [ -d "$WEB_ROOT" ]; then mv "$WEB_ROOT" "$WEB_ROOT.old"; fi
  mv "$WEB_ROOT.new" "$WEB_ROOT"
  rm -rf "$WEB_ROOT.old"
fi

echo "Deployed $(git rev-parse --short HEAD)"
