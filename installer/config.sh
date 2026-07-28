#!/usr/bin/env bash

PROJECT_NAME="WGManager"
REPOSITORY_URL="https://github.com/VadimContentHunter/WGManager.git"
INSTALL_DIRECTORY="/opt/wgmanager"
WEB_USER="www-data"
WEB_GROUP="www-data"
NGINX_SITE_NAME="wgmanager"
WIREGUARD_INTERFACE="wg0"

SERVER_NAME="_"
NGINX_CONFIG="/etc/nginx/sites-available/$NGINX_SITE_NAME"
NGINX_ENABLED="/etc/nginx/sites-enabled/$NGINX_SITE_NAME"
WEB_ROOT="$INSTALL_DIRECTORY/public"

DOCKER_NGINX_TEMPLATE="$ROOT_DIR/installer/templates/nginx-http.conf"
DOCKERFILE_TEMPLATE="$ROOT_DIR/installer/templates/Dockerfile"
DOCKER_COMPOSE_TEMPLATE="$ROOT_DIR/installer/templates/docker-compose.yml"
DOCKER_NGINX_TEMPLATE="$ROOT_DIR/installer/templates/nginx-http.conf"