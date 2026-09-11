#!/bin/sh
set -e

echo "Setting up database tables..."
php /app/db/migrate.php

echo "Starting Apache..."
exec "$@"
