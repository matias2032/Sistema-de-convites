<?php
require_once '../services/conexao.php';
include '../widgets/botao_voltar.php';
checarSessao();

$db = (new Conexao())->getConexao();
$msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);

    $stmt = $db->prepare("UPDATE usuario SET nome = :nome, email = :email WHERE id_usuario = :id");
    $stmt->execute([':nome' => $nome, ':email' => $email, ':id' => $_SESSION['id_usuario']]);
    $_SESSION['nome'] = $nome;
    $msg = "Perfil atualizado!";
}

$stmt = $db->prepare("SELECT nome, email FROM usuario WHERE id_usuario = :id");
$stmt->execute([':id' => $_SESSION['id_usuario']]);
$user = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="pt">
<head><title>Editar Perfil</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="../css/estilo.css">
<script src="../js/darkmode.js" defer></script>
</head>
<body>
    <div class="container">
        <?php
            $voltar_href = 'dashboard.php';
            $titulo_pagina = 'Editar Meu Perfil';
        
        ?>
        <?php if($msg) echo "<p class='msg-sucesso'>$msg</p>"; ?>
        <div class="form-wrapper">
            <form method="POST">
                <input type="text" name="nome" value="<?= htmlspecialchars($user['nome']) ?>" required>
                <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                <button type="submit">Atualizar</button>
            </form>
        </div>
    </div>
</body>
</html>