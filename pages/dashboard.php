<?php
require_once '../services/conexao.php';
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}

$db = (new Conexao())->getConexao();

// Agregações de status
$totalConvites = $db->query("SELECT COUNT(*) FROM convidado")->fetchColumn() ?? 0;
$totalPendentes = $db->query("SELECT COUNT(*) FROM convidado WHERE status = 'PENDENTE'")->fetchColumn() ?? 0;
$totalEmitidos = $db->query("SELECT COUNT(*) FROM convidado WHERE status = 'EMITIDO'")->fetchColumn() ?? 0;
$totalCancelados = $db->query("SELECT COUNT(*) FROM convidado WHERE status = 'CANCELADO'")->fetchColumn() ?? 0;

// Verifica se já existe qualquer configuração salva no banco
$totalConfig = $db->query("SELECT COUNT(*) FROM configuracao_convite")->fetchColumn() ?? 0;

// Exibe a dica apenas se a tabela estiver vazia (ainda não personalizado)
$exibirAvisoDesign = ($totalConfig == 0);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="../css/estilo.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="../js/darkmode.js" defer></script>
    <script src="../js/sidebar.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }
        .kpi-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 16px;
            box-shadow: var(--shadow-sm);
        }
        .kpi-card .title {
            font-size: 0.85rem;
            color: var(--text-secondary);
            text-transform: uppercase;
            font-weight: 600;
        }
        .kpi-card .value {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-top: 6px;
        }
        .charts-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .chart-box {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 20px;
            box-shadow: var(--shadow-sm);
        }
    </style>
</head>
<body>
    <div class="app-layout">
        <?php include_once '../widgets/sidebar.php'; ?>

        <main class="main-content">
            <button id="mobile-hamburger-btn" class="hamburger-btn mobile-only" aria-label="Abrir Menu">☰</button>

            <div class="container">
                <?php if ($exibirAvisoDesign): ?>
                    <div style="background: #e0f2fe; border-left: 4px solid #0284c7; padding: 12px 15px; margin-bottom: 20px; border-radius: 6px; color: #0369a1;">
                        🎨 <b>Dica de Design:</b> Seu convite está usando o modelo padrão. <a href="configuracao_convite.php" style="font-weight: bold; text-decoration: underline; color: #0284c7;">Clique aqui para personalizar</a>.
                    </div>
                <?php endif; ?>

                <h2>Painel Principal</h2>
                <p style="margin-bottom: 20px;">Bem-vindo, <b><?= htmlspecialchars($_SESSION['nome'] ?? $_SESSION['usuario_nome'] ?? '') ?></b>!</p>

                <div class="kpi-grid">
                    <div class="kpi-card">
                        <div class="title">Total de Convites</div>
                        <div class="value"><?= $totalConvites ?></div>
                    </div>
                    <div class="kpi-card">
                        <div class="title">Pendentes</div>
                        <div class="value" style="color: #f59e0b;"><?= $totalPendentes ?></div>
                    </div>
                    <div class="kpi-card">
                        <div class="title">Emitidos</div>
                        <div class="value" style="color: #2563eb;"><?= $totalEmitidos ?></div>
                    </div>
                    <div class="kpi-card">
                        <div class="title">Cancelados</div>
                        <div class="value" style="color: #ef4444;"><?= $totalCancelados ?></div>
                    </div>
                </div>

                <div class="charts-container">
                    <div class="chart-box">
                        <h3 style="font-size: 1.1rem; margin-bottom: 15px;">Distribuição de Status</h3>
                        <canvas id="chartStatus"></canvas>
                    </div>
                    <div class="chart-box">
                        <h3 style="font-size: 1.1rem; margin-bottom: 15px;">Progresso dos Envios</h3>
                        <canvas id="chartComparativo"></canvas>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const ctxStatus = document.getElementById('chartStatus').getContext('2d');
            new Chart(ctxStatus, {
                type: 'doughnut',
                data: {
                    labels: ['Pendentes', 'Emitidos', 'Cancelados'],
                    datasets: [{
                        data: [<?= $totalPendentes ?>, <?= $totalEmitidos ?>, <?= $totalCancelados ?>],
                        backgroundColor: ['#f59e0b', '#2563eb', '#ef4444']
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { position: 'bottom' } }
                }
            });

            const ctxComp = document.getElementById('chartComparativo').getContext('2d');
            new Chart(ctxComp, {
                type: 'bar',
                data: {
                    labels: ['Status dos Convites'],
                    datasets: [
                        { label: 'Pendentes', data: [<?= $totalPendentes ?>], backgroundColor: '#f59e0b' },
                        { label: 'Emitidos', data: [<?= $totalEmitidos ?>], backgroundColor: '#2563eb' }
                    ]
                },
                options: {
                    responsive: true,
                    scales: { y: { beginAtZero: true, precision: 0 } },
                    plugins: { legend: { position: 'bottom' } }
                }
            });
        });
    </script>
</body>
</html>