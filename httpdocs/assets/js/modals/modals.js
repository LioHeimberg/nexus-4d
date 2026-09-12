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

function openDummyModal() {
    const modal = document.createElement('div');
    modal.className = 'modal open';

    modal.innerHTML = `
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Generate Dummy Data</h3>
                <button
                    class="modal-close"
                    onclick="this.closest('.modal').remove()"
                >
                    &times;
                </button>
            </div>

            <form id="dummy-data-form">
                <div class="form-row">
                    <div class="form-group-full">
                        <blockquote>
                            To generate dummy data, please confirm your Admin Password.
                        </blockquote>
                    </div>

                    <div class="form-group-full">
                        <label for="dummy-admin-password">Password</label>
                        <input
                            type="password"
                            id="dummy-admin-password"
                            required
                        >
                    </div>
                </div>

                <div class="form-row" style="margin-top: 1rem;">
                    <button
                        type="submit"
                        class="btn-secondary"
                    >
                        Continue
                    </button>

                    <button
                        type="button"
                        class="btn-primary"
                        onclick="this.closest('.modal').remove()"
                    >
                        Cancel
                    </button>
                </div>

                <div id="error-message" class="error-message"></div>
            </form>
        </div>
    `;

    document.body.appendChild(modal);

    modal.querySelector('#dummy-data-form').addEventListener('submit', async (e) => {
        e.preventDefault();

        const AdminPass = modal.querySelector('#dummy-admin-password').value;

        if (!AdminPass) {
            modal.querySelector('#dummy-admin-password').reportValidity();
            return;
        }

        const bosses = parseInt(
            document.getElementById('dummy-bosses')?.value || 0,
            10
        );

        const members = parseInt(
            document.getElementById('dummy-members')?.value || 0,
            10
        );

        const events = parseInt(
            document.getElementById('dummy-events')?.value || 0,
            10
        );

        const posts = parseInt(
            document.getElementById('dummy-posts')?.value || 0,
            10
        );

        const bars = parseInt(
            document.getElementById('dummy-bars')?.value || 0,
            10
        );

        const reviews = parseInt(
            document.getElementById('dummy-reviews')?.value || 0,
            10
        );

        const values = [
            bosses,
            members,
            events,
            posts,
            bars,
            reviews
        ];

        if (values.some(value => isNaN(value) || value < 0 || value > 1000)) {
            Notification.show(
                'Please enter values between 0 and 1000.',
                'error'
            );
            return;
        }
        const confirmModal = document.createElement('div');
        confirmModal.className = 'modal open';

        confirmModal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">Are you sure?</h3>

                    <button
                        class="modal-close"
                        onclick="this.closest('.modal').remove()"
                    >
                        &times;
                    </button>
                </div>

                <div class="form-row">
                    <div class="form-group-full">
                        <p>
                            The following dummy data will be generated:
                        </p>

                        <ul>
                            <li>${bosses} bosses</li>
                            <li>${members} members</li>
                            <li>${events} events</li>
                            <li>${posts} posts</li>
                            <li>${bars} bars</li>
                            <li>${reviews} reviews</li>
                        </ul>
                    </div>
                </div>

                <div
                    class="form-row"
                    style="margin-top: 1rem;"
                >
                    <button
                        type="button"
                        class="btn-secondary"
                        id="confirm-dummy-generation"
                    >
                        Generate
                    </button>

                    <button
                        type="button"
                        class="btn-primary"
                        onclick="this.closest('.modal').remove()"
                    >
                        Cancel
                    </button>
                </div>

                <div
                    id="error-message"
                    class="error-message"
                ></div>
            </div>
        `;

        document.body.appendChild(confirmModal);

        confirmModal
            .querySelector('#confirm-dummy-generation')
            .addEventListener('click', async () => {

                const button = confirmModal.querySelector(
                    '#confirm-dummy-generation'
                );

                button.disabled = true;
                button.textContent = 'Generating...';

                try {
                    const result = await ApiClient.post(
                        'dummydata_create.php',
                        {
                            AdminPass,
                            bosses,
                            members,
                            events,
                            posts,
                            bars,
                            reviews
                        }
                    );

                    console.log('Dummy data generated:', result);

                    confirmModal.remove();
                    modal.remove();

                    await App.handleNavigation('showUsers');

                    Notification.show(
                        'Dummy data generated successfully'
                    );

                } catch (error) {
                    console.error(
                        'Generate dummy data error:',
                        error
                    );

                    const errorDiv = confirmModal.querySelector(
                        '#error-message'
                    );

                    errorDiv.textContent = error.message;
                    errorDiv.classList.add('visible');

                    button.disabled = false;
                    button.textContent = 'Generate';
                }
            });
    });
}

function openDummyRemoveModal() {
    const modal = document.createElement('div');
    modal.className = 'modal open';

    modal.innerHTML = `
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Remove Dummy Data</h3>
                <button
                    class="modal-close"
                    onclick="this.closest('.modal').remove()"
                >
                    &times;
                </button>
            </div>

            <form id="dummy-data-form">
                <div class="form-row">
                    <div class="form-group-full">
                        <blockquote>
                            To remove dummy data, please confirm your Admin Password.
                        </blockquote>
                    </div>

                    <div class="form-group-full">
                        <label for="dummy-admin-password">Password</label>
                        <input
                            type="password"
                            id="dummy-admin-password"
                            required
                        >
                    </div>
                </div>

                <div class="form-row" style="margin-top: 1rem;">
                    <button
                        type="submit"
                        class="btn-secondary"
                    >
                        Continue
                    </button>

                    <button
                        type="button"
                        class="btn-primary"
                        onclick="this.closest('.modal').remove()"
                    >
                        Cancel
                    </button>
                </div>

                <div id="error-message" class="error-message"></div>
            </form>
        </div>
    `;

    document.body.appendChild(modal);

    modal.querySelector('#dummy-data-form').addEventListener('submit', async (e) => {
        e.preventDefault();

        const AdminPass = modal.querySelector('#dummy-admin-password').value;

        if (!AdminPass) {
            modal.querySelector('#dummy-admin-password').reportValidity();
            return;
        }

        const confirmModal = document.createElement('div');
        confirmModal.className = 'modal open';

        confirmModal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">Are you sure?</h3>

                    <button
                        class="modal-close"
                        onclick="this.closest('.modal').remove()"
                    >
                        &times;
                    </button>
                </div>

                <div class="form-row">
                    <div class="form-group-full">
                        <p>
                            All dummy data will be removed from the system. This action cannot be undone.
                        </p>
                    </div>
                </div>

                <div
                    class="form-row"
                    style="margin-top: 1rem;"
                >
                    <button
                        type="button"
                        class="btn-secondary"
                        id="confirm-dummy-removal"
                    >
                        Remove
                    </button>

                    <button
                        type="button"
                        class="btn-primary"
                        onclick="this.closest('.modal').remove()"
                    >
                        Cancel
                    </button>
                </div>

                <div
                    id="error-message"
                    class="error-message"
                ></div>
            </div>
        `;

        document.body.appendChild(confirmModal);

        confirmModal
            .querySelector('#confirm-dummy-removal')
            .addEventListener('click', async () => {

                const button = confirmModal.querySelector(
                    '#confirm-dummy-removal'
                );

                button.disabled = true;
                button.textContent = 'Removing...';

                try {
                    const result = await ApiClient.post(
                        'dummydata_remove.php',
                        {
                            AdminPass
                        }
                    );

                    console.log('Dummy data removed:', result);

                    confirmModal.remove();
                    modal.remove();

                    await App.handleNavigation('showUsers');

                    Notification.show(
                        'Dummy data removed successfully'
                    );

                } catch (error) {
                    console.error(
                        'Remove dummy data error:',
                        error
                    );

                    const errorDiv = confirmModal.querySelector(
                        '#error-message'
                    );

                    errorDiv.textContent = error.message;
                    errorDiv.classList.add('visible');

                    button.disabled = false;
                    button.textContent = 'Remove';
                }
            });
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
export { openUserModal, openEventModal, openPostModal, openBarModal, openDummyModal, openDummyRemoveModal, editUser };