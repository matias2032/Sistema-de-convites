<?php
require_once 'conexao.php';
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
    $borda        = 'nenhuma'; // Forçado sem borda
    $img_fundo    = $_POST['imagem_fundo'] ?? 'nenhuma';
    $rodape       = trim($_POST['mensagem_rodape']);

    $stmt = $db->prepare("UPDATE configuracao_convite SET 
        titulo_evento = :titulo, 
        data_evento   = :data_e,
        hora_evento   = :hora_e,
        local_evento  = :local_e,
        cor_primaria  = :cor_p, 
        cor_codigo    = :cor_c, 
        cor_fundo     = :cor_f,
        fonte_familia = :fonte, 
        estilo_borda  = :borda, 
        imagem_fundo  = :img_f,
        mensagem_rodape = :rodape 
        WHERE id = 1");

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

    $msg = "Configurações do convite atualizadas com sucesso!";
}

$stmt = $db->query("SELECT * FROM configuracao_convite WHERE id = 1");
$config = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Personalizar Convite</title>
    <link rel="stylesheet" href="css/estilo.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Alex+Brush&family=Cinzel+Decorative:wght@400;700;900&family=Cinzel:wght@400..900&family=Cormorant+Garamond:ital,wght@0,300..700;1,300..700&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Merriweather:ital,opsz,wght@0,18..144,300..900;1,18..144,300..900&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Pinyon+Script&family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
</head>
<body>
<div class="container" style="max-width: 1100px;">
    <a href="dashboard.php">← Voltar ao Dashboard</a>
    <h2>🎨 Personalizar Design do Convite</h2>
    
    <?php if($msg) echo "<div class='msg-sucesso'>$msg</div>"; ?>

    <!-- Seção de Templates Prontos -->
    <div style="margin-top: 15px; background: #fff; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0;">
        <h4 style="margin-bottom: 10px; color: #334155;">✨ Presets Rápidos:</h4>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <button type="button" onclick="aplicarTemplate('#991b1b', '#854d0e', '#fefce8', 'GreatVibes', 'moldura_boho.png')" style="background: #fefce8; color: #991b1b; border: 1px solid #fef08a; padding: 8px 12px; border-radius: 6px; cursor: pointer; font-weight: bold;">📜 Casamento Boho</button>
            <button type="button" onclick="aplicarTemplate('#38bdf8', '#fb7185', '#0f172a', 'Montserrat', 'moldura_geometrica.png')" style="background: #0f172a; color: #38bdf8; border: 1px solid #1e293b; padding: 8px 12px; border-radius: 6px; cursor: pointer; font-weight: bold;">🌙 Moderno Dark</button>
            <button type="button" onclick="aplicarTemplate('#1e3a8a', '#1d4ed8', '#ffffff', 'Cinzel', 'nenhuma')" style="background: #ffffff; color: #1e3a8a; border: 1px solid #cbd5e1; padding: 8px 12px; border-radius: 6px; cursor: pointer; font-weight: bold;">💼 Corporativo Luxo</button>
            <button type="button" onclick="aplicarTemplate('#4a154b', '#d97706', '#faf5ff', 'MonsieurLaDoulaise', 'nenhuma')" style="background: #faf5ff; color: #4a154b; border: 1px solid #e9d5ff; padding: 8px 12px; border-radius: 6px; cursor: pointer; font-weight: bold;">👑 Monarquia Clássica</button>
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
                <input type="text" name="local_evento" value="<?= htmlspecialchars($config['local_evento'] ?? '') ?>" placeholder="Ex: Salão de Festas Imperial">

                <label><b>Cor dos Títulos / Destaques:</b></label>
                <input type="color" name="cor_primaria" value="<?= $config['cor_primaria'] ?>">

                <label><b>Cor do Código de Acesso:</b></label>
                <input type="color" name="cor_codigo" value="<?= $config['cor_codigo'] ?>">

                <label><b>Cor de Fundo do Convite:</b></label>
                <input type="color" name="cor_fundo" value="<?= $config['cor_fundo'] ?? '#ffffff' ?>">

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
                    <option value="nenhuma">Sem Moldura (Apenas Cor)</option>
                    <option value="moldura_boho1.png">Boho 1</option>
                    <option value="moldura_boho2.png">Boho 2</option>
                    <option value="moldura_boho3.png">Boho 3</option>
                    <option value="moldura_boho4.png">Boho 4</option>
                    <option value="moldura_boho5.png">Boho 5</option>
                    <!-- <option value="moldura_boho6.png">Boho 6</option>
                    <option value="moldura_boho7.png">Boho 7</option> -->
                    <option value="moldura_geometrica1.png">Geométrico Ouro 1</option>
                     <option value="moldura_geometrica2.png">Geométrico Ouro 2</option>
                </select>

                <label><b>Instrução do Rodapé:</b></label>
                <input type="text" name="mensagem_rodape" value="<?= htmlspecialchars($config['mensagem_rodape']) ?>" required>

                <button type="submit" style="margin-top: 15px;">Salvar Personalização</button>
            </form>
        </div>

        <div style="flex: 1; min-width: 320px; background: #f8fafc; padding: 20px; border-radius: 8px; border: 1px solid #e2e8f0; position: sticky; top: 20px; height: fit-content;">
            <h3 style="margin-bottom: 15px; font-size: 1.1rem; color: #475569;">👁️ Pré-visualização em Tempo Real</h3>
            
            <div id="preview-box" style="background: <?= $config['cor_fundo'] ?? '#ffffff' ?>; padding: 30px 20px; text-align: center; border-radius: 4px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border: none; transition: all 0.2s;">
                <h2 id="prev-titulo" style="font-size: 1.5rem; margin-bottom: 15px; color: <?= $config['cor_primaria'] ?>;"><?= htmlspecialchars($config['titulo_evento']) ?></h2>
                
                <div id="prev-bloco-data" style="padding: 5px 0; margin-bottom: 15px; font-weight: bold; color: #555; font-size: 0.9rem;">
                    <span id="prev-data">--/--/--</span> <span id="prev-hora"></span>
                </div>

                <p id="prev-local" style="font-size: 0.85rem; color: #000000; font-style: italic; margin-bottom: 15px;"></p>

                <p style="font-size: 0.9rem; color: #333; margin-bottom: 3px;">Convidado(a):</p>
                <p id="prev-nome" style="font-size: 1.1rem; font-weight: bold; color: <?= $config['cor_primaria'] ?>; margin-bottom: 20px;">Nome do Convidado Exemplo</p>
                
                <p style="font-size: 0.9rem; color: #000000; margin-bottom: 3px;">Código Único de Acesso:</p>
                <div id="prev-codigo" style="font-size: 1.4rem; font-weight: bold; color: <?= $config['cor_codigo'] ?>; border: none; padding: 8px; display: inline-block; min-width: 170px; margin-bottom: 20px; border-radius: 4px;">CONVIDADO-9999</div>
                
                <p id="prev-rodape" style="font-size: 0.8rem; color: #000000; font-style: italic;"><?= htmlspecialchars($config['mensagem_rodape']) ?></p>
            </div>
        </div>
    </div>
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
            prevBox.style.backgroundImage = `url('img/molduras/${selectImg.value}')`;
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