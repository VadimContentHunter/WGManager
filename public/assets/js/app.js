class App {

   constructor() {
        this.initComponents();
        this.registerEvents();
    }

    initComponents() {
        this.api = new Api();

        this.notify = new Notify(
            document.getElementById('notification')
        );

        this.clientTable = new ClientTable(
            document.getElementById('clients-table'),
            document.getElementById('client-search')
        );

        this.settingsModal = new SettingsModal(
            this.api,
            this.notify
        );

        this.refreshButton = document.getElementById(
            'refresh-clients'
        );
    }

    registerEvents() {
        this.refreshButton.addEventListener(
            'click',
            () => this.loadClients()
        );

        this.clientTable.onAction = (
            action,
            client
        ) => this.handleClientAction(
            action,
            client
        );
    }

    async init() {
        await this.settingsModal.load();
        await this.loadClients();
    }

    async loadClients() {
        try {
            const clients = await this.api.get(
                '/api/clients'
            );

            this.clientTable.setClients(clients);

        } catch (e) {
            this.notify.error(e.message);
        }
    }

    async handleClientAction(action, client) {

        switch (action) {

            case 'download':
                return this.downloadClient(client);

            case 'edit':
                return this.editClient(client);

            case 'delete':
                return this.deleteClient(client);
        }
    }

    async downloadClient(client) {
        try {

            await this.api.download(
                `/api/clients/${client.PublicKey}/config`,
                `${client.Name}.conf`
            );

        } catch (e) {
            this.notify.error(e.message);
        }
    }

    editClient(client) {
        console.log(
            'Edit client:',
            client
        );
    }

    async deleteClient(client) {
        console.log(
            'Delete client:',
            client
        );
    }

}

document.addEventListener(
    'DOMContentLoaded',
    async () => {
        const app = new App();
        await app.init();
    }
);