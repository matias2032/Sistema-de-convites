<?php
require_once '../services/conexao.php';
require_once '../fpdf/fpdf.php';

session_start();
if (!isset($_SESSION['usuario_id'])) {
    // header('Location: ../index.php');
}

$db = (new Conexao())->getConexao();

// Busca os convidados incluindo o tipo
$stmt = $db->query("SELECT codigo_unico, nome_completo, telefone, tipo_convite, status FROM convidado ORDER BY nome_completo ASC");
$convidados = $stmt->fetchAll(PDO::FETCH_ASSOC);

class PDF_Lista extends FPDF {
    function Header() {
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 10, utf8_decode('Lista Geral de Convidados'), 0, 1, 'C');
        $this->Ln(4);
        
        // Cabeçalho da Tabela - Ajustado para acomodar a coluna "Tipo"
        $this->SetFont('Arial', 'B', 9);
        $this->SetFillColor(230, 230, 230);
        
        $this->Cell(28, 7, utf8_decode('Código'), 1, 0, 'C', true);
        $this->Cell(52, 7, utf8_decode('Nome Completo'), 1, 0, 'L', true);
        $this->Cell(28, 7, utf8_decode('Tipo'), 1, 0, 'C', true);
          $this->Cell(28, 7, utf8_decode('Telefone'), 1, 0, 'C', true);
        $this->Cell(26, 7, utf8_decode('Status'), 1, 1, 'C', true);
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
$pdf->SetFont('Arial', '', 8.5);

if (empty($convidados)) {
    $pdf->Cell(0, 10, utf8_decode('Nenhum convidado encontrado.'), 1, 1, 'C');
} else {
    foreach ($convidados as $c) {
        $pdf->Cell(28, 6, utf8_decode($c['codigo_unico']), 1, 0, 'C');
        $pdf->Cell(52, 6, utf8_decode($c['nome_completo']), 1, 0, 'L');
        $pdf->Cell(28, 6, utf8_decode($c['tipo_convite'] ?? 'Normal'), 1, 0, 'C');
         $pdf->Cell(28, 6, utf8_decode($c['telefone'] ?? '-'), 1, 0, 'C');
        $pdf->Cell(26, 6, utf8_decode($c['status']), 1, 1, 'C');
    }
}

$pdf->Output('I', 'Lista_de_Convidados.pdf');