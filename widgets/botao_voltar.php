<?php
/**
 * Cabeçalho de página com botão "Voltar" reutilizável.
 *
 * Como usar (antes do <h2> na página filha):
 *   <?php
 *     $voltar_href = 'usuarios_lista.php';
 *     $titulo_pagina = 'Cadastrar Novo Usuário';
 *     include '../includes/botao_voltar.php';
 *   ?>
 *
 * Se $voltar_href não for definido, usa history.back() como fallback.
 */
$voltar_href  = $voltar_href  ?? 'javascript:history.back()';
$titulo_pagina = $titulo_pagina ?? '';
?>
<div class="page-header">
    <a href="<?= htmlspecialchars($voltar_href) ?>" class="btn-voltar" aria-label="Voltar" title="Voltar">
        &#8592;
    </a>
    <?php if ($titulo_pagina): ?>
        <h2><?= htmlspecialchars($titulo_pagina) ?></h2>
    <?php endif; ?>
</div>