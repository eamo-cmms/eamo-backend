FROM php:8.4-fpm-alpine

# Set working directory
WORKDIR /var/www/backend

# Install system dependencies
RUN apk add --no-cache \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    oniguruma-dev \
    libpq-dev \
    libzip-dev \
    icu-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    linux-headers \
    supervisor \
    bash

# Configure and install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_pgsql \
        pgsql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
        opcache

# Install Redis extension (optional but recommended)
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

# Install Composer
COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

# Copy project files
COPY . /var/www/backend

# Create storage and bootstrap/cache directories if not exist and set permissions
RUN mkdir -p /var/www/backend/storage /var/www/backend/bootstrap/cache \
    && chown -R www-data:www-data /var/www/backend/storage /var/www/backend/bootstrap/cache \
    && chmod -R 775 /var/www/backend/storage /var/www/backend/bootstrap/cache

# Copy custom php.ini
COPY ./docker/php.ini /usr/local/etc/php/conf.d/custom.ini

# Copy and set entrypoint script
COPY ./docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]
