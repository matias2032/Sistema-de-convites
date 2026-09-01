<?php
session_start();
require_once '../services/conexao.php';

$erro = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];

    $db = (new Conexao())->getConexao();
    $stmt = $db->prepare("SELECT * FROM usuario WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();

    if ($user && password_verify($senha, $user['senha_hash'])) {
        if (!$user['ativo']) {
            $erro = "Usuário inativo. Contate o administrador.";
        } else {
            $_SESSION['id_usuario'] = $user['id_usuario'];
            $_SESSION['nome'] = $user['nome'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['primeira_senha'] = $user['primeira_senha'];

            if ($user['primeira_senha']) {
                header("Location: primeira_senha.php");
            } else {
                header("Location: dashboard.php");
            }
            exit;
        }
    } else {
        $erro = "E-mail ou senha incorretos.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/estilo.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="../js/darkmode.js" defer></script>
    <script src="../js/toggle-senha.js" defer></script>
</head>
<body>
    <div class="container pagina-form">
        <h2>Login</h2>
        <?php if($erro) echo "<p class='msg-erro'>$erro</p>"; ?>
        <div class="form-wrapper">
<form method="POST">
    <div class="input-group">
        <i class="fa-solid fa-envelope input-icon"></i>
        <input type="email" name="email" placeholder="E-mail" required>
    </div>

    <div class="input-group">
        <i class="fa-solid fa-lock input-icon"></i>
        <input type="password" name="senha" placeholder="Senha" required>
        <button type="button" class="toggle-senha" aria-label="Mostrar/Ocultar Senha">
            <i class="fa-solid fa-eye"></i>
        </button>
    </div>

    <button type="submit">
        <i class="fa-solid fa-right-to-bracket"></i> Entrar
    </button>
</form>
        </div>
    </div>
</body>
</html>