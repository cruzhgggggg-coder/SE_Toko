FROM php:8.4-fpm-alpine

RUN apk add --no-cache \
    libzip-dev \
    sqlite-dev \
    unzip \
    git \
    && docker-php-ext-install zip bcmath pdo_sqlite \
    && rm -rf /var/cache/apk/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create non-root user for security
RUN addgroup -S appgroup && adduser -S appuser -G appgroup

WORKDIR /var/www

COPY composer.json composer.lock ./
RUN composer install --no-interaction --no-scripts --no-dev --optimize-autoloader

COPY . .

# Set proper permissions — no 775/777
RUN chown -R appuser:appgroup /var/www \
    && chmod -R 750 storage bootstrap/cache

USER appuser

EXPOSE 9000

CMD ["php-fpm"]