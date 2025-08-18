#!/bin/sh

# Set ownership and permissions for storage and cache folders
# This ensures the web server can write to them, even with a volume mount
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Execute the main command passed to the script (e.g., supervisord)
exec "$@"
