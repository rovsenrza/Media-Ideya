#!/usr/bin/env bash
# Media-Ideya — FTP deploy to jeywastudio hosting
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
ENV_FILE="${ROOT}/.env.deploy"

if [[ ! -f "$ENV_FILE" ]]; then
  echo "Missing .env.deploy — copy from .env.deploy.example"
  exit 1
fi

# shellcheck disable=SC1090
source "$ENV_FILE"

: "${FTP_HOST:?}"
: "${FTP_USER:?}"
: "${FTP_PASS:?}"
if [[ "$FTP_PASS" == "CHANGE_ME" ]]; then
  echo "Set FTP_PASS in .env.deploy (rovsenrza account password)"
  exit 1
fi
: "${FTP_PORT:=21}"
: "${FTP_REMOTE_DIR:=.}"
: "${SITE_URL:?}"

SITE_URL="${SITE_URL%/}/"
SITE_ICON="${SITE_URL}${SITE_ICON_PATH:-templates/MediaIdeya/images/media-ideya-logo.png}"

if ! command -v lftp >/dev/null 2>&1; then
  echo "Install lftp: brew install lftp"
  exit 1
fi

CONFIG="${ROOT}/engine/data/config.php"
DBCONFIG="${ROOT}/engine/data/dbconfig.php"
CONFIG_BAK="${ROOT}/engine/data/.config.local.bak"
DBCONFIG_BAK="${ROOT}/engine/data/.dbconfig.local.bak"

cleanup() {
  if [[ -f "$CONFIG_BAK" ]]; then
    mv -f "$CONFIG_BAK" "$CONFIG"
  fi
  if [[ -f "$DBCONFIG_BAK" ]]; then
    mv -f "$DBCONFIG_BAK" "$DBCONFIG"
  fi
}
trap cleanup EXIT

echo "==> Backup local config"
cp -f "$CONFIG" "$CONFIG_BAK"
cp -f "$DBCONFIG" "$DBCONFIG_BAK"

echo "==> Patch config for production"
php "$ROOT/scripts/mi-ftp-prepare.php" \
  --config "$CONFIG" \
  --url "$SITE_URL" \
  --icon "$SITE_ICON"

if [[ -n "${DB_NAME:-}" && -n "${DB_USER:-}" ]]; then
  php "$ROOT/scripts/mi-ftp-prepare.php" \
    --dbconfig "$DBCONFIG" \
    --db-host "${DB_HOST:-localhost}" \
    --db-name "$DB_NAME" \
    --db-user "$DB_USER" \
    --db-pass "${DB_PASS}"
else
  echo "WARN: DB_NAME/DB_USER empty — uploading current dbconfig.php (local MAMP credentials)"
fi

EXCLUDES=(
  --exclude-glob .git/
  --exclude-glob .cursor/
  --exclude-glob .codebase-memory/
  --exclude-glob .figma-cache/
  --exclude-glob .feedback/
  --exclude-glob .tunnel/
  --exclude-glob .env.deploy
  --exclude-glob .env.deploy.example
  --exclude-glob templates/_extract/
  --exclude-glob engine/cache/
  --exclude-glob backup/
  --exclude-glob engine/data/.config.local.bak
  --exclude-glob engine/data/.dbconfig.local.bak
  --exclude-glob '**/.DS_Store'
)

echo "==> FTP mirror → ${FTP_REMOTE_DIR} on ${FTP_HOST} (${FTP_USER})"
LFTP_CD="cd ${FTP_REMOTE_DIR}"
if [[ "$FTP_REMOTE_DIR" == "." ]]; then
  LFTP_MKDIR=""
  LFTP_CD="pwd"
else
  LFTP_MKDIR="mkdir -p ${FTP_REMOTE_DIR}"
fi
lftp -e "
set ssl:verify-certificate no
set ftp:passive-mode true
open -u ${FTP_USER},${FTP_PASS} -p ${FTP_PORT} ${FTP_HOST}
${LFTP_MKDIR}
lcd ${ROOT}
${LFTP_CD}
mirror --reverse --verbose --ignore-time ${EXCLUDES[*]} . .
mkdir -p engine/cache/system
mkdir -p engine/cache/system/plugins
mkdir -p engine/cache/system/HTML
bye
"

echo ""
echo "Done. Site: ${SITE_URL}"
echo ""
echo "Next steps:"
echo "  1. Import DB: ./scripts/mi-export-db.sh → phpMyAdmin"
echo "  2. engine/data/dbconfig.php — hosting MySQL ( .env.deploy DB_* )"
echo "  3. Purge cache: ${SITE_URL}scripts/mi-purge-cache.php?key=mi-purge-2026"
