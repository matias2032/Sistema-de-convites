<?php
require_once 'conexao.php';
require_once 'fpdf/fpdf.php'; 

// Permite acesso logado ou via parâmetro público com token
$id = $_GET['id'] ?? null;
if (!$id) die("Convidado não encontrado.");

$db = (new Conexao())->getConexao();
$stmt = $db->prepare("SELECT * FROM convidado WHERE id_convidado = :id");
$stmt->execute([':id' => $id]);
$convidado = $stmt->fetch();

if (!$convidado) die("Convidado não existe.");

$pdf = new FPDF('P', 'mm', 'A5');
$pdf->AddPage();

// Estilização do Convite
$pdf->SetFont('Arial', 'B', 18);
$pdf->Cell(0, 15, utf8_decode('CONVITE ESPECIAL'), 0, 1, 'C');

$pdf->Ln(10);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, utf8_decode('Convidado(a):'), 0, 1, 'C');

$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, utf8_decode($convidado['nome_completo']), 0, 1, 'C');

$pdf->Ln(10);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, utf8_decode('Código Único de Acesso:'), 0, 1, 'C');

$pdf->SetFont('Arial', 'B', 22);
$pdf->SetTextColor(200, 0, 0); 
$pdf->Cell(0, 15, $convidado['codigo_unico'], 1, 1, 'C');

$pdf->Ln(10);
$pdf->SetFont('Arial', 'I', 9);
$pdf->SetTextColor(100, 100, 100);
$pdf->Cell(0, 10, utf8_decode('Apresente este documento acompanhado do seu BI/Passaporte na entrada.'), 0, 1, 'C');

// Exibe o PDF diretamente na tela
$pdf->Output('I', 'Convite_' . $convidado['codigo_unico'] . '.pdf');
?>