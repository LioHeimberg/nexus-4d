import { AppState } from '../utils/state.js';
import { AuthManager } from '../auth/auth.js';
import { ApiClient } from '../api/client.js';
import { Notification } from '../ui/notifications.js';
import { ThemeManager } from '../utils/theme.js';
import { API_BASE_URL } from '../utils/constants.js';

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
            console.error('Error checking admin status:', error);
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
                { label: 'Posts', action: 'showPosts' }
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
                            `<button class="btn-primary" onclick="openUserModal()">+ Add User</button>` : ''}
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
                                <button onclick="editUser(${user.id})" class="btn-secondary">Edit</button>
                                <button onclick="deleteUser(${user.id})" class="btn-secondary" style="color: var(--status-no)">Delete</button>
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
                    ${user && (user.role === 'admin' || user.role === 'boss') ? '<button class="btn-primary" onclick="openEventModal()">+ Add Event</button>' : ''}
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
                    ${user && (user.role === 'admin' || user.role === 'boss') ? '<button class="btn-primary" onclick="openPostModal()">+ Add Post</button>' : ''}
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
                            <td>${review.reviewer_name ? review.reviewer_name : (review.reviewer_first_name ? `${review.reviewer_first_name} ${review.reviewer_last_name}` : 'Guest')}</td>
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
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
            `;
            
            if (data.reviews.length === 0) {
                html += `<tr><td colspan="9" class="empty-state">No reviews found</td></tr>`;
            } else {
                data.reviews.forEach(review => {
                    const reviewer = review.reviewer_first_name 
                        ? `${review.reviewer_first_name} ${review.reviewer_last_name}`
                        : 'Guest';
                    const location = review.event_title || review.bar_name || '-';
                    
                    html += `
                        <tr>
                            <td>${review.reviewer_name ? review.reviewer_name : (review.reviewer_first_name ? `${review.reviewer_first_name} ${review.reviewer_last_name}` : 'Guest')}</td>
                            <td>${review.target_first_name ? `${review.target_first_name} ${review.target_last_name}` : 'Unknown'}</td>
                            <td>${location}</td>
                            <td>${'★'.repeat(review.rating_friendly)}</td>
                            <td>${'★'.repeat(review.rating_professional)}</td>
                            <td>${'★'.repeat(review.rating_overall)}</td>
                            <td>${review.comment || '-'}</td>
                            <td>${new Date(review.created_at).toLocaleDateString()}</td>
                            <td>
                                ${review.can_remove ? `
                                    <button onclick="removeReview(${review.id})" class="btn-secondary" style="color: var(--status-no); padding: 4px 8px;">
                                        Remove
                                    </button>
                                ` : ''}
                            </td>
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
                        <button class="btn-primary" onclick="openBarModal()">+ Add Bar</button>
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
    
    async removeReview(id) {
        if (!confirm('Are you sure you want to remove this review? This action cannot be undone.')) return;
        
        try {
            await ApiClient.delete('reviews_delete.php', { review_id: id });
            await this.handleNavigation('showReviewsAll');
            Notification.show('Review removed successfully');
        } catch (error) {
            Notification.show(error.message, 'error');
        }
    }
};

// Make removeReview available globally for onclick handlers
window.removeReview = function(id) {
    App.removeReview(id);
};

export { App };