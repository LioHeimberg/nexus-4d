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

export { Notification };