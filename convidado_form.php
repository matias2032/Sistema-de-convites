<?php
require_once 'conexao.php';
checarSessao();

$db = (new Conexao())->getConexao();
$id = $_GET['id'] ?? null;
$convidado = ['nome_completo' => '', 'documento_id' => '', 'email' => '', 'telefone' => ''];

if ($id) {
    $stmt = $db->prepare("SELECT * FROM convidado WHERE id_convidado = :id");
    $stmt->execute([':id' => $id]);
    $convidado = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome_completo']);
    $doc = trim($_POST['documento_id']);
    $email = trim($_POST['email']);
    $telefone = trim($_POST['telefone']);

    if ($id) {
        $stmt = $db->prepare("UPDATE convidado SET nome_completo = :nome, documento_id = :doc, email = :email, telefone = :telefone WHERE id_convidado = :id");
        $stmt->execute([':nome' => $nome, ':doc' => $doc, ':email' => $email, ':telefone' => $telefone, ':id' => $id]);
    } else {
        $codigo = 'CONVIDADO-' . rand(1000, 9999);
$criado_por = $_SESSION['id_usuario'];
$stmt = $db->prepare("INSERT INTO convidado (codigo_unico, nome_completo, documento_id, email, telefone, criado_por) VALUES (:codigo, :nome, :doc, :email, :telefone, :criado_por)");
$stmt->execute([
    ':codigo'     => $codigo, 
    ':nome'       => $nome, 
    ':doc'        => $doc, 
    ':email'      => $email, 
    ':telefone'   => $telefone,
    ':criado_por' => $criado_por
]);
    }
    header("Location: convidados_lista.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt">
<head><title><?= $id ? 'Editar' : 'Novo' ?> Convidado</title>
<link rel="stylesheet" href="css/estilo.css">
</head>
<body>
    <a href="convidados_lista.php">Voltar</a>
    <h2><?= $id ? 'Editar' : 'Novo' ?> Convidado</h2>
    <form method="POST">
        <input type="text" name="nome_completo" value="<?= htmlspecialchars($convidado['nome_completo']) ?>" placeholder="Nome Completo" required><br><br>
        <input type="text" name="documento_id" value="<?= htmlspecialchars($convidado['documento_id']) ?>" placeholder="Documento (BI/Passaporte)" required><br><br>
        <input type="email" name="email" value="<?= htmlspecialchars($convidado['email']) ?>" placeholder="E-mail"><br><br>
        <input type="text" name="telefone" value="<?= htmlspecialchars($convidado['telefone']) ?>" placeholder="Telefone"><br><br>
        <button type="submit">Salvar</button>
    </form>
</body>
</html>