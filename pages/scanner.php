<?php
require_once '../services/conexao.php';
ob_start();
include_once '../widgets/sidebar.php';
$sidebar_html = ob_get_clean();
checarSessao();

$db = (new Conexao())->getConexao();

// Processamento AJAX da leitura do QR Code / Código
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['codigo_qr'])) {
    header('Content-Type: application/json');
    $codigo = trim($_POST['codigo_qr']);

    $stmt = $db->prepare("SELECT * FROM convidados WHERE codigo_acesso = :codigo OR id = :id");
    $stmt->execute([':codigo' => $codigo, ':id' => $codigo]);
    $convidado = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$convidado) {
        echo json_encode(['status' => 'error', 'message' => 'Convite não encontrado!']);
        exit;
    }

    if ($convidado['confirmado']) {
        echo json_encode([
            'status' => 'warning',
            'message' => 'Este convite JÁ FOI UTILIZADO!',
            'convidado' => $convidado
        ]);
        exit;
    }

    // Marcar como presente
    $update = $db->prepare("UPDATE convidados SET confirmado = 1, data_confirmacao = NOW() WHERE id = :id");
    $update->execute([':id' => $convidado['id']]);

    echo json_encode([
        'status' => 'success',
        'message' => 'Entrada Liberada!',
        'convidado' => $convidado
    ]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Validar Entrada (Scanner QR)</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/estilo.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <script src="../js/darkmode.js" defer></script>
    <script src="../js/sidebar.js" defer></script>
</head>
<body>
    <div class="app-layout">
        <?= $sidebar_html ?>
        <main class="main-content">
            <div class="container" style="max-width: 600px; text-align: center;">
                <h2><i class="fa-solid fa-qrcode"></i> Validação de Convites</h2>
                
                <!-- Leitor de QR Code pela Câmera -->
                <div id="reader" style="width: 100%; border-radius: 8px; overflow: hidden; margin-top: 15px;"></div>

                <!-- Input Manual alternativo -->
                <div style="margin-top: 20px;">
                    <form id="form-manual" onsubmit="validarManual(event)" style="display: flex; gap: 10px;">
                        <input type="text" id="codigo_manual" placeholder="Digite o código (ex: CONVIDADO-1234)" style="flex: 1;" required>
                        <button type="submit">Validar</button>
                    </form>
                </div>

                <!-- Painel de Resultado -->
                <div id="resultado-validacao" style="margin-top: 20px; display: none; padding: 20px; border-radius: 8px;">
                    <h3 id="res-status-titulo"></h3>
                    <p id="res-mensagem"></p>
                    <div id="res-detalhes" style="text-align: left; background: rgba(0,0,0,0.05); padding: 10px; border-radius: 6px; margin-top: 10px;">
                        <p><b>Nome:</b> <span id="res-nome"></span></p>
                        <p><b>Tipo:</b> <span id="res-tipo"></span></p>
                        <p><b>Acompanhantes:</b> <span id="res-acompanhantes"></span></p>
                    </div>
                    <button onclick="reiniciarScanner()" style="margin-top: 15px; background: #3b82f6; color: #fff;">Próxima Leitura</button>
                </div>
            </div>
        </main>
    </div>

<script>
let html5QrcodeScanner;

function processarCodigo(codigo) {
    const formData = new FormData();
    formData.append('codigo_qr', codigo);

    fetch('scanner.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        exibirResultado(data);
    })
    .catch(err => {
        alert('Erro ao processar validação.');
    });
}

function exibirResultado(data) {
    const box = document.getElementById('resultado-validacao');
    const titulo = document.getElementById('res-status-titulo');
    const msg = document.getElementById('res-mensagem');
    
    box.style.display = 'block';

    if (data.status === 'success') {
        box.style.background = '#dcfce7';
        box.style.color = '#15803d';
        titulo.innerText = '✅ ENTRADA LIBERADA';
    } else if (data.status === 'warning') {
        box.style.background = '#fef3c7';
        box.style.color = '#b45309';
        titulo.innerText = '⚠️ CONVITE JÁ USADO';
    } else {
        box.style.background = '#fee2e2';
        box.style.color = '#b91c1c';
        titulo.innerText = '❌ ERRO NA VALIDAÇÃO';
    }

    msg.innerText = data.message;

    if (data.convidado) {
        document.getElementById('res-detalhes').style.display = 'block';
        document.getElementById('res-nome').innerText = data.convidado.nome;
        document.getElementById('res-tipo').innerText = data.convidado.tipo_convite || 'INDIVIDUAL';
        document.getElementById('res-acompanhantes').innerText = data.convidado.qtd_acompanhantes || 0;
    } else {
        document.getElementById('res-detalhes').style.display = 'none';
    }

    if (html5QrcodeScanner) {
        html5QrcodeScanner.clear();
    }
}

function validarManual(e) {
    e.preventDefault();
    const codigo = document.getElementById('codigo_manual').value;
    processarCodigo(codigo);
}

function reiniciarScanner() {
    document.getElementById('resultado-validacao').style.display = 'none';
    document.getElementById('codigo_manual').value = '';
    iniciarScanner();
}

function iniciarScanner() {
    html5QrcodeScanner = new Html5QrcodeScanner("reader", { fps: 10, qrbox: {width: 250, height: 250} });
    html5QrcodeScanner.render((decodedText) => {
        processarCodigo(decodedText);
    });
}

document.addEventListener("DOMContentLoaded", iniciarScanner);
</script>
</body>
</html>