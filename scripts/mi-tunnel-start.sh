#!/usr/bin/env bash
# Media-Ideya — Cloudflare quick tunnel for stakeholder preview.
# Usage: ./scripts/mi-tunnel-start.sh [local_url]
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
STATE_DIR="$ROOT/.tunnel"
STATE_FILE="$STATE_DIR/state.env"
LOG_FILE="$STATE_DIR/cloudflared.log"
LOCAL_URL="${1:-http://localhost:8888}"
LOCAL_ORIGIN="${LOCAL_ORIGIN:-http://127.0.0.1:8888}"
LOCAL_HOST_HEADER="${LOCAL_HOST_HEADER:-localhost:8888}"
PHP_BIN="${PHP_BIN:-php}"
CFG_HELPER="$ROOT/scripts/mi-tunnel-config.php"

mkdir -p "$STATE_DIR"

if [[ -f "$STATE_FILE" ]]; then
  # shellcheck disable=SC1090
  source "$STATE_FILE"
  if [[ -n "${PID:-}" ]] && kill -0 "$PID" 2>/dev/null; then
    echo "Tunnel already running."
    echo "Public URL: ${PUBLIC_URL:-unknown}"
    echo "Stop: ./scripts/mi-tunnel-stop.sh"
    exit 0
  fi
fi

if ! command -v cloudflared >/dev/null 2>&1; then
  echo "cloudflared not found. Install: brew install cloudflared"
  exit 1
fi

if ! curl -fsS -o /dev/null --max-time 3 "$LOCAL_URL/"; then
  echo "Local site not reachable at $LOCAL_URL"
  echo "Start MAMP first, then retry."
  exit 1
fi

ORIGINAL_HOME="$("$PHP_BIN" "$CFG_HELPER" get http_home_url || true)"
if [[ -z "$ORIGINAL_HOME" ]]; then
  echo "Could not read http_home_url from engine/data/config.php"
  exit 1
fi

: >"$LOG_FILE"
if command -v setsid >/dev/null 2>&1; then
  setsid nohup cloudflared tunnel --url "$LOCAL_ORIGIN" --http-host-header "$LOCAL_HOST_HEADER" >>"$LOG_FILE" 2>&1 &
else
  nohup cloudflared tunnel --url "$LOCAL_ORIGIN" --http-host-header "$LOCAL_HOST_HEADER" >>"$LOG_FILE" 2>&1 &
fi
PID=$!
disown "$PID" 2>/dev/null || true
sleep 2
if ! kill -0 "$PID" 2>/dev/null; then
  echo "cloudflared failed to start. Log:"
  tail -20 "$LOG_FILE" || true
  exit 1
fi

PUBLIC_URL=""
for _ in $(seq 1 45); do
  PUBLIC_URL="$(grep -Eo 'https://[a-z0-9-]+\.trycloudflare\.com' "$LOG_FILE" | head -1 || true)"
  if [[ -n "$PUBLIC_URL" ]]; then
    break
  fi
  if ! kill -0 "$PID" 2>/dev/null; then
    echo "cloudflared exited early. Log:"
    tail -20 "$LOG_FILE" || true
    exit 1
  fi
  sleep 1
done

if [[ -z "$PUBLIC_URL" ]]; then
  kill "$PID" 2>/dev/null || true
  echo "Timed out waiting for tunnel URL. Log:"
  tail -20 "$LOG_FILE" || true
  exit 1
fi

PUBLIC_HOME="${PUBLIC_URL}/"
"$PHP_BIN" "$CFG_HELPER" set http_home_url "$PUBLIC_HOME"
ICON="${PUBLIC_URL}/templates/MediaIdeya/images/media-ideya-logo.png"
"$PHP_BIN" "$CFG_HELPER" set site_icon "$ICON"

rm -f "$ROOT/engine/cache/system/category.json"
find "$ROOT/engine/cache" -maxdepth 1 -name '*.php' -delete 2>/dev/null || true

cat >"$STATE_FILE" <<EOF
PID=$PID
PUBLIC_URL=$PUBLIC_URL
ORIGINAL_HOME=$ORIGINAL_HOME
LOCAL_URL=$LOCAL_URL
LOCAL_ORIGIN=$LOCAL_ORIGIN
LOCAL_HOST_HEADER=$LOCAL_HOST_HEADER
STARTED_AT=$(date -u +%Y-%m-%dT%H:%M:%SZ)
EOF

echo ""
echo "Media-Ideya stakeholder tunnel is live."
echo ""
echo "  Public:  $PUBLIC_URL"
echo "  Admin:   ${PUBLIC_URL}/admin.php"
echo "  Local:   $LOCAL_URL"
echo ""
echo "Share the public URL with stakeholders."
echo "Keep this Mac awake; tunnel stops when cloudflared exits."
echo "Stop tunnel: ./scripts/mi-tunnel-stop.sh"
echo ""
