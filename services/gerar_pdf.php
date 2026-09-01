<?php
require_once '../services/conexao.php';
require_once '../fpdf/fpdf.php'; 

$id = $_GET['id'] ?? null;
if (!$id) die("Convidado não encontrado.");

$db = (new Conexao())->getConexao();
$stmt = $db->prepare("SELECT * FROM convidado WHERE id_convidado = :id");
$stmt->execute([':id' => $id]);
$convidado = $stmt->fetch();

if (!$convidado) die("Convidado não existe.");

if ($convidado['status'] === 'PENDENTE') {
    $stmtUpdate = $db->prepare("UPDATE convidado SET status = 'EMITIDO' WHERE id_convidado = :id");
    $stmtUpdate->execute([':id' => $id]);
    $convidado['status'] = 'EMITIDO';
}

function hex2rgb($hex) {
    $hex = str_replace("#", "", $hex);
    if (strlen($hex) == 3) {
        $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
    }
    return [
        hexdec(substr($hex, 0, 2)),
        hexdec(substr($hex, 2, 2)),
        hexdec(substr($hex, 4, 2))
    ];
}

$stmtCfg = $db->query("SELECT * FROM configuracao_convite WHERE id = 1");
$cfg = $stmtCfg->fetch();

$cor_p = hex2rgb($cfg['cor_primaria'] ?? '#2563eb');
$cor_c = hex2rgb($cfg['cor_codigo'] ?? '#000000');
$cor_f = hex2rgb($cfg['cor_fundo'] ?? '#ffffff');
$fonte = $cfg['fonte_familia'] ?? 'AlexBrush';

$pdf = new FPDF('P', 'mm', [148, 150]);

$pasta_fontes = __DIR__ . '/../fontes/';
$fontes_customizadas = [
    'AlexBrush'          => ['path' => $pasta_fontes . 'Alex_Brush/AlexBrush-Regular', 'size' => 22],
    'PinyonScript'       => ['path' => $pasta_fontes . 'Pinyon_Script/PinyonScript-Regular', 'size' => 22],
    'Cinzel'             => ['path' => $pasta_fontes . 'Cinzel/Cinzel-Regular', 'size' => 16],
    'CinzelDecorative'   => ['path' => $pasta_fontes . 'Cinzel_Decorative/CinzelDecorative-Regular', 'size' => 14],
    'CormorantGaramond'  => ['path' => $pasta_fontes . 'Cormorant_Garamond/CormorantGaramond-Regular', 'size' => 18],
    'PlayfairDisplay'    => ['path' => $pasta_fontes . 'Playfair_Display/PlayfairDisplay-Regular', 'size' => 16],
    'Merriweather'       => ['path' => $pasta_fontes . 'Merriweather/Merriweather-Regular', 'size' => 15],
    'Montserrat'         => ['path' => $pasta_fontes . 'Montserrat/Montserrat-Regular', 'size' => 14],
    'Roboto'             => ['path' => $pasta_fontes . 'Roboto/Roboto-Regular', 'size' => 14],
    'Inter'              => ['path' => $pasta_fontes . 'Inter/Inter-Regular', 'size' => 14]
];

if (array_key_exists($fonte, $fontes_customizadas)) {
    $basePath = $fontes_customizadas[$fonte]['path'];
    if (file_exists($basePath . '.json')) {
        $pdf->AddFont($fonte, '', basename($basePath) . '.json', dirname($basePath) . '/');
    } elseif (file_exists($basePath . '.php')) {
        $pdf->AddFont($fonte, '', basename($basePath) . '.php', dirname($basePath) . '/');
    }
}

$pdf->AddPage();

// Fundo
$pdf->SetFillColor($cor_f[0], $cor_f[1], $cor_f[2]);
$pdf->Rect(0, 0, $pdf->GetPageWidth(), $pdf->GetPageHeight(), 'F');

// Moldura
if (!empty($cfg['imagem_fundo']) && $cfg['imagem_fundo'] !== 'nenhuma') {
    $caminho_moldura = '../img/molduras/' . $cfg['imagem_fundo'];
    if (file_exists($caminho_moldura)) {
        $pdf->Image($caminho_moldura, 0, 0, $pdf->GetPageWidth(), $pdf->GetPageHeight());
    }
}

$pdf->SetY(18);

// Subtítulo
if (!empty($cfg['subtitulo_evento'])) {
    $pdf->SetFont('Arial', '', 7);
    $pdf->SetTextColor(100, 100, 100);
    $pdf->Cell(0, 4, utf8_decode(mb_strtoupper($cfg['subtitulo_evento'], 'UTF-8')), 0, 1, 'C');
    $pdf->Ln(1);
}

// Título Principal
$tamanho_titulo = $fontes_customizadas[$fonte]['size'] ?? 16;
$pdf->SetFont($fonte, '', $tamanho_titulo);
$pdf->SetTextColor($cor_p[0], $cor_p[1], $cor_p[2]);
$pdf->Cell(0, 8, utf8_decode($cfg['titulo_evento']), 0, 1, 'C');

// Divisor ornamentado: linha - bolinha - linha
$y = $pdf->GetY() + 3;
$pdf->SetDrawColor($cor_p[0], $cor_p[1], $cor_p[2]);
$pdf->SetLineWidth(0.15);
$pdf->Line(48, $y, 70, $y);
$pdf->Line(78, $y, 100, $y);

$pdf->SetFillColor($cor_p[0], $cor_p[1], $cor_p[2]);
$pdf->Rect(73.3, $y - 0.7, 1.4, 1.4, 'F'); // quadradinho central (bem pequeno, quase imperceptível que não é losango)

$pdf->Ln(8);

$pdf->Ln(8);

// Convidado
$pdf->SetFont('Arial', '', 8);
$pdf->SetTextColor(120, 120, 120);
$pdf->Cell(0, 4, utf8_decode('Convidado(a) Especial:'), 0, 1, 'C');

$pdf->SetFont('Arial', 'B', 12);
$pdf->SetTextColor($cor_p[0], $cor_p[1], $cor_p[2]);
$pdf->Cell(0, 7, utf8_decode($convidado['nome_completo']), 0, 1, 'C');
$pdf->Ln(3);

// Tratamento de Data e Hora
$data_str = '';
if (!empty($cfg['data_evento']) && $cfg['data_evento'] !== '0000-00-00' && strtotime($cfg['data_evento']) > 0) {
    $data_str = date('d/m/Y', strtotime($cfg['data_evento']));
}
if (!empty($cfg['hora_evento'])) {
    $hora_formatada = date('G', strtotime($cfg['hora_evento'])) . 'hrs';
    $data_str = $data_str ? ($data_str . ' - ' . $hora_formatada) : $hora_formatada;
}

// Bloco de Informações do Evento
if ($data_str || !empty($cfg['local_evento']) || !empty($cfg['traje_evento'])) {
    $y_bloco = $pdf->GetY();

    $pdf->SetY($y_bloco + 2);
    $pdf->SetFont('Arial', '', 9);
    $pdf->SetTextColor(50, 50, 50);

    if ($data_str) {
        $pdf->Cell(0, 4, utf8_decode($data_str), 0, 1, 'C');
    }

    if (!empty($cfg['local_evento'])) {
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(0, 4, utf8_decode('Local: ' . $cfg['local_evento']), 0, 1, 'C');
    }

    if (!empty($cfg['traje_evento'])) {
        $pdf->SetFont('Arial', 'I', 8);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->Cell(0, 4, utf8_decode('Traje: ' . $cfg['traje_evento']), 0, 1, 'C');
    }
    $pdf->Ln(6);
}

// Bloco de QR Code e Código de Acesso
if (!empty($cfg['exibir_qrcode']) && $cfg['exibir_qrcode'] == 1) {
    $qr_url = 'https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=' . $convidado['codigo_unico'];
    $y_qr = $pdf->GetY();
    
    // QR Code alinhado à esquerda do centro
    $pdf->Image($qr_url, 40, $y_qr, 16, 16, 'PNG');
    
    // Texto alinhado à direita do QR Code
    $pdf->SetY($y_qr + 2);
    $pdf->SetX(60);
    $pdf->SetFont('Arial', '', 7);
    $pdf->SetTextColor(100, 100, 100);
    $pdf->Cell(50, 3, utf8_decode('Código de Acesso:'), 0, 1, 'L');
    
    $pdf->SetX(60);
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->SetTextColor($cor_c[0], $cor_c[1], $cor_c[2]);
    $pdf->Cell(50, 6, $convidado['codigo_unico'], 0, 1, 'L');
    $pdf->Ln(7);
} else {
    $pdf->SetFont('Arial', '', 7);
    $pdf->SetTextColor(100, 100, 100);
    $pdf->Cell(0, 3, utf8_decode('Código de Acesso:'), 0, 1, 'C');
    
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->SetTextColor($cor_c[0], $cor_c[1], $cor_c[2]);
    $pdf->Cell(0, 6, $convidado['codigo_unico'], 0, 1, 'C');
    $pdf->Ln(3);
}

// Rodapé
if (!empty($cfg['mensagem_rodape'])) {
    $pdf->SetFont('Arial', 'I', 7);
    $pdf->SetTextColor(100, 100, 100);
    $pdf->Cell(0, 5, utf8_decode($cfg['mensagem_rodape']), 0, 1, 'C');
}

$pdf->Output('I', 'Convite_' . $convidado['codigo_unico'] . '.pdf');