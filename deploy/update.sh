#!/usr/bin/env bash
# Run on EC2 to deploy a version:
#   sudo bash ~/deploy/update.sh v3      (a specific version)
#   sudo bash ~/deploy/update.sh         (whatever is newest on Docker Hub)
#
# Before switching, it backs up the database to ~/deploy/backups/.
set -euo pipefail
cd "$(dirname "$0")"

TAG="${1:-latest}"

if ! docker compose version >/dev/null 2>&1; then
  echo "ERROR: 'docker compose' is not installed. Run: sudo apt install -y docker-compose-v2"
  exit 1
fi
touch version.env
DC=(docker compose --env-file /secrets/app.env --env-file version.env)

# 1. Back up the database (skipped on the very first deploy)
mkdir -p backups && chmod 700 backups
if "${DC[@]}" ps --status running --services 2>/dev/null | grep -qx db; then
  FILE="backups/$(date +%Y%m%d-%H%M%S)-before-$TAG.sql"
  echo "==> Backing up database to $FILE"
  if "${DC[@]}" exec -T db sh -c \
      'exec mysqldump --no-tablespaces -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE"' \
      > "$FILE" 2>/dev/null; then
    chmod 600 "$FILE"
    # keep only the 10 newest backups
    ls -1t backups/*.sql | tail -n +11 | xargs -r rm --
  else
    echo "    WARNING: backup failed, continuing anyway"
    rm -f "$FILE"
  fi
fi

# 2. Download and start the requested version
echo "==> Deploying web image tag: $TAG"
echo "WEB_TAG=$TAG" > version.env
"${DC[@]}" pull
"${DC[@]}" up -d

# 3. Find out which version is actually running and pin it
CID=$("${DC[@]}" ps -q web)
VERSION=$(docker inspect -f '{{index .Config.Labels "app.version"}}' "$CID" 2>/dev/null || true)
VERSION=${VERSION:-$TAG}
[[ "$VERSION" == "<no value>" ]] && VERSION=$TAG
echo "WEB_TAG=$VERSION" > version.env
echo "$(date '+%Y-%m-%d %H:%M:%S')  $VERSION" >> history.log

# 4. Clean up images nobody uses (numbered versions are kept for fast rollback)
docker image prune -f >/dev/null

echo
"${DC[@]}" ps
echo
echo "Now running: $VERSION"
