class ClientModal {

    constructor(api, notify) {

        this.api = api;
        this.notify = notify;

        this.modal = document.getElementById(
            'client-modal'
        );

        this.name = document.getElementById(
            'client-name'
        );

        this.closeButton = document.getElementById(
            'client-close'
        );

        this.cancelButton = document.getElementById(
            'client-cancel'
        );

        this.createButton = document.getElementById(
            'client-create'
        );

        this.nameError = document.getElementById(
            'client-name-error'
        );

        this.onCreate = null;
        this.loading = false;
        this.registerEvents();

    }

    registerEvents() {

        this.closeButton.addEventListener(
            'click',
            () => this.close()
        );

        this.cancelButton.addEventListener(
            'click',
            () => this.close()
        );

        this.createButton.addEventListener(
            'click',
            () => this.create()
        );

        this.modal.addEventListener('click',event => {
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

        this.name.addEventListener('keydown', event => {
            if (event.key === 'Enter') {
                this.create();
            }

        });

       this.name.addEventListener(
            'input',
            () => {
                this.name.value = this.name.value
                    .replace(/[^A-Za-z0-9_-]/g, '')
                    .slice(0, 32);
    
                this.clearNameError();
            }
        );

    }

    open() {
        this.name.value = '';
        this.clearNameError();
        this.loading = false;
        this.createButton.disabled = false;
        this.createButton.innerHTML = `
            <i class="fa-solid fa-plus"></i>
            Создать
        `;

        this.modal.classList.remove(
            'hidden'
        );

        this.name.focus();

    }

    close() {
        this.modal.classList.add(
            'hidden'
        );

    }

    async create() {
        if (this.loading) {
            return;
        }

        const name = this.name.value.trim();
        if (!name) {
            this.showNameError(
                'Введите имя клиента.'
            );
            this.name.focus();
            return;
        }

        this.loading = true;
        this.createButton.disabled = true;
        this.createButton.innerHTML = `
            <i class="fa-solid fa-spinner fa-spin"></i>
            Создание...
        `;

        try {
            const client = await this.api.post('/api/clients', {
                name: name
            });

            this.notify.success(
                'Клиент успешно создан.'
            );

            this.close();
            if (this.onCreate) {
                this.onCreate(client);
            }

        } catch (error) {
            if (error.message === 'Клиент уже существует.') {
                this.showNameError(
                    error.message
                );
                this.name.focus();
                return;
            }

            this.notify.error(
                error.message
            );

        } finally {
            this.loading = false;
            this.createButton.disabled = false;
            this.createButton.innerHTML = `
                <i class="fa-solid fa-plus"></i>
                Создать
            `;
        }

    }

    showNameError(message) {
        this.name.classList.add('invalid');
        this.nameError.textContent = message;
        this.nameError.classList.remove('hidden');
    }

    clearNameError() {
        this.name.classList.remove('invalid');
        this.nameError.textContent = '';
        this.nameError.classList.add('hidden');

    }
}