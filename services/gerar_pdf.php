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

function hex2rgb($hex) {
    $hex = str_replace("#", "", $hex);
    return [
        hexdec(substr($hex, 0, 2)),
        hexdec(substr($hex, 2, 2)),
        hexdec(substr($hex, 4, 2))
    ];
}

$stmtCfg = $db->query("SELECT * FROM configuracao_convite WHERE id = 1");
$cfg = $stmtCfg->fetch();

$cor_p = hex2rgb($cfg['cor_primaria']);
$cor_c = hex2rgb($cfg['cor_codigo']);
$fonte = $cfg['fonte_familia'];
$cor_f = hex2rgb($cfg['cor_fundo'] ?? '#ffffff');

// Define tamanho customizado (Largura: 148mm, Altura: 150mm - Proporção exata do Preview)
$pdf = new FPDF('P', 'mm', [148, 150]);

// Configuração das fontes customizadas
// __DIR__ = pasta atual (services/); ../fontes = raiz do projeto → pasta fontes/
$pasta_fontes = __DIR__ . '/../fontes/';

$fontes_customizadas = [
    'AlexBrush'          => ['path' => $pasta_fontes . 'Alex_Brush/AlexBrush-Regular', 'size' => 24],
    'PinyonScript'       => ['path' => $pasta_fontes . 'Pinyon_Script/PinyonScript-Regular', 'size' => 24],
    'Cinzel'             => ['path' => $pasta_fontes . 'Cinzel/Cinzel-Regular', 'size' => 17],
    'CinzelDecorative'   => ['path' => $pasta_fontes . 'Cinzel_Decorative/CinzelDecorative-Regular', 'size' => 15],
    'CormorantGaramond'  => ['path' => $pasta_fontes . 'Cormorant_Garamond/CormorantGaramond-Regular', 'size' => 19],
    'PlayfairDisplay'    => ['path' => $pasta_fontes . 'Playfair_Display/PlayfairDisplay-Regular', 'size' => 17],
    'Merriweather'       => ['path' => $pasta_fontes . 'Merriweather/Merriweather-Regular', 'size' => 16],
    'Montserrat'         => ['path' => $pasta_fontes . 'Montserrat/Montserrat-Regular', 'size' => 15],
    'Roboto'             => ['path' => $pasta_fontes . 'Roboto/Roboto-Regular', 'size' => 15],
    'Inter'              => ['path' => $pasta_fontes . 'Inter/Inter-Regular', 'size' => 15]
];

// Registra a fonte no FPDF
if (array_key_exists($fonte, $fontes_customizadas)) {
    $basePath = $fontes_customizadas[$fonte]['path'];
    if (file_exists($basePath . '.json')) {
        $pdf->AddFont($fonte, '', basename($basePath) . '.json', dirname($basePath) . '/');
    } elseif (file_exists($basePath . '.php')) {
        $pdf->AddFont($fonte, '', basename($basePath) . '.php', dirname($basePath) . '/');
    }
}

$pdf->AddPage();

// 1. Fundo
$pdf->SetFillColor($cor_f[0], $cor_f[1], $cor_f[2]);
$pdf->Rect(0, 0, $pdf->GetPageWidth(), $pdf->GetPageHeight(), 'F');

// 2. Moldura (Preenche as dimensões exatas do cartão)
if (!empty($cfg['imagem_fundo']) && $cfg['imagem_fundo'] !== 'nenhuma') {
    $caminho_moldura = '../img/molduras/' . $cfg['imagem_fundo'];
    if (file_exists($caminho_moldura)) {
        $pdf->Image($caminho_moldura, 0, 0, $pdf->GetPageWidth(), $pdf->GetPageHeight());
    }
}

$is_cursiva = in_array($fonte, ['AlexBrush', 'PinyonScript']);

// Espaçamento inicial do topo
$pdf->SetY(16);

// 3. Título
$tamanho_titulo = $fontes_customizadas[$fonte]['size'] ?? 17;
$pdf->SetFont($fonte, '', $tamanho_titulo);
$pdf->SetTextColor($cor_p[0], $cor_p[1], $cor_p[2]);
$pdf->Cell(0, 10, utf8_decode($cfg['titulo_evento']), 0, 1, 'C');

// 4. Data e Hora
if (!empty($cfg['data_evento'])) {
    $pdf->Ln(2);
    $timestamp = strtotime($cfg['data_evento']);
    $data_formatada = date('d/m/y', $timestamp);
    
    $hora_formatada = '';
    if (!empty($cfg['hora_evento'])) {
        $hora_formatada = ' - ' . date('G', strtotime($cfg['hora_evento'])) . 'hrs';
    }

    $tam_data = $is_cursiva ? 16 : 10;
    $pdf->SetFont($fonte, '', $tam_data);
    $pdf->SetTextColor(80, 80, 80);
    $pdf->Cell(0, 6, utf8_decode($data_formatada . $hora_formatada), 0, 1, 'C');
    $pdf->Ln(3);
}

// 5. Local
if (!empty($cfg['local_evento'])) {
    $tam_local = $is_cursiva ? 14 : 9;
    $pdf->SetFont($fonte, '', $tam_local);
    $pdf->SetTextColor(0, 0, 0); 
    $pdf->Cell(0, 6, utf8_decode('LOCAL: ' . mb_strtoupper($cfg['local_evento'], 'UTF-8')), 0, 1, 'C');
    $pdf->Ln(3);
}

// 6. Convidado e Código
$tam_rotulo = $is_cursiva ? 14 : 10;
$tam_nome   = $is_cursiva ? 20 : 14;
$tam_codigo = $is_cursiva ? 22 : 18;
$tam_rodape = $is_cursiva ? 12 : 8;

$pdf->SetFont($fonte, '', $tam_rotulo);
$pdf->SetTextColor(80, 80, 80);
$pdf->Cell(0, 6, utf8_decode('CONVIDADO(A):'), 0, 1, 'C');

$pdf->SetFont($fonte, '', $tam_nome);
$pdf->SetTextColor($cor_p[0], $cor_p[1], $cor_p[2]);
$pdf->Cell(0, 7, utf8_decode($convidado['nome_completo']), 0, 1, 'C');

$pdf->Ln(3);
$pdf->SetFont($fonte, '', $tam_rotulo);
$pdf->SetTextColor(80, 80, 80);
$pdf->Cell(0, 6, utf8_decode('CÓDIGO ÚNICO DE ACESSO:'), 0, 1, 'C');

$pdf->SetFont($fonte, '', $tam_codigo);
$pdf->SetTextColor($cor_c[0], $cor_c[1], $cor_c[2]); 
$pdf->Cell(0, 10, $convidado['codigo_unico'], 0, 1, 'C');

$pdf->Ln(4);
$pdf->SetFont($fonte, '', $tam_rodape);
$pdf->SetTextColor(100, 100, 100);
$pdf->Cell(0, 6, utf8_decode($cfg['mensagem_rodape']), 0, 1, 'C');

$pdf->Output('I', 'Convite_' . $convidado['codigo_unico'] . '.pdf');
?>