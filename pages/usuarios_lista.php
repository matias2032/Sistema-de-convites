<?php
require_once '../services/conexao.php';
ob_start();
include_once '../widgets/sidebar.php';
$sidebar_html = ob_get_clean();
checarSessao();

$db = (new Conexao())->getConexao();
$id_logado = $_SESSION['id_usuario'];

// Ação do Toggle via GET
if (isset($_GET['toggle_id'])) {
    $id = (int)$_GET['toggle_id'];

    // Segurança extra: impede desativar o próprio usuário logado via URL
    if ($id !== $id_logado) {
        $stmt = $db->prepare("UPDATE usuario SET ativo = NOT ativo WHERE id_usuario = :id");
        $stmt->execute([':id' => $id]);
    }

    header("Location: usuarios_lista.php");
    exit;
}

// Oculta o usuário atualmente logado da listagem
$stmt = $db->prepare("SELECT id_usuario, nome, email, ativo, primeira_senha FROM usuario WHERE id_usuario != :id_logado ORDER BY nome ASC");
$stmt->execute([':id_logado' => $id_logado]);
$usuarios = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Usuários</title>
    <link rel="stylesheet" href="../css/estilo.css">
    <script src="../js/darkmode.js" defer></script>
    <script src="../js/sidebar.js" defer></script>
    <style>
        .switch { position: relative; display: inline-block; width: 34px; height: 20px; }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 20px; }
        .slider:before { position: absolute; content: ""; height: 14px; width: 14px; left: 3px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%; }
        input:checked + .slider { background-color: #2196F3; }
        input:checked + .slider:before { transform: translateX(14px); }
    </style>
</head>
<body>
    <div class="app-layout">
        <?= $sidebar_html ?>
        <main class="main-content">
            <a href="dashboard.php">Voltar</a> | <a href="usuario_criar.php">Novo Usuário</a>
            <h2>Outros Usuários do Sistema</h2>
    
    <?php if (empty($usuarios)): ?>
        <p>Nenhum outro usuário cadastrado no momento.</p>
    <?php else: ?>
        <div class="table-responsive">
        <table border="1" cellpadding="8" cellspacing="0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Activar/Desactivar</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $u): ?>
                <tr>
    <td><?= $u['id_usuario'] ?></td>
    <td><?= htmlspecialchars($u['nome']) ?></td>
    <td><?= htmlspecialchars($u['email']) ?></td>
    <td><?= $u['ativo'] ? 'Ativo' : 'Inativo' ?></td>
    <td>
        <a href="usuario_detalhes.php?id=<?= $u['id_usuario'] ?>">🔍 Detalhes</a> | 
        <label class="switch">
            <input type="checkbox" <?= $u['ativo'] ? 'checked' : '' ?> onchange="location.href='?toggle_id=<?= $u['id_usuario'] ?>'">
            <span class="slider"></span>
        </label>
    </td>
</tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    <?php endif; ?>
        </main>
    </div>
</body>
</html>