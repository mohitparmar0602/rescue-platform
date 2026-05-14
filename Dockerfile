# Stage 1: Build assets
FROM node:20-alpine as assets-builder
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

# Stage 2: Main Application
FROM php:8.3-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    nginx \
    supervisor \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    postgresql-dev \
    oniguruma-dev \
    linux-headers

# Install PHP extensions
RUN docker-php-ext-install pdo_pgsql mbstring gd zip bcmath pcntl posix

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy application files
COPY . .
COPY --from=assets-builder /app/public/build ./public/build

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Setup Nginx and Supervisor
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisord.conf

# Set permissions
RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 80

# Start command (handled via supervisord or a custom script)
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
