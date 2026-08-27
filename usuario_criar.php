<?php
require_once 'conexao.php';
checarSessao();

$msg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha_padrao = "12345678"; // Senha automatica conforme regra (1 a 8)
    $hash = password_hash($senha_padrao, PASSWORD_DEFAULT);

    try {
        $db = (new Conexao())->getConexao();
        $stmt = $db->prepare("INSERT INTO usuario (nome, email, senha_hash, primeira_senha, ativo) VALUES (:nome, :email, :hash, TRUE, TRUE)");
        $stmt->execute([':nome' => $nome, ':email' => $email, ':hash' => $hash]);
        $msg = "Usuário cadastrado com sucesso! Senha padrão: <b>12345678</b>";
    } catch(PDOException $e) {
        $msg = "Erro ao cadastrar: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head><title>Criar Usuário</title>
<link rel="stylesheet" href="css/estilo.css">
</head>
<body>
    <a href="dashboard.php">Voltar</a> | <a href="usuarios_lista.php">Listar Usuários</a>
    <h2>Cadastrar Novo Usuário</h2>
    <?php if($msg) echo "<p>$msg</p>"; ?>
    <form method="POST">
        <input type="text" name="nome" placeholder="Nome Completo" required><br><br>
        <input type="email" name="email" placeholder="E-mail" required><br><br>
        <button type="submit">Salvar Usuário</button>
    </form>
</body>
</html>