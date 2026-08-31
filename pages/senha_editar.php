<?php
require_once '../services/conexao.php';
include '../widgets/botao_voltar.php';
checarSessao();

$msg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $atual = $_POST['senha_atual'];
    $nova = $_POST['nova_senha'];

    $db = (new Conexao())->getConexao();
    $stmt = $db->prepare("SELECT senha_hash FROM usuario WHERE id_usuario = :id");
    $stmt->execute([':id' => $_SESSION['id_usuario']]);
    $user = $stmt->fetch();

    if (password_verify($atual, $user['senha_hash'])) {
        $hash = password_hash($nova, PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE usuario SET senha_hash = :hash WHERE id_usuario = :id");
        $stmt->execute([':hash' => $hash, ':id' => $_SESSION['id_usuario']]);
        $msg = "Senha alterada com sucesso!";
    } else {
        $msg = "Senha atual incorreta.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head><title>Alterar Senha</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="../css/estilo.css">
<script src="../js/darkmode.js" defer></script>
</head>
<body>
    <div class="container">
        <?php
            $voltar_href = 'dashboard.php';
            $titulo_pagina = 'Alterar Minha Senha';

        ?>
        <?php if($msg) echo "<p class='msg-sucesso'>$msg</p>"; ?>
        <div class="form-wrapper">
            <form method="POST">
                <input type="password" name="senha_atual" placeholder="Senha Atual" required>
                <input type="password" name="nova_senha" placeholder="Nova Senha" required>
                <button type="submit">Atualizar Senha</button>
            </form>
        </div>
    </div>
</body>
</html>