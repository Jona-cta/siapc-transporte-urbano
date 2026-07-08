FROM php:8.2-apache

RUN docker-php-ext-install mysqli pdo_mysql && a2enmod rewrite

# Bloquea el acceso web a archivos sensibles (dumps, .env, etc.)
COPY docker/deny-sensitive.conf /etc/apache2/conf-enabled/deny-sensitive.conf
