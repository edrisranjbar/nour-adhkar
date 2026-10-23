#!/usr/bin/env bash
# Deploys the checked-out commit: backend (Laravel) and website (built Vue app).
# Run by CI as the deploy user; CI uploads backend-vendor.tgz and web-dist.tgz next to APP_DIR.
set -euo pipefail
APP_DIR=${APP_DIR:?APP_DIR must point at the git checkout}
UPLOADS=$(dirname "$APP_DIR")
cd "$APP_DIR"

# Serialize website swaps and the background article publisher using the same private lock.
mkdir -p "$APP_DIR/backend/storage/app/website"
exec 9>"$APP_DIR/backend/storage/app/website/publish.lock"
flock 9

# The host's system Node can be old. Use the official setup-node runtime shipped by CI,
# scoped to this project; never replace the system runtime used by other applications.
RUNTIME_DIR="$APP_DIR/backend/storage/app/website/runtime"
if [ -f "$UPLOADS/website-runtime.tgz" ]; then
  mkdir -p "$RUNTIME_DIR"
  tar xzf "$UPLOADS/website-runtime.tgz" -C "$RUNTIME_DIR"
fi
NODE_BIN="$RUNTIME_DIR/node"
"$NODE_BIN" --version
"$NODE_BIN" -e 'if (Number(process.versions.node.split(".")[0]) < 22) process.exit(1)'
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
  # Strict live data here: never leave a build-time fallback snapshot as the public blog.
  if ! "$NODE_BIN" "$APP_DIR/Nour Adhkar/scripts/refresh-articles.mjs" --root "$WEB_ROOT" --state "$APP_DIR/backend/storage/app/website/articles.json" --force; then
    mv "$WEB_ROOT" "$WEB_ROOT.failed-$(date +%s)"
    if [ -d "$WEB_ROOT.old" ]; then mv "$WEB_ROOT.old" "$WEB_ROOT"; fi
    echo 'Article refresh failed; previous website restored.' >&2
    exit 1
  fi
  rm -rf "$WEB_ROOT.old"
fi
APP_DIR="$APP_DIR" WEB_ROOT="$WEB_ROOT" bash "$APP_DIR/deploy/install-article-timer.sh"

# Retire the removed Instagram panel's generated public assets without deleting them.
# Uploaded post images and historical database rows remain untouched.
if [ -d "$APP_DIR/backend/public/instagram" ]; then
  RETIRED_PANEL_DIR="$APP_DIR/backend/storage/app/retired-instagram-assets"
  mkdir -p "$RETIRED_PANEL_DIR"
  mv "$APP_DIR/backend/public/instagram" "$RETIRED_PANEL_DIR/$(date +%s)-${1:-retired}"
fi

echo "Deployed $(git rev-parse --short HEAD)"
