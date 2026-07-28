FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    wireguard-tools \
    iproute2 \
    iptables \
    iputils-ping \
    curl \
    git \
    unzip \
    sudo \
    && docker-php-ext-install \
    bcmath \
    pdo_mysql \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/project

CMD ["php-fpm"]