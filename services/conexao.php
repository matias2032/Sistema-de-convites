<?php
class Conexao {
    private $host = "localhost";
    private $bd = "sistema_convites";
    private $root = "root";
    private $password = "";
    private $conn;

    public function getConexao() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->bd . ";charset=utf8mb4", $this->root, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            die("Erro na conexão: " . $e->getMessage());
        }
        return $this->conn;
    }
}

// Auxiliar para iniciar sessões protegidas
function checarSessao() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (!isset($_SESSION['id_usuario'])) {
        header("Location: login.php");
        exit;
    }
    if ($_SESSION['primeira_senha'] && basename($_SERVER['PHP_SELF']) !== 'primeira_senha.php') {
        header("Location: primeira_senha.php");
        exit;
    }
}
?>