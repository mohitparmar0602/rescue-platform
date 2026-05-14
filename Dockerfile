# Stage 1: Build assets
FROM node:20-alpine as assets-builder
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

# Stage 2: Main Application
FROM php:8.4-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    nginx \
    supervisor \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    postgresql-dev \
    oniguruma-dev \
    linux-headers \
    dos2unix

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_pgsql mbstring gd zip bcmath pcntl posix

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy application files
COPY . .
COPY --from=assets-builder /app/public/build ./public/build

# Install dependencies (ignoring scripts to avoid boot errors during build)
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Setup Nginx and Supervisor
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisord.conf

# Set permissions
RUN chown -R www-data:www-data storage bootstrap/cache
RUN dos2unix docker/entrypoint.sh
RUN chmod +x docker/entrypoint.sh

EXPOSE 80

# Start command
ENTRYPOINT ["docker/entrypoint.sh"]
