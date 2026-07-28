class App {

    static AUTO_REFRESH_INTERVAL = 2000;

    constructor() {
        this.refreshInterval = null;
        this.loadingClients = false;

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
        await this.refresh();
        this.startAutoRefresh();
        this.startAutoRefresh();

    }

    async loadClients() {
        if (this.loadingClients) {
            return;
        }

        this.loadingClients = true;
        try {
            const response = await this.api.clients.list();
            this.clientTable.setClients(
                response.data ?? []
            );
        } catch (e) {
            this.notify.error(e.message);
        } finally {
            this.loadingClients = false;
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
                client.publicKey,
                `${client.name}.conf`
            );
        } catch (e) {
            this.notify.error(
                e.message
            );

        }

    }

    editClient(client) {
        this.clientModal.open(client);
    }

    async deleteClient(client) {
        this.clientDeleteModal.open(
            client
        );
    }

    async refresh() {
        await Promise.all([
            this.loadClients(),
            this.systemStatus.load()
        ]);
    }

    startAutoRefresh() {
        this.stopAutoRefresh();

        this.refreshInterval = setInterval(
            () => this.refresh(),
            App.AUTO_REFRESH_INTERVAL
        );
    }

    stopAutoRefresh() {
        if (this.refreshInterval !== null) {
            clearInterval(this.refreshInterval);
            this.refreshInterval = null;
        }
    }

}

document.addEventListener(
    'DOMContentLoaded',
    async () => {
        const app = new App();
        await app.init();

    }
);