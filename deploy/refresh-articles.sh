#!/usr/bin/env bash
# Called once per minute by the deploy user's crontab, and synchronously after a deployment.
set -euo pipefail
APP_DIR=${APP_DIR:-$(cd "$(dirname "$0")/.." && pwd)}
WEB_ROOT=${WEB_ROOT:-"$APP_DIR/Nour Adhkar/dist"}
STATE_DIR="$APP_DIR/backend/storage/app/website"
mkdir -p "$STATE_DIR"
exec 9>"$STATE_DIR/publish.lock"
# The deployment holds the same lock, so a timer cannot write into a directory being swapped.
flock -n 9 || exit 0
node "$APP_DIR/Nour Adhkar/scripts/refresh-articles.mjs" --root "$WEB_ROOT" --state "$STATE_DIR/articles.json" "$@"
