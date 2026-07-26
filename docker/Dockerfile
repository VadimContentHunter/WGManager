FROM php:8.5-fpm

RUN apt-get update && apt-get install -y \
    default-mysql-client \
    default-libmysqlclient-dev \
    nginx \
    wireguard-tools \
    sudo \
    && docker-php-ext-install \
    bcmath \
    pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

WORKDIR /var/www/project

CMD ["php-fpm"]