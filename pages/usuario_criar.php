<?php
require_once '../services/conexao.php';
include '../widgets/botao_voltar.php';
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
        $msg = "Usuário cadastrado com sucesso! Senha padrão: <b>12345678</b>. Redirecionando á lista de usuários...";
        header("Refresh: 3; url=usuarios_lista.php");
    } catch(PDOException $e) {
        $msg = "Erro ao cadastrar: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head><title>Criar Usuário</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="../css/estilo.css">
<script src="../js/darkmode.js" defer></script>
</head>
<body>
    <div class="container">
        <?php
            $voltar_href = 'usuarios_lista.php';
            $titulo_pagina = 'Cadastrar Novo Usuário';

        ?>
        <?php if($msg) echo "<p class='msg-sucesso'>$msg</p>"; ?>
        <div class="form-wrapper">
            <form method="POST">
                <input type="text" name="nome" placeholder="Nome Completo" required>
                <input type="email" name="email" placeholder="E-mail" required>
                <button type="submit">Salvar Usuário</button>
            </form>
        </div>
    </div>
</body>
</html>