<?php
require_once 'conexao.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$telefone = filter_input(INPUT_GET, 'tel', FILTER_SANITIZE_SPECIAL_CHARS);
$texto = $_GET['text'] ?? '';

if ($id) {
    $db = (new Conexao())->getConexao();
    // Atualiza para EMITIDO se estiver PENDENTE
    $stmt = $db->prepare("UPDATE convidado SET status = 'EMITIDO' WHERE id_convidado = :id AND status = 'PENDENTE'");
    $stmt->execute([':id' => $id]);
}

// Redireciona para o WhatsApp
header("Location: https://wa.me/{$telefone}?text=" . urlencode($texto));
exit;