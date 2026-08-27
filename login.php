<?php
session_start();
require_once 'conexao.php';

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
<head><title>Login</title>
<link rel="stylesheet" href="css/estilo.css">
</head>
<body>
    <h2>Login</h2>
    <?php if($erro) echo "<p style='color:red;'>$erro</p>"; ?>
    <form method="POST">
        <input type="email" name="email" placeholder="E-mail" required><br><br>
        <input type="password" name="senha" placeholder="Senha" required><br><br>
        <button type="submit">Entrar</button>
    </form>
</body>
</html>