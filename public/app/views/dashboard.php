<header class="header">
    <h1>WGManager</h1>
</header>

<main class="container">

    <section class="card" id="api-key">

        <h2>API Key</h2>

        <p id="api-key-status">
            Статус: неизвестно
        </p>

        <button id="api-key-button">
            Загрузка...
        </button>

    </section>

    <section class="card" id="settings">

        <h2>Конфигурация WireGuard</h2>

        <div class="field">
            <label>Config Path</label>
            <input type="text" id="config-path">
        </div>

        <div class="field">
            <label>Endpoint</label>
            <input type="text" id="endpoint">
        </div>

        <div class="field">
            <label>DNS</label>
            <input type="text" id="dns">
        </div>

        <div class="field">
            <label>Allowed IPs</label>
            <input type="text" id="allowed-ips">
        </div>

        <div class="field">
            <label>Persistent Keepalive</label>
            <input type="number" id="persistent-keepalive">
        </div>

        <button id="save-settings">
            Сохранить
        </button>

    </section>

    <section class="card" id="clients">

        <div class="clients-header">

            <h2>Клиенты WireGuard</h2>

            <button id="create-client">
                + Создать клиента
            </button>

        </div>

        <table>

            <thead>

                <tr>

                    <th>Имя</th>
                    <th>IP</th>
                    <th>Handshake</th>
                    <th>RX</th>
                    <th>TX</th>
                    <th></th>

                </tr>

            </thead>

            <tbody id="clients-table">

            </tbody>

        </table>

    </section>

</main>

<div id="client-modal" class="modal hidden">

</div>