class SettingsModal {

    constructor(api, notify) {

        this.api = api;
        this.notify = notify;

        this.modal = document.getElementById('settings-modal');
        this.modalContent = this.modal.querySelector('.modal-content');
        this.loader = this.modal.querySelector('.modal-loader');
        this.formControls = this.modal.querySelectorAll('input, button, select, textarea');

        this.openButton = document.getElementById('settings-button');
        this.closeButton = document.getElementById('settings-close');
        this.cancelButton = document.getElementById('settings-cancel');
        this.saveButton = document.getElementById('settings-save');
        this.rotateApiKeyButton = document.getElementById('generate-api-key');

        this.configPath = document.getElementById('config-path');
        this.clientsPath = document.getElementById('clients-path');
        this.server = document.getElementById('server');
        this.serverPort = document.getElementById('server-port');
        this.dns = document.getElementById('dns');
        this.allowedIps = document.getElementById('allowed-ips');
        this.persistentKeepalive = document.getElementById('persistent-keepalive');

        this.apiKey = document.getElementById('api-key');
        this.apiKeyStatus = document.getElementById('api-key-status');

        this.registerEvents();
    }

    registerEvents() {

        this.openButton.addEventListener('click', () => this.open());
        this.closeButton.addEventListener('click', () => this.close());
        this.cancelButton.addEventListener('click', () => this.close());
        this.saveButton.addEventListener('click', () => this.save());
        this.rotateApiKeyButton.addEventListener('click', () => this.rotateApiKey());

        this.modal.addEventListener('click', event => {

            if (event.target === this.modal) {
                this.close();
            }

        });

        document.addEventListener('keydown', event => {

            if (
                event.key === 'Escape' &&
                !this.modal.classList.contains('hidden')
            ) {
                this.close();
            }

        });

    }

    async load() {

        await Promise.all([
            this.loadSettings(),
            this.loadApiKey()
        ]);

    }

    async open() {
        this.modal.classList.remove('hidden');

        this.setDisabled(true);
        this.setLoading(true);

        try {
            await this.load();
        } catch (e) {
            this.notify.error(e.message);
        } finally {
            this.setLoading(false);
            this.setDisabled(false);
        }
    }

    close() {

        this.modal.classList.add('hidden');

    }

    async loadSettings() {
        const response = await this.api.settings.get();
        const settings = response.data ?? {};

        this.configPath.value = settings.configPath ?? '';
        this.clientsPath.value = settings.clientsPath ?? '';
        this.server.value = settings.server ?? '';
        this.serverPort.value = settings.serverPort ?? '';
        this.dns.value = settings.dns ?? '';
        this.allowedIps.value = settings.allowedIps ?? '';
        this.persistentKeepalive.value = settings.persistentKeepalive ?? '';
    }

    async save() {
        this.setDisabled(true);
        this.setLoading(true);

        try {
            await this.api.settings.update({
                configPath: this.configPath.value,
                clientsPath: this.clientsPath.value,
                server: this.server.value,
                serverPort: Number(this.serverPort.value),
                dns: this.dns.value,
                allowedIps: this.allowedIps.value,
                persistentKeepalive: this.persistentKeepalive.value
            });

            this.notify.success('Настройки сохранены.');

            this.close();
        } catch (e) {
            this.notify.error(e.message);
        } finally {
            this.setLoading(false);
            this.setDisabled(false);
        }
    }

    async loadApiKey() {
        const response = await this.api.apiKeys.get();
        const result = response.data ?? {};

        this.apiKey.value = result.apiKey ?? '';
        this.apiKeyStatus.value = result.apiKey
            ? 'Создан'
            : 'Не создан';

        this.api.apiKey = result.apiKey ?? null;
    }

    async rotateApiKey() {
        this.setDisabled(true);
        this.setLoading(true);

        try {
            const response = await this.api.apiKeys.rotate();
            const result = response.data ?? {};

            this.apiKey.value = result.apiKey ?? '';
            this.apiKeyStatus.value = 'Создан';
            this.api.apiKey = result.apiKey ?? null;

            this.notify.success(
                'API-ключ успешно обновлён.'
            );
        } catch (e) {
            this.notify.error(e.message);
        } finally {
            this.setLoading(false);
            this.setDisabled(false);
        }
    }

    setLoading(state) {
        this.modalContent.classList.toggle(
            'modal-loading',
            state
        );

        this.loader.classList.toggle(
            'hidden',
            !state
        );
    }

    setDisabled(state) {
        this.formControls.forEach(element => {
            element.disabled = state;
        });
    }

}