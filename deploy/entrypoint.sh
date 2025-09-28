#!/bin/sh

set -e

# Install dependencies
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# Generate application key if not set
if [ -z "$(grep APP_KEY=base64 .env)" ]; then
    php artisan key:generate
fi

# Run database migrations
php artisan migrate --force

# Clear and cache routes
php artisan route:cache

# Clear and cache config
php artisan config:cache

# Clear and cache views
php artisan view:cache

# Link storage
php artisan storage:link

# Set directory permissions
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage
chmod -R 775 /var/www/html/bootstrap/cache

# Start supervisor
/usr/bin/supervisord -n -c /etc/supervisor/conf.d/supervisord.conf

# Execute CMD
exec "$@"
