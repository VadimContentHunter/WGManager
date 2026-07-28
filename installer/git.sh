#!/usr/bin/env bash

clone_repository() {
    if [[ -d "$INSTALL_DIRECTORY/.git" ]]; then
        print_warning "Repository already exists."
        return
    fi

    print_info "Cloning repository..."
    git clone "$REPOSITORY_URL" "$INSTALL_DIRECTORY"
    print_success "Repository cloned."
}

update_repository() {
    [[ -d "$INSTALL_DIRECTORY/.git" ]] || fatal "Repository not found."

    print_info "Updating repository..."
    git -C "$INSTALL_DIRECTORY" pull --ff-only
    print_success "Repository updated."
}