<?php
require_once '../services/conexao.php';
include '../widgets/botao_voltar.php';
checarSessao();

$id_usuario = $_GET['id'] ?? null;
if (!$id_usuario) {
    header("Location: usuarios_lista.php");
    exit;
}

$db = (new Conexao())->getConexao();

// Busca dados do usuário
$stmtUser = $db->prepare("SELECT id_usuario, nome, email, ativo, criado_em FROM usuario WHERE id_usuario = :id");
$stmtUser->execute([':id' => $id_usuario]);
$usuario = $stmtUser->fetch();

if (!$usuario) {
    die("Usuário não encontrado.");
}

// Busca convidados cadastrados por este usuário
$stmtConv = $db->prepare("SELECT * FROM convidado WHERE criado_por = :id ORDER BY id_convidado DESC");
$stmtConv->execute([':id' => $id_usuario]);
$convidados = $stmtConv->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Detalhes do Usuário</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/estilo.css">
    <script src="../js/darkmode.js" defer></script>
</head>
<body>
<div class="container">
    <?php
        $voltar_href = 'usuarios_lista.php';
        $titulo_pagina = 'Detalhes do Usuário: ' . $usuario['nome'];
       
    ?>
        
        <div style="margin-bottom: 20px; padding: 15px; border-radius: 6px;">
            <p><b>E-mail:</b> <?= htmlspecialchars($usuario['email']) ?></p>
            <p><b>Status:</b> <?= $usuario['ativo'] ? 'Ativo' : 'Inativo' ?></p>
            <p><b>Cadastrado em:</b> <?= date('d/m/Y H:i', strtotime($usuario['criado_em'])) ?></p>
            <p><b>Total de Convidados Criados:</b> <?= count($convidados) ?></p>
        </div>

        <h3>Atividade: Convidados Cadastrados</h3>
        <?php if (empty($convidados)): ?>
            <p>Este usuário ainda não cadastrou nenhum convidado.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nome do Convidado</th>
                        <th>Documento</th>
                        <th>Status</th>
                        <th>Data de Criação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($convidados as $c): ?>
                    <tr>
                        <td><b><?= htmlspecialchars($c['codigo_unico']) ?></b></td>
                        <td><?= htmlspecialchars($c['nome_completo']) ?></td>
                        <td><?= htmlspecialchars($c['documento_id']) ?></td>
                        <td><?= htmlspecialchars($c['status']) ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($c['criado_em'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>