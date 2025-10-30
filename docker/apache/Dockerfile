FROM php:8.2-apache

# Install dependencies
RUN apt-get update && apt-get install -y \
    git curl zip unzip cron libpq-dev libpng-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo pdo_pgsql pgsql mbstring exif pcntl bcmath gd

# Aktifkan mod_rewrite untuk Laravel
RUN a2enmod rewrite

# Copy konfigurasi virtual host
COPY docker/apache/vhost.conf /etc/apache2/sites-available/000-default.conf

# Set working directory
WORKDIR /var/www/html

# Copy semua source Laravel ke container
COPY . .

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Install dependensi Laravel
RUN composer install --no-interaction --optimize-autoloader

# Pastikan direktori penting tersedia dan bisa ditulis
RUN mkdir -p /var/www/html/storage /var/www/html/bootstrap/cache \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Salin entrypoint script untuk menjalankan Apache + Cron
COPY docker/apache/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80

# Jalankan entrypoint (ini akan start cron + apache)
ENTRYPOINT ["/entrypoint.sh"]
