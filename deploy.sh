#!/usr/bin/env bash
# Run on EC2 inside the cloned repo folder:  ./deploy.sh
# Pulls the latest code from GitHub and puts it live.
set -euo pipefail
cd "$(dirname "$0")"

echo "==> Pulling latest code"
git pull

echo "==> Copying web/ to /var/www/html"
sudo rsync -a --delete web/ /var/www/html/
sudo chown -R root:www-data /var/www/html
sudo find /var/www/html -type d -exec chmod 750 {} +
sudo find /var/www/html -type f -exec chmod 640 {} +

echo "==> Updating database tables"
DB_NAME=$(sudo grep -m1 '^DB_NAME=' /secrets/app.env | cut -d= -f2-)
sudo mysql "$DB_NAME" < db/schema.sql

echo "Done."
