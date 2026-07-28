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

check_native() {
    echo
    print_info "Running post-install checks..."
    echo

    check_command git
    check_command php
    check_command nginx
    check_command wg

    echo

    check_service "php${PHP_VERSION}-fpm"
    check_service nginx

    if systemctl list-unit-files | grep -q "^wg-quick@${WIREGUARD_INTERFACE}\.service"; then
        check_service "wg-quick@${WIREGUARD_INTERFACE}"
    else
        print_warning "WireGuard service is not configured."
    fi

    echo
}

check_docker() {
    echo
    print_info "Running post-install checks..."
    echo

    check_command docker

    if docker compose version >/dev/null 2>&1; then
        print_success "Docker Compose found."
    else
        print_warning "Docker Compose not found."
        return
    fi

    echo

    cd "$INSTALL_DIRECTORY" || return

    docker compose ps

    echo
}