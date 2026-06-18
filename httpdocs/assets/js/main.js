import { AppState } from './utils/state.js';
import { ThemeManager } from './utils/theme.js';
import { AuthManager } from './auth/auth.js';
import { ApiClient } from './api/client.js';
import { Notification } from './ui/notifications.js';
import { App } from './app/app.js';
import { openUserModal, openEventModal, openPostModal, openBarModal, editUser } from './modals/modals.js';

// Make modal functions and deleteUser available globally for onclick handlers in HTML
window.openUserModal = openUserModal;
window.openEventModal = openEventModal;
window.openPostModal = openPostModal;
window.openBarModal = openBarModal;
window.editUser = editUser;
window.deleteUser = App.deleteUser;

// Initialize the application
App.init();