<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['id_usuario']) || !$_SESSION['primeira_senha']) {
    header("Location: login.php");
    exit;
}

$erro = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nova_senha = $_POST['nova_senha'];
    $confirma_senha = $_POST['confirma_senha'];

    if (strlen($nova_senha) < 6) {
        $erro = "A nova senha deve ter no mínimo 6 caracteres.";
    } elseif ($nova_senha !== $confirma_senha) {
        $erro = "As senhas não coincidem.";
    } else {
        $hash = password_hash($nova_senha, PASSWORD_DEFAULT);
        $db = (new Conexao())->getConexao();
        
        $stmt = $db->prepare("UPDATE usuario SET senha_hash = :hash, primeira_senha = FALSE WHERE id_usuario = :id");
        $stmt->execute([':hash' => $hash, ':id' => $_SESSION['id_usuario']]);

        session_destroy();
        header("Location: login.php?msg=Senha alterada com sucesso! Faça login novamente.");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head><title>Primeira Troca de Senha</title>
<link rel="stylesheet" href="css/estilo.css">
</head>
<body>
    <h2>Primeiro Acesso - Troca Obrigatoria de Senha</h2>
    <?php if($erro) echo "<p style='color:red;'>$erro</p>"; ?>
    <form method="POST">
        <input type="password" name="nova_senha" placeholder="Nova Senha" required><br><br>
        <input type="password" name="confirma_senha" placeholder="Confirme a Nova Senha" required><br><br>
        <button type="submit">Salvar e Fazer Login</button>
    </form>
</body>
</html>