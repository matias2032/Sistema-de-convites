<?php
require_once '../services/conexao.php';
include '../widgets/botao_voltar.php';
checarSessao();

$db = (new Conexao())->getConexao();
$id = $_GET['id'] ?? null;
$convidado = [
    'nome_completo' => '', 
    'tipo_convite' => 'INDIVIDUAL', 
    'quantidade_acompanhantes' => 0, 
     'telefone' => ''
];
$msg = "";

if ($id) {
    $stmt = $db->prepare("SELECT * FROM convidado WHERE id_convidado = :id");
    $stmt->execute([':id' => $id]);
    $convidado = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo_convite = $_POST['tipo_convite'] ?? 'INDIVIDUAL';
    $telefone     = trim($_POST['telefone']);

    // Montagem dinâmica do campo nome_completo conforme o tipo de convite
    if ($tipo_convite === 'CASAL') {
        $conjuge1 = trim($_POST['nome_conjuge1'] ?? '');
        $conjuge2 = trim($_POST['nome_conjuge2'] ?? '');
        $nome_completo = $conjuge1 . ' & ' . $conjuge2;
        $qtd_acompanhantes = 1;
    } elseif ($tipo_convite === 'FAMILIA') {
        $nome_completo = trim($_POST['nome_familia'] ?? '');
        $qtd_acompanhantes = (int)($_POST['quantidade_acompanhantes'] ?? 1);
    } else {
        $nome_completo = trim($_POST['nome_completo'] ?? '');
        $qtd_acompanhantes = 0;
    }

    if ($id) {
        $stmt = $db->prepare("UPDATE convidado SET 
            nome_completo = :nome, 
            tipo_convite = :tipo, 
            quantidade_acompanhantes = :qtd, 
            telefone = :telefone 
            WHERE id_convidado = :id");
        
        $stmt->execute([
            ':nome'     => $nome_completo,
            ':tipo'     => $tipo_convite,
            ':qtd'      => $qtd_acompanhantes,
            ':telefone' => $telefone,
            ':id'       => $id
        ]);
        $msg = "Convidado atualizado com sucesso! Redirecionando...";
    } else {
        $codigo = 'CONVIDADO-' . rand(1000, 9999);
        $criado_por = $_SESSION['id_usuario'];
        $stmt = $db->prepare("INSERT INTO convidado (codigo_unico, nome_completo, tipo_convite, quantidade_acompanhantes, telefone, criado_por) VALUES (:codigo, :nome, :tipo, :qtd, :telefone, :criado_por)");
        $stmt->execute([
            ':codigo'     => $codigo, 
            ':nome'       => $nome_completo, 
            ':tipo'       => $tipo_convite, 
            ':qtd'        => $qtd_acompanhantes, 
             ':telefone'   => $telefone,
            ':criado_por' => $criado_por
        ]);
        $msg = "Convidado criado com sucesso! Redirecionando à lista...";
    }

    header("Refresh: 3; url=convidados_lista.php");
}

// Desmembra os nomes dos cônjuges para edição, se for CASAL
$conjuge1 = '';
$conjuge2 = '';
if ($convidado['tipo_convite'] === 'CASAL' && strpos($convidado['nome_completo'], '&') !== false) {
    $partes = explode('&', $convidado['nome_completo']);
    $conjuge1 = trim($partes[0]);
    $conjuge2 = trim($partes[1]);
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title><?= $id ? 'Editar' : 'Novo' ?> Convidado</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/estilo.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="../js/darkmode.js" defer></script>
</head>
<body>
    <div class="container">
        <?php
            $voltar_href = 'convidados_lista.php';
            $titulo_pagina = ($id ? 'Editar' : 'Novo') . ' Convidado';
        ?>

        <?php if ($msg): ?>
            <div class="msg-sucesso"><?= $msg ?></div>
        <?php endif; ?>

        <div class="form-wrapper">
            <form method="POST">
                <!-- Tipo de Convite -->
                <div class="input-group">
                    <i class="fa-solid fa-users input-icon"></i>
                    <select name="tipo_convite" id="tipo_convite" onchange="alternarCamposTipo()" style="width: 100%; padding: 10px; border-radius: 6px;">
                        <option value="INDIVIDUAL" <?= $convidado['tipo_convite'] === 'INDIVIDUAL' ? 'selected' : '' ?>>Individual</option>
                        <option value="CASAL" <?= $convidado['tipo_convite'] === 'CASAL' ? 'selected' : '' ?>>Casal</option>
                        <option value="FAMILIA" <?= $convidado['tipo_convite'] === 'FAMILIA' ? 'selected' : '' ?>>Família / Grupo</option>
                    </select>
                </div>

                <!-- Campo Individual -->
                <div id="campo-individual" class="input-group" style="display: <?= $convidado['tipo_convite'] === 'INDIVIDUAL' ? 'flex' : 'none' ?>;">
                    <i class="fa-solid fa-user input-icon"></i>
                    <input type="text" name="nome_completo" value="<?= htmlspecialchars($convidado['tipo_convite'] === 'INDIVIDUAL' ? $convidado['nome_completo'] : '') ?>" placeholder="Nome Completo do Convidado">
                </div>

                <!-- Campos Casal -->
                <div id="campos-casal" style="display: <?= $convidado['tipo_convite'] === 'CASAL' ? 'block' : 'none' ?>;">
                    <div class="input-group">
                        <i class="fa-solid fa-heart input-icon"></i>
                        <input type="text" name="nome_conjuge1" value="<?= htmlspecialchars($conjuge1) ?>" placeholder="Nome do 1º Cônjuge">
                    </div>
                    <div class="input-group">
                        <i class="fa-solid fa-heart input-icon"></i>
                        <input type="text" name="nome_conjuge2" value="<?= htmlspecialchars($conjuge2) ?>" placeholder="Nome do 2º Cônjuge">
                    </div>
                </div>

                <!-- Campos Família -->
                <div id="campos-familia" style="display: <?= $convidado['tipo_convite'] === 'FAMILIA' ? 'block' : 'none' ?>;">
                    <div class="input-group">
                        <i class="fa-solid fa-house-user input-icon"></i>
                        <input type="text" name="nome_familia" value="<?= htmlspecialchars($convidado['tipo_convite'] === 'FAMILIA' ? $convidado['nome_completo'] : '') ?>" placeholder="Ex: Família Silva">
                    </div>
                    <div class="input-group">
                        <i class="fa-solid fa-user-plus input-icon"></i>
                        <input type="number" name="quantidade_acompanhantes" min="1" max="20" value="<?= $convidado['quantidade_acompanhantes'] ?: 1 ?>" placeholder="Quantidade de Acompanhantes">
                    </div>
                </div>



                <div class="input-group">
                    <i class="fa-solid fa-phone input-icon"></i>
                    <input type="text" name="telefone" value="<?= htmlspecialchars($convidado['telefone']) ?>" placeholder="Telefone">
                </div>

                <button type="submit">
                    <i class="fa-solid fa-floppy-disk"></i> Salvar
                </button>
            </form>
        </div>
    </div>

    <script>
        function alternarCamposTipo() {
            const tipo = document.getElementById('tipo_convite').value;
            document.getElementById('campo-individual').style.display = (tipo === 'INDIVIDUAL') ? 'flex' : 'none';
            document.getElementById('campos-casal').style.display = (tipo === 'CASAL') ? 'block' : 'none';
            document.getElementById('campos-familia').style.display = (tipo === 'FAMILIA') ? 'block' : 'none';
        }
    </script>
</body>
</html>