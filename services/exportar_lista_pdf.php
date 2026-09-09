<?php
require_once '../services/conexao.php';
require_once '../fpdf/fpdf.php';

// Proteção simples / verificação de sessão se necessário
session_start();
if (!isset($_SESSION['usuario_id'])) { // Ajuste conforme a sua função checarSessao()
    // header('Location: ../index.php');
}

$db = (new Conexao())->getConexao();
$stmt = $db->query("SELECT * FROM convidado ORDER BY nome_completo ASC");
$convidados = $stmt->fetchAll(PDO::FETCH_ASSOC);

class PDF_Lista extends FPDF {
    function Header() {
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 10, utf8_decode('Lista Geral de Convidados'), 0, 1, 'C');
        $this->Ln(4);
        
        // Cabeçalho da Tabela
        $this->SetFont('Arial', 'B', 9);
        $this->SetFillColor(230, 230, 230);
        
        $this->Cell(30, 7, utf8_decode('Código'), 1, 0, 'C', true);
        $this->Cell(65, 7, utf8_decode('Nome Completo'), 1, 0, 'L', true);
        $this->Cell(35, 7, utf8_decode('Documento'), 1, 0, 'L', true);
        $this->Cell(30, 7, utf8_decode('Telefone'), 1, 0, 'C', true);
        $this->Cell(30, 7, utf8_decode('Status'), 1, 1, 'C', true);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, utf8_decode('Página ') . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}

$pdf = new PDF_Lista('P', 'mm', 'A4');
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 9);

if (empty($convidados)) {
    $pdf->Cell(0, 10, utf8_decode('Nenhum convidado encontrado.'), 1, 1, 'C');
} else {
    foreach ($convidados as $c) {
        $pdf->Cell(30, 6, utf8_decode($c['codigo_unico']), 1, 0, 'C');
        $pdf->Cell(65, 6, utf8_decode($c['nome_completo']), 1, 0, 'L');
        $pdf->Cell(35, 6, utf8_decode($c['documento_id'] ?? '-'), 1, 0, 'L');
        $pdf->Cell(30, 6, utf8_decode($c['telefone'] ?? '-'), 1, 0, 'C');
        $pdf->Cell(30, 6, utf8_decode($c['status']), 1, 1, 'C');
    }
}

$pdf->Output('I', 'Lista_de_Convidados.pdf');