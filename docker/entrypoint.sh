#!/bin/bash
set -e

cd /var/www/html

if [ ! -d vendor/smarty ]; then
    composer install --no-dev --no-interaction --optimize-autoloader
fi

mkdir -p templates_c img
chown -R www-data:www-data templates_c img 2>/dev/null || true

exec "$@"
