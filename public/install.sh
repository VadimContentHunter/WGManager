#!/usr/bin/env bash

set -e

echo "Обновление списка пакетов..."
apt update

echo "Установка PHP..."
apt install -y \
    php-fpm \
    php-cli \
    php-common \
    php-mbstring \
    php-json \
    php-opcache \
    php-curl \
    php-zip

echo "Установка Nginx..."
apt install -y nginx

echo "Установка WireGuard..."
apt install -y wireguard

echo
echo "WGManager: все необходимые зависимости установлены."