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

echo "Проверка зависимостей..."

install_if_missing nginx
install_if_missing wireguard-tools
install_if_missing sudo

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

echo "Настройка маршрутизации WireGuard..."

mkdir -p /etc/sysctl.d

cat >/etc/sysctl.d/99-wireguard.conf <<EOF
net.ipv4.ip_forward=1
EOF

sysctl --system >/dev/null

iptables -C FORWARD -i wg0 -o wg0 -j ACCEPT 2>/dev/null || \
iptables -I FORWARD -i wg0 -o wg0 -j ACCEPT

echo "Маршрутизация настроена."

echo "Настройка sudo..."

SUDOERS_FILE="/etc/sudoers.d/wgmanager"

cat >"$SUDOERS_FILE" <<EOF
www-data ALL=(root) NOPASSWD: /usr/bin/wg
www-data ALL=(root) NOPASSWD: /usr/bin/wg-quick
www-data ALL=(root) NOPASSWD: /usr/sbin/ip
EOF

chmod 440 "$SUDOERS_FILE"

if visudo -cf "$SUDOERS_FILE"; then
    echo "Sudo успешно настроен."
else
    echo "Ошибка проверки sudoers."
    rm -f "$SUDOERS_FILE"
    exit 1
fi

echo "Настройка WireGuard..."

if [ -d /etc/wireguard ]; then

    chown root:www-data /etc/wireguard
    chmod 2775 /etc/wireguard

    find /etc/wireguard -type f -exec chgrp www-data {} \;
    find /etc/wireguard -type f -exec chmod 660 {} \;

    echo "Права WireGuard обновлены."
fi

echo "Настройка каталога config..."

CONFIG_DIR="$PROJECT_ROOT/public/config"

if [ -d "$CONFIG_DIR" ]; then

    chown -R root:www-data "$CONFIG_DIR"

    find "$CONFIG_DIR" -type d -exec chmod 775 {} \;

    find "$CONFIG_DIR" -type f -exec chmod 664 {} \;

    if [ ! -f "$CONFIG_DIR/settings.json" ]; then

        if [ -f "$CONFIG_DIR/settings.default.json" ]; then
            cp "$CONFIG_DIR/settings.default.json" \
               "$CONFIG_DIR/settings.json"

            echo "Создан settings.json."
        fi
    fi

    echo "Каталог config настроен."
fi

echo
echo "WGManager успешно настроен."