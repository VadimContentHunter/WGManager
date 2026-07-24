<header class="header">

    <div class="logo">
        <i class="fa-solid fa-shield-halved"></i>
        WGManager
    </div>

    <button id="settings-button" class="icon-button">
        <i class="fa-solid fa-gear"></i>
        Настройки
    </button>

</header>

<main class="container">

    <div id="notification" class="notification hidden">

    </div>

    <section class="card">

        <div class="card-header">

            <h2>
                <i class="fa-solid fa-users"></i>
                Клиенты WireGuard
            </h2>

            <div class="actions">

                <button id="refresh-clients">
                    <i class="fa-solid fa-rotate"></i>
                    Обновить
                </button>

                <button id="create-client">
                    <i class="fa-solid fa-plus"></i>
                    Добавить
                </button>

            </div>

        </div>

        <input
            id="client-search"
            class="search"
            type="text"
            placeholder="Поиск клиента...">

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

                <tr>

                    <td colspan="6" class="empty">

                        Клиенты отсутствуют

                    </td>

                </tr>

            </tbody>

        </table>

    </section>

</main>

<div id="settings-modal" class="modal hidden">

    <div class="modal-window">

        <div class="modal-header">

            <h2>
                <i class="fa-solid fa-gear"></i>
                Настройки
            </h2>

            <button
                id="settings-close"
                class="icon-button">
                <i class="fa-solid fa-xmark"></i>
            </button>

        </div>

        <div class="modal-body">

            <h3>WireGuard</h3>

            <div class="field">
                <label>Config Path</label>
                <input id="config-path" type="text">
            </div>

            <div class="field">
                <label>Endpoint</label>
                <input id="endpoint" type="text">
            </div>

            <div class="field">
                <label>DNS</label>
                <input id="dns" type="text">
            </div>

            <div class="field">
                <label>Allowed IPs</label>
                <input id="allowed-ips" type="text">
            </div>

            <div class="field">
                <label>Persistent Keepalive</label>
                <input id="persistent-keepalive" type="number">
            </div>

            <hr>

            <h3>API Key</h3>

            <div class="field">

                <label>Статус</label>

                <p id="api-key-status">
                    Не создан
                </p>

            </div>

            <div class="field">

                <label>Ключ</label>

                <input
                    id="api-key"
                    type="text"
                    readonly>

            </div>

            <button id="generate-api-key">
                <i class="fa-solid fa-key"></i>
                Пересоздать новый ключ
            </button>

        </div>

        <div class="modal-footer">

            <button id="settings-cancel">
                Отмена
            </button>

            <button id="settings-save">
                <i class="fa-solid fa-floppy-disk"></i>
                Сохранить
            </button>

        </div>

    </div>

</div>

<!-- Авторизация -->
<div id="api-key-modal" class="modal hidden">

    <div class="modal-content">

        <h2>Авторизация</h2>

        <p>
            Введите API-ключ для доступа к панели управления.
        </p>

        <input
            id="api-key-input"
            type="password"
            placeholder="API-ключ">

        <div class="modal-actions">

            <button id="api-key-login">
                Войти
            </button>

        </div>

    </div>

</div>

<div id="client-modal" class="modal hidden">

</div>