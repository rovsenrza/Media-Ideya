#!/usr/bin/env bash
# Media-Ideya — stop Cloudflare quick tunnel and restore local URL.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
STATE_FILE="$ROOT/.tunnel/state.env"
PHP_BIN="${PHP_BIN:-php}"
CFG_HELPER="$ROOT/scripts/mi-tunnel-config.php"

if [[ ! -f "$STATE_FILE" ]]; then
  echo "No active tunnel state found."
  exit 0
fi

# shellcheck disable=SC1090
source "$STATE_FILE"

if [[ -n "${PID:-}" ]] && kill -0 "$PID" 2>/dev/null; then
  kill "$PID" 2>/dev/null || true
  sleep 1
  kill -9 "$PID" 2>/dev/null || true
  echo "Stopped cloudflared (pid $PID)."
else
  echo "cloudflared process not running."
fi

if [[ -n "${ORIGINAL_HOME:-}" ]]; then
  "$PHP_BIN" "$CFG_HELPER" set http_home_url "$ORIGINAL_HOME"
  ICON="$(echo "$ORIGINAL_HOME" | sed 's:/*$::')/templates/MediaIdeya/images/media-ideya-logo.png"
  "$PHP_BIN" "$CFG_HELPER" set site_icon "$ICON"
  echo "Restored http_home_url: ${ORIGINAL_HOME}"
fi

rm -f "$STATE_FILE"
rm -f "$ROOT/engine/cache/system/category.json"
find "$ROOT/engine/cache" -maxdepth 1 -name '*.php' -delete 2>/dev/null || true

echo "Tunnel stopped."
