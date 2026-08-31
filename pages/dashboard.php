<?php
require_once '../services/conexao.php';
session_start();

// Função auxiliar de sessão se necessário
if (!isset($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}

$db = (new Conexao())->getConexao();

// Busca estatísticas agregadas dos convites
$stmtTotal = $db->query("SELECT COUNT(*) AS total FROM convidado");
$totalConvites = $stmtTotal->fetch()['total'] ?? 0;

$stmtEmitidos = $db->query("SELECT COUNT(*) AS total FROM convidado WHERE status = 'EMITIDO'");
$totalEmitidos = $stmtEmitidos->fetch()['total'] ?? 0;

$stmtPresentes = $db->query("SELECT COUNT(*) AS total FROM convidado WHERE status = 'PRESENTE'");
$totalPresentes = $stmtPresentes->fetch()['total'] ?? 0;

$stmtCancelados = $db->query("SELECT COUNT(*) AS total FROM convidado WHERE status = 'CANCELADO'");
$totalCancelados = $stmtCancelados->fetch()['total'] ?? 0;

$taxaPresenca = $totalConvites > 0 ? round(($totalPresentes / $totalConvites) * 100, 1) : 0;
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="../css/estilo.css">
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
        <!-- Renderização do Widget da Sidebar -->
        <?php include_once '../widgets/sidebar.php'; ?>

        <!-- Conteúdo Principal -->
        <main class="main-content">
            <!-- Botão Hambúrguer Visível apenas no Mobile se a Sidebar estiver fechada -->
            <button id="mobile-hamburger-btn" class="hamburger-btn mobile-only" aria-label="Abrir Menu">☰</button>

            <div class="container">
                <div style="background: #e0f2fe; border-left: 4px solid #0284c7; padding: 12px 15px; margin-bottom: 20px; border-radius: 6px; color: #0369a1;">
                    🎨 <b>Dica de Design:</b> Seu convite está usando o modelo padrão. <a href="configuracao_convite.php" style="font-weight: bold; text-decoration: underline; color: #0284c7;">Clique aqui para personalizar as cores e fontes do seu evento</a>.
                </div>

                <h2>Painel Principal</h2>
                <p style="margin-bottom: 20px;">Bem-vindo, <b><?= htmlspecialchars($_SESSION['nome'] ?? $_SESSION['usuario_nome'] ?? '') ?></b>!</p>

                <div class="kpi-grid">
                    <div class="kpi-card">
                        <div class="title">Total de Convites</div>
                        <div class="value"><?= $totalConvites ?></div>
                    </div>
                    <div class="kpi-card">
                        <div class="title">Emitidos (Pendentes)</div>
                        <div class="value" style="color: #2563eb;"><?= $totalEmitidos ?></div>
                    </div>
                    <div class="kpi-card">
                        <div class="title">Presentes (Validados)</div>
                        <div class="value" style="color: #10b981;"><?= $totalPresentes ?></div>
                    </div>
                    <div class="kpi-card">
                        <div class="title">Cancelados</div>
                        <div class="value" style="color: #ef4444;"><?= $totalCancelados ?></div>
                    </div>
                    <div class="kpi-card">
                        <div class="title">Taxa de Presença</div>
                        <div class="value" style="color: #8b5cf6;"><?= $taxaPresenca ?>%</div>
                    </div>
                </div>

                <div class="charts-container">
                    <div class="chart-box">
                        <h3 style="font-size: 1.1rem; margin-bottom: 15px;">Distribuição de Status</h3>
                        <canvas id="chartStatus"></canvas>
                    </div>
                    <div class="chart-box">
                        <h3 style="font-size: 1.1rem; margin-bottom: 15px;">Proporção Entradas vs Pendentes</h3>
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
                    labels: ['Emitidos', 'Presentes', 'Cancelados'],
                    datasets: [{
                        data: [<?= $totalEmitidos ?>, <?= $totalPresentes ?>, <?= $totalCancelados ?>],
                        backgroundColor: ['#2563eb', '#10b981', '#ef4444']
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
                    labels: ['Convites'],
                    datasets: [
                        { label: 'Presentes', data: [<?= $totalPresentes ?>], backgroundColor: '#10b981' },
                        { label: 'Pendentes', data: [<?= $totalEmitidos ?>], backgroundColor: '#2563eb' }
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