#!/usr/bin/env bash

check_requirements() {
    print_info "Checking system..."

    command -v apt-get >/dev/null 2>&1 \
        || fatal "apt-get not found."

    command -v git >/dev/null 2>&1 \
        || print_warning "Git is not installed yet."

    command -v curl >/dev/null 2>&1 \
        || print_warning "Curl is not installed yet."

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