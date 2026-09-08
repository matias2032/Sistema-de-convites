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

// Rotaciona um PNG preservando transparência e retorna o caminho de um arquivo temporário.
// O FPDF puro não tem rotação nativa (é preciso o addon fpdf_rotation.php), então giramos
// o próprio arquivo de imagem antes de entregá-lo ao FPDF.
function rotacionarMoldura($caminhoOriginal, $angulo) {
    if ($angulo == 0 || !extension_loaded('gd')) {
        return null; // sem rotação: quem chamou usa a imagem original
    }
    $img = @imagecreatefrompng($caminhoOriginal);
    if (!$img) return null;

    imagealphablending($img, false);
    imagesavealpha($img, true);
    $transparente = imagecolorallocatealpha($img, 0, 0, 0, 127);
    // imagerotate() gira em sentido anti-horário; invertendo o sinal para bater com o slider (sentido horário)
    $rotada = imagerotate($img, -$angulo, $transparente);
    imagealphablending($rotada, false);
    imagesavealpha($rotada, true);

    $tmp = sys_get_temp_dir() . '/moldura_' . uniqid() . '.png';
    imagepng($rotada, $tmp);

    // Guarda o tamanho REAL da tela após rotacionar (fica maior que o original,
    // exceto em ângulos múltiplos de 90°) — é isso que faltava usar depois.
    $largura_px = imagesx($rotada);
    $altura_px  = imagesy($rotada);

    imagedestroy($img);
    imagedestroy($rotada);

    return [
        'caminho' => $tmp,
        'largura_px' => $largura_px,
        'altura_px'  => $altura_px
    ];
}

$stmtCfg = $db->query("SELECT * FROM configuracao_convite WHERE id = 1");
$cfg = $stmtCfg->fetch();

$cor_p = hex2rgb($cfg['cor_primaria'] ?? '#2563eb');
$cor_c = hex2rgb($cfg['cor_codigo'] ?? '#000000');
$cor_f = hex2rgb($cfg['cor_fundo'] ?? '#ffffff');
$cor_texto = hex2rgb($cfg['cor_texto'] ?? '#555555');

$fonte       = $cfg['fonte_familia'] ?? 'AlexBrush';
$fonte_texto = $cfg['fonte_texto']   ?? 'Montserrat';

$moldura_escala  = (int)($cfg['moldura_escala'] ?? 100);
$moldura_pos_x   = (int)($cfg['moldura_pos_x'] ?? 0);
$moldura_pos_y   = (int)($cfg['moldura_pos_y'] ?? 0);
$moldura_rotacao = (int)($cfg['moldura_rotacao'] ?? 0);

$pdf = new FPDF('P', 'mm', [148, 150]);

$pasta_fontes = __DIR__ . '/../fontes/';

// Tamanho-base de cada fonte quando usada em TÍTULO (grande, destaque)
$fontes_customizadas = [
    'AlexBrush'          => ['path' => $pasta_fontes . 'Alex_Brush/AlexBrush-Regular',            'size_titulo' => 22],
    'PinyonScript'       => ['path' => $pasta_fontes . 'Pinyon_Script/PinyonScript-Regular',       'size_titulo' => 22],
    'Cinzel'             => ['path' => $pasta_fontes . 'Cinzel/Cinzel-Regular',                    'size_titulo' => 16],
    'CinzelDecorative'   => ['path' => $pasta_fontes . 'Cinzel_Decorative/CinzelDecorative-Regular','size_titulo' => 14],
    'CormorantGaramond'  => ['path' => $pasta_fontes . 'Cormorant_Garamond/CormorantGaramond-Regular','size_titulo' => 18],
    'PlayfairDisplay'    => ['path' => $pasta_fontes . 'Playfair_Display/PlayfairDisplay-Regular', 'size_titulo' => 16],
    'Merriweather'       => ['path' => $pasta_fontes . 'Merriweather/Merriweather-Regular',        'size_titulo' => 15],
    'Montserrat'         => ['path' => $pasta_fontes . 'Montserrat/Montserrat-Regular',            'size_titulo' => 14],
    'Roboto'             => ['path' => $pasta_fontes . 'Roboto/Roboto-Regular',                    'size_titulo' => 14],
    'Inter'              => ['path' => $pasta_fontes . 'Inter/Inter-Regular',                      'size_titulo' => 14]
];

// Registra uma fonte customizada no FPDF (aceita .json do FPDF moderno ou .php do formato antigo)
function registrarFonteCustomizada($pdf, $nomeFonte, $fontesCustomizadas) {
    if (!array_key_exists($nomeFonte, $fontesCustomizadas)) return false;
    $basePath = $fontesCustomizadas[$nomeFonte]['path'];
    if (file_exists($basePath . '.json')) {
        $pdf->AddFont($nomeFonte, '', basename($basePath) . '.json', dirname($basePath) . '/');
        return true;
    } elseif (file_exists($basePath . '.php')) {
        $pdf->AddFont($nomeFonte, '', basename($basePath) . '.php', dirname($basePath) . '/');
        return true;
    }
    return false;
}

// Registra a fonte do título
$titulo_ok = registrarFonteCustomizada($pdf, $fonte, $fontes_customizadas);

// Registra a fonte do texto de apoio (só se for diferente da do título, para não duplicar)
$apoio_ok = true;
if ($fonte_texto !== $fonte) {
    $apoio_ok = registrarFonteCustomizada($pdf, $fonte_texto, $fontes_customizadas);
}

// Nomes efetivos a usar no PDF (caem para Arial se o arquivo da fonte não existir)
$fonte_titulo_pdf = $titulo_ok ? $fonte : 'Arial';
$fonte_apoio_pdf  = $apoio_ok  ? $fonte_texto : 'Arial';

$pdf->AddPage();

// Fundo
$pdf->SetFillColor($cor_f[0], $cor_f[1], $cor_f[2]);
$pdf->Rect(0, 0, $pdf->GetPageWidth(), $pdf->GetPageHeight(), 'F');

// Moldura (respeitando escala, posição e rotação definidas no preview)
if (!empty($cfg['imagem_fundo']) && $cfg['imagem_fundo'] !== 'nenhuma') {
    $caminho_moldura = '../img/molduras/' . $cfg['imagem_fundo'];
    if (file_exists($caminho_moldura)) {
        // Tamanho original (antes de rotacionar), em pixels
        list($orig_px_w, $orig_px_h) = getimagesize($caminho_moldura);

        // Largura/altura-alvo em mm SEM rotação, conforme a escala (100% = tamanho da página)
        $img_w = $pdf->GetPageWidth()  * ($moldura_escala / 100);
        $img_h = $pdf->GetPageHeight() * ($moldura_escala / 100);

        // Fator de conversão px -> mm baseado no tamanho original — usado também
        // depois de rotacionar, pra não distorcer a imagem, só ampliar a "moldura" da tela
        $escala_px_mm_x = $img_w / $orig_px_w;
        $escala_px_mm_y = $img_h / $orig_px_h;

        $caminho_final = $caminho_moldura;
        $final_w = $img_w;
        $final_h = $img_h;

        $rot = rotacionarMoldura($caminho_moldura, $moldura_rotacao);
        if ($rot !== null) {
            $caminho_final = $rot['caminho'];
            $final_w = $rot['largura_px'] * $escala_px_mm_x;
            $final_h = $rot['altura_px']  * $escala_px_mm_y;
        }

        // O preview usa deslocamento em px sobre uma caixa de ~350px de largura;
        // convertendo essa proporção para mm da página do PDF
        $fator_px_para_mm = $pdf->GetPageWidth() / 350;
        $offset_x = $moldura_pos_x * $fator_px_para_mm;
        $offset_y = $moldura_pos_y * $fator_px_para_mm;

        // Centraliza a moldura (já rotacionada, se houver) e aplica o deslocamento
        $img_x = ($pdf->GetPageWidth()  - $final_w) / 2 + $offset_x;
        $img_y = ($pdf->GetPageHeight() - $final_h) / 2 + $offset_y;

        $pdf->Image($caminho_final, $img_x, $img_y, $final_w, $final_h);

        if ($caminho_final !== $caminho_moldura) {
            @unlink($caminho_final);
        }
    }
}

$pdf->SetY(18);

// Subtítulo (texto de apoio)
if (!empty($cfg['subtitulo_evento'])) {
    $pdf->SetFont($fonte_apoio_pdf, '', 7);
    $pdf->SetTextColor($cor_texto[0], $cor_texto[1], $cor_texto[2]);
    $pdf->Cell(0, 4, utf8_decode(mb_strtoupper($cfg['subtitulo_evento'], 'UTF-8')), 0, 1, 'C');
    $pdf->Ln(1);
}

// Título Principal (fonte de títulos)
$tamanho_titulo = $fontes_customizadas[$fonte]['size_titulo'] ?? 16;
$pdf->SetFont($fonte_titulo_pdf, '', $tamanho_titulo);
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

// Convidado — rótulo em fonte de apoio, nome em fonte de títulos (destaque, acompanha o título)
$pdf->SetFont($fonte_apoio_pdf, '', 8);
$pdf->SetTextColor($cor_texto[0], $cor_texto[1], $cor_texto[2]);
$pdf->Cell(0, 4, utf8_decode('Convidado(a) Especial:'), 0, 1, 'C');

$pdf->SetFont($fonte_titulo_pdf, '', min(14, $tamanho_titulo * 0.6));
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

// Bloco de Informações do Evento (texto de apoio)
if ($data_str || !empty($cfg['local_evento']) || !empty($cfg['traje_evento'])) {
    $y_bloco = $pdf->GetY();

    $pdf->SetY($y_bloco + 2);
    $pdf->SetFont($fonte_apoio_pdf, '', 9);
    $pdf->SetTextColor($cor_texto[0], $cor_texto[1], $cor_texto[2]);

    if ($data_str) {
        $pdf->Cell(0, 4, utf8_decode($data_str), 0, 1, 'C');
    }

    if (!empty($cfg['local_evento'])) {
        $pdf->SetFont($fonte_apoio_pdf, 'B', 8);
        $pdf->Cell(0, 4, utf8_decode('Local: ' . $cfg['local_evento']), 0, 1, 'C');
    }

    if (!empty($cfg['traje_evento'])) {
        $pdf->SetFont($fonte_apoio_pdf, '', 8);
        $pdf->SetTextColor($cor_texto[0], $cor_texto[1], $cor_texto[2]);
        $pdf->Cell(0, 4, utf8_decode('Traje: ' . $cfg['traje_evento']), 0, 1, 'C');
    }
    $pdf->Ln(6);
}

// Bloco de QR Code e Código de Acesso (rótulo em fonte de apoio, código em Arial Bold para legibilidade)
if (!empty($cfg['exibir_qrcode']) && $cfg['exibir_qrcode'] == 1) {
    $qr_url = 'https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=' . $convidado['codigo_unico'];
    $y_qr = $pdf->GetY();

    // QR Code alinhado à esquerda do centro
    $pdf->Image($qr_url, 40, $y_qr, 16, 16, 'PNG');

    // Texto alinhado à direita do QR Code
    $pdf->SetY($y_qr + 2);
    $pdf->SetX(60);
    $pdf->SetFont($fonte_apoio_pdf, '', 7);
    $pdf->SetTextColor($cor_texto[0], $cor_texto[1], $cor_texto[2]);
    $pdf->Cell(50, 3, utf8_decode('Código de Acesso:'), 0, 1, 'L');

    $pdf->SetX(60);
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->SetTextColor($cor_c[0], $cor_c[1], $cor_c[2]);
    $pdf->Cell(50, 6, $convidado['codigo_unico'], 0, 1, 'L');
    $pdf->Ln(7);
} else {
    $pdf->SetFont($fonte_apoio_pdf, '', 7);
    $pdf->SetTextColor($cor_texto[0], $cor_texto[1], $cor_texto[2]);
    $pdf->Cell(0, 3, utf8_decode('Código de Acesso:'), 0, 1, 'C');

    $pdf->SetFont('Arial', 'B', 11);
    $pdf->SetTextColor($cor_c[0], $cor_c[1], $cor_c[2]);
    $pdf->Cell(0, 6, $convidado['codigo_unico'], 0, 1, 'C');
    $pdf->Ln(3);
}

// Rodapé (texto de apoio)
if (!empty($cfg['mensagem_rodape'])) {
    $pdf->SetFont($fonte_apoio_pdf, '', 7);
    $pdf->SetTextColor($cor_texto[0], $cor_texto[1], $cor_texto[2]);
    $pdf->Cell(0, 5, utf8_decode($cfg['mensagem_rodape']), 0, 1, 'C');
}

$pdf->Output('I', 'Convite_' . $convidado['codigo_unico'] . '.pdf');