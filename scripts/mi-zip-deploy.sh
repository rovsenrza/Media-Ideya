#!/usr/bin/env bash
# Media-Ideya — zip for cPanel File Manager upload (subdomain docroot)
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
ENV_FILE="${ROOT}/.env.deploy"
SITE_URL="https://mediaideya.jeywastudio.com/"

if [[ -f "$ENV_FILE" ]]; then
  # shellcheck disable=SC1090
  source "$ENV_FILE"
  SITE_URL="${SITE_URL:-https://mediaideya.jeywastudio.com/}"
fi

SITE_URL="${SITE_URL%/}/"
OUT="${ROOT}/backup/mediaideya-deploy.zip"
mkdir -p "${ROOT}/backup"

CONFIG="${ROOT}/engine/data/config.php"
CONFIG_BAK="${ROOT}/engine/data/.config.local.bak"
trap '[[ -f "$CONFIG_BAK" ]] && mv -f "$CONFIG_BAK" "$CONFIG"' EXIT

cp -f "$CONFIG" "$CONFIG_BAK"
php "$ROOT/scripts/mi-ftp-prepare.php" \
  --config "$CONFIG" \
  --url "$SITE_URL" \
  --icon "${SITE_URL}templates/MediaIdeya/images/media-ideya-logo.png"

echo "==> Building ${OUT}"
rm -f "$OUT"
cd "$ROOT"
zip -r "$OUT" . \
  -x '*.git*' \
  -x '.cursor/*' \
  -x '.codebase-memory/*' \
  -x '.figma-cache/*' \
  -x '.feedback/*' \
  -x '.tunnel/*' \
  -x '.env.deploy' \
  -x 'templates/_extract/*' \
  -x 'engine/cache/*.php' \
  -x 'engine/cache/system/*' \
  -x 'backup/*' \
  -x '*.DS_Store'

echo ""
echo "Done: $OUT"
echo "cPanel File Manager → mediaideya docroot → Upload → Extract"
echo "Then: import DB + fix engine/data/dbconfig.php"
