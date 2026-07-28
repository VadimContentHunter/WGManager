#!/usr/bin/env bash

install_native() {
    install_package_group "Common" "${COMMON_PACKAGES[@]}"
    install_package_group "PHP" "${PHP_PACKAGES[@]}"
    install_package_group "Web Server" "${WEB_PACKAGES[@]}"
    install_package_group "WireGuard" "${WIREGUARD_PACKAGES[@]}"
    detect_php
    clone_repository
    print_success "Native installation completed."
}