#!/bin/bash
set -e

# Port configuration for Render
PORT=${PORT:-80}
sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost \*:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# Ensure .env exists
if [ ! -f /var/www/html/.env ]; then
    cp /var/www/html/.env.example /var/www/html/.env
fi

# Ensure SQLite file exists
mkdir -p /var/www/html/database
touch /var/www/html/database/database.sqlite

# Ensure APP_KEY
if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force
fi

# Run database setup
php artisan migrate --force
php artisan db:seed --force

# Cache config and routes
php artisan config:cache
php artisan route:cache

# Ensure www-data permissions on all runtime directories and sqlite database
chown -R www-data:www-data /var/www/html/database /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 777 /var/www/html/database
chmod 666 /var/www/html/database/database.sqlite

# Start Apache in the foreground
exec apache2-foreground
