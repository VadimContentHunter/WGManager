#!/usr/bin/env bash

show_banner() {
    clear
    echo "========================================"
    echo "         WGManager Installer"
    echo "========================================"
}

print_info() {
    echo "[INFO] $1"
}

print_success() {
    echo "[ OK ] $1"
}

print_warning() {
    echo "[WARN] $1"
}

print_error() {
    echo "[FAIL] $1"
}

fatal() {
    print_error "$1"
    exit 1
}

check_root() {
    [[ $EUID -eq 0 ]] || fatal "Please run installer as root."
}