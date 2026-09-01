<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pagina_atual = basename($_SERVER['SCRIPT_NAME']);
$nome_usuario = $_SESSION['usuario_nome'] ?? $_SESSION['nome'] ?? 'Usuário';
$email_usuario = $_SESSION['usuario_email'] ?? $_SESSION['email'] ?? 'usuario@sistema.com';
$inicial_nome = strtoupper(substr($nome_usuario, 0, 1));
?>

<aside id="sidebar" class="sidebar">
    <div class="sidebar-top">
        <div class="sidebar-header">
            <span class="sidebar-brand">Sistema Convites</span>
            <button id="toggle-sidebar" class="hamburger-btn" aria-label="Alternar Menu">
                <i class="fa-solid fa-bars"></i>
            </button>
        </div>

        <nav class="sidebar-nav">
            <ul>
                <li>
                    <a href="dashboard.php" class="<?= $pagina_atual === 'dashboard.php' ? 'active' : '' ?>">
                        <i class="fa-solid fa-chart-pie sidebar-icon"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="usuarios_lista.php" class="<?= $pagina_atual === 'usuarios_lista.php' ? 'active' : '' ?>">
                        <i class="fa-solid fa-users sidebar-icon"></i>
                        <span>Lista de Usuários</span>
                    </a>
                </li>
                <li>
                    <a href="convidados_lista.php" class="<?= $pagina_atual === 'convidados_lista.php' ? 'active' : '' ?>">
                        <i class="fa-solid fa-address-book sidebar-icon"></i>
                        <span>Lista de Convidados</span>
                    </a>
                </li>
                <li>
                    <a href="configuracao_convite.php" class="<?= $pagina_atual === 'configuracao_convite.php' ? 'active' : '' ?>">
                        <i class="fa-solid fa-wand-magic-sparkles sidebar-icon"></i>
                        <span>Personalizar Convite</span>
                    </a>
                </li>

                <!-- TOGGLE DARK MODE -->
                <li class="sidebar-theme-item">
                    <div class="theme-switch-wrapper">
                        <span class="theme-label">
                            <i class="fa-solid fa-moon sidebar-icon"></i> Mudar Tema
                        </span>
                        <label class="switch">
                            <input type="checkbox" id="theme-toggle-checkbox">
                            <span class="slider"></span>
                        </label>
                    </div>
                </li>
            </ul>
        </nav>
    </div>

    <!-- Dropdown de Perfil -->
    <div class="profile-dropdown-container">
        <div id="profile-menu" class="profile-menu">
            <a href="perfil_editar.php" class="<?= $pagina_atual === 'perfil_editar.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-user-gear dropdown-icon"></i> Editar Meu Perfil
            </a>
            <a href="senha_editar.php" class="<?= $pagina_atual === 'senha_editar.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-key dropdown-icon"></i> Alterar Minha Senha
            </a>
            <a href="../services/logout.php" class="btn-logout">
                <i class="fa-solid fa-right-from-bracket dropdown-icon"></i> Sair
            </a>
        </div>

        <button id="profile-trigger" class="profile-trigger" type="button">
            <div class="avatar-circle"><?= htmlspecialchars($inicial_nome) ?></div>
            <div class="user-info-text">
                <span class="user-name"><?= htmlspecialchars($nome_usuario) ?></span>
                <span class="user-email"><?= htmlspecialchars($email_usuario) ?></span>
            </div>
        </button>
    </div>
</aside>