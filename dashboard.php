<?php
require_once 'conexao.php';
checarSessao();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="css/estilo.css">
</head>
<body>
    <div class="container">
        <!-- Banner orientativo do Fluxo Guia -->
        <div style="background: #e0f2fe; border-left: 4px solid #0284c7; padding: 12px 15px; margin-bottom: 20px; border-radius: 6px;">
            🎨 <b>Dica de Design:</b> Seu convite está usando o modelo padrão. <a href="configuracao_convite.php" style="font-weight: bold; text-decoration: underline;">Clique aqui para personalizar as cores e fontes do seu evento</a>.
        </div>

        <h2>Painel Principal</h2>
        <p>Bem-vindo, <b><?= htmlspecialchars($_SESSION['nome']) ?></b>!</p>
        <hr><br>
        <ul>
         
            <li><a href="usuarios_lista.php">Lista de Usuários</a></li>
             <li><a href="convidados_lista.php">Lista de Convidados</a></li>
            <li><a href="configuracao_convite.php">🎨 Personalizar Design do Convite</a></li>
            <li><a href="perfil_editar.php">Editar Meu Perfil</a></li>
            <li><a href="senha_editar.php">Alterar Minha Senha</a></li>
            <li><a href="logout.php" style="background-color: #fee2e2; color: #991b1b;">Sair</a></li>
        </ul>
    </div>
</body>
</html>