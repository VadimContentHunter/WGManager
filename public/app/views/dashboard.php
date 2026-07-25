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
    <div class="modal-content modal-md">
        <div class="modal-header">
            <h2 class="modal-title">
                <i class="fa-solid fa-gear"></i>
                Настройки
            </h2>
            <button
                id="settings-close"
                class="modal-close"
                type="button">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="modal-body">
            <h3>WireGuard</h3>

            <div class="field">
                <label for="config-path">Config Path</label>
                <input
                    id="config-path"
                    type="text">
            </div>

            <div class="field-row">
                <div class="field">
                    <label for="endpoint">Endpoint</label>
                    <input
                        id="endpoint"
                        type="text">
                </div>

                <div class="field">
                    <label for="dns">DNS</label>
                    <input
                        id="dns"
                        type="text">
                </div>
            </div>

            <div class="field-row">
                <div class="field">
                    <label for="allowed-ips">Allowed IPs</label>
                    <input
                        id="allowed-ips"
                        type="text">
                </div>

                <div class="field">
                    <label for="persistent-keepalive">Keepalive</label>
                    <input
                        id="persistent-keepalive"
                        type="number">
                </div>
            </div>

            <div class="modal-divider"></div>

            <h3>API Key</h3>

            <div class="field-row">
                <div class="field">
                    <label for="api-key-status">Статус</label>
                    <input
                        id="api-key-status"
                        type="text"
                        readonly>
                </div>

                <div class="field">
                    <label for="api-key">API Key</label>
                    <input
                        id="api-key"
                        type="text"
                        readonly>
                </div>
            </div>

            <button id="generate-api-key">
                <i class="fa-solid fa-key"></i>
                Пересоздать ключ
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

        <div class="modal-loader hidden">
            <div class="loader-spinner"></div>
        </div>
    </div>
</div>
<div id="api-key-modal" class="modal hidden">
    <div class="modal-content modal-sm">
        <div class="modal-header">
            <h2 class="modal-title">
                <i class="fa-solid fa-key"></i>
                Авторизация
            </h2>
        </div>

        <div class="modal-body">
            <p>
                Для доступа к панели управления необходимо ввести API-ключ.
            </p>

            <div class="field">
                <label for="api-key-input">
                    API Key
                </label>

                <input
                    id="api-key-input"
                    type="password"
                    placeholder="Введите API Key">
            </div>
        </div>

        <div class="modal-footer">
            <button id="api-key-login">
                <i class="fa-solid fa-right-to-bracket"></i>
                Войти
            </button>
        </div>
    </div>
</div>

<div id="client-modal" class="modal hidden">
</div>