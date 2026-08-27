<?php
require_once 'conexao.php';
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
<link rel="stylesheet" href="css/estilo.css">
</head>
<body>
    <a href="dashboard.php">Voltar</a>
    <h2>Editar Meu Perfil</h2>
    <?php if($msg) echo "<p style='color:green;'>$msg</p>"; ?>
    <form method="POST">
        <input type="text" name="nome" value="<?= htmlspecialchars($user['nome']) ?>" required><br><br>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required><br><br>
        <button type="submit">Atualizar</button>
    </form>
</body>
</html>