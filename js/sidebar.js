// Impede o navegador de restaurar a posição de scroll da visita anterior
if ('scrollRestoration' in history) {
    history.scrollRestoration = 'manual';
}
window.scrollTo(0, 0);

document.addEventListener('DOMContentLoaded', () => {
    window.scrollTo(0, 0);
    const sidebar = document.getElementById('sidebar');
    const toggleSidebarBtn = document.getElementById('toggle-sidebar');
    const mobileHamburgerBtn = document.getElementById('mobile-hamburger-btn');
    const profileTrigger = document.getElementById('profile-trigger');
    const profileMenu = document.getElementById('profile-menu');

    const SIDEBAR_STATE_KEY = 'sidebar_collapsed';

    // Se quiser limpar o estado antigo do navegador para forçar a exibição:
    // localStorage.removeItem(SIDEBAR_STATE_KEY);

    // Restaura o estado salvo no localStorage apenas se explicitamente definido
    if (localStorage.getItem(SIDEBAR_STATE_KEY) === 'true' && sidebar) {
        sidebar.classList.add('collapsed');
    }

    const toggleSidebar = () => {
        if (sidebar) {
            sidebar.classList.toggle('collapsed');
            localStorage.setItem(SIDEBAR_STATE_KEY, sidebar.classList.contains('collapsed'));
        }
    };

    if (toggleSidebarBtn) {
        toggleSidebarBtn.addEventListener('click', toggleSidebar);
    }

    if (mobileHamburgerBtn) {
        mobileHamburgerBtn.addEventListener('click', toggleSidebar);
    }

    // Dropdown de Perfil
    if (profileTrigger && profileMenu) {
        profileTrigger.addEventListener('click', (e) => {
            e.stopPropagation();
            profileMenu.classList.toggle('show');
        });

        document.addEventListener('click', (e) => {
            if (!profileMenu.contains(e.target) && !profileTrigger.contains(e.target)) {
                profileMenu.classList.remove('show');
            }
        });
    }
});