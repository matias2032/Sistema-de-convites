<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pagina_atual = basename($_SERVER['SCRIPT_NAME']);
$nome_usuario = $_SESSION['usuario_nome'] ?? $_SESSION['nome'] ?? 'Usuário';
$email_usuario = $_SESSION['usuario_email'] ?? $_SESSION['email'] ?? 'usuario@sistema.com';
$inicial_nome = strtoupper(substr($nome_usuario, 0, 1));
?>

<!-- ASIDE FIXO LATERAL -->
<aside id="sidebar" class="sidebar">
    <div class="sidebar-top">
        <div class="sidebar-header">
            <span class="sidebar-brand">Sistema Convites</span>
            <!-- Botão Hambúrguer DENTRO da Sidebar para telas Desktop -->
            <button id="toggle-sidebar" class="hamburger-btn" aria-label="Alternar Menu">☰</button>
        </div>

        <nav class="sidebar-nav">
            <ul>
                <li>
                    <a href="dashboard.php" class="<?= $pagina_atual === 'dashboard.php' ? 'active' : '' ?>">
                        Dashboard
                    </a>
                </li>
                <li>
                    <a href="usuarios_lista.php" class="<?= $pagina_atual === 'usuarios_lista.php' ? 'active' : '' ?>">
                        Lista de Usuários
                    </a>
                </li>
                <li>
                    <a href="convidados_lista.php" class="<?= $pagina_atual === 'convidados_lista.php' ? 'active' : '' ?>">
                        Lista de Convidados
                    </a>
                </li>
                <li>
                    <a href="configuracao_convite.php" class="<?= $pagina_atual === 'configuracao_convite.php' ? 'active' : '' ?>">
                        Personalizar Design
                    </a>
                </li>

                <!-- TOGGLE DARK MODE -->
                <li class="sidebar-theme-item">
                    <div class="theme-switch-wrapper">
                        <span class="theme-label">Mudar Tema</span>
                        <label class="switch">
                            <input type="checkbox" id="theme-toggle-checkbox">
                            <span class="slider"></span>
                        </label>
                    </div>
                </li>
            </ul>
        </nav>
    </div>

    <!-- Dropdown de Perfil Estilo Gemini -->
    <div class="profile-dropdown-container">
        <div id="profile-menu" class="profile-menu">
            <a href="perfil_editar.php" class="<?= $pagina_atual === 'perfil_editar.php' ? 'active' : '' ?>">
                Editar Meu Perfil
            </a>
            <a href="senha_editar.php" class="<?= $pagina_atual === 'senha_editar.php' ? 'active' : '' ?>">
                Alterar Minha Senha
            </a>
            <a href="../services/logout.php" class="btn-logout">
                Sair
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