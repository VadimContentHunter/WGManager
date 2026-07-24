class Notify {

    constructor() {

        this.container = document.getElementById(
            'notify'
        );

    }

    success(message) {

        this.show(
            message,
            'success'
        );

    }

    error(message) {

        this.show(
            message,
            'error'
        );

    }

    warning(message) {

        this.show(
            message,
            'warning'
        );

    }

    info(message) {

        this.show(
            message,
            'info'
        );

    }

    show(message, type = 'info') {

        this.container.innerHTML = `
            <div class="notify notify-${type}">
                <span class="notify-message">
                    ${message}
                </span>

                <button
                    class="notify-close"
                    type="button"
                    aria-label="Закрыть"
                >
                    &times;
                </button>
            </div>
        `;

        this.container
            .querySelector('.notify-close')
            .addEventListener(
                'click',
                () => this.hide()
            );

    }

    hide() {

        this.container.innerHTML = '';

    }

}