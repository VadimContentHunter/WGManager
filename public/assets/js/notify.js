class Notify {

    constructor(element) {

        this.element = element;

        this.timeout = null;
    }

    /**
     * Успешное сообщение.
     */
    success(message) {

        this.show(
            message,
            'success'
        );
    }

    /**
     * Ошибка.
     */
    error(message) {

        this.show(
            message,
            'error'
        );
    }

    /**
     * Предупреждение.
     */
    warning(message) {

        this.show(
            message,
            'warning'
        );
    }

    /**
     * Информация.
     */
    info(message) {

        this.show(
            message,
            'info'
        );
    }

    /**
     * Показать уведомление.
     */
    show(message, type = 'info') {

        clearTimeout(
            this.timeout
        );

        this.element.className =
            `notification ${type}`;

        this.element.textContent =
            message;

        this.element.classList.remove(
            'hidden'
        );

        this.timeout = setTimeout(
            () => this.hide(),
            5000
        );
    }

    /**
     * Скрыть уведомление.
     */
    hide() {

        clearTimeout(
            this.timeout
        );

        this.element.classList.add(
            'hidden'
        );

        this.element.textContent = '';
    }
}