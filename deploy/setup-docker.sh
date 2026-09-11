#!/usr/bin/env bash
# Run ONCE on EC2:  sudo bash ~/deploy/setup-docker.sh
# Switches the server from the LAMP install to Docker.
set -euo pipefail
if [[ $EUID -ne 0 ]]; then echo "Run with: sudo bash $0"; exit 1; fi

echo "==> 1/3 Stopping the Apache and MySQL installed directly on Ubuntu"
# They would block ports 80 and 3306 and waste RAM. Docker runs its own.
systemctl disable --now apache2 2>/dev/null || true
systemctl disable --now mysql 2>/dev/null || true

echo "==> 2/3 Installing Docker"
if ! command -v docker >/dev/null; then
  curl -fsSL https://get.docker.com | sh
fi
# Docker from Ubuntu's own package (docker.io) has no "docker compose"
if ! docker compose version >/dev/null 2>&1; then
  apt-get update -y
  apt-get install -y docker-compose-v2 || apt-get install -y docker-compose-plugin
fi
docker compose version
systemctl enable --now docker

echo "==> 3/3 Checking /secrets/app.env"
if [[ ! -f /secrets/app.env ]]; then
  mkdir -p /secrets
  IP=$(curl -s https://checkip.amazonaws.com | tr -d '[:space:]')
  cat > /secrets/app.env <<ENV
DB_HOST=db
DB_PORT=3306
DB_NAME=ICT2212_STUDENT17_DB
DB_USER=student17-sql
DB_PASS=ICT2212
APP_URL=http://${IP}
ENV
  echo "    Created /secrets/app.env. Change DB_PASS with: sudo nano /secrets/app.env"
else
  # Inside Docker the database is the "db" container, not 127.0.0.1
  sed -i 's/^DB_HOST=.*/DB_HOST=db/' /secrets/app.env
  echo "    Updated DB_HOST=db in existing /secrets/app.env"
fi
chown -R root:www-data /secrets
chmod 750 /secrets
chmod 640 /secrets/app.env

echo
echo "Done. Next:"
echo "  1. sudo docker login          (your Docker Hub username + access token)"
echo "  2. On your laptop: ./release.sh v1"
echo "  3. sudo bash ~/deploy/update.sh v1"
