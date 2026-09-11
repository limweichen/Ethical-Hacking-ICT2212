#!/usr/bin/env bash
# Fresh LAMP setup for an Ubuntu EC2 instance.
# Usage:  nano setup-lamp.sh   (edit the 3 values below)
#         sudo bash setup-lamp.sh
set -euo pipefail

# ====== EDIT THESE ======
DB_NAME="ICT2212_STUDENT17"          # your database name
DB_USER="student17-mysql"        # your MySQL username
DB_PASS="ICT2212"      # your MySQL password (no ' or " characters)
# ========================

if [[ $EUID -ne 0 ]]; then echo "Run with: sudo bash $0"; exit 1; fi

echo "==> 1/5 Installing Apache, MySQL, PHP"
apt-get update -y
DEBIAN_FRONTEND=noninteractive apt-get install -y \
  apache2 mysql-server php libapache2-mod-php php-mysql git curl
systemctl enable --now apache2 mysql

echo "==> 2/5 Creating database and user"
mysql <<SQL
CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\`;
DROP USER IF EXISTS '${DB_USER}'@'localhost';
CREATE USER '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
GRANT ALL ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';
FLUSH PRIVILEGES;
SQL

echo "==> 3/5 Detecting this instance's public IP"
TOKEN=$(curl -s -X PUT "http://169.254.169.254/latest/api/token" \
  -H "X-aws-ec2-metadata-token-ttl-seconds: 60" || true)
PUBLIC_IP=$(curl -s -H "X-aws-ec2-metadata-token: ${TOKEN}" \
  http://169.254.169.254/latest/meta-data/public-ipv4 || true)
[[ -z "$PUBLIC_IP" ]] && PUBLIC_IP=$(curl -s https://checkip.amazonaws.com | tr -d '[:space:]')
echo "    Public IP: ${PUBLIC_IP}"

echo "==> 4/5 Writing secrets to /secrets/app.env"
mkdir -p /secrets
cat > /secrets/app.env <<ENV
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=${DB_NAME}
DB_USER=${DB_USER}
DB_PASS=${DB_PASS}
APP_URL=http://${PUBLIC_IP}
ENV
chown -R root:www-data /secrets
chmod 750 /secrets
chmod 640 /secrets/app.env

echo "==> 5/5 Creating a test page"
cat > /var/www/html/dbtest.php <<'PHP'
<?php
$env = parse_ini_file('/secrets/app.env', false, INI_SCANNER_RAW);
try {
    $pdo = new PDO(
        "mysql:host={$env['DB_HOST']};port={$env['DB_PORT']};dbname={$env['DB_NAME']};charset=utf8mb4",
        $env['DB_USER'], $env['DB_PASS'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "OK: PHP " . PHP_VERSION . " connected to MySQL " .
         $pdo->query("SELECT VERSION()")->fetchColumn();
} catch (PDOException $e) {
    http_response_code(500);
    echo "DB connection failed: " . htmlspecialchars($e->getMessage());
}
PHP
chown www-data:www-data /var/www/html/dbtest.php

echo
echo "Done. Open http://${PUBLIC_IP}/dbtest.php in your browser."
echo "If it does not load, allow inbound HTTP (port 80) in the EC2 security group."
