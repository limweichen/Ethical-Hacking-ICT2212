#!/usr/bin/env bash
# Run on EC2 to go back to an older version.
#   sudo bash ~/deploy/rollback.sh        (show history and available versions)
#   sudo bash ~/deploy/rollback.sh v2     (switch to v2)
#
# Note: this changes the CODE only. Database data is not rolled back.
# To also restore data, use restore-db.sh with a backup from ~/deploy/backups/.
set -euo pipefail
cd "$(dirname "$0")"

if [[ $# -eq 0 ]]; then
  echo "Currently running: $(sed -n 's/^WEB_TAG=//p' version.env 2>/dev/null || echo unknown)"
  echo
  echo "Deploy history (newest last):"
  if [[ -f history.log ]]; then tail -n 10 history.log | sed 's/^/  /'; else echo "  (none yet)"; fi
  echo
  echo "Versions already on this server (instant rollback):"
  LOCAL=$(docker image ls --filter label=app.version \
    --format '{{.Tag}}  ({{.CreatedSince}})' | grep -v '^latest ' || true)
  if [[ -n "$LOCAL" ]]; then echo "$LOCAL" | sed 's/^/  /'; else echo "  (none)"; fi
  echo
  echo "Any older version on Docker Hub also works (it will be downloaded)."
  echo "Usage: sudo bash ~/deploy/rollback.sh v2"
  exit 0
fi

echo "==> Rolling back to $1"
exec bash ./update.sh "$1"
