#!/usr/bin/env bash

PHP_VERSION=""
PHP_FPM_SOCKET=""

detect_php() {
    PHP_VERSION="$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')"
    PHP_FPM_SOCKET="/run/php/php${PHP_VERSION}-fpm.sock"

    [[ -S "$PHP_FPM_SOCKET" ]] || fatal "PHP-FPM socket not found: $PHP_FPM_SOCKET"

    print_success "Detected PHP ${PHP_VERSION}"
}