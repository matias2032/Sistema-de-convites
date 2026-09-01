<?php
require_once '../services/conexao.php';
ob_start();
include_once '../widgets/sidebar.php';
$sidebar_html = ob_get_clean();
checarSessao();

$db = (new Conexao())->getConexao();
$id_logado = isset($_SESSION['id_usuario']) ? (int)$_SESSION['id_usuario'] : 0;

// Ação do Toggle via GET
if (isset($_GET['toggle_id'])) {
    $id = (int)$_GET['toggle_id'];

    // Segurança extra: impede desativar o próprio usuário logado via URL
    if ($id !== $id_logado && $id > 0) {
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="../js/darkmode.js" defer></script>
    <script src="../js/sidebar.js" defer></script>
    <style>
        /* CSS dos botões mantido com variáveis do sistema */
        .btn-detalhes {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 5px 10px;
            font-size: 0.82rem;
            font-weight: 600;
            border-radius: var(--radius-sm, 6px);
            text-decoration: none;
            transition: all var(--transition-fast, 0.2s);
            border: 1px solid transparent;
            cursor: pointer;
            background-color: rgba(37, 99, 235, 0.1);
            color: var(--primary, #2563eb);
            border-color: rgba(37, 99, 235, 0.2);
        }
        .btn-detalhes:hover {
            text-decoration: none;
            transform: translateY(-1px);
            background-color: var(--primary, #2563eb);
            color: #ffffff;
        }
    </style>
</head>
<body>
    <div class="app-layout">
        <?= $sidebar_html ?>
        <main class="main-content">

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
                                <th>Ativar / Desativar</th>
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
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <a href="usuario_detalhes.php?id=<?= $u['id_usuario'] ?>" class="btn-detalhes">Detalhes</a> 
                                        <label class="switch">
                                            <input type="checkbox" <?= $u['ativo'] ? 'checked' : '' ?> onchange="location.href='?toggle_id=<?= $u['id_usuario'] ?>'">
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <a href="usuario_criar.php" class="fab-btn" title="Novo Usuário" aria-label="Novo Usuário">+</a>
        </main>
    </div>
</body>
</html>