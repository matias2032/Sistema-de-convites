<?php
require_once '../services/conexao.php';
ob_start();
include_once '../widgets/sidebar.php';
$sidebar_html = ob_get_clean();
checarSessao();

$db = (new Conexao())->getConexao();
$msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
    $borda           = 'nenhuma';
    $img_fundo       = $_POST['imagem_fundo'] ?? 'nenhuma';
    $rodape          = trim($_POST['mensagem_rodape']);
    $exibir_qrcode   = isset($_POST['exibir_qrcode']) ? 1 : 0;

    $stmt = $db->prepare("INSERT INTO configuracao_convite (
            id, titulo_evento, subtitulo_evento, data_evento, hora_evento, local_evento, traje_evento,
            cor_primaria, cor_codigo, cor_fundo, fonte_familia, estilo_borda, 
            imagem_fundo, mensagem_rodape, exibir_qrcode
        ) VALUES (
            1, :titulo, :subtitulo, :data_e, :hora_e, :local_e, :traje,
            :cor_p, :cor_c, :cor_f, :fonte, :borda, 
            :img_f, :rodape, :qr
        ) ON DUPLICATE KEY UPDATE 
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
            estilo_borda     = VALUES(estilo_borda),
            imagem_fundo     = VALUES(imagem_fundo),
            mensagem_rodape  = VALUES(mensagem_rodape),
            exibir_qrcode    = VALUES(exibir_qrcode)");

    $stmt->execute([
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
        ':borda'     => $borda,
        ':img_f'     => $img_fundo,
        ':rodape'    => $rodape,
        ':qr'        => $exibir_qrcode
    ]);

    $msg = "Configurações atualizadas com sucesso! Redirecionando...";
    header("Refresh: 2; url=dashboard.php");
}

$stmt = $db->query("SELECT * FROM configuracao_convite WHERE id = 1");
$configData = $stmt->fetch(PDO::FETCH_ASSOC);

$config = [
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
    'imagem_fundo'     => $configData['imagem_fundo']     ?? 'nenhuma',
    'mensagem_rodape'  => $configData['mensagem_rodape']  ?? 'Apresente este convite na entrada.',
    'exibir_qrcode'    => $configData['exibir_qrcode']    ?? 1
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
                        <button type="button" onclick="aplicarTemplate('Casamento', '#991b1b', '#854d0e', '#fefce8', 'AlexBrush', 'moldura_boho1.png')" style="background: #fefce8; color: #991b1b; border: 1px solid #fef08a; padding: 8px 12px; border-radius: 6px; cursor: pointer; font-weight: bold;">Casamento Boho</button>
                        <button type="button" onclick="aplicarTemplate('Corporativo', '#1e3a8a', '#1d4ed8', '#ffffff', 'Cinzel', 'nenhuma')" style="background: #ffffff; color: #1e3a8a; border: 1px solid #cbd5e1; padding: 8px 12px; border-radius: 6px; cursor: pointer; font-weight: bold;">Corporativo Luxo</button>
                        <button type="button" onclick="aplicarTemplate('Gala', '#4a154b', '#d97706', '#faf5ff', 'PinyonScript', 'nenhuma')" style="background: #faf5ff; color: #4a154b; border: 1px solid #e9d5ff; padding: 8px 12px; border-radius: 6px; cursor: pointer; font-weight: bold;">Monarquia Clássica</button>
                    </div>
                </div>

                <div style="display: flex; gap: 40px; flex-wrap: wrap; margin-top: 20px;">
                    <div style="flex: 1; min-width: 320px;">
                        <form method="POST" style="max-width: 100%;">
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
                                </optgroup>
                            </select>

                            <label><b>Moldura / Fundo Gráfico:</b></label>
                            <select name="imagem_fundo" style="padding: 10px; border-radius: 6px;">
                                <option value="nenhuma" <?= $config['imagem_fundo'] === 'nenhuma' ? 'selected' : '' ?>>Sem Moldura (Apenas Cor)</option>
                                <option value="moldura_boho1.png" <?= $config['imagem_fundo'] === 'moldura_boho1.png' ? 'selected' : '' ?>>Boho Floral</option>
                                <option value="moldura_geometrica1.png" <?= $config['imagem_fundo'] === 'moldura_geometrica1.png' ? 'selected' : '' ?>>Geométrico Ouro 1</option>
                                <option value="moldura_geometrica2.png" <?= $config['imagem_fundo'] === 'moldura_geometrica2.png' ? 'selected' : '' ?>>Geométrico Ouro 2</option>
                            </select>

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
                        
                        <div id="preview-box" style="background: <?= $config['cor_fundo'] ?>; text-align: center; border-radius: 6px; box-shadow: 0 4px 12px rgba(0,0,0,0.08);">
                            
                            <div>
                                <p id="prev-subtitulo" class="text-suporte" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 2px; color: #555; margin-bottom: 5px;"><?= htmlspecialchars($config['subtitulo_evento']) ?></p>
                                <h2 id="prev-titulo" style="font-size: 1.8rem; margin: 0; border: none; padding-bottom: 0; color: <?= $config['cor_primaria'] ?>;"><?= htmlspecialchars($config['titulo_evento']) ?></h2>
                            </div>

                            <div class="divisor-ornamento" id="prev-divisor" style="color: <?= $config['cor_primaria'] ?>;">◆</div>

                            <div>
                                <p class="text-suporte" style="font-size: 0.75rem; color: #666; margin: 0;">Convidado(a) Especial:</p>
                                <p id="prev-nome" style="font-size: 1.15rem; font-weight: bold; color: <?= $config['cor_primaria'] ?>; margin: 2px 0 10px 0;">Nome do Convidado Exemplo</p>
                            </div>

<div class="text-suporte" style="background: transparent; padding: 8px; border-radius: 6px; font-size: 0.8rem; color: #333;">
                                <div><span id="prev-data">--/--/--</span> <span id="prev-hora"></span></div>
                                <div id="prev-local" style="font-weight: 600;"></div>
                                <div id="prev-traje" style="font-style: italic; color: #555;"></div>
                            </div>

                            <div id="prev-qr-container" style="display: flex; align-items: center; justify-content: center; gap: 12px; margin: 8px 0;">
                                <img id="prev-qr-img" src="https://api.qrserver.com/v1/create-qr-code/?size=55x55&data=CONVIDADO-9999" alt="QR Code" style="width: 50px; height: 50px; border: 1px solid #ddd; padding: 2px; background: #fff;">
                                <div style="text-align: left;">
                                    <span class="text-suporte" style="font-size: 0.65rem; color: #666; display: block;">Código de Acesso:</span>
                                    <div id="prev-codigo" style="font-size: 1.1rem; font-weight: bold; color: <?= $config['cor_codigo'] ?>;">CONVIDADO-9999</div>
                                </div>
                            </div>

                            <p id="prev-rodape" class="text-suporte" style="font-size: 0.7rem; color: #555; font-style: italic; margin: 0;"><?= htmlspecialchars($config['mensagem_rodape']) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

<!-- Passo 4: Script JavaScript de Sincronização -->
<script>
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

    const prevTitulo    = document.getElementById('prev-titulo');
    const prevSubtitulo = document.getElementById('prev-subtitulo');
    const prevData      = document.getElementById('prev-data');
    const prevHora      = document.getElementById('prev-hora');
    const prevLocal     = document.getElementById('prev-local');
    const prevTraje     = document.getElementById('prev-traje');
    const prevNome      = document.getElementById('prev-nome');
    const prevCodigo    = document.getElementById('prev-codigo');
    const prevDivisor   = document.getElementById('prev-divisor');
    const prevQRImg     = document.getElementById('prev-qr-img');
    const prevRodape    = document.getElementById('prev-rodape');
    const prevBox       = document.getElementById('preview-box');

    function atualizarPreview() {
        prevTitulo.textContent    = inputTitulo.value || '';
        prevSubtitulo.textContent = inputSubtitulo.value || '';
        prevTitulo.style.color    = inputCorPri.value;
        prevNome.style.color      = inputCorPri.value;
        prevDivisor.style.color   = inputCorPri.value;
        prevCodigo.style.color    = inputCorCod.value;
        prevBox.style.backgroundColor = inputCorFun.value;

        if (inputData.value) {
            const partes = inputData.value.split('-');
            prevData.textContent = `${partes[2]}/${partes[1]}/${partes[0].slice(-2)}`;
        } else {
            prevData.textContent = 'DATA DO EVENTO';
        }

        if (inputHora.value) {
            const horaApenas = inputHora.value.split(':')[0];
            prevHora.textContent = ' - ' + parseInt(horaApenas, 10) + 'hrs';
        } else {
            prevHora.textContent = '';
        }

        prevLocal.textContent = inputLocal.value ? 'Local: ' + inputLocal.value : '';
        prevTraje.textContent = inputTraje.value ? 'Traje: ' + inputTraje.value : '';

        const fontMap = { 
            'AlexBrush': "'Alex Brush', cursive",
            'PinyonScript': "'Pinyon Script', cursive",
            'Cinzel': "'Cinzel', serif",
            'CinzelDecorative': "'Cinzel Decorative', serif",
            'CormorantGaramond': "'Cormorant Garamond', serif",
            'PlayfairDisplay': "'Playfair Display', serif"
        };
        
        prevTitulo.style.fontFamily = fontMap[selectFonte.value] || "'Alex Brush', cursive";

        if (selectImg.value && selectImg.value !== 'nenhuma') {
            prevBox.style.backgroundImage = `url('../img/molduras/${selectImg.value}')`;
            prevBox.style.backgroundSize = '100% 100%';
            prevBox.style.backgroundRepeat = 'no-repeat';
        } else {
            prevBox.style.backgroundImage = 'none';
        }

        prevQRImg.style.display = inputQR.checked ? 'block' : 'none';
        prevRodape.textContent = inputRodape.value;
    }

    function aplicarTemplate(tipo, corPri, corCod, corFun, fonte, img) {
        inputCorPri.value = corPri;
        inputCorCod.value = corCod;
        inputCorFun.value = corFun;
        selectFonte.value = fonte;
        if(selectImg) selectImg.value = img;
        atualizarPreview();
    }

    const inputs = [inputTitulo, inputSubtitulo, inputData, inputHora, inputLocal, inputTraje, inputCorPri, inputCorCod, inputCorFun, selectFonte, selectImg, inputQR, inputRodape];
    inputs.forEach(el => {
        if(el) {
            el.addEventListener('input', atualizarPreview);
            el.addEventListener('change', atualizarPreview);
        }
    });

    atualizarPreview();
</script>
</body>
</html>