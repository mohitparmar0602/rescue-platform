#!/bin/sh

# Run migrations
php artisan migrate --force

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start Supervisor
exec /usr/bin/supervisord -c /etc/supervisord.conf
