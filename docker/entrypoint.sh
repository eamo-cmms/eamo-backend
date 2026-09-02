#!/bin/sh
set -e

# Ensure permissions
chown -R www-data:www-data /var/www/backend/storage /var/www/backend/bootstrap/cache
chmod -R 775 /var/www/backend/storage /var/www/backend/bootstrap/cache

# Execute command passed to docker container
exec "$@"
