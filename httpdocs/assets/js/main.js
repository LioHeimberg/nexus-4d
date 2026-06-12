const API_BASE_URL = '/api';

const AppState = {
    user: null,
    token: null,
    theme: 'light'
};

const ThemeManager = {
    toggle() {
        document.body.classList.toggle('dark-mode');
        AppState.theme = document.body.classList.contains('dark-mode') ? 'dark' : 'light';
        localStorage.setItem('nexus4d_theme', AppState.theme);
    },
    
    init() {
        const savedTheme = localStorage.getItem('nexus4d_theme');
        if (savedTheme === 'dark') {
            document.body.classList.add('dark-mode');
            AppState.theme = 'dark';
        }
        
        document.getElementById('theme-toggle').addEventListener('click', () => {
            this.toggle();
        });
    }
};

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

const Notification = {
    show(message, type = 'info') {
        const errorDiv = document.getElementById('error-message');
        if (errorDiv) {
            errorDiv.textContent = message;
            errorDiv.classList.add('visible');
            
            setTimeout(() => {
                errorDiv.classList.remove('visible');
            }, 5000);
        }
    },
    
    hide() {
        const errorDiv = document.getElementById('error-message');
        if (errorDiv) {
            errorDiv.classList.remove('visible');
        }
    }
};

const App = {
    async init() {
        ThemeManager.init();
        
        console.log('Checking auth...');
        const isAuthenticated = await AuthManager.checkAuth();
        console.log('Is authenticated:', isAuthenticated);
        
        if (isAuthenticated) {
            console.log('Showing dashboard');
            this.showDashboard();
        } else {
            console.log('Not authenticated, checking admin');
            const adminExists = await this.checkAdminExists();
            console.log('Admin exists:', adminExists);
            if (adminExists) {
                console.log('Showing login');
                this.showLogin();
            } else {
                console.log('Showing setup');
                this.showSetup();
            }
        }
        
        this.setupEventListeners();
    },
    
    async checkAdminExists() {
        try {
            const response = await fetch(`${API_BASE_URL}/check_admin.php`);
            const data = await response.json();
            return data.admin_exists;
        } catch (error) {
            return false;
        }
    },
    
    async showSetup() {
        document.getElementById('setup-page').classList.remove('hidden');
        document.getElementById('setup-page').classList.add('active');
        document.getElementById('login-page').classList.add('hidden');
        document.getElementById('dashboard').classList.add('hidden');
        document.body.classList.remove('dashboard-active');
        Notification.hide();
    },
    
    async showLogin() {
        document.getElementById('setup-page').classList.add('hidden');
        document.getElementById('login-page').classList.remove('hidden');
        document.getElementById('login-page').classList.add('active');
        document.getElementById('dashboard').classList.add('hidden');
        document.body.classList.remove('dashboard-active');
        Notification.hide();
    },
    
    async showDashboard() {
        console.log('showDashboard called');
        const setupPage = document.getElementById('setup-page');
        const loginPage = document.getElementById('login-page');
        const dashboard = document.getElementById('dashboard');
        console.log('Setup page:', setupPage, 'hidden class:', setupPage.classList.contains('hidden'));
        console.log('Login page:', loginPage, 'hidden class:', loginPage.classList.contains('hidden'));
        console.log('Dashboard:', dashboard, 'hidden class:', dashboard.classList.contains('hidden'));
        
        setupPage.classList.add('hidden');
        loginPage.classList.add('hidden');
        dashboard.classList.remove('hidden');
        document.body.classList.add('dashboard-active');
        
        console.log('After - Setup page hidden:', setupPage.classList.contains('hidden'));
        console.log('After - Login page hidden:', loginPage.classList.contains('hidden'));
        console.log('After - Dashboard hidden:', dashboard.classList.contains('hidden'));
        
        const user = AuthManager.getUser();
        document.getElementById('user-name').textContent = `${user.first_name} ${user.last_name}`;
        
        const roleBadge = document.getElementById('user-role');
        roleBadge.textContent = user.role.toUpperCase();
        roleBadge.className = `role-badge role-${user.role}`;
        
        this.renderNavMenu(user.role);
    },
    
    renderNavMenu(role) {
        const navMenu = document.getElementById('nav-menu');
        let menuItems = [];
        
        if (role === 'admin') {
            menuItems = [
                { label: 'Users', action: 'showUsers' },
                { label: 'Events', action: 'showEvents' },
                { label: 'Posts', action: 'showPosts' },
                { label: 'Reviews', action: 'showReviewsAll' },
                { label: 'Member Stats', action: 'showMemberStats' }
            ];
        } else if (role === 'boss') {
            menuItems = [
                { label: 'Users', action: 'showUsers' },
                { label: 'Events', action: 'showEvents' },
                { label: 'Posts', action: 'showPosts' },
                { label: 'Reviews', action: 'showReviewsAll' },
                { label: 'Member Stats', action: 'showMemberStats' },
                { label: 'Bars', action: 'showBars' }
            ];
        } else if (role === 'member') {
            menuItems = [
                { label: 'My Reviews', action: 'showMyReviews' },
                { label: 'Events', action: 'showEvents' },
                { label: 'Posts', action: 'showPosts' },
                { label: 'Participation', action: 'showParticipation' }
            ];
        }
        
        navMenu.innerHTML = menuItems.map(item => 
            `<li class="nav-item" data-action="${item.action}">${item.label}</li>`
        ).join('');
        
        navMenu.querySelectorAll('.nav-item').forEach(item => {
            item.addEventListener('click', () => {
                navMenu.querySelectorAll('.nav-item').forEach(i => i.classList.remove('active'));
                item.classList.add('active');
                this.handleNavigation(item.dataset.action);
            });
        });
        
        if (menuItems.length > 0) {
            navMenu.querySelector('.nav-item').classList.add('active');
            this.handleNavigation(menuItems[0].action);
        }
    },
    
    async handleNavigation(action) {
        const contentArea = document.getElementById('content-area');
        contentArea.innerHTML = '<div class="loading-spinner"><div class="spinner"></div></div>';
        
        try {
            switch (action) {
                case 'showUsers':
                    await this.showUsers();
                    break;
                case 'showEvents':
                    await this.showEvents();
                    break;
                case 'showPosts':
                    await this.showPosts();
                    break;
                case 'showReviewsAll':
                    await this.showReviewsAll();
                    break;
                case 'showMyReviews':
                    await this.showMyReviews();
                    break;
                case 'showMemberStats':
                    await this.showMemberStats();
                    break;
                case 'showBars':
                    await this.showBars();
                    break;
                case 'showParticipation':
                    await this.showParticipation();
                    break;
            }
        } catch (error) {
            Notification.show(error.message, 'error');
        }
    },
    
    setupEventListeners() {
        document.getElementById('login-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            
            try {
                await AuthManager.login(email, password);
                this.showDashboard();
                Notification.hide();
            } catch (error) {
                Notification.show(error.message, 'error');
            }
        });
        
        // Guest login button
        const guestButton = document.querySelector('.guest-button a');
        if (guestButton) {
            guestButton.addEventListener('click', async (e) => {
                e.preventDefault();
                window.location.href = '/guest-review.html';
            });
        }
        
        document.getElementById('logout-btn').addEventListener('click', async () => {
            await AuthManager.logout();
            this.showLogin();
        });
        
        document.getElementById('setup-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const email = document.getElementById('admin-email').value;
            const password = document.getElementById('admin-password').value;
            const firstName = document.getElementById('admin-firstname').value;
            const lastName = document.getElementById('admin-lastname').value;
            
            try {
                await AuthManager.setupAdmin(email, password, firstName, lastName);
                this.showDashboard();
                Notification.hide();
            } catch (error) {
                Notification.show(error.message, 'error');
            }
        });
    },
    
    async showUsers() {
        const contentArea = document.getElementById('content-area');
        const user = AuthManager.getUser();
        
        try {
            const data = await ApiClient.get('users.php');
            
            let html = `
                <h2 class="page-title">Users</h2>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">User List</span>
                        ${user.role === 'admin' || user.role === 'boss' ? 
                            `<button class="btn-primary" onclick="App.openUserModal()">+ Add User</button>` : ''}
                    </div>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Created</th>
                                    ${user.role === 'admin' || user.role === 'boss' ? '<th>Actions</th>' : ''}
                                </tr>
                            </thead>
                            <tbody>
            `;
            
            if (data.users.length === 0) {
                html += `<tr><td colspan="${user.role === 'admin' || user.role === 'boss' ? '6' : '5'}" class="empty-state">No users found</td></tr>`;
            } else {
                data.users.forEach(user => {
                    html += `
                        <tr>
                            <td>${user.id}</td>
                            <td>${user.first_name} ${user.last_name}</td>
                            <td>${user.email}</td>
                            <td><span class="role-badge role-${user.role}">${user.role}</span></td>
                            <td>${new Date(user.created_at).toLocaleDateString()}</td>
                            ${user.role !== 'admin' ? `<td>
                                <button onclick="App.editUser(${user.id})" class="btn-secondary">Edit</button>
                                <button onclick="App.deleteUser(${user.id})" class="btn-secondary" style="color: var(--status-no)">Delete</button>
                            </td>` : ''}
                        </tr>
                    `;
                });
            }
            
            html += `
                            </tbody>
                        </table>
                    </div>
                </div>
            `;
            
            contentArea.innerHTML = html;
        } catch (error) {
            Notification.show(error.message, 'error');
        }
    },
    
    async showEvents() {
        const contentArea = document.getElementById('content-area');
        const user = AuthManager.getUser();
        
        try {
            const data = await ApiClient.get('events.php');
            
            let html = `
                <h2 class="page-title">Events</h2>
                <div class="card">
                <div class="card-header">
                    <span class="card-title">Event Feed</span>
                    ${user && (user.role === 'admin' || user.role === 'boss') ? '<button class="btn-primary" onclick="App.openEventModal()">+ Add Event</button>' : ''}
                </div>
                    <div class="feed-container">
            `;
            
            if (data.events.length === 0) {
                html += `<div class="empty-state">No events found</div>`;
            } else {
                data.events.forEach(event => {
                    const eventDate = new Date(event.event_date);
                    const formattedDate = eventDate.toLocaleDateString('de-DE', { 
                        weekday: 'long', 
                        year: 'numeric', 
                        month: 'long', 
                        day: 'numeric' 
                    });
                    
                    html += `
                        <div class="feed-item">
                            <div class="feed-header">
                                <h3 class="feed-title">${event.title}</h3>
                                <span class="feed-date">${formattedDate}</span>
                            </div>
                            <div class="feed-content">
                                <p class="feed-location">📍 ${event.location}</p>
                                ${event.description ? `<p class="feed-description">${event.description}</p>` : ''}
                            </div>
                            <div class="feed-footer">
                                <span class="badge badge-yes">${event.yes_count} attending</span>
                                <span class="badge badge-maybe">${event.maybe_count} maybe</span>
                                <span class="badge badge-no">${event.no_count} not going</span>
                            </div>
                        </div>
                    `;
                });
            }
            
            html += `
                    </div>
                </div>
            `;
            
            contentArea.innerHTML = html;
        } catch (error) {
            Notification.show(error.message, 'error');
        }
    },
    
    async showPosts() {
        const contentArea = document.getElementById('content-area');
        const user = AuthManager.getUser();
        
        try {
            const data = await ApiClient.get('posts.php');
            
            let html = `
                <h2 class="page-title">Posts</h2>
                <div class="card">
                <div class="card-header">
                    <span class="card-title">Posts</span>
                    ${user && (user.role === 'admin' || user.role === 'boss') ? '<button class="btn-primary" onclick="App.openPostModal()">+ Add Post</button>' : ''}
                </div>
                <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Author</th>
                                    <th>Published</th>
                                </tr>
                            </thead>
                            <tbody>
            `;
            
            if (data.posts.length === 0) {
                html += `<tr><td colspan="3" class="empty-state">No posts found</td></tr>`;
            } else {
                data.posts.forEach(post => {
                    html += `
                        <tr>
                            <td>${post.title}</td>
                            <td>${post.first_name} ${post.last_name}</td>
                            <td>${new Date(post.published_at).toLocaleDateString()}</td>
                        </tr>
                    `;
                });
            }
            
            html += `
                            </tbody>
                        </table>
                    </div>
                </div>
            `;
            
            contentArea.innerHTML = html;
        } catch (error) {
            Notification.show(error.message, 'error');
        }
    },
    
    async showMyReviews() {
        const contentArea = document.getElementById('content-area');
        const user = AuthManager.getUser();
        
        try {
            const data = await ApiClient.get(`reviews.php?user_id=${user.id}`);
            
            let html = `
                <h2 class="page-title">My Reviews</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-value">${data.stats.avg_friendly || 0}</div>
                        <div class="stat-label">Avg Friendly</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">${data.stats.avg_professional || 0}</div>
                        <div class="stat-label">Avg Professional</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">${data.stats.avg_overall || 0}</div>
                        <div class="stat-label">Avg Overall</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">${data.stats.total_reviews || 0}</div>
                        <div class="stat-label">Total Reviews</div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Reviews</span>
                    </div>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Reviewer</th>
                                    <th>Event/Bar</th>
                                    <th>Friendly</th>
                                    <th>Professional</th>
                                    <th>Overall</th>
                                    <th>Comment</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
            `;
            
            if (data.reviews.length === 0) {
                html += `<tr><td colspan="7" class="empty-state">No reviews yet</td></tr>`;
            } else {
                data.reviews.forEach(review => {
                    const reviewer = review.reviewer_first_name 
                        ? `${review.reviewer_first_name} ${review.reviewer_last_name}`
                        : 'Guest';
                    const location = review.event_title || review.bar_name || '-';
                    
                    html += `
                        <tr>
                            <td>${reviewer}</td>
                            <td>${location}</td>
                            <td>${'★'.repeat(review.rating_friendly)}</td>
                            <td>${'★'.repeat(review.rating_professional)}</td>
                            <td>${'★'.repeat(review.rating_overall)}</td>
                            <td>${review.comment || '-'}</td>
                            <td>${new Date(review.created_at).toLocaleDateString()}</td>
                        </tr>
                    `;
                });
            }
            
            html += `
                            </tbody>
                        </table>
                    </div>
                </div>
            `;
            
            contentArea.innerHTML = html;
        } catch (error) {
            Notification.show(error.message, 'error');
        }
    },
    
    async showReviewsAll() {
        const contentArea = document.getElementById('content-area');
        
        try {
            const data = await ApiClient.get('reviews_all.php');
            
            let html = `
                <h2 class="page-title">All Reviews</h2>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">All Reviews</span>
                    </div>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Reviewer</th>
                                    <th>Target</th>
                                    <th>Event/Bar</th>
                                    <th>Friendly</th>
                                    <th>Professional</th>
                                    <th>Overall</th>
                                    <th>Comment</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
            `;
            
            if (data.reviews.length === 0) {
                html += `<tr><td colspan="8" class="empty-state">No reviews found</td></tr>`;
            } else {
                data.reviews.forEach(review => {
                    const reviewer = review.reviewer_first_name 
                        ? `${review.reviewer_first_name} ${review.reviewer_last_name}`
                        : 'Guest';
                    const location = review.event_title || review.bar_name || '-';
                    
                    html += `
                        <tr>
                            <td>${reviewer}</td>
                            <td>${review.reviewer_type}</td>
                            <td>${location}</td>
                            <td>${'★'.repeat(review.rating_friendly)}</td>
                            <td>${'★'.repeat(review.rating_professional)}</td>
                            <td>${'★'.repeat(review.rating_overall)}</td>
                            <td>${review.comment || '-'}</td>
                            <td>${new Date(review.created_at).toLocaleDateString()}</td>
                        </tr>
                    `;
                });
            }
            
            html += `
                            </tbody>
                        </table>
                    </div>
                </div>
            `;
            
            contentArea.innerHTML = html;
        } catch (error) {
            Notification.show(error.message, 'error');
        }
    },
    
    async showMemberStats() {
        const contentArea = document.getElementById('content-area');
        
        try {
            const data = await ApiClient.get('stats_members.php');
            
            let html = `
                <h2 class="page-title">Member Statistics</h2>
                <div class="card">
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Reviews</th>
                                    <th>Avg Rating</th>
                                </tr>
                            </thead>
                            <tbody>
            `;
            
            if (data.members.length === 0) {
                html += `<tr><td colspan="3" class="empty-state">No members found</td></tr>`;
            } else {
                data.members.forEach(member => {
                    html += `
                        <tr>
                            <td>${member.first_name} ${member.last_name}</td>
                            <td>${member.review_count}</td>
                            <td>${member.avg_rating || 0}</td>
                        </tr>
                    `;
                });
            }
            
            html += `
                            </tbody>
                        </table>
                    </div>
                </div>
            `;
            
            contentArea.innerHTML = html;
        } catch (error) {
            Notification.show(error.message, 'error');
        }
    },
    
    async showBars() {
        const contentArea = document.getElementById('content-area');
        
        try {
            const data = await ApiClient.get('bars.php');
            
            let html = `
                <h2 class="page-title">Bars (Rümli)</h2>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Bar List</span>
                        <button class="btn-primary" onclick="App.openBarModal()">+ Add Bar</button>
                    </div>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Location</th>
                                </tr>
                            </thead>
                            <tbody>
            `;
            
            if (data.bars.length === 0) {
                html += `<tr><td colspan="2" class="empty-state">No bars found</td></tr>`;
            } else {
                data.bars.forEach(bar => {
                    html += `
                        <tr>
                            <td>${bar.name}</td>
                            <td>${bar.location}</td>
                        </tr>
                    `;
                });
            }
            
            html += `
                            </tbody>
                        </table>
                    </div>
                </div>
            `;
            
            contentArea.innerHTML = html;
        } catch (error) {
            Notification.show(error.message, 'error');
        }
    },
    
    async showParticipation() {
        const contentArea = document.getElementById('content-area');
        
        try {
            const data = await ApiClient.get('participation_my.php');
            
            let html = `
                <h2 class="page-title">Event Participation</h2>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">My Votes</span>
                    </div>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Event</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Voted</th>
                                </tr>
                            </thead>
                            <tbody>
            `;
            
            if (data.participation.length === 0) {
                html += `<tr><td colspan="4" class="empty-state">No participation recorded</td></tr>`;
            } else {
                data.participation.forEach(p => {
                    html += `
                        <tr>
                            <td>${p.event_title}</td>
                            <td>${new Date(p.event_date).toLocaleDateString()}</td>
                            <td><span class="status-${p.status}">${p.status}</span></td>
                            <td>${new Date(p.voted_at).toLocaleDateString()}</td>
                        </tr>
                    `;
                });
            }
            
            html += `
                            </tbody>
                        </table>
                    </div>
                </div>
            `;
            
            contentArea.innerHTML = html;
        } catch (error) {
            Notification.show(error.message, 'error');
        }
    },
    
    async deleteUser(id) {
        if (!confirm('Are you sure you want to delete this user?')) return;
        
        try {
            await ApiClient.delete('users_delete.php', { id });
            await this.handleNavigation('showUsers');
            Notification.show('User deleted successfully');
        } catch (error) {
            Notification.show(error.message, 'error');
        }
    },
    
    openUserModal() {
        const modal = document.createElement('div');
        modal.className = 'modal open';
        modal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">Add User</h3>
                    <button class="modal-close" onclick="this.closest('.modal').classList.remove('open')">&times;</button>
                </div>
                <form id="add-user-form">
                    <div class="form-row">
                        <div class="form-group-full">
                            <label for="user-email">Email</label>
                            <input type="email" id="user-email" required>
                        </div>
                        <div class="form-group-full">
                            <label for="user-password">Password</label>
                            <input type="password" id="user-password" required>
                        </div>
                        <div>
                            <label for="user-role">Role</label>
                            <select id="user-role" required>
                                <option value="" disabled selected>Select a role</option>
                                <option value="member">Member</option>
                                <option value="boss">Boss</option>
                            </select>
                        </div>
                        <div>
                            <label for="user-firstname">First Name</label>
                            <input type="text" id="user-firstname" required>
                        </div>
                        <div>
                            <label for="user-lastname">Last Name</label>
                            <input type="text" id="user-lastname" required>
                        </div>
                    </div>
                    <div class="form-row" style="margin-top: 1rem;">
                        <button type="submit" class="btn-primary">Create User</button>
                    </div>
                </form>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        const submitBtn = modal.querySelector('button[type="submit"]');
        submitBtn.addEventListener('click', async (e) => {
            e.preventDefault();
            
            const email = modal.querySelector('#user-email').value;
            const password = modal.querySelector('#user-password').value;
            const role = modal.querySelector('#user-role').value;
            const firstName = modal.querySelector('#user-firstname').value;
            const lastName = modal.querySelector('#user-lastname').value;
            
            console.log('Submit clicked, data:', { email, password, role, first_name: firstName, last_name: lastName });
            
            if (!role || role === '') {
                Notification.show('Please select a role', 'error');
                return;
            }
            
            try {
                const result = await ApiClient.post('users_create.php', { email, password, role, first_name: firstName, last_name: lastName });
                console.log('User created:', result);
                modal.classList.remove('open');
                await this.handleNavigation('showUsers');
                Notification.show('User created successfully');
            } catch (error) {
                console.error('Create user error:', error);
                Notification.show(error.message, 'error');
            }
        });
    },
    
    openEventModal() {
        const modal = document.createElement('div');
        modal.className = 'modal open';
        modal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">Add Event</h3>
                    <button class="modal-close" onclick="this.closest('.modal').classList.remove('open')">&times;</button>
                </div>
                <form id="add-event-form">
                    <div class="form-row">
                        <div class="form-group-full">
                            <label for="event-title">Title</label>
                            <input type="text" id="event-title" required>
                        </div>
                        <div class="form-group-full">
                            <label for="event-date">Date</label>
                            <input type="date" id="event-date" required>
                        </div>
                        <div class="form-group-full">
                            <label for="event-location">Location</label>
                            <input type="text" id="event-location" required>
                        </div>
                        <div class="form-group-full">
                            <label for="event-description">Description</label>
                            <textarea id="event-description" rows="4"></textarea>
                        </div>
                    </div>
                    <div class="form-row" style="margin-top: 1rem;">
                        <button type="submit" class="btn-primary">Create Event</button>
                    </div>
                </form>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        const submitBtn = modal.querySelector('button[type="submit"]');
        submitBtn.addEventListener('click', async (e) => {
            e.preventDefault();
            
            const title = modal.querySelector('#event-title').value;
            const date = modal.querySelector('#event-date').value;
            const location = modal.querySelector('#event-location').value;
            const description = modal.querySelector('#event-description').value;
            
            try {
                const result = await ApiClient.post('events_create.php', { title, event_date: date, location, description });
                console.log('Event created:', result);
                modal.classList.remove('open');
                await this.handleNavigation('showEvents');
                Notification.show('Event created successfully');
            } catch (error) {
                console.error('Create event error:', error);
                Notification.show(error.message, 'error');
            }
        });
    },
    
    openPostModal() {
        const modal = document.createElement('div');
        modal.className = 'modal open';
        modal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">Add Post</h3>
                    <button class="modal-close" onclick="this.closest('.modal').classList.remove('open')">&times;</button>
                </div>
                <form id="add-post-form">
                    <div class="form-row">
                        <div class="form-group-full">
                            <label for="post-title">Title</label>
                            <input type="text" id="post-title" required>
                        </div>
                        <div class="form-group-full">
                            <label for="post-content">Content</label>
                            <textarea id="post-content" rows="6" required></textarea>
                        </div>
                    </div>
                    <div class="form-row" style="margin-top: 1rem;">
                        <button type="submit" class="btn-primary">Create Post</button>
                    </div>
                </form>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        const submitBtn = modal.querySelector('button[type="submit"]');
        submitBtn.addEventListener('click', async (e) => {
            e.preventDefault();
            
            const title = modal.querySelector('#post-title').value;
            const content = modal.querySelector('#post-content').value;
            
            try {
                const result = await ApiClient.post('posts_create.php', { title, content });
                console.log('Post created:', result);
                modal.classList.remove('open');
                await this.handleNavigation('showPosts');
                Notification.show('Post created successfully');
            } catch (error) {
                console.error('Create post error:', error);
                Notification.show(error.message, 'error');
            }
        });
    },
    
    openBarModal() {
        const modal = document.createElement('div');
        modal.className = 'modal open';
        modal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">Add Bar (Rümli)</h3>
                    <button class="modal-close" onclick="this.closest('.modal').classList.remove('open')">&times;</button>
                </div>
                <form id="add-bar-form">
                    <div class="form-row">
                        <div class="form-group-full">
                            <label for="bar-name">Name</label>
                            <input type="text" id="bar-name" required>
                        </div>
                        <div class="form-group-full">
                            <label for="bar-location">Location</label>
                            <input type="text" id="bar-location" required>
                        </div>
                    </div>
                    <div class="form-row" style="margin-top: 1rem;">
                        <button type="submit" class="btn-primary">Create Bar</button>
                    </div>
                </form>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        const submitBtn = modal.querySelector('button[type="submit"]');
        submitBtn.addEventListener('click', async (e) => {
            e.preventDefault();
            
            const name = modal.querySelector('#bar-name').value;
            const location = modal.querySelector('#bar-location').value;
            
            try {
                const result = await ApiClient.post('bars_create.php', { name, location });
                console.log('Bar created:', result);
                modal.classList.remove('open');
                await this.handleNavigation('showBars');
                Notification.show('Bar created successfully');
            } catch (error) {
                console.error('Create bar error:', error);
                Notification.show(error.message, 'error');
            }
        });
    },
    
    async editUser(id) {
        console.log('Edit user:', id);
        
        try {
            const data = await ApiClient.get('users.php');
            const user = data.users.find(u => u.id === id);
            
            if (!user) {
                Notification.show('User not found', 'error');
                return;
            }
            
            const modal = document.createElement('div');
            modal.className = 'modal open';
            modal.innerHTML = `
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="modal-title">Edit User</h3>
                        <button class="modal-close" onclick="this.closest('.modal').classList.remove('open')">&times;</button>
                    </div>
                    <form id="edit-user-form">
                        <div class="form-row">
                            <div class="form-group-full">
                                <label for="edit-user-email">Email</label>
                                <input type="email" id="edit-user-email" value="${user.email}" required>
                            </div>
                            <div>
                                <label for="edit-user-role">Role</label>
                                <select id="edit-user-role" required>
                                    <option value="member" ${user.role === 'member' ? 'selected' : ''}>Member</option>
                                    <option value="boss" ${user.role === 'boss' ? 'selected' : ''}>Boss</option>
                                    <option value="admin" ${user.role === 'admin' ? 'selected' : ''}>Admin</option>
                                </select>
                            </div>
                            <div>
                                <label for="edit-user-firstname">First Name</label>
                                <input type="text" id="edit-user-firstname" value="${user.first_name}" required>
                            </div>
                            <div>
                                <label for="edit-user-lastname">Last Name</label>
                                <input type="text" id="edit-user-lastname" value="${user.last_name}" required>
                            </div>
                        </div>
                        <div class="form-row" style="margin-top: 1rem;">
                            <button type="submit" class="btn-primary">Update User</button>
                        </div>
                    </form>
                </div>
            `;
            
            document.body.appendChild(modal);
            
            const submitBtn = modal.querySelector('button[type="submit"]');
            submitBtn.addEventListener('click', async (e) => {
                e.preventDefault();
                
                const email = modal.querySelector('#edit-user-email').value;
                const role = modal.querySelector('#edit-user-role').value;
                const firstName = modal.querySelector('#edit-user-firstname').value;
                const lastName = modal.querySelector('#edit-user-lastname').value;
                
                try {
                    const result = await ApiClient.put('users_update.php', { 
                        id, 
                        email, 
                        role, 
                        first_name: firstName, 
                        last_name: lastName 
                    });
                    console.log('User updated:', result);
                    modal.classList.remove('open');
                    await this.handleNavigation('showUsers');
                    Notification.show('User updated successfully');
                } catch (error) {
                    console.error('Update user error:', error);
                    Notification.show(error.message, 'error');
                }
            });
        } catch (error) {
            console.error('Error fetching user data:', error);
            Notification.show(error.message, 'error');
        }
    }
};

document.addEventListener('DOMContentLoaded', () => {
    App.init();
});
