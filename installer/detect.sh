#!/usr/bin/env bash

detect_os() {
    [[ -f /etc/os-release ]] || fatal "Unable to detect operating system."

    source /etc/os-release

    case "$ID" in
        ubuntu|debian)
            print_success "Detected $PRETTY_NAME"
            ;;
        *)
            fatal "Unsupported operating system: $PRETTY_NAME"
            ;;
    esac
}