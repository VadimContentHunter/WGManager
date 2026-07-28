#!/usr/bin/env bash

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
export ROOT_DIR

source "$ROOT_DIR/installer/common.sh"
source "$ROOT_DIR/installer/config.sh"
source "$ROOT_DIR/installer/detect.sh"
source "$ROOT_DIR/installer/check.sh"
source "$ROOT_DIR/installer/packages.sh"
source "$ROOT_DIR/installer/git.sh"
source "$ROOT_DIR/installer/native.sh"
source "$ROOT_DIR/installer/docker.sh"

main() {
    show_banner
    check_root
    detect_os
    check_requirements

    while true; do
        echo "1) Native (Nginx + PHP-FPM)"
        echo "2) Docker"
        echo "3) Exit"
        read -rp "Choose installation method: " choice
        case "$choice" in
            1)
                install_native
                show_summary native
                break
                ;;
            2)
                install_docker
                show_summary docker
                break
                ;;
            3)
                exit 0
                ;;
            *)
                print_error "Invalid selection."
                ;;
        esac
    done
}

main "$@"