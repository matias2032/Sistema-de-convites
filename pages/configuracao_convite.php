<?php
require_once '../services/conexao.php';
ob_start();
include_once '../widgets/sidebar.php';
$sidebar_html = ob_get_clean();
checarSessao();

$db = (new Conexao())->getConexao();
$msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo_design     = $_POST['tipo_design'] ?? 'MOLDURA'; // Captura do campo enviada pelo formulário
    $titulo          = trim($_POST['titulo_evento']);
    $subtitulo       = trim($_POST['subtitulo_evento'] ?? '');
    $data_evento     = $_POST['data_evento'] ?? null;
    $hora_evento     = $_POST['hora_evento'] ?? null;
    $local_evento    = trim($_POST['local_evento'] ?? '');
    $traje_evento    = trim($_POST['traje_evento'] ?? '');
    $cor_primaria    = $_POST['cor_primaria'];
    $cor_codigo      = $_POST['cor_codigo'];
    $cor_fundo       = $_POST['cor_fundo'];
    $fonte           = $_POST['fonte_familia'];
    $fonte_texto     = $_POST['fonte_texto'] ?? 'Montserrat';
    $borda           = 'nenhuma';
    $img_fundo       = $_POST['imagem_fundo'] ?? 'nenhuma';

    // Lógica para upload de Layout Completo
    if ($tipo_design === 'LAYOUT_COMPLETO' && isset($_FILES['upload_layout']) && $_FILES['upload_layout']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['upload_layout']['name'], PATHINFO_EXTENSION));
        $ext_permitidas = ['png', 'jpg', 'jpeg'];
        if (in_array($ext, $ext_permitidas)) {
            $nome_arquivo = 'layout_custom_' . uniqid() . '.' . $ext;
            $destino = '../img/molduras/' . $nome_arquivo;
            if (move_uploaded_file($_FILES['upload_layout']['tmp_name'], $destino)) {
                $img_fundo = $nome_arquivo;
            }
        }
    }

    $rodape          = trim($_POST['mensagem_rodape']);
    $exibir_qrcode   = isset($_POST['exibir_qrcode']) ? 1 : 0;
    $moldura_escala  = (int)($_POST['moldura_escala'] ?? 100);
    $moldura_pos_x   = (int)($_POST['moldura_pos_x'] ?? 0);
    $moldura_pos_y   = (int)($_POST['moldura_pos_y'] ?? 0);
    $moldura_rotacao = (int)($_POST['moldura_rotacao'] ?? 0);
    $cor_texto       = $_POST['cor_texto'] ?? '#555555';

    $stmt = $db->prepare("INSERT INTO configuracao_convite (
            id, tipo_design, titulo_evento, subtitulo_evento, data_evento, hora_evento, local_evento, traje_evento,
            cor_primaria, cor_codigo, cor_fundo, fonte_familia, fonte_texto, estilo_borda, 
            imagem_fundo, mensagem_rodape, exibir_qrcode, moldura_escala, moldura_pos_x, moldura_pos_y,
            moldura_rotacao, cor_texto
        ) VALUES (
            1, :tipo_d, :titulo, :subtitulo, :data_e, :hora_e, :local_e, :traje,
            :cor_p, :cor_c, :cor_f, :fonte, :fonte_texto, :borda, 
            :img_f, :rodape, :qr, :m_escala, :m_x, :m_y,
            :m_rot, :cor_t
        ) ON DUPLICATE KEY UPDATE 
                tipo_design      = VALUES(tipo_design),
                titulo_evento    = VALUES(titulo_evento),
                subtitulo_evento = VALUES(subtitulo_evento),
                data_evento      = VALUES(data_evento),
                hora_evento      = VALUES(hora_evento),
                local_evento     = VALUES(local_evento),
                traje_evento     = VALUES(traje_evento),
                cor_primaria     = VALUES(cor_primaria),
                cor_codigo       = VALUES(cor_codigo),
                cor_fundo        = VALUES(cor_fundo),
                fonte_familia    = VALUES(fonte_familia),
                fonte_texto      = VALUES(fonte_texto),
                estilo_borda     = VALUES(estilo_borda),
                imagem_fundo     = VALUES(imagem_fundo),
                mensagem_rodape  = VALUES(mensagem_rodape),
                exibir_qrcode    = VALUES(exibir_qrcode),
                moldura_escala   = VALUES(moldura_escala),
                moldura_pos_x    = VALUES(moldura_pos_x),
                moldura_pos_y    = VALUES(moldura_pos_y),
                moldura_rotacao  = VALUES(moldura_rotacao),
                cor_texto        = VALUES(cor_texto)");

    $stmt->execute([
        ':tipo_d'    => $tipo_design,
        ':titulo'    => $titulo,
        ':subtitulo' => $subtitulo,
        ':data_e'    => $data_evento,
        ':hora_e'    => $hora_evento,
        ':local_e'   => $local_evento,
        ':traje'     => $traje_evento,
        ':cor_p'     => $cor_primaria,
        ':cor_c'     => $cor_codigo,
        ':cor_f'     => $cor_fundo,
        ':fonte'     => $fonte,
        ':fonte_texto' => $fonte_texto,
        ':borda'     => $borda,
        ':img_f'     => $img_fundo,
        ':rodape'    => $rodape,
        ':qr'        => $exibir_qrcode,
        ':m_escala'  => $moldura_escala,
        ':m_x'       => $moldura_pos_x,
        ':m_y'       => $moldura_pos_y,
        ':m_rot'     => $moldura_rotacao,
        ':cor_t'     => $cor_texto
    ]);

    $msg = "Configurações atualizadas com sucesso! Redirecionando...";
    header("Refresh: 2; url=dashboard.php");
}

$stmt = $db->query("SELECT * FROM configuracao_convite WHERE id = 1");
$configData = $stmt->fetch(PDO::FETCH_ASSOC);

$config = [
    'tipo_design'      => $configData['tipo_design']      ?? 'MOLDURA',    
    'titulo_evento'    => $configData['titulo_evento']    ?? 'CONVITE ESPECIAL',
    'subtitulo_evento' => $configData['subtitulo_evento'] ?? 'Convidamos você para celebrar conosco',
    'data_evento'      => $configData['data_evento']      ?? '',
    'hora_evento'      => $configData['hora_evento']      ?? '',
    'local_evento'     => $configData['local_evento']     ?? '',
    'traje_evento'     => $configData['traje_evento']     ?? 'Esporte Fino',
    'cor_primaria'     => $configData['cor_primaria']     ?? '#2563eb',
    'cor_codigo'       => $configData['cor_codigo']       ?? '#000000',
    'cor_fundo'        => $configData['cor_fundo']        ?? '#ffffff',
    'fonte_familia'    => $configData['fonte_familia']    ?? 'AlexBrush',
    'fonte_texto'      => $configData['fonte_texto']      ?? 'Montserrat',
    'imagem_fundo'     => $configData['imagem_fundo']     ?? 'nenhuma',
    'mensagem_rodape'  => $configData['mensagem_rodape']  ?? 'Apresente este convite na entrada.',
    'exibir_qrcode'    => $configData['exibir_qrcode']    ?? 1,
    'moldura_escala'   => $configData['moldura_escala']   ?? 100,
    'moldura_pos_x'    => $configData['moldura_pos_x']    ?? 0,
    'moldura_pos_y'    => $configData['moldura_pos_y']    ?? 0,
    'moldura_rotacao'  => $configData['moldura_rotacao']  ?? 0,
    'cor_texto'        => $configData['cor_texto']        ?? '#555555'
];
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Personalizar Convite</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/estilo.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Alex+Brush&family=Cinzel+Decorative:wght@400;700;900&family=Cinzel:wght@400..900&family=Cormorant+Garamond:ital,wght@0,300..700;1,300..700&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Merriweather:ital,opsz,wght@0,18..144,300..900;1,18..144,300..900&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Pinyon+Script&family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="../js/darkmode.js" defer></script>
    <script src="../js/sidebar.js" defer></script>
</head>
<body>
    <div class="app-layout">
        <?= $sidebar_html ?>
        <main class="main-content">
            <div class="container" style="max-width: 1100px;">
                <h2>🎨 Personalizar Design do Convite</h2>
                
                <?php if($msg) echo "<div class='msg-sucesso'>$msg</div>"; ?>

                <div style="margin-top: 15px; padding: 15px; border-radius: 8px;">
                    <h4 style="margin-bottom: 10px;">Ajustes Rápidos:</h4>
                    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
<button type="button" onclick="aplicarTemplate('Casamento', '#991b1b', '#854d0e', '#fefce8', 'CinzelDecorative', 'Cinzel', 'moldura_geometrica2.png')" style="background: #fefce8; color: rgb(211, 175, 55); border: none; padding: 8px 12px; border-radius: 6px; cursor: pointer; font-weight: bold;">Casamento Boho</button>
                        <button type="button" onclick="aplicarTemplate('Corporativo', '#1e3a8a', '#1d4ed8', '#ffffff', 'Cinzel', 'nenhuma')" style="background: #ffffff; color: #1e3a8a; border: 1px solid #cbd5e1; padding: 8px 12px; border-radius: 6px; cursor: pointer; font-weight: bold;">Corporativo Luxo</button>
                        <button type="button" onclick="aplicarTemplate('Gala', '#4a154b', '#d97706', '#faf5ff', 'PinyonScript', 'nenhuma')" style="background: #faf5ff; color: #4a154b; border: 1px solid #e9d5ff; padding: 8px 12px; border-radius: 6px; cursor: pointer; font-weight: bold;">Monarquia Clássica</button>
                    </div>
                </div>

                <div style="display: flex; gap: 40px; flex-wrap: wrap; margin-top: 20px;">
                    <div style="flex: 1; min-width: 320px;">
<form method="POST" enctype="multipart/form-data" style="max-width: 100%;">
                            <label><b>Título do Evento:</b></label>
                            <input type="text" name="titulo_evento" value="<?= htmlspecialchars($config['titulo_evento']) ?>" required>

                            <label><b>Subtítulo / Mensagem de Boas-Vindas:</b></label>
                            <input type="text" name="subtitulo_evento" value="<?= htmlspecialchars($config['subtitulo_evento']) ?>" placeholder="Ex: Convidamos você para celebrar conosco">

                            <div style="display: flex; gap: 10px;">
                                <div style="flex: 1;">
                                    <label><b>Data do Evento:</b></label>
                                    <input type="date" name="data_evento" value="<?= $config['data_evento'] ?>">
                                </div>
                                <div style="flex: 1;">
                                    <label><b>Hora:</b></label>
                                    <input type="time" name="hora_evento" value="<?= $config['hora_evento'] ?>">
                                </div>
                            </div>

                            <div style="display: flex; gap: 10px;">
                                <div style="flex: 1;">
                                    <label><b>Local do Evento:</b></label>
                                    <input type="text" name="local_evento" value="<?= htmlspecialchars($config['local_evento']) ?>" placeholder="Ex: Salão Imperial">
                                </div>
                                <div style="flex: 1;">
                                    <label><b>Traje Recomendado:</b></label>
                                    <input type="text" name="traje_evento" value="<?= htmlspecialchars($config['traje_evento']) ?>" placeholder="Ex: Esporte Fino">
                                </div>
                            </div>

                            <label><b>Cor dos Títulos / Destaques:</b></label>
                            <input type="color" name="cor_primaria" value="<?= $config['cor_primaria'] ?>">

                            
                             <label><b>Cor do Texto de Apoio:</b></label>
                            <input type="color" name="cor_texto" value="<?= $config['cor_texto'] ?>">

                            <label><b>Cor do Código de Acesso:</b></label>
                            <input type="color" name="cor_codigo" value="<?= $config['cor_codigo'] ?>">

                            <label><b>Cor de Fundo do Convite:</b></label>
                            <input type="color" name="cor_fundo" value="<?= $config['cor_fundo'] ?>">


<label><b>Fonte dos Títulos:</b></label>
<select name="fonte_familia" style="padding: 10px; border-radius: 6px;">
    <optgroup label="Cursivas / Elegantes">
        <option value="AlexBrush" <?= $config['fonte_familia'] === 'AlexBrush' ? 'selected' : '' ?>>Alex Brush</option>
        <option value="PinyonScript" <?= $config['fonte_familia'] === 'PinyonScript' ? 'selected' : '' ?>>Pinyon Script</option>
    </optgroup>
    <optgroup label="Serifadas / Clássicas">
        <option value="Cinzel" <?= $config['fonte_familia'] === 'Cinzel' ? 'selected' : '' ?>>Cinzel</option>
        <option value="CinzelDecorative" <?= $config['fonte_familia'] === 'CinzelDecorative' ? 'selected' : '' ?>>Cinzel Decorative</option>
        <option value="CormorantGaramond" <?= $config['fonte_familia'] === 'CormorantGaramond' ? 'selected' : '' ?>>Cormorant Garamond</option>
        <option value="PlayfairDisplay" <?= $config['fonte_familia'] === 'PlayfairDisplay' ? 'selected' : '' ?>>Playfair Display</option>
        <option value="Merriweather" <?= $config['fonte_familia'] === 'Merriweather' ? 'selected' : '' ?>>Merriweather</option>
    </optgroup>
    <optgroup label="Modernas / Sans-serif">
        <option value="Montserrat" <?= $config['fonte_familia'] === 'Montserrat' ? 'selected' : '' ?>>Montserrat</option>
        <option value="Roboto" <?= $config['fonte_familia'] === 'Roboto' ? 'selected' : '' ?>>Roboto</option>
        <option value="Inter" <?= $config['fonte_familia'] === 'Inter' ? 'selected' : '' ?>>Inter</option>
    </optgroup>
</select>

<label><b>Fonte do Texto de Apoio:</b></label>
<select name="fonte_texto" style="padding: 10px; border-radius: 6px;">
    <optgroup label="Cursivas / Elegantes">
        <option value="AlexBrush" <?= $config['fonte_texto'] === 'AlexBrush' ? 'selected' : '' ?>>Alex Brush</option>
        <option value="PinyonScript" <?= $config['fonte_texto'] === 'PinyonScript' ? 'selected' : '' ?>>Pinyon Script</option>
    </optgroup>
    <optgroup label="Serifadas / Clássicas">
        <option value="Cinzel" <?= $config['fonte_texto'] === 'Cinzel' ? 'selected' : '' ?>>Cinzel</option>
        <option value="CinzelDecorative" <?= $config['fonte_texto'] === 'CinzelDecorative' ? 'selected' : '' ?>>Cinzel Decorative</option>
        <option value="CormorantGaramond" <?= $config['fonte_texto'] === 'CormorantGaramond' ? 'selected' : '' ?>>Cormorant Garamond</option>
        <option value="PlayfairDisplay" <?= $config['fonte_texto'] === 'PlayfairDisplay' ? 'selected' : '' ?>>Playfair Display</option>
        <option value="Merriweather" <?= $config['fonte_texto'] === 'Merriweather' ? 'selected' : '' ?>>Merriweather</option>
    </optgroup>
    <optgroup label="Modernas / Sans-serif">
        <option value="Montserrat" <?= $config['fonte_texto'] === 'Montserrat' ? 'selected' : '' ?>>Montserrat</option>
        <option value="Roboto" <?= $config['fonte_texto'] === 'Roboto' ? 'selected' : '' ?>>Roboto</option>
        <option value="Inter" <?= $config['fonte_texto'] === 'Inter' ? 'selected' : '' ?>>Inter</option>
    </optgroup>
</select>

<!-- Seletor do Modo de Design -->
<label><b>Modo de Design:</b></label>
<select name="tipo_design" id="tipo_design" onchange="atualizarPreview()" style="padding: 10px; border-radius: 6px;">
    <option value="MOLDURA" <?= $config['tipo_design'] === 'MOLDURA' ? 'selected' : '' ?>>Usar Moldura (Gera textos no sistema)</option>
    <option value="LAYOUT_COMPLETO" <?= $config['tipo_design'] === 'LAYOUT_COMPLETO' ? 'selected' : '' ?>>Upload de Layout Completo (Apenas sobrepõe o convidado e QR)</option>
</select>

<!-- Bloco de Upload para Layout Completo -->
<div id="bloco-upload-layout" style="margin-top: 10px; display: <?= $config['tipo_design'] === 'LAYOUT_COMPLETO' ? 'block' : 'none' ?>;">
    <label><b>Enviar Arte/Layout Completo (PNG/JPG):</b></label>
    <input type="file" name="upload_layout" accept="image/*" onchange="previewUploadArquivo(this)">
</div>

<!-- Bloco de Seleção de Moldura (Exibido apenas no modo MOLDURA) -->
<div id="bloco-selecao-moldura" style="display: <?= $config['tipo_design'] === 'MOLDURA' ? 'block' : 'none' ?>;">
    <label><b>Moldura / Fundo Gráfico:</b></label>
    <select name="imagem_fundo" style="padding: 10px; border-radius: 6px;">
        <option value="nenhuma" <?= $config['imagem_fundo'] === 'nenhuma' ? 'selected' : '' ?>>Sem Moldura</option>
        <option value="moldura_boho1.png" <?= $config['imagem_fundo'] === 'moldura_boho1.png' ? 'selected' : '' ?>>Boho Floral</option>
        <option value="moldura_geometrica1.png" <?= $config['imagem_fundo'] === 'moldura_geometrica1.png' ? 'selected' : '' ?>>Geométrico Ouro 1</option>
        <option value="moldura_geometrica2.png" <?= $config['imagem_fundo'] === 'moldura_geometrica2.png' ? 'selected' : '' ?>>Geométrico Ouro 2</option>
    </select>
</div>

<!-- ENVOLVA ESTA PARTE NA DIV -->
<div id="controles-moldura" style="margin-top: 15px;">
    <div style="margin-bottom: 12px;">
        <label style="display: block; margin-bottom: 4px;"><b>Tamanho da Moldura:</b> <span id="valor-escala"><?= $config['moldura_escala'] ?>%</span></label>
        <input type="range" name="moldura_escala" min="50" max="200" step="5" value="<?= $config['moldura_escala'] ?>" style="width: 100%;">
    </div>

    <div style="display: flex; gap: 15px; margin-bottom: 12px; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 140px;">
            <label style="display: block; margin-bottom: 4px;"><b>Posição Horiz.:</b> <span id="valor-pos-x"><?= $config['moldura_pos_x'] ?>px</span></label>
            <input type="range" name="moldura_pos_x" min="-150" max="150" step="5" value="<?= $config['moldura_pos_x'] ?>" style="width: 100%;">
        </div>
        <div style="flex: 1; min-width: 140px;">
            <label style="display: block; margin-bottom: 4px;"><b>Posição Vert.:</b> <span id="valor-pos-y"><?= $config['moldura_pos_y'] ?>px</span></label>
            <input type="range" name="moldura_pos_y" min="-150" max="150" step="5" value="<?= $config['moldura_pos_y'] ?>" style="width: 100%;">
        </div>
    </div>

    <div style="margin-bottom: 12px;">
        <label style="display: block; margin-bottom: 4px;"><b>Rotação da Moldura:</b> <span id="valor-rotacao"><?= $config['moldura_rotacao'] ?>°</span></label>
        <input type="range" name="moldura_rotacao" min="-180" max="180" step="1" value="<?= $config['moldura_rotacao'] ?>" style="width: 100%;">
    </div>
</div>

                        

                            <label style="display: flex; align-items: center; gap: 8px; margin-top: 10px; cursor: pointer;">
                                <input type="checkbox" name="exibir_qrcode" value="1" <?= $config['exibir_qrcode'] ? 'checked' : '' ?>>
                                <b>Exibir QR Code Visual no Convite</b>
                            </label>

                            <label style="margin-top: 10px;"><b>Instrução do Rodapé:</b></label>
                            <input type="text" name="mensagem_rodape" value="<?= htmlspecialchars($config['mensagem_rodape']) ?>" required>

                            <button type="submit" style="margin-top: 15px;">Salvar Personalização</button>
                        </form>
                    </div>

                    <!-- Pré-visualização em Tempo Real -->
                    <div style="flex: 1; min-width: 320px; position: sticky; top: 20px; height: fit-content;">
                        <h3 style="margin-bottom: 15px; font-size: 1.1rem;">Pré-visualização em Tempo Real</h3>
                        
<div id="preview-box" style="background: <?= $config['cor_fundo'] ?>; text-align: center; border-radius: 6px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); position: relative; z-index: 0; overflow: hidden;">

<div id="prev-moldura" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; z-index: -1; background-repeat: no-repeat; pointer-events: none;"></div>

<!-- Gizmo interativo: mover / redimensionar / rodar a moldura com o mouse -->
<div id="moldura-gizmo" style="position: absolute; border: 1.5px dashed rgba(37, 99, 235, 0.85); box-sizing: border-box; z-index: 5; display: none;">
    <div id="gizmo-mover" title="Arraste para mover" style="position: absolute; inset: 0; cursor: move;"></div>
    <div id="gizmo-redimensionar" title="Arraste para redimensionar" style="position: absolute; right: -7px; bottom: -7px; width: 14px; height: 14px; background: #2563eb; border: 2px solid #fff; border-radius: 50%; cursor: nwse-resize;"></div>
    <div style="position: absolute; left: 50%; top: -26px; width: 1.5px; height: 26px; background: rgba(37, 99, 235, 0.85); transform: translateX(-50%); pointer-events: none;"></div>
    <div id="gizmo-rotacionar" title="Arraste para rodar" style="position: absolute; left: 50%; top: -34px; width: 14px; height: 14px; background: #2563eb; border: 2px solid #fff; border-radius: 50%; cursor: grab; transform: translateX(-50%);"></div>
</div>

                            <div>
                                <p id="prev-subtitulo" class="text-suporte" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 2px; color: <?= $config['cor_texto'] ?>; margin-bottom: 5px;"><?= htmlspecialchars($config['subtitulo_evento']) ?></p>
                                <h2 id="prev-titulo" style="font-size: 1.8rem; margin: 0; border: none; padding-bottom: 0; color: <?= $config['cor_primaria'] ?>;"><?= htmlspecialchars($config['titulo_evento']) ?></h2>
                            </div>

                            <div class="divisor-ornamento" id="prev-divisor" style="color: <?= $config['cor_primaria'] ?>;">◆</div>

                            <div>
                                <p id="prev-label-convidado" class="text-suporte" style="font-size: 0.75rem; color: <?= $config['cor_texto'] ?>; margin: 0;">Convidado(a) Especial:</p>
                                <p id="prev-nome" style="font-size: 1.15rem; font-weight: bold; color: <?= $config['cor_primaria'] ?>; margin: 2px 0 10px 0;">Nome do Convidado Exemplo</p>
                            </div>

<div id="prev-info-box" class="text-suporte" style="background: transparent; padding: 8px; border-radius: 6px; font-size: 0.8rem; color: <?= $config['cor_texto'] ?>;">
                                <div><span id="prev-data">--/--/--</span> <span id="prev-hora"></span></div>
                                <div id="prev-local" style="font-weight: 600;"></div>
                                <div id="prev-traje" style="font-style: italic;"></div>
                            </div>

                            <div id="prev-qr-container" style="display: flex; align-items: center; justify-content: center; gap: 12px; margin: 8px 0;">
                                <img id="prev-qr-img" src="https://api.qrserver.com/v1/create-qr-code/?size=55x55&data=CONVIDADO-9999" alt="QR Code" style="width: 50px; height: 50px; border: 1px solid #ddd; padding: 2px; background: #fff;">
                                <div style="text-align: left;">
                                    <span id="prev-label-codigo" class="text-suporte" style="font-size: 0.65rem; color: <?= $config['cor_texto'] ?>; display: block;">Código de Acesso:</span>
                                    <div id="prev-codigo" style="font-size: 1.1rem; font-weight: bold; color: <?= $config['cor_codigo'] ?>;">CONVIDADO-9999</div>
                                </div>
                            </div>

                            <p id="prev-rodape" class="text-suporte" style="font-size: 0.7rem; color: <?= $config['cor_texto'] ?>; font-style: italic; margin: 0;"><?= htmlspecialchars($config['mensagem_rodape']) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

<!-- Passo 4: Script JavaScript de Sincronização -->
<script>
    // --- Seletores de Controles e Formulário ---
    const controlesMoldura = document.getElementById('controles-moldura');
    const inputTitulo    = document.querySelector('input[name="titulo_evento"]');
    const inputSubtitulo = document.querySelector('input[name="subtitulo_evento"]');
    const inputData      = document.querySelector('input[name="data_evento"]');
    const inputHora      = document.querySelector('input[name="hora_evento"]');
    const inputLocal     = document.querySelector('input[name="local_evento"]');
    const inputTraje     = document.querySelector('input[name="traje_evento"]');
    const inputCorPri    = document.querySelector('input[name="cor_primaria"]');
    const inputCorCod    = document.querySelector('input[name="cor_codigo"]');
    const inputCorFun    = document.querySelector('input[name="cor_fundo"]');
    const selectFonte    = document.querySelector('select[name="fonte_familia"]');
    const selectImg      = document.querySelector('select[name="imagem_fundo"]');
    const inputQR        = document.querySelector('input[name="exibir_qrcode"]');
    const inputRodape    = document.querySelector('input[name="mensagem_rodape"]');
    const inputEscala    = document.querySelector('input[name="moldura_escala"]');
    const inputPosX      = document.querySelector('input[name="moldura_pos_x"]');
    const inputPosY      = document.querySelector('input[name="moldura_pos_y"]');
    const inputRotacao   = document.querySelector('input[name="moldura_rotacao"]');
    const inputCorTexto  = document.querySelector('input[name="cor_texto"]');
    const selectFonteTexto = document.querySelector('select[name="fonte_texto"]');

    // --- Controle de Modo de Design e Upload ---
    const selectTipoDesign = document.getElementById('tipo_design') || document.querySelector('select[name="tipo_design"]');
    const inputUploadLayout = document.querySelector('input[name="upload_layout"]');
    const blocoUploadLayout = document.getElementById('bloco-upload-layout');
    const blocoSelecaoMoldura = document.getElementById('bloco-selecao-moldura');

    // --- Elementos de Preview ---
    const prevTitulo         = document.getElementById('prev-titulo');
    const prevSubtitulo      = document.getElementById('prev-subtitulo');
    const prevData           = document.getElementById('prev-data');
    const prevHora           = document.getElementById('prev-hora');
    const prevLocal          = document.getElementById('prev-local');
    const prevTraje          = document.getElementById('prev-traje');
    const prevNome           = document.getElementById('prev-nome');
    const prevCodigo         = document.getElementById('prev-codigo');
    const prevDivisor        = document.getElementById('prev-divisor');
    const prevQRImg          = document.getElementById('prev-qr-img');
    const prevRodape         = document.getElementById('prev-rodape');
    const prevBox            = document.getElementById('preview-box');
    const prevMoldura        = document.getElementById('prev-moldura');
    const prevLabelConvidado = document.getElementById('prev-label-convidado');
    const prevInfoBox        = document.getElementById('prev-info-box');
    const prevLabelCodigo    = document.getElementById('prev-label-codigo');
    
    // --- Controles de Manipulação Visual (Gizmo) ---
    const gizmo               = document.getElementById('moldura-gizmo');
    const gizmoMover          = document.getElementById('gizmo-mover');
    const gizmoRedimensionar  = document.getElementById('gizmo-redimensionar');
    const gizmoRotacionar     = document.getElementById('gizmo-rotacionar');

let layoutCustomDataUrl = <?php echo ($config['tipo_design'] === 'LAYOUT_COMPLETO' && !empty($config['imagem_fundo']) && $config['imagem_fundo'] !== 'nenhuma') ? json_encode('../img/molduras/' . $config['imagem_fundo']) : 'null'; ?>;

    // Função para pré-visualizar imagem selecionada localmente no Upload
    function previewUploadArquivo(input) {
        const file = input ? (input.files ? input.files[0] : null) : null;
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                layoutCustomDataUrl = e.target.result;
                atualizarPreview();
            };
            reader.readAsDataURL(file);
        }
    }

    function atualizarPreview() {
        const modo = selectTipoDesign ? selectTipoDesign.value : 'MOLDURA';

        // Alterna visibilidade dos blocos de controle no formulário
        if (blocoUploadLayout) blocoUploadLayout.style.display = (modo === 'LAYOUT_COMPLETO') ? 'block' : 'none';
        if (blocoSelecaoMoldura) blocoSelecaoMoldura.style.display = (modo === 'MOLDURA') ? 'block' : 'none';

        // Atualização de Textos Base
        if (prevTitulo) prevTitulo.textContent = inputTitulo ? inputTitulo.value : '';
        if (prevSubtitulo) prevSubtitulo.textContent = inputSubtitulo ? inputSubtitulo.value : '';
        if (prevTitulo && inputCorPri) prevTitulo.style.color = inputCorPri.value;
        if (prevNome && inputCorPri) prevNome.style.color = inputCorPri.value;
        if (prevDivisor && inputCorPri) prevDivisor.style.color = inputCorPri.value;
        if (prevCodigo && inputCorCod) prevCodigo.style.color = inputCorCod.value;
        if (prevBox && inputCorFun) prevBox.style.backgroundColor = inputCorFun.value;

        // Formatação da Data do Evento
        if (inputData && inputData.value) {
            const partes = inputData.value.split('-');
            if (prevData) prevData.textContent = `${partes[2]}/${partes[1]}/${partes[0].slice(-2)}`;
        } else if (prevData) {
            prevData.textContent = 'DATA DO EVENTO';
        }

        // Formatação da Hora do Evento
        if (inputHora && inputHora.value) {
            const horaApenas = inputHora.value.split(':')[0];
            if (prevHora) prevHora.textContent = ' - ' + parseInt(horaApenas, 10) + 'hrs';
        } else if (prevHora) {
            prevHora.textContent = '';
        }

        if (prevLocal) prevLocal.textContent = (inputLocal && inputLocal.value) ? 'Local: ' + inputLocal.value : '';
        if (prevTraje) prevTraje.textContent = (inputTraje && inputTraje.value) ? 'Traje: ' + inputTraje.value : '';

        // Mapeamento de Fontes Tipográficas
        const fontMap = { 
            'AlexBrush': "'Alex Brush', cursive",
            'PinyonScript': "'Pinyon Script', cursive",
            'Cinzel': "'Cinzel', serif",
            'CinzelDecorative': "'Cinzel Decorative', serif",
            'CormorantGaramond': "'Cormorant Garamond', serif",
            'PlayfairDisplay': "'Playfair Display', serif",
            'Merriweather': "'Merriweather', serif",
            'Montserrat': "'Montserrat', sans-serif",
            'Roboto': "'Roboto', sans-serif",
            'Inter': "'Inter', sans-serif"
        };
        
        const fonteTitulo = (selectFonte && fontMap[selectFonte.value]) ? fontMap[selectFonte.value] : "'Alex Brush', cursive";
        const fonteApoio  = (selectFonteTexto && fontMap[selectFonteTexto.value]) ? fontMap[selectFonteTexto.value] : "'Montserrat', sans-serif";

        if (prevTitulo) prevTitulo.style.fontFamily = fonteTitulo;
        if (prevNome) prevNome.style.fontFamily = fonteTitulo;

        [prevSubtitulo, prevLabelConvidado, prevInfoBox, prevData, prevHora, prevLocal, prevTraje, prevLabelCodigo, prevRodape]
            .forEach(el => { if (el) el.style.fontFamily = fonteApoio; });

        if (prevBox) prevBox.style.fontFamily = fonteApoio;

// Regras específicas de exibição por Modo de Design
        if (modo === 'LAYOUT_COMPLETO') {
            // Oculta os dados gerais do evento pois já fazem parte da arte do fundo
            [prevSubtitulo, prevTitulo, prevDivisor, prevInfoBox, prevRodape].forEach(el => {
                if (el) el.style.display = 'none';
            });

            const temImagem = layoutCustomDataUrl || (selectImg && selectImg.value && selectImg.value !== 'nenhuma');

            if (prevMoldura) {
                if (layoutCustomDataUrl) {
                    prevMoldura.style.backgroundImage = `url('${layoutCustomDataUrl}')`;
                } else if (selectImg && selectImg.value && selectImg.value !== 'nenhuma') {
                    prevMoldura.style.backgroundImage = `url('../img/molduras/${selectImg.value}')`;
                } else {
                    prevMoldura.style.backgroundImage = 'none';
                }

                if (temImagem) {
                    // Aplica as propriedades de escala, posição e rotação na foto de upload
                    prevMoldura.style.backgroundSize = `${inputEscala.value}% ${inputEscala.value}%`;
                    prevMoldura.style.backgroundPosition = `calc(50% + ${inputPosX.value}px) calc(50% + ${inputPosY.value}px)`;
                    prevMoldura.style.backgroundRepeat = 'no-repeat';
                    prevMoldura.style.transform = `rotate(${inputRotacao.value}deg)`;
                }
            }

            // Exibe os controles e o gizmo interativo caso haja uma imagem carregada
            if (temImagem) {
                if (gizmo) gizmo.style.display = 'block';
                if (controlesMoldura) controlesMoldura.style.display = 'block';
                sincronizarGizmo();
            } else {
                if (gizmo) gizmo.style.display = 'none';
                if (controlesMoldura) controlesMoldura.style.display = 'none';
            }

        } else {
            // Modo MOLDURA: Exibe todas as seções de texto do evento
            [prevSubtitulo, prevTitulo, prevDivisor, prevInfoBox, prevRodape].forEach(el => {
                if (el) el.style.display = 'block';
            });

            if (selectImg && selectImg.value && selectImg.value !== 'nenhuma') {
                if (prevMoldura) {
                    prevMoldura.style.backgroundImage = `url('../img/molduras/${selectImg.value}')`;
                    prevMoldura.style.backgroundSize = `${inputEscala.value}% ${inputEscala.value}%`;
                    prevMoldura.style.backgroundPosition = `calc(50% + ${inputPosX.value}px) calc(50% + ${inputPosY.value}px)`;
                    prevMoldura.style.backgroundRepeat = 'no-repeat';
                    prevMoldura.style.transform = `rotate(${inputRotacao.value}deg)`;
                }
                if (gizmo) gizmo.style.display = 'block';
                if (controlesMoldura) controlesMoldura.style.display = 'block';
            } else {
                if (prevMoldura) prevMoldura.style.backgroundImage = 'none';
                if (gizmo) gizmo.style.display = 'none';
                if (controlesMoldura) controlesMoldura.style.display = 'none';
            }

            sincronizarGizmo();
        }

        // Aplicação de Cores aos Textos de Apoio
        if (inputCorTexto) {
            const corTextoAtual = inputCorTexto.value;
            [prevSubtitulo, prevLabelConvidado, prevInfoBox, prevLabelCodigo, prevRodape].forEach(el => {
                if (el) el.style.color = corTextoAtual;
            });
        }

        // Atualização dos Rótulos dos Sliders
        const elEscala  = document.getElementById('valor-escala');
        const elPosX    = document.getElementById('valor-pos-x');
        const elPosY    = document.getElementById('valor-pos-y');
        const elRotacao = document.getElementById('valor-rotacao');

        if (elEscala && inputEscala)   elEscala.textContent  = inputEscala.value + '%';
        if (elPosX && inputPosX)     elPosX.textContent    = inputPosX.value + 'px';
        if (elPosY && inputPosY)     elPosY.textContent    = inputPosY.value + 'px';
        if (elRotacao && inputRotacao) elRotacao.textContent = inputRotacao.value + '°';

        if (prevQRImg && inputQR)      prevQRImg.style.display = inputQR.checked ? 'block' : 'none';
        if (prevRodape && inputRodape) prevRodape.textContent = inputRodape.value;
    }

    function sincronizarGizmo() {
        if (!prevBox || !inputEscala || !inputPosX || !inputPosY || !inputRotacao || !gizmo) return;
        const boxW = prevBox.clientWidth;
        const boxH = prevBox.clientHeight;
        const escala = parseFloat(inputEscala.value) / 100;
        const w = boxW * escala;
        const h = boxH * escala;
        const x = (boxW - w) / 2 + parseFloat(inputPosX.value);
        const y = (boxH - h) / 2 + parseFloat(inputPosY.value);

        gizmo.style.width  = w + 'px';
        gizmo.style.height = h + 'px';
        gizmo.style.left   = x + 'px';
        gizmo.style.top    = y + 'px';
        gizmo.style.transform = `rotate(${inputRotacao.value}deg)`;
    }

    function aplicarTemplate(tipo, corPri, corCod, corFun, fonte, fonteTexto, img) {
        if (inputCorPri) inputCorPri.value = corPri;
        if (inputCorCod) inputCorCod.value = corCod;
        if (inputCorFun) inputCorFun.value = corFun;
        if (selectFonte) selectFonte.value = fonte;
        if (selectFonteTexto) selectFonteTexto.value = fonteTexto;
        if (selectImg) selectImg.value = img;
        atualizarPreview();
    }

    // --- Mover, Redimensionar e Rodar a Moldura Arrastando o Mouse ---
    let arrastando = null; // 'mover' | 'redimensionar' | 'rotacionar'
    let inicio = {};

    function centroPreviewBox() {
        if (!prevBox) return { x: 0, y: 0 };
        const rect = prevBox.getBoundingClientRect();
        return { x: rect.left + rect.width / 2, y: rect.top + rect.height / 2 };
    }

    if (gizmoMover) {
        gizmoMover.addEventListener('mousedown', (e) => {
            e.preventDefault();
            arrastando = 'mover';
            inicio = {
                mouseX: e.clientX,
                mouseY: e.clientY,
                posX: parseFloat(inputPosX.value),
                posY: parseFloat(inputPosY.value)
            };
        });
    }

    if (gizmoRedimensionar) {
        gizmoRedimensionar.addEventListener('mousedown', (e) => {
            e.preventDefault();
            e.stopPropagation();
            arrastando = 'redimensionar';
            const centro = centroPreviewBox();
            const dx = e.clientX - centro.x;
            const dy = e.clientY - centro.y;
            inicio = {
                distancia: Math.sqrt(dx * dx + dy * dy) || 1,
                escala: parseFloat(inputEscala.value)
            };
        });
    }

    if (gizmoRotacionar) {
        gizmoRotacionar.addEventListener('mousedown', (e) => {
            e.preventDefault();
            e.stopPropagation();
            arrastando = 'rotacionar';
            const centro = centroPreviewBox();
            inicio = {
                angulo: Math.atan2(e.clientY - centro.y, e.clientX - centro.x) * (180 / Math.PI),
                rotacao: parseFloat(inputRotacao.value)
            };
        });
    }

    document.addEventListener('mousemove', (e) => {
        if (!arrastando) return;

        if (arrastando === 'mover' && inputPosX && inputPosY) {
            const deltaX = e.clientX - inicio.mouseX;
            const deltaY = e.clientY - inicio.mouseY;
            inputPosX.value = Math.max(-150, Math.min(150, Math.round(inicio.posX + deltaX)));
            inputPosY.value = Math.max(-150, Math.min(150, Math.round(inicio.posY + deltaY)));
        }

        if (arrastando === 'redimensionar' && inputEscala) {
            const centro = centroPreviewBox();
            const dx = e.clientX - centro.x;
            const dy = e.clientY - centro.y;
            const distanciaAtual = Math.sqrt(dx * dx + dy * dy);
            const novaEscala = Math.round(inicio.escala * (distanciaAtual / inicio.distancia));
            inputEscala.value = Math.max(50, Math.min(200, novaEscala));
        }

        if (arrastando === 'rotacionar' && inputRotacao) {
            const centro = centroPreviewBox();
            const anguloAtual = Math.atan2(e.clientY - centro.y, e.clientX - centro.x) * (180 / Math.PI);
            const novaRotacao = Math.round(inicio.rotacao + (anguloAtual - inicio.angulo));
            inputRotacao.value = Math.max(-180, Math.min(180, novaRotacao));
        }

        atualizarPreview();
    });

    document.addEventListener('mouseup', () => {
        arrastando = null;
    });

window.addEventListener('resize', () => {
    sincronizarGizmo();
});

    // Registra os Listeners de Input e Change
    const inputs = [
        inputTitulo, inputSubtitulo, inputData, inputHora, inputLocal, inputTraje,
        inputCorPri, inputCorCod, inputCorFun, selectFonte, selectFonteTexto, selectImg,
        inputQR, inputRodape, inputEscala, inputPosX, inputPosY, inputRotacao, inputCorTexto,
        selectTipoDesign, inputUploadLayout
    ];
        
    inputs.forEach(el => {
        if (el) {
            el.addEventListener('input', atualizarPreview);
            el.addEventListener('change', (e) => {
                if (el === inputUploadLayout) {
                    previewUploadArquivo(el);
                } else {
                    atualizarPreview();
                }
            });
        }
    });

    // Execução Inicial
    atualizarPreview();
</script>
</body>
</html>