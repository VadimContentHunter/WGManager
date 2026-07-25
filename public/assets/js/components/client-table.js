class ClientTable {

    constructor(table, search) {
        this.table = table;
        this.search = search;

        this.clients = [];
        this.filteredClients = [];

        this.onAction = null;

        this.registerEvents();
    }

    registerEvents() {
        this.search.addEventListener(
            'input',
            () => this.filter()
        );

        this.table.addEventListener(
            'click',
            event => this.handleClick(event)
        );
    }

    setClients(clients) {
        this.clients = clients;
        this.filter();
    }

    filter() {
        const value = this.search.value
            .trim()
            .toLowerCase();

        this.filteredClients = this.clients.filter(client =>
            (client.Name ?? '')
                .toLowerCase()
                .includes(value)
        );

        this.render();
    }

    render() {

        if (!this.filteredClients.length) {

            this.table.innerHTML = `
                <tr>
                    <td colspan="7" class="empty">
                        Клиенты отсутствуют
                    </td>
                </tr>
            `;

            return;
        }

        this.table.innerHTML = this.filteredClients
            .map(client => this.renderRow(client))
            .join('');
    }

    renderRow(client) {
        return `
            <tr data-key="${client.PublicKey}">
                <td>${client.Name ?? '-'}</td>
                <td>${client.AllowedIPs ?? '-'}</td>
                <td>${this.renderStatus(client.Status)}</td>
                <td>${client.LastHandshake ?? '-'}</td>
                <td>${client.ReceiveBytes ?? '-'}</td>
                <td>${client.TransmitBytes ?? '-'}</td>
                <td>

                    <button
                        class="action download"
                        data-action="download"
                        data-key="${client.PublicKey}"
                    >
                        <i class="fa-solid fa-download"></i>
                    </button>

                    <button
                        class="action edit"
                        data-action="edit"
                        data-key="${client.PublicKey}"
                    >
                        <i class="fa-solid fa-pen"></i>
                    </button>

                    <button
                        class="action delete"
                        data-action="delete"
                        data-key="${client.PublicKey}"
                    >
                        <i class="fa-solid fa-trash"></i>
                    </button>

                </td>
            </tr>
        `;
    }

    renderStatus(status = '') {
        switch (status) {
            case 'OK':
                return `
                    <span class="status status-ok">
                        OK
                    </span>
                `;

            case 'Некорректный':
                return `
                    <span class="status status-error">
                        Некорректный
                    </span>
                `;

            default:
                return `
                    <span class="status">
                        —
                    </span>
                `;
        }

    }

    handleClick(event) {

        const button = event.target.closest('button');

        if (!button) {
            return;
        }

        const client = this.clients.find(client =>
            client.PublicKey === button.dataset.key
        );

        if (!client) {
            return;
        }

        this.onAction?.(
            button.dataset.action,
            client
        );
    }

}