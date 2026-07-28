#!/usr/bin/env bash

check_command() {
    local command="$1"

    if command -v "$command" >/dev/null 2>&1; then
        print_success "$command found."
    else
        print_warning "$command not found."
    fi
}

check_service() {
    local service="$1"

    if systemctl is-active --quiet "$service"; then
        print_success "$service is running."
    else
        print_warning "$service is not running."
    fi
}

get_server_ip() {
    hostname -I | awk '{print $1}'
}

show_summary() {
    local mode="$1"

    echo
    echo "========================================"
    echo "        WGManager Installation"
    echo "========================================"
    echo
    echo "Project:"
    echo "  $INSTALL_DIRECTORY"
    echo

    if [[ "$mode" == "native" ]]; then
        echo "Mode:"
        echo "  Native"
        echo
        echo "Web:"
        echo "  http://$(get_server_ip)"
        echo
        echo "WireGuard:"
        echo "  /etc/wireguard/${WIREGUARD_INTERFACE}.conf"
        echo
        echo "Checks:"
        check_command git
        check_command php
        check_command nginx
        check_command wg
        check_service nginx
        check_service "php${PHP_VERSION}-fpm"
        check_service "wg-quick@${WIREGUARD_INTERFACE}"
    else
        echo "Mode:"
        echo "  Docker"
        echo
        echo "Open:"
        echo "  http://$(get_server_ip)"
        echo
        echo "Checks:"
        check_command docker
        if docker compose version >/dev/null 2>&1; then
            print_success "docker compose found."
        else
            print_warning "docker compose not found."
        fi
        echo
        cd "$INSTALL_DIRECTORY" || return
        docker compose ps
    fi

    echo
    echo "========================================"
}

check_requirements() {
    print_info "Checking system..."

    command -v apt-get >/dev/null 2>&1 || fatal "apt-get not found."

    if ! ping -c1 -W2 github.com >/dev/null 2>&1; then
        fatal "Internet connection is unavailable."
    fi

    if ss -tln | grep -q ':80 '; then
        fatal "Port 80 is already in use."
    fi

    if ss -uln | grep -q ':51820 '; then
        fatal "UDP port 51820 is already in use."
    fi

    print_success "System check completed."
}