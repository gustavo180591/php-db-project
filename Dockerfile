FROM php:8.2-apache

# Instala la extensión pdo_mysql
RUN docker-php-ext-install pdo pdo_mysql

# Copia tu código PHP al contenedor (opcional si usás volumes)
COPY ./public /var/www/html
