#!/bin/sh
set -e

# Cache configuration, routes, and views at runtime
# This ensures environment variables injected by Cloud Run are captured
echo "Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Execute the passed command (usually supervisord)
echo "Starting application..."
exec "$@"
