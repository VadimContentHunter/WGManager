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
        this.endpoint = document.getElementById('endpoint');
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
        const settings = await this.api.get('/api/settings');

        this.configPath.value = settings.ConfigPath ?? '';
        this.endpoint.value = settings.Endpoint ?? '';
        this.dns.value = settings.DNS ?? '';
        this.allowedIps.value = settings.AllowedIPs ?? '';
        this.persistentKeepalive.value = settings.PersistentKeepalive ?? '';
    }

    async save() {
        this.setDisabled(true);
        this.setLoading(true);

        try {
            await this.api.put('/api/settings', {
                ConfigPath: this.configPath.value,
                Endpoint: this.endpoint.value,
                DNS: this.dns.value,
                AllowedIPs: this.allowedIps.value,
                PersistentKeepalive: this.persistentKeepalive.value
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
        const result = await this.api.get('/api/api-key');

        this.apiKey.value = result.apiKey ?? '';
        this.apiKeyStatus.textContent = result.apiKey
            ? 'Создан'
            : 'Не создан';

        this.api.apiKey = result.apiKey ?? null;
    }

    async rotateApiKey() {
        this.setDisabled(true);
        this.setLoading(true);

        try {
            const result = await this.api.put('/api/api-key');

            this.apiKey.value = result.apiKey;
            this.apiKeyStatus.textContent = 'Создан';
            this.api.apiKey = result.apiKey;

            this.notify.success('API-ключ успешно обновлён.');
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