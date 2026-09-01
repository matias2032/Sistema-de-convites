<?php
require_once '../services/conexao.php';
ob_start();
include_once '../widgets/sidebar.php';
$sidebar_html = ob_get_clean();
checarSessao();

$db = (new Conexao())->getConexao();
$stmt = $db->query("SELECT * FROM convidado ORDER BY id_convidado DESC");
$convidados = $stmt->fetchAll();

// Trata a barra no final do dirname para evitar o erro "pages../"
$protocolo = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$caminho_atual = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
$url_base = $protocolo . "://" . $_SERVER['HTTP_HOST'] . $caminho_atual . "/";
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
    <style>
        /* CSS Interno para Botões de Ações */
        .actions-cell {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
            white-space: nowrap;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 5px 10px;
            font-size: 0.82rem;
            font-weight: 600;
            border-radius: var(--radius-sm, 6px);
            text-decoration: none;
            transition: all var(--transition-fast, 0.2s);
            border: 1px solid transparent;
            cursor: pointer;
        }

        .btn-action:hover {
            text-decoration: none;
            transform: translateY(-1px);
        }

        /* 1. Editar (Azul) */
        .btn-action-edit {
            background-color: rgba(37, 99, 235, 0.1);
            color: var(--primary, #2563eb);
            border-color: rgba(37, 99, 235, 0.2);
        }
        .btn-action-edit:hover {
            background-color: var(--primary, #2563eb);
            color: #ffffff;
        }

        /* 2. PDF (Cinza Neutro) */
        .btn-action-pdf {
            background-color: var(--nav-bg, #f1f5f9);
            color: var(--text-primary, #1e293b);
            border-color: var(--border-color, #e2e8f0);
        }
        .btn-action-pdf:hover {
            background-color: var(--nav-bg-hover, #e2e8f0);
        }

        /* 3. WhatsApp (Verde) */
        .btn-action-wa {
            background-color: rgba(16, 185, 129, 0.1);
            color: #10b981;
            border-color: rgba(16, 185, 129, 0.2);
        }
        .btn-action-wa:hover {
            background-color: #10b981;
            color: #ffffff;
        }

        /* 4. Cancelar (Vermelho) */
        .btn-action-cancel {
            background-color: var(--msg-error-bg, #fee2e2);
            color: var(--msg-error-text, #991b1b);
            border-color: rgba(239, 68, 68, 0.2);
        }
        .btn-action-cancel:hover {
            background-color: var(--msg-error-border, #ef4444);
            color: #ffffff;
        }

        .btn-action-disabled {
            opacity: 0.5;
            cursor: not-allowed;
            pointer-events: none;
        }
    </style>
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
    
    // Gera a URL limpa apontando para a pasta services
    $link_pdf = $url_base . "../services/gerar_pdf.php?id=" . $c['id_convidado'];
    
    // Mensagem contendo o código E o link do PDF
    $texto_mensagem = "Olá " . $c['nome_completo'] . "!\n\nO seu código de acesso é: *" . $c['codigo_unico'] . "*.\n\nBaixe seu convite em PDF aqui: " . $link_pdf;
?>
<tr>
    <td><b><?= htmlspecialchars($c['codigo_unico']) ?></b></td>
    <td><?= htmlspecialchars($c['nome_completo']) ?></td>
    <td><?= htmlspecialchars($c['documento_id']) ?></td>
    <td><?= htmlspecialchars($c['telefone'] ?? 'N/A') ?></td>
    <td><?= htmlspecialchars($c['status']) ?></td>
    <td class="actions-cell">
        <!-- 1. Editar -->
        <a href="convidado_form.php?id=<?= $c['id_convidado'] ?>" class="btn-action btn-action-edit">
            Editar
        </a>
        
        <!-- 2. PDF -->
        <a href="../services/gerar_pdf.php?id=<?= $c['id_convidado'] ?>" target="_blank" class="btn-action btn-action-pdf">
            PDF
        </a>
        
        <!-- 3. WhatsApp (Aponta para o emitir_whatsapp.php para atualizar status) -->
        <?php if (!empty($telefone_limpo)): ?>
            <a href="../services/emitir_whatsapp.php?id=<?= $c['id_convidado'] ?>&tel=<?= $telefone_limpo ?>&text=<?= urlencode($texto_mensagem) ?>" target="_blank" class="btn-action btn-action-wa">
                WhatsApp
            </a>
        <?php else: ?>
            <span class="btn-action btn-action-pdf btn-action-disabled" title="Sem Telefone">WhatsApp</span>
        <?php endif; ?>

        <!-- 4. Cancelamento -->
        <?php if ($c['status'] !== 'CANCELADO'): ?>
            <a href="../services/cancelar_convite.php?id=<?= $c['id_convidado'] ?>" 
               class="btn-action btn-action-cancel" 
               onclick="return confirm('Tem certeza que deseja cancelar o convite de <?= htmlspecialchars($c['nome_completo'], ENT_QUOTES) ?>?');">
                Cancelar
            </a>
        <?php else: ?>
            <span class="btn-action btn-action-cancel btn-action-disabled">Cancelado</span>
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