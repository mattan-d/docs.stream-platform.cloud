#!/usr/bin/env bash
#
# Deploy docs.stream-platform.cloud (static guides — git pull only).
#   cd /var/www/html/docs.stream-platform.cloud && ./deploy.sh
#
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

mkdir -p data data/deploy-logs
LOCK_FILE="${SCRIPT_DIR}/data/deploy.lock"
exec 9>"$LOCK_FILE"
if ! flock -n 9; then
  echo "[deploy] Another deploy is already running; exiting."
  exit 0
fi

DEPLOY_WWW_USER="${DEPLOY_WWW_USER:-www-data}"
DO_PULL=true

for arg in "$@"; do
  case "$arg" in
    --pull) DO_PULL=true ;;
    --no-pull) DO_PULL=false ;;
    --help|-h)
      echo "Usage: $0 [--no-pull]"
      exit 0
      ;;
  esac
done

echo "[deploy] Project root: $SCRIPT_DIR"
echo "[deploy] Started at $(date -Is)"

if [ "$DO_PULL" = true ]; then
  if [ -d .git ]; then
    echo "[deploy] git pull..."
    git pull --ff-only || git pull
  else
    echo "[deploy] No .git dir, skipping pull."
  fi
fi

chmod -R ug+rwX data 2>/dev/null || true
chown -R "$DEPLOY_WWW_USER:$DEPLOY_WWW_USER" data guides 2>/dev/null || true

echo "[deploy] HEAD: $(git rev-parse --short HEAD 2>/dev/null || echo unknown)"
echo "[deploy] Done at $(date -Is)."
