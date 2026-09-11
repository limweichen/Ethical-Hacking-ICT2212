#!/usr/bin/env bash
# Run on EC2 to restore the database from a backup made by update.sh.
#   sudo bash ~/deploy/restore-db.sh                         (list backups)
#   sudo bash ~/deploy/restore-db.sh backups/FILE.sql         (restore it)
#
# WARNING: replaces the current tables with the backup's contents.
set -euo pipefail
cd "$(dirname "$0")"
DC=(docker compose --env-file /secrets/app.env --env-file version.env)

if [[ $# -eq 0 ]]; then
  echo "Backups (newest first):"
  ls -1t backups/*.sql 2>/dev/null | sed 's/^/  /' || echo "  (none yet)"
  echo "Usage: sudo bash ~/deploy/restore-db.sh backups/FILE.sql"
  exit 0
fi

[[ -f "$1" ]] || { echo "ERROR: $1 not found"; exit 1; }
read -r -p "Replace the current database with $1? Type yes: " ans
[[ "$ans" == "yes" ]] || { echo "Cancelled."; exit 1; }

"${DC[@]}" exec -T db sh -c \
  'exec mysql -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE"' < "$1"
echo "Database restored from $1"
