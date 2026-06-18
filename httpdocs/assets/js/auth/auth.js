import { AppState } from '../utils/state.js';
import { API_BASE_URL } from '../utils/constants.js';

const AuthManager = {
    async setupAdmin(email, password, firstName, lastName) {
        try {
            const response = await fetch(`${API_BASE_URL}/setup.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ email, password, first_name: firstName, last_name: lastName })
            });
            
            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.message || 'Setup failed');
            }
            
            AppState.user = data.user;
            AppState.token = data.token;
            
            localStorage.setItem('nexus4d_token', data.token);
            localStorage.setItem('nexus4d_user', JSON.stringify(data.user));
            
            return data;
        } catch (error) {
            throw error;
        }
    },
    
    async login(email, password) {
        try {
            const response = await fetch(`${API_BASE_URL}/login.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ email, password })
            });
            
            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.message || 'Login failed');
            }
            
            AppState.user = data.user;
            AppState.token = data.token;
            
            localStorage.setItem('nexus4d_token', data.token);
            localStorage.setItem('nexus4d_user', JSON.stringify(data.user));
            
            return data;
        } catch (error) {
            throw error;
        }
    },
    
    async logout() {
        try {
            await fetch(`${API_BASE_URL}/logout.php`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${AppState.token}`
                }
            });
        } catch (error) {
            console.error('Logout error:', error);
        } finally {
            localStorage.removeItem('nexus4d_token');
            localStorage.removeItem('nexus4d_user');
            AppState.user = null;
            AppState.token = null;
        }
    },
    
    async refresh() {
        try {
            const response = await fetch(`${API_BASE_URL}/refresh.php`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${AppState.token}`
                }
            });
            
            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.message || 'Session expired');
            }
            
            AppState.user = data.user;
            AppState.token = data.token;
            
            localStorage.setItem('nexus4d_token', data.token);
            localStorage.setItem('nexus4d_user', JSON.stringify(data.user));
            
            return data;
        } catch (error) {
            localStorage.removeItem('nexus4d_token');
            localStorage.removeItem('nexus4d_user');
            AppState.user = null;
            AppState.token = null;
            throw error;
        }
    },
    
    async checkAuth() {
        const savedToken = localStorage.getItem('nexus4d_token');
        const savedUser = localStorage.getItem('nexus4d_user');
        
        if (savedToken && savedUser) {
            AppState.token = savedToken;
            AppState.user = JSON.parse(savedUser);
            
            try {
                await this.refresh();
                return true;
            } catch (error) {
                localStorage.removeItem('nexus4d_token');
                localStorage.removeItem('nexus4d_user');
                AppState.user = null;
                AppState.token = null;
                return false;
            }
        }
        
        return false;
    },
    
    getToken() {
        return AppState.token;
    },
    
    getUser() {
        return AppState.user;
    }
};

export { AuthManager };