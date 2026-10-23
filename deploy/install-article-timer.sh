#!/usr/bin/env bash
# Preserve unrelated cron entries; replace only this project's marked entry.
set -euo pipefail
APP_DIR=${APP_DIR:?APP_DIR is required}
WEB_ROOT=${WEB_ROOT:?WEB_ROOT is required}
if [[ "$APP_DIR$WEB_ROOT" == *%* || "$APP_DIR$WEB_ROOT" == *$'\n'* ]]; then
  echo 'Cron paths must not contain percent signs or newlines' >&2
  exit 1
fi
command -v crontab >/dev/null
command -v flock >/dev/null
command -v node >/dev/null
printf -v RUNNER 'APP_DIR=%q WEB_ROOT=%q /bin/bash %q >> %q 2>&1' "$APP_DIR" "$WEB_ROOT" "$APP_DIR/deploy/refresh-articles.sh" "$APP_DIR/backend/storage/logs/website-articles.log"
EXISTING=$(crontab -l 2>/dev/null || true)
{ printf '%s\n' "$EXISTING" | sed '/# nour-adhkar-articles$/d'; printf '* * * * * %s # nour-adhkar-articles\n' "$RUNNER"; } | crontab -
echo 'Automatic article publishing installed (once per minute).'
