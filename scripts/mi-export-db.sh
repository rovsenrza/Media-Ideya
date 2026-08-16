#!/usr/bin/env bash
# Export local MAMP database for hosting import
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
OUT="${ROOT}/backup/media_ideya_hosting.sql.gz"
mkdir -p "${ROOT}/backup"

MAMP_MYSQL="/Applications/MAMP/Library/bin/mysql57/bin/mysqldump"
MAMP_SOCK="/Applications/MAMP/tmp/mysql/mysql.sock"
if [[ ! -x "$MAMP_MYSQL" ]]; then
  MAMP_MYSQL="$(command -v mysqldump || true)"
  MAMP_SOCK=""
fi
if [[ -z "$MAMP_MYSQL" || ! -x "$MAMP_MYSQL" ]]; then
  echo "mysqldump not found"
  exit 1
fi

OUT="${ROOT}/backup/media_ideya_hosting.sql.gz"
mkdir -p "${ROOT}/backup"

SOCK_ARG=()
if [[ -n "$MAMP_SOCK" && -S "$MAMP_SOCK" ]]; then
  SOCK_ARG=(-S "$MAMP_SOCK")
fi

"$MAMP_MYSQL" -uroot -proot "${SOCK_ARG[@]}" --single-transaction --routines --triggers media_ideya | gzip > "$OUT"
echo "Exported: $OUT"
echo "Import in cPanel → phpMyAdmin → Import"
