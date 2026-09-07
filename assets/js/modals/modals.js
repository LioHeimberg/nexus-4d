import { App } from '../app/app.js';
import { AuthManager } from '../auth/auth.js';
import { Notification } from '../ui/notifications.js';
import { ApiClient } from '../api/client.js';

// Modal functions
function openUserModal() {
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
            await App.handleNavigation('showUsers');
            Notification.show('User created successfully');
        } catch (error) {
            console.error('Create user error:', error);
            Notification.show(error.message, 'error');
        }
    });
}

function openEventModal() {
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
            await App.handleNavigation('showEvents');
            Notification.show('Event created successfully');
        } catch (error) {
            console.error('Create event error:', error);
            Notification.show(error.message, 'error');
        }
    });
}

function openPostModal() {
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
            await App.handleNavigation('showPosts');
            Notification.show('Post created successfully');
        } catch (error) {
            console.error('Create post error:', error);
            Notification.show(error.message, 'error');
        }
    });
}

function openBarModal() {
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
            await App.handleNavigation('showBars');
            Notification.show('Bar created successfully');
        } catch (error) {
            console.error('Create bar error:', error);
            Notification.show(error.message, 'error');
        }
    });
}

async function editUser(id) {
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
                            ${AuthManager.getUser().role === 'admin' ? '<option value="admin" ' + (user.role === 'admin' ? 'selected' : '') + '>Admin</option>' : ''}
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
                await App.handleNavigation('showUsers');
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

// Export all modal functions
export { openUserModal, openEventModal, openPostModal, openBarModal, editUser };