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

        this.onChange = null;
        this.loading = false;
        this.client = null;

        this.title = document.getElementById(
            'client-title'
        );
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

    open(client = null) {
        this.client = client;
        this.loading = false;
        this.clearNameError();
        this.createButton.disabled = false;
        if (client) {
            this.title.textContent = 'Редактирование клиента';
            this.name.value = client.name ?? '';
            this.createButton.innerHTML = `
                <i class="fa-solid fa-floppy-disk"></i>
                Сохранить
            `;

        } else {
            this.title.textContent = 'Создание клиента';
            this.name.value = '';
            this.createButton.innerHTML = `
                <i class="fa-solid fa-plus"></i>
                Создать
            `;

        }

        this.modal.classList.remove(
            'hidden'
        );

        this.name.focus();
        this.name.select();
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
            this.showNameError('Введите имя клиента.');
            this.name.focus();
            return;
        }

        this.loading = true;
        this.createButton.disabled = true;
        this.createButton.innerHTML = this.client
            ? `
                <i class="fa-solid fa-spinner fa-spin"></i>
                Сохранение...
            `
            : `
                <i class="fa-solid fa-spinner fa-spin"></i>
                Создание...
            `;

        try {
            let response;
            if (this.client) {
                response = await this.api.clients.update(
                    this.client.publicKey,
                    { name }
                );
                this.notify.success('Клиент успешно обновлен.');
            } else {
                response = await this.api.clients.create({ name });
                this.notify.success('Клиент успешно создан.');
            }
            this.close();
            this.onChange?.(response.data);
        } catch (e) {
            if (e.message === 'Клиент уже существует.') {
                this.showNameError(e.message);
                this.name.focus();
                return;
            }
            this.notify.error(e.message);
        } finally {
            this.loading = false;
            this.createButton.disabled = false;
            this.createButton.innerHTML = this.client
                ? `
                    <i class="fa-solid fa-floppy-disk"></i>
                    Сохранить
                `
                : `
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