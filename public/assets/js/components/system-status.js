class SystemStatus {

    constructor(api, notify) {

        this.api = api;
        this.notify = notify;

        this.installed = document.getElementById(
            'wg-installed'
        );

        this.version = document.getElementById(
            'wg-version'
        );

        this.root = document.getElementById(
            'wg-root'
        );

        this.running = document.getElementById(
            'wg-running'
        );

        this.config = document.getElementById(
            'wg-config'
        );

        this.clients = document.getElementById(
            'wg-clients'
        );

        this.buttons = {

            initialize: document.getElementById(
                'wg-initialize'
            ),

            start: document.getElementById(
                'wg-start'
            ),

            stop: document.getElementById(
                'wg-stop'
            ),

            restart: document.getElementById(
                'wg-restart'
            ),

        };

        this.registerEvents();

    }

    registerEvents() {

        this.buttons.initialize.addEventListener(
            'click',
            () => this.execute('initialize')
        );

        this.buttons.start.addEventListener(
            'click',
            () => this.execute('start')
        );

        this.buttons.stop.addEventListener(
            'click',
            () => this.execute('stop')
        );

        this.buttons.restart.addEventListener(
            'click',
            () => this.execute('restart')
        );

    }

    async load() {

        try {

            const response = await this.api.setup.get();

            this.render(
                response.data ?? {}
            );

        } catch (e) {

            this.notify.error(
                e.message
            );

        }

    }

    render(data) {

        this.data = data;

        this.renderStatus(
            this.installed,
            data.wireGuard?.installed,
            'Установлен',
            'Не установлен'
        );

        this.version.textContent =
            data.wireGuard?.version ?? '—';

        this.renderStatus(
            this.root,
            data.permissions?.root,
            'Есть',
            'Нет'
        );

        this.renderStatus(
            this.running,
            data.interface?.running,
            'Запущен',
            'Остановлен'
        );

        this.renderStatus(
            this.config,
            data.config?.exists &&
            data.config?.readable,
            'Доступна',
            'Недоступна'
        );

        this.renderStatus(
            this.clients,
            data.clients?.exists &&
            data.clients?.writable,
            'Доступен',
            'Недоступен'
        );

        this.updateButtons();

    }

    renderStatus(
        element,
        state,
        okText,
        errorText
    ) {

        element.innerHTML = `
            <span class="status ${
                state
                    ? 'status-ok'
                    : 'status-error'
            }">
                ${
                    state
                        ? okText
                        : errorText
                }
            </span>
        `;

    }
    
    updateButtons() {
    const installed = this.data.wireGuard?.installed === true;
    const configured = this.data.config?.exists === true && this.data.config?.readable === true;
    const running = this.data.interface?.running === true;
    const root = this.data.permissions?.root === true;

    this.buttons.initialize.disabled = !installed || !root || running;
    this.buttons.start.disabled = !configured || !root || running;
    this.buttons.stop.disabled = !root || !running;
    this.buttons.restart.disabled = !root || !running;
}

    async execute(action) {
        this.disableButtons();
        try {
            await this.api.setup[action]();
            const messages = {
                initialize: 'WireGuard успешно инициализирован.',
                start: 'WireGuard успешно запущен.',
                stop: 'WireGuard успешно остановлен.',
                restart: 'WireGuard успешно перезапущен.',
            };

            this.notify.success(
                messages[action]
            );
        } catch (e) {
            this.notify.error(
                e.message
            );
        }
        return this.load();
    }

    disableButtons() {
        Object.values(this.buttons).forEach(button => {
            button.disabled = true;
        });

    }

}