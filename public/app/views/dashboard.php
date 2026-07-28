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
                <i class="fa-solid fa-server"></i>
                Состояние системы
            </h2>
        </div>

        <table>
            <tbody>

                <tr>
                    <td>WireGuard</td>
                    <td id="wg-installed">
                        <span class="status">—</span>
                    </td>
                </tr>

                <tr>
                    <td>Версия</td>
                    <td id="wg-version">—</td>
                </tr>

                <tr>
                    <td>Права администратора</td>
                    <td id="wg-root">
                        <span class="status">—</span>
                    </td>
                </tr>

                <tr>
                    <td>Состояние WireGuard</td>
                    <td id="wg-running">
                        <span class="status">—</span>
                    </td>
                </tr>

                <tr>
                    <td>Конфигурация</td>
                    <td id="wg-config">
                        <span class="status">—</span>
                    </td>
                </tr>

                <tr>
                    <td>Каталог клиентов</td>
                    <td id="wg-clients">
                        <span class="status">—</span>
                    </td>
                </tr>

            </tbody>
        </table>

        <div class="actions system-actions">

            <button
                id="wg-initialize"
                disabled>
                <i class="fa-solid fa-wrench"></i>
                Инициализировать
            </button>

            <button
                id="wg-start"
                disabled>
                <i class="fa-solid fa-play"></i>
                Запустить
            </button>

            <button
                id="wg-stop"
                disabled>
                <i class="fa-solid fa-stop"></i>
                Остановить
            </button>

            <button
                id="wg-restart"
                disabled>
                <i class="fa-solid fa-rotate"></i>
                Перезапустить
            </button>

        </div>
    </section>

    <section class="card">
        <div class="card-header">
            <h2>
                <i class="fa-solid fa-users"></i>
                Клиенты WireGuard
            </h2>
            <div class="actions">
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
                    <th>Статус</th>
                    <th>Handshake</th>
                    <th>RX</th>
                    <th>TX</th>
                    <th></th>
                </tr>
            </thead>

            <tbody id="clients-table">
                <tr>
                    <td colspan="7" class="empty">
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

            <div class="field">
                <label for="clients-path">Clients Path</label>
                <input
                    id="clients-path"
                    type="text">
            </div>

            <div class="field-row">
                <div class="field">
                    <label for="server">
                        Server
                    </label>

                    <input
                        id="server"
                        type="text">
                </div>

                <div class="field">
                    <label for="server-port">
                        Server Port
                    </label>

                    <input
                        id="server-port"
                        type="number"
                        min="1"
                        max="65535">
                </div>
            </div>

            <div class="field">
                <label for="dns">
                    DNS
                </label>

                <input id="dns" type="text">
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
    <div class="modal-content modal-sm">
        <div class="modal-header">
            <h2 id="client-title" class="modal-title">
                <i class="fa-solid fa-user-plus"></i>
                Добавить клиента
            </h2>

            <button
                id="client-close"
                class="modal-close"
                type="button">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="modal-body">
            <div class="field">
                <label for="client-name">
                    Имя клиента
                </label>

                <input
                    id="client-name"
                    type="text"
                    maxlength="32"
                    autocomplete="off"
                    spellcheck="false"
                    placeholder="Например: phone">

                <div
                    id="client-name-error"
                    class="field-error hidden">
                </div>
            </div>

        </div>
        <div class="modal-footer">
            <button id="client-cancel">
                Отмена
            </button>

            <button id="client-create">
                <i class="fa-solid fa-plus"></i>
                Создать
            </button>
        </div>
    </div>

</div>

<div id="client-delete-modal" class="modal hidden">
    <div class="modal-content modal-sm">
        <div class="modal-header">
            <h2 class="modal-title">
                <i class="fa-solid fa-trash"></i>
                Удаление клиента
            </h2>
            <button
                id="client-delete-close"
                class="modal-close"
                type="button">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="modal-body">
            <p>
                Вы действительно хотите удалить клиента
                <strong id="client-delete-name"></strong>?
            </p>
            <p>
                Это действие нельзя отменить.
            </p>
        </div>
        <div class="modal-footer">
            <button id="client-delete-cancel">
                Отмена
            </button>
            <button id="client-delete-confirm">
                <i class="fa-solid fa-trash"></i>
                Удалить
            </button>
        </div>
    </div>
</div>