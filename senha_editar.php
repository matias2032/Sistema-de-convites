<?php
require_once 'conexao.php';
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
<link rel="stylesheet" href="css/estilo.css">
</head>
<body>
    <a href="dashboard.php">Voltar</a>
    <h2>Alterar Minha Senha</h2>
    <?php if($msg) echo "<p>$msg</p>"; ?>
    <form method="POST">
        <input type="password" name="senha_atual" placeholder="Senha Atual" required><br><br>
        <input type="password" name="nova_senha" placeholder="Nova Senha" required><br><br>
        <button type="submit">Atualizar Senha</button>
    </form>
</body>
</html>