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
        $msg = "Senha alterada com sucesso! Redirecionando á dashboard...";
        header("Refresh: 3; url=dashboard.php");
    } else {
        $msg = "Senha atual incorreta.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Alterar Senha</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/estilo.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="../js/darkmode.js" defer></script>
    <script src="../js/toggle-senha.js" defer></script>
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
    <div class="input-group">
        <i class="fa-solid fa-lock input-icon"></i>
        <input type="password" name="senha_atual" placeholder="Senha Atual" required>
        <button type="button" class="toggle-senha" aria-label="Mostrar/Ocultar Senha">
            <i class="fa-solid fa-eye"></i>
        </button>
    </div>
    
    <div class="input-group">
        <i class="fa-solid fa-key input-icon"></i>
        <input type="password" name="nova_senha" placeholder="Nova Senha" required>
        <button type="button" class="toggle-senha" aria-label="Mostrar/Ocultar Senha">
            <i class="fa-solid fa-eye"></i>
        </button>
    </div>

    <button type="submit">
        <i class="fa-solid fa-floppy-disk"></i> Atualizar Senha
    </button>
</form>
        </div>
    </div>
</body>
</html>