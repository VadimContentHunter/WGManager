#!/usr/bin/env bash

PACKAGE_INDEX_UPDATED=false

COMMON_PACKAGES=(
    git
    curl
)

PHP_PACKAGES=(
    php
    php-fpm
)

WEB_PACKAGES=(
    nginx
)

WIREGUARD_PACKAGES=(
    wireguard
)

package_installed() {
    dpkg -s "$1" >/dev/null 2>&1
}

update_package_index() {

    if [[ "$PACKAGE_INDEX_UPDATED" == true ]]; then
        return
    fi

    print_info "Updating package index..."
    apt-get update
    PACKAGE_INDEX_UPDATED=true
    print_success "Package index updated."
}

install_package() {

    local package="$1"

    if package_installed "$package"; then
        print_success "$package is already installed."
        return
    fi

    update_package_index
    print_info "Installing $package..."
    apt-get install -y "$package"
    print_success "$package installed."
}

install_package_group() {

    local group_name="$1"
    shift
    print_info "Installing $group_name packages..."

    for package in "$@"; do
        install_package "$package"
    done
}