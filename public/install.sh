#!/usr/bin/env bash

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"

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
install_if_missing sudo

echo "Настройка sudo для WGManager..."

SUDOERS_FILE="/etc/sudoers.d/wgmanager"

cat > "$SUDOERS_FILE" <<EOF
www-data ALL=(root) NOPASSWD: /usr/bin/wg
www-data ALL=(root) NOPASSWD: /usr/bin/wg-quick
www-data ALL=(root) NOPASSWD: /usr/sbin/ip
EOF

chmod 440 "$SUDOERS_FILE"

if visudo -cf "$SUDOERS_FILE"; then
    echo "Права sudo успешно настроены."
else
    echo "Ошибка проверки sudoers."
    rm -f "$SUDOERS_FILE"
    exit 1
fi

echo "Настройка прав WireGuard..."

if [ -d /etc/wireguard ]; then

    chgrp www-data /etc/wireguard
    chmod 2775 /etc/wireguard

    if [ -f /etc/wireguard/wg0.conf ]; then
        chgrp www-data /etc/wireguard/wg0.conf
        chmod 660 /etc/wireguard/wg0.conf
    fi

    echo "Права WireGuard настроены."
fi

echo "Настройка прав WGManager..."

CONFIG_DIR="$PROJECT_ROOT/public/config"

if [ -d "$CONFIG_DIR" ]; then

    chown -R root:www-data "$CONFIG_DIR"

    find "$CONFIG_DIR" -type d -exec chmod 775 {} \;

    find "$CONFIG_DIR" -type f -exec chmod 664 {} \;

    echo "Права каталога config настроены."
fi

echo
echo "WGManager: все необходимые зависимости установлены."