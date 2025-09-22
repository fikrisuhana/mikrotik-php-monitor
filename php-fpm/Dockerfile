FROM php:8.1-fpm-alpine

WORKDIR /var/www/html

RUN apk add --no-cache \
    mysql-client \
    curl \
    libzip-dev \
    zip \
    unzip \
    git \
    icu-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    freetype-dev \
    libxml2-dev \
    oniguruma-dev \
    gmp-dev \
    libpq

RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp
RUN docker-php-ext-install -j$(nproc) gd pdo_mysql mysqli zip bcmath gmp intl opcache xml

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy composer.json and install dependencies
COPY src/composer.json .
RUN composer install --no-dev --optimize-autoloader
