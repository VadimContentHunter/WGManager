class Notify {

    constructor() {
        this.container = document.getElementById('notify');
        this.notifications = [];
        this.maxNotifications = 10;
    }

    success(message) {

        this.show(message,'success');

    }

    error(message) {
        this.show(message,'error');
    }

    warning(message) {
        this.show(message,'warning');

    }

    info(message) {
        this.show(message,'info');
    }

    show(message, type = 'info') {
        const notification = document.createElement('div');

        notification.className =
            `notify notify-${type}`;

        notification.innerHTML = `
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
        `;

        notification
            .querySelector('.notify-close')
            .addEventListener(
                'click',
                () => this.remove(notification)
            );

        this.container.prepend(notification);
        this.notifications.unshift(notification);
        while (this.notifications.length > this.maxNotifications) {
            this.remove(
                this.notifications.at(-1),
                false
            );
        }

    }

    remove(notification, removeFromArray = true) {
        notification.remove();
        if (!removeFromArray) {
            return;
        }

        this.notifications =
            this.notifications.filter(item => item !== notification);

    }
}