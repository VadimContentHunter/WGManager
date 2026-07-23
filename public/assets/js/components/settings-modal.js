class SettingsModal {

    constructor(api, notify) {
        this.api = api;
        this.notify = notify;

        this.modal = document.getElementById('settings-modal');

        this.openButton = document.getElementById('settings-button');
        this.closeButton = document.getElementById('settings-close');
        this.cancelButton = document.getElementById('settings-cancel');
        this.saveButton = document.getElementById('settings-save');

        this.generateApiKeyButton = document.getElementById(
            'generate-api-key'
        );

        this.configPath = document.getElementById('config-path');
        this.endpoint = document.getElementById('endpoint');
        this.dns = document.getElementById('dns');
        this.allowedIps = document.getElementById('allowed-ips');
        this.persistentKeepalive = document.getElementById(
            'persistent-keepalive'
        );

        this.apiKey = document.getElementById('api-key');
        this.apiKeyStatus = document.getElementById(
            'api-key-status'
        );

        this.registerEvents();
    }

    registerEvents() {

        this.openButton.addEventListener(
            'click',
            () => this.open()
        );

        this.closeButton.addEventListener(
            'click',
            () => this.close()
        );

        this.cancelButton.addEventListener(
            'click',
            () => this.close()
        );

        this.saveButton.addEventListener(
            'click',
            () => this.save()
        );

        this.generateApiKeyButton.addEventListener(
            'click',
            () => this.generateApiKey()
        );

        this.modal.addEventListener(
            'click',
            event => {

                if (event.target === this.modal) {
                    this.close();
                }

            }
        );

        document.addEventListener(
            'keydown',
            event => {

                if (
                    event.key === 'Escape' &&
                    !this.modal.classList.contains('hidden')
                ) {
                    this.close();
                }

            }
        );
    }

    async load() {
        await Promise.all([
            this.loadSettings(),
            this.loadApiKey()
        ]);
    }

    async open() {
        await this.load();

        this.modal.classList.remove('hidden');
    }

    close() {
        this.modal.classList.add('hidden');
    }

    async loadSettings() {

        try {

            const settings = await this.api.get(
                '/api/settings'
            );

            this.configPath.value =
                settings.ConfigPath ?? '';

            this.endpoint.value =
                settings.Endpoint ?? '';

            this.dns.value =
                settings.DNS ?? '';

            this.allowedIps.value =
                settings.AllowedIPs ?? '';

            this.persistentKeepalive.value =
                settings.PersistentKeepalive ?? '';

        } catch (e) {

            this.notify.error(
                e.message
            );

        }

    }

    async save() {

        try {

            await this.api.put(
                '/api/settings',
                {
                    ConfigPath: this.configPath.value,
                    Endpoint: this.endpoint.value,
                    DNS: this.dns.value,
                    AllowedIPs: this.allowedIps.value,
                    PersistentKeepalive:
                        this.persistentKeepalive.value
                }
            );

            this.notify.success(
                'Настройки сохранены'
            );

            this.close();

        } catch (e) {

            this.notify.error(
                e.message
            );

        }

    }

    async loadApiKey() {

        try {

            const result = await this.api.get(
                '/api/api-key'
            );

            this.apiKey.value =
                result.ApiKey ?? '';

            this.apiKeyStatus.textContent =
                result.ApiKey
                    ? 'Создан'
                    : 'Не создан';

            if (result.ApiKey) {
                this.api.apiKey = result.ApiKey;
            }

        } catch (e) {

            this.apiKey.value = '';
            this.apiKeyStatus.textContent =
                'Не создан';

        }

    }

    async generateApiKey() {

        try {

            const result = await this.api.post(
                '/api/api-key'
            );

            this.apiKey.value =
                result.ApiKey;

            this.apiKeyStatus.textContent =
                'Создан';

            this.api.apiKey = result.ApiKey;

            this.notify.success(
                'API Key успешно создан'
            );

        } catch (e) {

            this.notify.error(
                e.message
            );

        }

    }

}