FROM dunglas/frankenphp:latest

RUN install-php-extensions pdo_mysql

WORKDIR /app

COPY . .

RUN composer install --no-dev --optimize-autoloader

ENV FRANKENPHP_CONFIG="worker ./public/index.php"