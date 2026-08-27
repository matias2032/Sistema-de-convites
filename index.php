<?php
session_start();

// Se o usuário já estiver logado, redireciona diretamente para o dashboard
if (isset($_SESSION['id_usuario'])) {
    header("Location: dashboard.php");
    exit;
} else {
    // Se não estiver logado, redireciona para a tela de login
    header("Location: login.php");
    exit;
}
?>