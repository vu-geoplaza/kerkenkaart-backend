FROM php:8.3-apache

RUN apt-get update && apt-get install -y libpq-dev && docker-php-ext-install pdo pdo_pgsql

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY . /var/www/html/resources

# Cannot start with unprivileged user on port 80
RUN sed -i 's/Listen 80/Listen 8080/' /etc/apache2/ports.conf