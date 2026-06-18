import { AppState } from '../utils/state.js';
import { API_BASE_URL } from '../utils/constants.js';

const ApiClient = {
    async request(endpoint, options = {}) {
        const url = `${API_BASE_URL}/${endpoint}`;
        const headers = {
            'Content-Type': 'application/json',
            ...options.headers
        };
        
        if (AppState.token) {
            headers['Authorization'] = `Bearer ${AppState.token}`;
        }
        
        try {
            const response = await fetch(url, {
                ...options,
                headers
            });
            
            const data = await response.json();
            
            if (!response.ok) {
                if (response.status === 401) {
                    const authSuccess = await AuthManager.checkAuth();
                    if (authSuccess) {
                        return await this.request(endpoint, options);
                    }
                    throw new Error(data.message || 'Unauthorized');
                }
                throw new Error(data.message || 'Request failed');
            }
            
            return data;
        } catch (error) {
            throw error;
        }
    },
    
    async get(endpoint) {
        return this.request(endpoint, { method: 'GET' });
    },
    
    async post(endpoint, data) {
        return this.request(endpoint, { method: 'POST', body: JSON.stringify(data) });
    },
    
    async put(endpoint, data) {
        return this.request(endpoint, { method: 'PUT', body: JSON.stringify(data) });
    },
    
    async delete(endpoint, data) {
        return this.request(endpoint, { method: 'DELETE', body: JSON.stringify(data) });
    }
};

export { ApiClient };