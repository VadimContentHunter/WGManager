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

        this.onCreate = null;

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

    }

    open() {
        this.name.value = '';
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
        const name = this.name.value.trim();
        if (!name) {
            this.notify.error(
                'Введите имя клиента.'
            );

            this.name.focus();

            return;
        }

        this.createButton.disabled = true;
        try {
            const client = await this.api.post('/clients',{
                Name: name
            });

            this.notify.success(
                'Клиент успешно создан.'
            );

            this.close();
            if (this.onCreate) {
                this.onCreate(client);
            }

        } catch (error) {
            this.notify.error(
                error.message
            );
        } finally {
            this.createButton.disabled = false;
        }

    }
}