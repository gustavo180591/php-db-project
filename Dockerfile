FROM php:8.2-apache

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    unzip \
    git \
    libicu-dev \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
    && docker-php-ext-install \
        pdo_mysql \
        gd \
        zip \
        bcmath \
        intl \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug

# Configurar Apache
RUN a2enmod rewrite

# Configurar PHP
RUN { \
    echo 'error_reporting = E_ALL'; \
    echo 'display_errors = On'; \
    echo 'display_startup_errors = On'; \
    echo 'error_log = /var/log/php_errors.log'; \
    echo 'xdebug.mode = debug'; \
    echo 'xdebug.client_host = host.docker.internal'; \
    echo 'xdebug.client_port = 9003'; \
} > /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Copiar archivos del proyecto
COPY ./public /var/www/html
COPY ./config /var/www/html/config
COPY ./vendor /var/www/html/vendor

# Configurar permisos
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html

# Establecer directorio de trabajo
WORKDIR /var/www/html
