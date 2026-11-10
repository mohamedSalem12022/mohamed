FROM php:8.3-cli-alpine

# Install system dependencies and required PHP extensions
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libxml2-dev \
    zip \
    unzip \
    sqlite-dev \
    oniguruma-dev \
    icu-dev

# Configure and install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_mysql \
        pdo_sqlite \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        intl

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copy application files
COPY . /var/www

# Environment variables for production defaults
ENV APP_NAME="MO Life OS" \
    APP_ENV="production" \
    APP_DEBUG="false" \
    APP_KEY="base64:mo9IhZhQsWOlDQ5WKgexpGASmWMW1gIcaSupGEk3ox0=" \
    APP_URL="https://mo-life-os.onrender.com" \
    APP_TIMEZONE="Asia/Gaza" \
    DB_CONNECTION="sqlite" \
    DB_DATABASE="/var/www/database/database.sqlite" \
    SESSION_DRIVER="file" \
    CACHE_STORE="file" \
    PHP_CLI_SERVER_WORKERS="4"

# Copy environment example to .env if not present
RUN cp -n .env.example .env || true && chmod 777 .env

# Install Composer dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Ensure storage, cache, and database directories & permissions
RUN mkdir -p /var/www/storage/framework/sessions \
    && mkdir -p /var/www/storage/framework/views \
    && mkdir -p /var/www/storage/framework/cache \
    && mkdir -p /var/www/storage/fonts \
    && touch /var/www/database/database.sqlite \
    && chmod -R 777 /var/www/storage /var/www/bootstrap/cache /var/www/database

EXPOSE 8080

CMD ["sh", "-c", "php artisan migrate --force && php artisan db:seed --force && php artisan optimize:clear && php artisan serve --host=0.0.0.0 --port=${PORT:-8080}"]
