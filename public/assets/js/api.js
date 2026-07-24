class Api {

    constructor(baseUrl = '') {

        this.baseUrl = baseUrl;
    }

    /**
     * Возвращает API Key.
     */
    get apiKey() {

        return localStorage.getItem('apiKey');
    }

    /**
     * Сохраняет API Key.
     */
    set apiKey(value) {

        if (value === null) {

            localStorage.removeItem('apiKey');

            return;
        }

        localStorage.setItem('apiKey', value);
    }

    /**
     * GET
     */
    async get(url) {

        return this.request(url);
    }

    /**
     * POST
     */
    async post(url, body = {}) {

        return this.request(url, {
            method: 'POST',
            body
        });
    }

    /**
     * PUT
     */
    async put(url, body = {}) {

        return this.request(url, {
            method: 'PUT',
            body
        });
    }

    /**
     * DELETE
     */
    async delete(url) {

        return this.request(url, {
            method: 'DELETE'
        });
    }

    /**
     * PATCH
     */
    async patch(url, body = {}) {

        return this.request(url, {
            method: 'PATCH',
            body
        });
    }

    /**
     * Универсальный запрос.
     */
    async request(url, options = {}) {

        const headers = {

            Accept: 'application/json',

            ...(options.headers ?? {})
        };

        if (this.apiKey) {

            headers['X-API-Key'] = this.apiKey;
        }

        if (options.body !== undefined) {

            headers['Content-Type'] = 'application/json';

            options.body = JSON.stringify(
                options.body
            );
        }

        const response = await fetch(

            this.baseUrl + url,

            {
                ...options,
                headers
            }
        );

        const contentType = response.headers.get(
            'Content-Type'
        ) ?? '';

        let data = null;

        if (
            contentType.includes(
                'application/json'
            )
        ) {

            data = await response.json();
        }

        if (!response.ok) {

            throw new Error(

                data?.message ??

                `HTTP ${response.status}`
            );
        }

        if (
            data &&
            data.success === false
        ) {

            throw new Error(
                data.message
            );
        }

        return data;
    }

    /**
     * Скачать файл.
     */
    async download(url, filename = null) {

        const headers = {};

        if (this.apiKey) {

            headers['X-API-Key'] = this.apiKey;
        }

        const response = await fetch(

            this.baseUrl + url,

            {
                headers
            }
        );

        if (!response.ok) {

            throw new Error(
                'Ошибка скачивания.'
            );
        }

        const blob = await response.blob();

        const objectUrl = URL.createObjectURL(
            blob
        );

        const link = document.createElement(
            'a'
        );

        link.href = objectUrl;

        link.download = filename ?? '';

        document.body.appendChild(
            link
        );

        link.click();

        link.remove();

        URL.revokeObjectURL(
            objectUrl
        );
    }
}