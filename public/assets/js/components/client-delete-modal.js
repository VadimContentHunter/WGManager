class ClientDeleteModal {

    constructor(api, notify) {
        this.api = api;
        this.notify = notify;

        this.modal = document.getElementById(
            'client-delete-modal'
        );

        this.title = document.getElementById(
            'client-delete-name'
        );

        this.closeButton = document.getElementById(
            'client-delete-close'
        );

        this.cancelButton = document.getElementById(
            'client-delete-cancel'
        );

        this.deleteButton = document.getElementById(
            'client-delete-confirm'
        );

        this.client = null;
        this.loading = false;
        this.onDelete = null;

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

        this.deleteButton.addEventListener(
            'click',
            () => this.remove()
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
                    event.key === 'Escape'
                    && !this.modal.classList.contains('hidden')
                ) {
                    this.close();
                }

            }
        );
    }

    open(client) {
        this.client = client;
        this.loading = false;

        this.title.textContent = client.Name;

        this.deleteButton.disabled = false;
        this.deleteButton.innerHTML = `
            <i class="fa-solid fa-trash"></i>
            Удалить
        `;

        this.modal.classList.remove(
            'hidden'
        );
    }

    close() {
        if (this.loading) {
            return;
        }

        this.modal.classList.add(
            'hidden'
        );
    }

    async remove() {
        if (this.loading|| !this.client) {
            return;
        }

        this.loading = true;
        this.deleteButton.disabled = true;
        this.deleteButton.innerHTML = `
            <i class="fa-solid fa-spinner fa-spin"></i>
            Удаление...
        `;
        try {
            await this.api.clients.delete(this.client.PublicKey);
            this.loading = false;
            this.close();
            this.notify.success('Клиент успешно удалён.');
            this.onChange?.();
        } catch (e) {
            this.notify.error(e.message);
        } finally {
            this.loading = false;
            this.deleteButton.disabled = false;
            this.deleteButton.innerHTML = `
                <i class="fa-solid fa-trash"></i>
                Удалить
            `;

        }

    }

}