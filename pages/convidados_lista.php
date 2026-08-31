<?php
require_once '../services/conexao.php';
ob_start();
include_once '../widgets/sidebar.php';
$sidebar_html = ob_get_clean();
checarSessao();

$db = (new Conexao())->getConexao();
$stmt = $db->query("SELECT * FROM convidado ORDER BY id_convidado DESC");
$convidados = $stmt->fetchAll();

// Descobre a URL base do seu sistema dinamicamente
$protocolo = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$url_base  = $protocolo . "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Lista de Convidados</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/estilo.css">
    <script src="../js/darkmode.js" defer></script>
    <script src="../js/sidebar.js" defer></script>
</head>
<body>
    <div class="app-layout">
        <?= $sidebar_html ?>
        <main class="main-content">
         
            <h2>Convidados</h2>
            <div class="table-responsive">
            <table border="1" cellpadding="8" cellspacing="0">
        <thead>
            <tr>
                <th>Código</th>
                <th>Nome</th>
                <th>Documento</th>
                <th>Telefone</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($convidados as $c): 
                $telefone_limpo = preg_replace('/[^0-9]/', '', $c['telefone']);
                
                // Link direto de download/visualização do PDF para o convidado
                $link_pdf = $url_base . "/gerar_pdf.php?id=" . $c['id_convidado'];
                
                // Mensagem contendo o código E o link do PDF
                $texto_mensagem = "Olá " . $c['nome_completo'] . "!\n\nO seu código de acesso é: *" . $c['codigo_unico'] . "*.\n\nBaixe seu convite em PDF aqui: " . $link_pdf;
                $mensagem_wa = urlencode($texto_mensagem);
            ?>
            <tr>
                <td><b><?= htmlspecialchars($c['codigo_unico']) ?></b></td>
                <td><?= htmlspecialchars($c['nome_completo']) ?></td>
                <td><?= htmlspecialchars($c['documento_id']) ?></td>
                <td><?= htmlspecialchars($c['telefone'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($c['status']) ?></td>
                <td>
                    <a href="convidado_form.php?id=<?= $c['id_convidado'] ?>">Editar</a> | 
                    <a href="gerar_pdf.php?id=<?= $c['id_convidado'] ?>" target="_blank">📄 PDF</a> | 
                    
                    <?php if (!empty($telefone_limpo)): ?>
                        <a href="https://wa.me/<?= $telefone_limpo ?>?text=<?= $mensagem_wa ?>" target="_blank" style="color: green;">📱 Enviar Convite via WhatsApp</a>
                    <?php else: ?>
                        <span style="color: #999;">📱 Sem Telefone</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
            </table>
            </div>
            <a href="convidado_form.php" class="fab-btn" title="Novo Convidado" aria-label="Novo Convidado">+</a>
        </main>
    </div>
</body>
</html>