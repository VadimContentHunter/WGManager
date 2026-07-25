class ApiKeyModal {

    constructor(api, notify) {
        this.api = api;
        this.notify = notify;

        this.modal = document.getElementById('api-key-modal');
        this.input = document.getElementById('api-key-input');
        this.button = document.getElementById('api-key-login');

        this.registerEvents();

    }

    registerEvents() {
        this.button.addEventListener('click', () => this.submit());
        this.input.addEventListener('keydown', event => {
            if (event.key === 'Enter') {
                this.submit();
            }

        });

    }

    open() {
        this.input.value = '';
        this.modal.classList.remove('hidden');
        this.input.focus();

    }

    close() {
        this.modal.classList.add('hidden');
    }

    async submit() {
        const apiKey = this.input.value.trim();
        if (!apiKey) {

            this.notify.warning('Введите API-ключ.');

            this.input.focus();

            return;

        }

        this.api.apiKey = apiKey;
        try {
            await this.api.get('/api/auth/check');
            this.close();
            this.notify.success('Авторизация выполнена.');
            location.reload();
        } catch (e) {
            this.api.apiKey = null;
            this.notify.error(e.message);
            this.input.select();
        }

    }

}