class App {

    constructor() {
        this.initComponents();
        this.registerEvents();

    }

    initComponents() {
        this.api = new Api();
        this.notify = new Notify();
        this.systemStatus = new SystemStatus(
            this.api,
            this.notify
        );

        this.clientTable = new ClientTable(
            document.getElementById('clients-table'),
            document.getElementById('client-search')
        );

        this.clientModal = new ClientModal(
            this.api,
            this.notify
        );

        this.clientDeleteModal = new ClientDeleteModal(
            this.api,
            this.notify
        );

        this.settingsModal = new SettingsModal(
            this.api,
            this.notify
        );

        this.apiKeyModal = new ApiKeyModal(
            this.api,
            this.notify
        );

        this.refreshButton = document.getElementById(
            'refresh-clients'
        );

        this.createButton = document.getElementById(
            'create-client'
        );

    }

    registerEvents() {
        this.refreshButton.addEventListener(
            'click',
            () => this.loadClients()
        );

        this.createButton.addEventListener(
            'click',
            () => this.clientModal.open()
        );

        this.clientModal.onChange = () => {
            this.loadClients();
        };

        this.clientDeleteModal.onChange = () => {
            this.loadClients();
        };

        this.clientTable.onAction = (action, client) => this.handleClientAction(action,client);

        document.addEventListener('auth:required', () => {
                this.clientTable.setClients([]);
                this.apiKeyModal.open();
            }
        );

    }

    async init() {
        if (!this.api.apiKey) {

            this.apiKeyModal.open();

            return;

        }

        await this.settingsModal.load();
        await this.systemStatus.load();
        await this.loadClients();

    }

    async loadClients() {
        try {
            const response = await this.api.clients.list();

            this.clientTable.setClients(
                response.data ?? []
            );

        } catch (e) {
            this.notify.error(
                e.message
            );

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
            await this.api.clients.download(
                client.PublicKey,
                `${client.Name}.conf`
            );
        } catch (e) {
            this.notify.error(
                e.message
            );

        }

    }

    editClient(client) {
        console.log(
            'Edit client:',
            client
        );

    }

    async deleteClient(client) {
        this.clientDeleteModal.open(
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