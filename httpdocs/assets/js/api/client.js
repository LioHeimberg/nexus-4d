import { AppState } from '../utils/state.js';
import { API_BASE_URL } from '../utils/constants.js';
import { AuthManager } from '../auth/auth.js';

const ApiClient = {
    async request(endpoint, options = {}, retry = false) {
        const url = `${API_BASE_URL}/${endpoint}`;

        const headers = {
            'Content-Type': 'application/json',
            ...options.headers
        };

        if (AppState.token) {
            headers['Authorization'] = `Bearer ${AppState.token}`;
        }

        const response = await fetch(url, {
            ...options,
            headers
        });

        let data = {};

        try {
            data = await response.json();
        } catch {
            // Response was not JSON
        }

        if (!response.ok) {
            if (response.status === 401) {

                // Nur EINEN Retry erlauben
                if (!retry) {
                    const authSuccess = await AuthManager.checkAuth();

                    if (authSuccess) {
                        return await this.request(endpoint, options, true);
                    }
                }

                // Kein weiterer Retry
                throw new Error(data.message || 'Unauthorized');
            }

            throw new Error(data.message || 'Request failed');
        }

        return data;
    },

    async get(endpoint) {
        return this.request(endpoint, {
            method: 'GET'
        });
    },

    async post(endpoint, data) {
        return this.request(endpoint, {
            method: 'POST',
            body: JSON.stringify(data)
        });
    },

    async put(endpoint, data) {
        return this.request(endpoint, {
            method: 'PUT',
            body: JSON.stringify(data)
        });
    },

    async delete(endpoint, data) {
        return this.request(endpoint, {
            method: 'DELETE',
            body: JSON.stringify(data)
        });
    }
};

export { ApiClient };