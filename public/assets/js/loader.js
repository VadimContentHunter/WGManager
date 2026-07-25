class Loader {

    constructor() {
        this.element = document.getElementById('loader');
        this.requests = 0;

        document.addEventListener(
            'request:start',
            () => this.start()
        );

        document.addEventListener(
            'request:end',
            () => this.end()
        );
    }

    start() {
        this.requests++;

        if (this.requests === 1) {
            this.element.classList.remove('hidden');
        }

    }

    end() {
        this.requests = Math.max(
            0,
            this.requests - 1
        );

        if (this.requests === 0) {
            this.element.classList.add('hidden');
        }

    }

}