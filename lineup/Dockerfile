FROM dunglas/frankenphp

RUN install-php-extensions \
    pdo_mysql \
    opcache \
    mbstring

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY . .

RUN composer install --no-dev --optimize-autoloader

ENV SERVER_NAME=":8080"
ENV FRANKENPHP_CONFIG="worker ./public/index.php"