import { AppState } from './state.js';

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

export { ThemeManager };