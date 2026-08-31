/**
 * SISTEMA DE GESTÃO DE CONVITES - THEME ENGINE (DARK MODE)
 * Gerencia a alternância de temas, persistência no localStorage
 * e detecção automática de preferências do sistema.
 */

document.addEventListener('DOMContentLoaded', () => {
    const STORAGE_KEY = 'sistema_convites_theme';
    const THEME_DARK = 'dark';
    const THEME_LIGHT = 'light';

    /**
     * Obter o tema preferido salvo ou do sistema
     */
    function getPreferredTheme() {
        const savedTheme = localStorage.getItem(STORAGE_KEY);
        if (savedTheme) {
            return savedTheme;
        }
        return window.matchMedia('(prefers-color-scheme: dark)').matches ? THEME_DARK : THEME_LIGHT;
    }

    /**
     * Aplica o tema especificado ao documento HTML
     */
    function applyTheme(theme) {
        if (theme === THEME_DARK) {
            document.documentElement.setAttribute('data-theme', 'dark');
            document.body.classList.add('dark-mode');
        } else {
            document.documentElement.removeAttribute('data-theme');
            document.body.classList.remove('dark-mode');
        }
        
        // Atualiza estado visual do botão/toggle se existir na página
        updateToggleButtonUI(theme);
    }

    /**
     * Alterna entre modo claro e escuro
     */
    function toggleTheme() {
        const currentTheme = document.documentElement.getAttribute('data-theme') === 'dark' ? THEME_DARK : THEME_LIGHT;
        const newTheme = currentTheme === THEME_DARK ? THEME_LIGHT : THEME_DARK;
        
        localStorage.setItem(STORAGE_KEY, newTheme);
        applyTheme(newTheme);
    }

    /**
     * Atualiza o estado de elementos visuais de toggle presentes na página
     */
    function updateToggleButtonUI(theme) {
        const toggleBtn = document.getElementById('theme-toggle-btn');
        const toggleCheckbox = document.getElementById('theme-toggle-checkbox');

        if (toggleBtn) {
            toggleBtn.setAttribute('aria-label', theme === THEME_DARK ? 'Mudar para Modo Claro' : 'Mudar para Modo Escuro');
            toggleBtn.textContent = theme === THEME_DARK ? '☀️ Modo Claro' : '🌙 Modo Escuro';
        }

        if (toggleCheckbox) {
            toggleCheckbox.checked = (theme === THEME_DARK);
        }
    }

    // Inicializa o tema assim que o script carrega
    const initialTheme = getPreferredTheme();
    applyTheme(initialTheme);

    // Adiciona listener para botões ou toggles de troca de tema
    document.addEventListener('click', (event) => {
        if (event.target && (event.target.id === 'theme-toggle-btn' || event.target.closest('#theme-toggle-btn'))) {
            toggleTheme();
        }
    });

    document.addEventListener('change', (event) => {
        if (event.target && event.target.id === 'theme-toggle-checkbox') {
            toggleTheme();
        }
    });

    // Escuta mudanças na preferência de tema do sistema operacional
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
        if (!localStorage.getItem(STORAGE_KEY)) {
            applyTheme(e.matches ? THEME_DARK : THEME_LIGHT);
        }
    });
});
