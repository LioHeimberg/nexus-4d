import { AppState } from './utils/state.js';
import { ThemeManager } from './utils/theme.js';
import { AuthManager } from './auth/auth.js';
import { ApiClient } from './api/client.js';
import { Notification } from './ui/notifications.js';
import { App } from './app/app.js';
import { openUserModal, openEventModal, openPostModal, openBarModal, openDummyModal, editUser } from './modals/modals.js';
import { BUILD_VERSION } from './utils/constants.js';

// Make modal functions and deleteUser available globally for onclick handlers in HTML
window.openUserModal = openUserModal;
window.openEventModal = openEventModal;
window.openPostModal = openPostModal;
window.openBarModal = openBarModal;
window.openDummyModal = openDummyModal;
window.editUser = editUser;
window.deleteUser = App.deleteUser;
window.deleteEvent = App.deleteEvent;
window.deletePost = App.deletePost;

// Initialize the application
App.init();


const versionTag = document.getElementById("version");
versionTag.innerText = 'Version: ' + BUILD_VERSION