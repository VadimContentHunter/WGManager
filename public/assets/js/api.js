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

    get clients() {
        return {
            list: () => this.get('/api/clients'),

            show: (publicKey) =>
                this.get(`/api/clients/${publicKey}`),

            create: (data) =>
                this.post('/api/clients', data),

            update: (publicKey, data) =>
                this.put(`/api/clients/${publicKey}`, data),

            delete: (publicKey) =>
                this.delete(`/api/clients/${publicKey}`),

            download: (publicKey, filename = null) =>
                this.download(
                    `/api/clients/${publicKey}/config`,
                    filename
                ),
        };
    }

    get settings() {
        return {
            get: () =>
                this.get('/api/settings'),

            update: (data) =>
                this.put('/api/settings', data),
        };
    }

    get apiKeys() {
        return {
            get: () =>
                this.get('/api/api-key'),

            create: () =>
                this.post('/api/api-key'),

            rotate: () =>
                this.put('/api/api-key'),
        };
    }

    get setup() {
        return {
            get: () =>
                this.get('/api/setup'),

            install: () =>
                this.post('/api/setup/install'),

            update: () =>
                this.post('/api/setup/update'),

            initialize: () =>
                this.post('/api/setup/initialize'),

            start: () =>
                this.post('/api/setup/start'),

            stop: () =>
                this.post('/api/setup/stop'),

            restart: () =>
                this.post('/api/setup/restart'),
        };
    }

    get auth() {
        return {
            check: () =>
                this.get('/api/auth/check'),
        };
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