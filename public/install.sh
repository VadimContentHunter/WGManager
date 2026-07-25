#!/usr/bin/env bash

set -e

echo "Обновление списка пакетов..."
apt update

install_if_missing() {
    if dpkg -s "$1" >/dev/null 2>&1; then
        echo "$1 уже установлен."
    else
        echo "Установка $1..."
        apt install -y "$1"
    fi
}

echo "Проверка PHP..."

if ! command -v php >/dev/null 2>&1; then
    PHP_VERSION=$(apt-cache search '^php[0-9]\.[0-9]-cli$' | head -n1 | cut -d' ' -f1)

    if [ -z "$PHP_VERSION" ]; then
        echo "Не удалось определить доступную версию PHP."
        exit 1
    fi

    VERSION="${PHP_VERSION%-cli}"

    apt install -y \
        "${VERSION}-cli" \
        "${VERSION}-fpm" \
        "${VERSION}-common" \
        "${VERSION}-mbstring" \
        "${VERSION}-curl" \
        "${VERSION}-zip" \
        "${VERSION}-opcache"
else
    echo "PHP уже установлен."
fi

install_if_missing nginx
install_if_missing wireguard-tools

echo
echo "WGManager: все необходимые зависимости установлены."