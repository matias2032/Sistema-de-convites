<?php
require_once 'conexao.php';

// Garante que a sessão esteja ativa para validações
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verifica se o usuário está autenticado
if (!isset($_SESSION['id_usuario'])) {
    header('Location: ../pages/login.php');
    exit;
}

// Captura e valida o ID do convidado enviado via URL (GET)
$id_convidado = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($id_convidado) {
    try {
        $db = (new Conexao())->getConexao();

        // Atualiza o status do convidado para CANCELADO
        $stmt = $db->prepare("UPDATE convidado SET status = 'CANCELADO' WHERE id_convidado = :id");
        $stmt->bindValue(':id', $id_convidado, PDO::PARAM_INT);
        $stmt->execute();

        $_SESSION['msg_sucesso'] = "Convite cancelado com sucesso!";
    } catch (PDOException $e) {
        $_SESSION['msg_erro'] = "Erro ao cancelar convite: " . $e->getMessage();
    }
} else {
    $_SESSION['msg_erro'] = "Identificador de convidado inválido.";
}

// Redireciona de volta para a listagem
header('Location: ../pages/convidados_lista.php');
exit;