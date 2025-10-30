#!/bin/bash
set -e

# Pastikan folder writable setiap kali container start
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Start cron
service cron start

# Jalankan Apache
exec apache2-foreground

