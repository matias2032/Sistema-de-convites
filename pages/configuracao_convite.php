<?php
require_once '../services/conexao.php';
ob_start();
include_once '../widgets/sidebar.php';
$sidebar_html = ob_get_clean();
checarSessao();

$db = (new Conexao())->getConexao();
$msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo       = trim($_POST['titulo_evento']);
    $data_evento  = $_POST['data_evento'] ?? null;
    $hora_evento  = $_POST['hora_evento'] ?? null;
    $local_evento = trim($_POST['local_evento'] ?? '');
    $cor_primaria = $_POST['cor_primaria'];
    $cor_codigo   = $_POST['cor_codigo'];
    $cor_fundo    = $_POST['cor_fundo'];
    $fonte        = $_POST['fonte_familia'];
    $borda        = 'nenhuma';
    $img_fundo    = $_POST['imagem_fundo'] ?? 'nenhuma';
    $rodape       = trim($_POST['mensagem_rodape']);

    // Insere com id=1 se estiver vazio, ou atualiza se já existir
    $stmt = $db->prepare("INSERT INTO configuracao_convite (
            id, titulo_evento, data_evento, hora_evento, local_evento, 
            cor_primaria, cor_codigo, cor_fundo, fonte_familia, estilo_borda, 
            imagem_fundo, mensagem_rodape
        ) VALUES (
            1, :titulo, :data_e, :hora_e, :local_e, 
            :cor_p, :cor_c, :cor_f, :fonte, :borda, 
            :img_f, :rodape
        ) ON DUPLICATE KEY UPDATE 
            titulo_evento   = VALUES(titulo_evento),
            data_evento     = VALUES(data_evento),
            hora_evento     = VALUES(hora_evento),
            local_evento    = VALUES(local_evento),
            cor_primaria    = VALUES(cor_primaria),
            cor_codigo      = VALUES(cor_codigo),
            cor_fundo       = VALUES(cor_fundo),
            fonte_familia   = VALUES(fonte_familia),
            estilo_borda    = VALUES(estilo_borda),
            imagem_fundo    = VALUES(imagem_fundo),
            mensagem_rodape = VALUES(mensagem_rodape)");

    $stmt->execute([
        ':titulo'  => $titulo,
        ':data_e'  => $data_evento,
        ':hora_e'  => $hora_evento,
        ':local_e' => $local_evento,
        ':cor_p'   => $cor_primaria,
        ':cor_c'   => $cor_codigo,
        ':cor_f'   => $cor_fundo,
        ':fonte'   => $fonte,
        ':borda'   => $borda,
        ':img_f'   => $img_fundo,
        ':rodape'  => $rodape
    ]);

    $msg = "Configurações atualizadas com sucesso! Redirecionando á dashboard...";
    
    // Aguarda 3 segundos e redireciona para a dashboard
    header("Refresh: 3; url=dashboard.php");
}

// Busca a configuração existente
$stmt = $db->query("SELECT * FROM configuracao_convite WHERE id = 1");
$configData = $stmt->fetch(PDO::FETCH_ASSOC);

// Define valores padrão de fallback caso a tabela esteja vazia (ex: pós TRUNCATE)
$config = [
    'titulo_evento'   => $configData['titulo_evento']   ?? 'CONVITE ESPECIAL',
    'data_evento'     => $configData['data_evento']     ?? '',
    'hora_evento'     => $configData['hora_evento']     ?? '',
    'local_evento'    => $configData['local_evento']    ?? '',
    'cor_primaria'    => $configData['cor_primaria']    ?? '#2563eb',
    'cor_codigo'      => $configData['cor_codigo']      ?? '#000000',
    'cor_fundo'       => $configData['cor_fundo']       ?? '#ffffff',
    'fonte_familia'   => $configData['fonte_familia']   ?? 'Arial',
    'imagem_fundo'    => $configData['imagem_fundo']    ?? 'nenhuma',
    'mensagem_rodape' => $configData['mensagem_rodape'] ?? 'Apresente este convite na entrada.'
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

                <!-- Seção de Templates Prontos -->
                <div style="margin-top: 15px; padding: 15px; border-radius: 8px; border: none;">
                    <h4 style="margin-bottom: 10px;"> Ajustes Rápidos:</h4>
                    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                        <button type="button" onclick="aplicarTemplate('#991b1b', '#854d0e', '#fefce8', 'AlexBrush', 'moldura_boho1.png')" style="background: #fefce8; color: #991b1b; border: 1px solid #fef08a; padding: 8px 12px; border-radius: 6px; cursor: pointer; font-weight: bold;"> Casamento Boho</button>
                        <button type="button" onclick="aplicarTemplate('#1e3a8a', '#1d4ed8', '#ffffff', 'Cinzel', 'nenhuma')" style="background: #ffffff; color: #1e3a8a; border: 1px solid #cbd5e1; padding: 8px 12px; border-radius: 6px; cursor: pointer; font-weight: bold;"> Corporativo Luxo</button>
                        <button type="button" onclick="aplicarTemplate('#4a154b', '#d97706', '#faf5ff', 'PinyonScript', 'nenhuma')" style="background: #faf5ff; color: #4a154b; border: 1px solid #e9d5ff; padding: 8px 12px; border-radius: 6px; cursor: pointer; font-weight: bold;"> Monarquia Clássica</button>
                    </div>
                </div>

                <div style="display: flex; gap: 40px; flex-wrap: wrap; margin-top: 20px;">
                    <div style="flex: 1; min-width: 320px;">
                        <form method="POST" style="max-width: 100%;">
                            <label><b>Título do Evento:</b></label>
                            <input type="text" name="titulo_evento" value="<?= htmlspecialchars($config['titulo_evento']) ?>" required>

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

                            <label><b>Local do Evento:</b></label>
                            <input type="text" name="local_evento" value="<?= htmlspecialchars($config['local_evento']) ?>" placeholder="Ex: Salão de Festas Imperial">

                            <label><b>Cor dos Títulos / Destaques:</b></label>
                            <input type="color" name="cor_primaria" value="<?= $config['cor_primaria'] ?>">

                            <label><b>Cor do Código de Acesso:</b></label>
                            <input type="color" name="cor_codigo" value="<?= $config['cor_codigo'] ?>">

                            <label><b>Cor de Fundo do Convite:</b></label>
                            <input type="color" name="cor_fundo" value="<?= $config['cor_fundo'] ?>">

                            <label><b>Fonte da Letra:</b></label>
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

                                <optgroup label="Sans-Serif / Modernas">
                                    <option value="Montserrat" <?= $config['fonte_familia'] === 'Montserrat' ? 'selected' : '' ?>>Montserrat</option>
                                    <option value="Roboto" <?= $config['fonte_familia'] === 'Roboto' ? 'selected' : '' ?>>Roboto</option>
                                    <option value="Inter" <?= $config['fonte_familia'] === 'Inter' ? 'selected' : '' ?>>Inter</option>
                                    <option value="Arial" <?= $config['fonte_familia'] === 'Arial' ? 'selected' : '' ?>>Arial (Nativa)</option>
                                </optgroup>
                            </select>

                            <label><b>Moldura / Fundo Gráfico:</b></label>
                            <select name="imagem_fundo" style="padding: 10px; border-radius: 6px;">
                                <option value="nenhuma" <?= $config['imagem_fundo'] === 'nenhuma' ? 'selected' : '' ?>>Sem Moldura (Apenas Cor)</option>
                                <option value="moldura_boho1.png" <?= $config['imagem_fundo'] === 'moldura_boho1.png' ? 'selected' : '' ?>>Boho 1</option>
                                <option value="moldura_boho2.png" <?= $config['imagem_fundo'] === 'moldura_boho2.png' ? 'selected' : '' ?>>Boho 2</option>
                                <option value="moldura_boho3.png" <?= $config['imagem_fundo'] === 'moldura_boho3.png' ? 'selected' : '' ?>>Boho 3</option>
                                <option value="moldura_boho4.png" <?= $config['imagem_fundo'] === 'moldura_boho4.png' ? 'selected' : '' ?>>Boho 4</option>
                                <option value="moldura_boho5.png" <?= $config['imagem_fundo'] === 'moldura_boho5.png' ? 'selected' : '' ?>>Boho 5</option>
                                <option value="moldura_boho7.png" <?= $config['imagem_fundo'] === 'moldura_boho7.png' ? 'selected' : '' ?>>Boho 7</option>
                                <option value="moldura_geometrica1.png" <?= $config['imagem_fundo'] === 'moldura_geometrica1.png' ? 'selected' : '' ?>>Geométrico Ouro 1</option>
                                <option value="moldura_geometrica2.png" <?= $config['imagem_fundo'] === 'moldura_geometrica2.png' ? 'selected' : '' ?>>Geométrico Ouro 2</option>
                            </select>

                            <label><b>Instrução do Rodapé:</b></label>
                            <input type="text" name="mensagem_rodape" value="<?= htmlspecialchars($config['mensagem_rodape']) ?>" required>

                            <button type="submit" style="margin-top: 15px;">Salvar Personalização</button>
                        </form>
                    </div>

                    <div style="flex: 1; min-width: 320px; position: sticky; top: 20px; height: fit-content;">
                        <h3 style="margin-bottom: 15px; font-size: 1.1rem;"> Pré-visualização em Tempo Real</h3>
                        
                        <div id="preview-box" style="background: <?= $config['cor_fundo'] ?>; padding: 30px 20px; text-align: center; border-radius: 4px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border: none; transition: all 0.2s;">
                            <h2 id="prev-titulo" style="font-size: 1.5rem; margin-bottom: 15px; color: <?= $config['cor_primaria'] ?>;"><?= htmlspecialchars($config['titulo_evento']) ?></h2>
                            
                            <div id="prev-bloco-data" style="padding: 5px 0; margin-bottom: 15px; font-weight: bold; color: #555; font-size: 0.9rem;">
                                <span id="prev-data">--/--/--</span> <span id="prev-hora"></span>
                            </div>

                            <p id="prev-local" style="font-size: 0.85rem; color: #000000; font-style: italic; margin-bottom: 15px;"></p>

                            <p style="font-size: 0.9rem; color: #0000; margin-bottom: 3px;">Convidado(a):</p>
                            <p id="prev-nome" style="font-size: 1.1rem; font-weight: bold; color: <?= $config['cor_primaria'] ?>; margin-bottom: 20px;">Nome do Convidado Exemplo</p>
                            
                            <p style="font-size: 0.9rem; color: #000000; margin-bottom: 3px;">Código Único de Acesso:</p>
                            <div id="prev-codigo" style="font-size: 1.4rem; font-weight: bold; color: <?= $config['cor_codigo'] ?>; border: none; padding: 8px; display: inline-block; min-width: 170px; margin-bottom: 20px; border-radius: 4px;">CONVIDADO-9999</div>
                            
                            <p id="prev-rodape" style="font-size: 0.8rem; color: #000000; font-style: italic;"><?= htmlspecialchars($config['mensagem_rodape']) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

<script>
    const inputTitulo = document.querySelector('input[name="titulo_evento"]');
    const inputData   = document.querySelector('input[name="data_evento"]');
    const inputHora   = document.querySelector('input[name="hora_evento"]');
    const inputLocal  = document.querySelector('input[name="local_evento"]');
    const inputCorPri = document.querySelector('input[name="cor_primaria"]');
    const inputCorCod = document.querySelector('input[name="cor_codigo"]');
    const inputCorFun = document.querySelector('input[name="cor_fundo"]');
    const selectFonte = document.querySelector('select[name="fonte_familia"]');
    const selectImg   = document.querySelector('select[name="imagem_fundo"]');
    const inputRodape = document.querySelector('input[name="mensagem_rodape"]');

    const prevTitulo = document.getElementById('prev-titulo');
    const prevData   = document.getElementById('prev-data');
    const prevHora   = document.getElementById('prev-hora');
    const prevLocal  = document.getElementById('prev-local');
    const prevNome   = document.getElementById('prev-nome');
    const prevCodigo = document.getElementById('prev-codigo');
    const prevRodape = document.getElementById('prev-rodape');
    const prevBox    = document.getElementById('preview-box');

    function atualizarPreview() {
        prevTitulo.textContent = inputTitulo.value || ' ';
        prevTitulo.style.color = inputCorPri.value;
        prevNome.style.color   = inputCorPri.value;
        prevCodigo.style.color = inputCorCod.value;
        prevBox.style.backgroundColor = inputCorFun.value;

        // Formatação da Data (dd/mm/aa)
        if (inputData.value) {
            const partes = inputData.value.split('-'); // YYYY-MM-DD
            const anoCurto = partes[0].slice(-2);
            prevData.textContent = `${partes[2]}/${partes[1]}/${anoCurto}`;
        } else {
            prevData.textContent = 'DATA DO EVENTO';
        }

        // Formatação do Horário (13hrs)
        if (inputHora.value) {
            const horaApenas = inputHora.value.split(':')[0];
            prevHora.textContent = ' - ' + parseInt(horaApenas, 10) + 'hrs';
        } else {
            prevHora.textContent = '';
        }

        prevLocal.textContent = inputLocal.value ? 'Local: ' + inputLocal.value : '';

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
            'Inter': "'Inter', sans-serif",
            'Arial': "Arial, sans-serif"
        };
        
        prevBox.style.fontFamily = fontMap[selectFonte.value] || 'Arial, sans-serif';

        if (selectImg.value && selectImg.value !== 'nenhuma') {
            prevBox.style.backgroundImage = `url('../img/molduras/${selectImg.value}')`;
            prevBox.style.backgroundSize = '100% 100%';
            prevBox.style.backgroundRepeat = 'no-repeat';
        } else {
            prevBox.style.backgroundImage = 'none';
        }

        prevRodape.textContent = inputRodape.value;
    }

    function aplicarTemplate(corPri, corCod, corFun, fonte, img) {
        inputCorPri.value = corPri;
        inputCorCod.value = corCod;
        inputCorFun.value = corFun;
        selectFonte.value = fonte;
        if(selectImg) selectImg.value = img;
        atualizarPreview();
    }

    inputTitulo.addEventListener('input', atualizarPreview);
    if(inputData) inputData.addEventListener('change', atualizarPreview);
    if(inputHora) inputHora.addEventListener('change', atualizarPreview);
    if(inputLocal) inputLocal.addEventListener('input', atualizarPreview);
    inputCorPri.addEventListener('input', atualizarPreview);
    inputCorCod.addEventListener('input', atualizarPreview);
    inputCorFun.addEventListener('input', atualizarPreview);
    selectFonte.addEventListener('change', atualizarPreview);
    if(selectImg) selectImg.addEventListener('change', atualizarPreview);
    inputRodape.addEventListener('input', atualizarPreview);

    atualizarPreview();
</script>
</body>
</html>