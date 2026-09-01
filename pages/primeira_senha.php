<?php
session_start();
require_once '../services/conexao.php';

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
<head>
    <meta charset="UTF-8">
    <title>Primeira Troca de Senha</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/estilo.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="../js/darkmode.js" defer></script>
    <script src="../js/toggle-senha.js" defer></script>
</head>
<body>
    <div class="container pagina-form">
        <h2>Primeiro Acesso (Troca Obrigatória de Senha)</h2>
        <?php if($erro) echo "<p class='msg-erro'>$erro</p>"; ?>
        <div class="form-wrapper">
<form method="POST">
    <div class="input-group">
        <i class="fa-solid fa-key input-icon"></i>
        <input type="password" name="nova_senha" placeholder="Nova Senha" required>
        <button type="button" class="toggle-senha" aria-label="Mostrar/Ocultar Senha">
            <i class="fa-solid fa-eye"></i>
        </button>
    </div>

    <div class="input-group">
        <i class="fa-solid fa-shield-halved input-icon"></i>
        <input type="password" name="confirma_senha" placeholder="Confirme a Nova Senha" required>
        <button type="button" class="toggle-senha" aria-label="Mostrar/Ocultar Senha">
            <i class="fa-solid fa-eye"></i>
        </button>
    </div>

    <button type="submit">
        <i class="fa-solid fa-floppy-disk"></i> Salvar e Fazer Login
    </button>
</form>
        </div>
    </div>
</body>
</html>