FROM php:7.4-fpm

RUN apt-get update && apt-get install -y \
    nginx \
    unzip \
    git \
    curl \
    libzip-dev \
    libpng-dev \
    libxml2-dev \
    libicu-dev \
    libonig-dev \
    librabbitmq-dev \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        mbstring \
        intl \
        zip \
        bcmath \
        soap \
        sockets \
    && pecl install amqp \
    && docker-php-ext-enable amqp

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN rm -rf /etc/nginx/sites-available \
 && rm -rf /etc/nginx/sites-enabled
COPY ./docker/default.conf /etc/nginx/conf.d/default.conf

WORKDIR /var/www/html

COPY . .

RUN composer install --no-dev --optimize-autoloader

RUN mkdir -p storage/app/files \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 80

# CMD ["sh", "-c", "php-fpm -D && nginx -g 'daemon off;'"]
CMD php-fpm -D && nginx -g 'daemon off;'