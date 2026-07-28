#!/usr/bin/env bash

install_docker() {
    install_package docker.io
    install_package docker-compose-v2

    if [[ -d "$INSTALL_DIRECTORY/.git" ]]; then
        print_warning "Repository already exists."

        while true; do
            echo
            echo "1) Update repository"
            echo "2) Skip"
            echo

            read -rp "Choose: " choice

            case "$choice" in
                1)
                    update_repository
                    break
                    ;;
                2)
                    break
                    ;;
                *)
                    print_error "Invalid selection."
                    ;;
            esac
        done
    else
        clone_repository
    fi

    cd "$INSTALL_DIRECTORY" || fatal "Unable to access $INSTALL_DIRECTORY."
    docker compose up -d --build || fatal "Failed to start Docker containers."

    print_success "Docker installation completed."
}