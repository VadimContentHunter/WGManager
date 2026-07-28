#!/usr/bin/env bash

install_native() {
    install_package_group "Common" "${COMMON_PACKAGES[@]}"
    install_package_group "PHP" "${PHP_PACKAGES[@]}"
    install_package_group "Web Server" "${WEB_PACKAGES[@]}"
    install_package_group "WireGuard" "${WIREGUARD_PACKAGES[@]}"

    detect_php

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

    chown -R "$WEB_USER:$WEB_GROUP" "$INSTALL_DIRECTORY"
    chmod -R 755 "$INSTALL_DIRECTORY"

    configure_nginx
    configure_wireguard
    check_native
    print_success "Native installation completed."
}

detect_php() {
    PHP_VERSION="$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')"
    PHP_FPM_SOCKET="/run/php/php${PHP_VERSION}-fpm.sock"

    [[ -S "$PHP_FPM_SOCKET" ]] || fatal "PHP-FPM socket not found: $PHP_FPM_SOCKET"

    systemctl enable "php${PHP_VERSION}-fpm"
    systemctl restart "php${PHP_VERSION}-fpm"

    systemctl is-active --quiet "php${PHP_VERSION}-fpm" || fatal "PHP-FPM failed to start."

    print_success "Detected PHP $PHP_VERSION"
}

configure_nginx() {
    print_info "Configure Nginx?"

    while true; do
        echo
        echo "1) Configure Nginx"
        echo "2) Skip"
        echo

        read -rp "Choose: " choice

        case "$choice" in
            1)
                print_info "Configuring Nginx..."

                cat > "$NGINX_CONFIG" <<EOF
server {
    listen 80;
    listen [::]:80;

    server_name $SERVER_NAME;

    root $WEB_ROOT;
    index index.php index.html;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:$PHP_FPM_SOCKET;
    }

    location ~ /\. {
        deny all;
    }
}
EOF

                ln -sf "$NGINX_CONFIG" "$NGINX_ENABLED"
                rm -f /etc/nginx/sites-enabled/default

                nginx -t || fatal "Nginx configuration test failed."

                systemctl enable nginx
                systemctl restart nginx

                systemctl is-active --quiet nginx \
                    || fatal "Nginx failed to start."

                print_success "Nginx configured."

                break
                ;;
            2)
                print_warning "Skipping Nginx configuration."
                break
                ;;
            *)
                print_error "Invalid selection."
                ;;
        esac
    done
}

configure_wireguard() {
    print_info "Configuring WireGuard..."

    local config="/etc/wireguard/${WIREGUARD_INTERFACE}.conf"

    if [[ -f "$config" ]]; then
        print_success "WireGuard configuration found."
    else
        print_warning "WireGuard configuration not found."

        while true; do
            echo
            echo "1) Create new configuration"
            echo "2) Skip"
            echo

            read -rp "Choose: " choice

            case "$choice" in
                1)
                    wg genkey | tee /etc/wireguard/private.key | wg pubkey >/etc/wireguard/public.key

                    chmod 600 /etc/wireguard/private.key
                    chmod 644 /etc/wireguard/public.key

                    cat > "$config" <<EOF
[Interface]
Address = 10.0.0.1/24
ListenPort = 51820
PrivateKey = $(cat /etc/wireguard/private.key)
EOF

                    chmod 600 "$config"

                    print_success "WireGuard configuration created."
                    break
                    ;;
                2)
                    return
                    ;;
                *)
                    print_error "Invalid selection."
                    ;;
            esac
        done
    fi

    systemctl enable "wg-quick@${WIREGUARD_INTERFACE}"

    if systemctl start "wg-quick@${WIREGUARD_INTERFACE}"; then
        print_success "WireGuard started."
    else
        print_warning "Unable to start WireGuard."
    fi
}