<?php
require_once 'conexao.php';
checarSessao();
?>
<!DOCTYPE html>
<html lang="pt">
<head><title>Dashboard</title>
<link rel="stylesheet" href="css/estilo.css">
</head>
<body>
    <h2>Painel Principal</h2>
    <p>Bem-vindo, <b><?= htmlspecialchars($_SESSION['nome']) ?></b>!</p>
    <hr>
    <ul>

        <li><a href="usuarios_lista.php">Lista de Usuários</a></li>
         <li><a href="convidados_lista.php">Lista de Convidados</a></li>
        <li><a href="perfil_editar.php">Editar Meu Perfil</a></li>
        <li><a href="senha_editar.php">Alterar Minha Senha</a></li>
        <li><a href="logout.php">Sair</a></li>
    </ul>
</body>
</html>