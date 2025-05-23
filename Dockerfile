# Use official PHP image with extensions needed for Laravel
FROM php:8.2-fpm

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Install Composer
# COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy application files
COPY . .

# Install Composer dependencies
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer install --no-dev --optimize-autoloader

# Laravel permission fixes
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage

# Expose HTTP port (instead of 9000)
EXPOSE 3000

# Start Laravel using PHP built-in server
CMD php -S 0.0.0.0:3000 -t public