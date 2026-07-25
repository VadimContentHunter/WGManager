class Api {

    constructor(baseUrl = '') {
        this.baseUrl = baseUrl;
    }

    get apiKey() {
        return localStorage.getItem('apiKey');
    }

    set apiKey(value) {
        if (value === null) {
            localStorage.removeItem('apiKey');
            return;
        }

        localStorage.setItem('apiKey', value);
    }

    get(url) {
        return this.request(url);
    }

    post(url, body = {}) {
        return this.request(url, {
            method: 'POST',
            body
        });
    }

    put(url, body = {}) {
        return this.request(url, {
            method: 'PUT',
            body
        });
    }

    delete(url) {
        return this.request(url, {
            method: 'DELETE'
        });
    }

    patch(url, body = {}) {
        return this.request(url, {
            method: 'PATCH',
            body
        });
    }

    async request(url, options = {}) {
        document.dispatchEvent(
            new CustomEvent('request:start')
        );

        try {
            const headers = {
                Accept: 'application/json',
                ...(options.headers ?? {})
            };

            if (this.apiKey) {
                headers['X-API-Key'] = this.apiKey;
            }

            if (options.body !== undefined) {
                headers['Content-Type'] = 'application/json';
                options.body = JSON.stringify(options.body);
            }

            const response = await fetch(
                this.baseUrl + url,
                {
                    ...options,
                    headers
                }
            );

            const contentType = response.headers.get('Content-Type') ?? '';

            let data = null;

            if (contentType.includes('application/json')) {
                data = await response.json();
            }

            this.handleUnauthorized(
                response,
                data?.message
            );

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

        } finally {

            document.dispatchEvent(
                new CustomEvent('request:end')
            );

        }
    }

    async download(url, filename = null) {
        document.dispatchEvent(
            new CustomEvent('request:start')
        );

        try {
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

            this.handleUnauthorized(response);

            if (!response.ok) {
                throw new Error(
                    'Ошибка скачивания.'
                );
            }

            const blob = await response.blob();
            const objectUrl = URL.createObjectURL(blob);

            const link = document.createElement('a');
            link.href = objectUrl;
            link.download = filename ?? '';

            document.body.appendChild(link);
            link.click();
            link.remove();

            URL.revokeObjectURL(objectUrl);

        } finally {

            document.dispatchEvent(
                new CustomEvent('request:end')
            );

        }
    }

    handleUnauthorized(
        response,
        message = 'Неверный API ключ.'
    ) {
        if (response.status !== 401) {
            return;
        }

        this.apiKey = null;

        document.dispatchEvent(
            new CustomEvent('auth:required')
        );

        throw new Error(message);
    }

}