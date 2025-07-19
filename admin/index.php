<?php
require 'db.php';
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

// Estatísticas gerais
$totalCampanhas = $pdo->query('SELECT COUNT(*) FROM campanhas')->fetchColumn();
$totalDoacoes = $pdo->query('SELECT COUNT(*) FROM doacoes')->fetchColumn();
$totalUsuarios = $pdo->query('SELECT COUNT(*) FROM usuarios')->fetchColumn();
$totalArrecadado = $pdo->query('SELECT SUM(arrecadado) FROM campanhas')->fetchColumn() ?: 0;

// Campanhas por status
$campanhasPendentes = $pdo->query('SELECT COUNT(*) FROM campanhas WHERE status="pendente"')->fetchColumn();
$campanhasAprovadas = $pdo->query('SELECT COUNT(*) FROM campanhas WHERE status="aprovada"')->fetchColumn();
$campanhasFinalizadas = $pdo->query('SELECT COUNT(*) FROM campanhas WHERE status="finalizada"')->fetchColumn();

// Doações por status
$doacoesPendentes = $pdo->query('SELECT COUNT(*) FROM doacoes WHERE status="pendente"')->fetchColumn();
$doacoesConfirmadas = $pdo->query('SELECT COUNT(*) FROM doacoes WHERE status="confirmada"')->fetchColumn();

// Dados para gráficos - últimas 7 campanhas
$ultimasCampanhas = $pdo->query('SELECT titulo, arrecadado, meta FROM campanhas ORDER BY criado_em DESC LIMIT 7')->fetchAll(PDO::FETCH_ASSOC);

// Dados para gráfico de doações dos últimos 30 dias
$doacoes30dias = $pdo->query('SELECT DATE(criado_em) as data, COUNT(*) as total, SUM(valor) as valor_total FROM doacoes WHERE criado_em >= DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY DATE(criado_em) ORDER BY data')->fetchAll(PDO::FETCH_ASSOC);

// Campanhas em destaque
$campanhasDestaque = $pdo->query('SELECT * FROM campanhas WHERE destaque=1 AND status="aprovada" ORDER BY criado_em DESC LIMIT 5')->fetchAll(PDO::FETCH_ASSOC);

// Últimas doações
$ultimasDoacoes = $pdo->query('SELECT d.*, c.titulo as campanha_titulo, u.nome as doador_nome FROM doacoes d LEFT JOIN campanhas c ON d.campanha_id=c.id LEFT JOIN usuarios u ON d.usuario_id=u.id ORDER BY d.criado_em DESC LIMIT 10')->fetchAll(PDO::FETCH_ASSOC);

// Usuários recentes
$usuariosRecentes = $pdo->query('SELECT * FROM usuarios ORDER BY criado_em DESC LIMIT 5')->fetchAll(PDO::FETCH_ASSOC);
?>
<?php include '_head.php'; ?>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
    <?php include 'sidebar.php'; ?>
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Dashboard</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item active">Dashboard</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>
        
        <section class="content">
            <div class="container-fluid">
                <!-- Cards de estatísticas principais -->
                <div class="row mb-4">
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-gradient-primary">
                            <div class="inner">
                                <h3><?= number_format($totalCampanhas, 0, ',', '.') ?></h3>
                                <p>Total de Campanhas</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-hand-holding-heart"></i>
                            </div>
                            <a href="campanhas.php" class="small-box-footer">
                                Ver todas <i class="fas fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-gradient-success">
                            <div class="inner">
                                <h3>R$ <?= number_format($totalArrecadado, 2, ',', '.') ?></h3>
                                <p>Total Arrecadado</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                            <a href="doacoes.php" class="small-box-footer">
                                Ver doações <i class="fas fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-gradient-info">
                            <div class="inner">
                                <h3><?= number_format($totalDoacoes, 0, ',', '.') ?></h3>
                                <p>Total de Doações</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-heart"></i>
                            </div>
                            <a href="doacoes.php" class="small-box-footer">
                                Ver detalhes <i class="fas fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-gradient-warning">
                            <div class="inner">
                                <h3><?= number_format($totalUsuarios, 0, ',', '.') ?></h3>
                                <p>Usuários Cadastrados</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <a href="usuarios.php" class="small-box-footer">
                                Ver usuários <i class="fas fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Gráficos -->
                <div class="row mb-4">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-chart-line"></i> Doações dos Últimos 30 Dias
                                </h3>
                            </div>
                            <div class="card-body">
                                <canvas id="doacoesChart" style="height: 300px;"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-chart-pie"></i> Status das Campanhas
                                </h3>
                            </div>
                            <div class="card-body">
                                <canvas id="campanhasChart" style="height: 300px;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Gráfico de barras - Campanhas -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-chart-bar"></i> Últimas Campanhas - Arrecadação vs Meta
                                </h3>
                            </div>
                            <div class="card-body">
                                <canvas id="campanhasBarChart" style="height: 300px;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Tabelas de informações -->
                <div class="row">
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-star"></i> Campanhas em Destaque
                                </h3>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Campanha</th>
                                                <th>Arrecadado</th>
                                                <th>Meta</th>
                                                <th>Progresso</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($campanhasDestaque as $camp): ?>
                                            <tr>
                                                <td>
                                                    <strong><?= htmlspecialchars($camp['titulo']) ?></strong>
                                                    <br><small class="text-muted">Taxa: <?= $camp['taxa_destaque'] ?>%</small>
                                                </td>
                                                <td>R$ <?= number_format($camp['arrecadado'], 2, ',', '.') ?></td>
                                                <td>R$ <?= number_format($camp['meta'], 2, ',', '.') ?></td>
                                                <td>
                                                    <?php $progresso = ($camp['meta'] > 0) ? ($camp['arrecadado'] / $camp['meta']) * 100 : 0; ?>
                                                    <div class="progress" style="height: 20px;">
                                                        <div class="progress-bar bg-success" style="width: <?= min($progresso, 100) ?>%">
                                                            <?= number_format($progresso, 1) ?>%
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-clock"></i> Últimas Doações
                                </h3>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Doador</th>
                                                <th>Campanha</th>
                                                <th>Valor</th>
                                                <th>Status</th>
                                                <th>Data</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($ultimasDoacoes as $doacao): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($doacao['doador_nome'] ?: 'Anônimo') ?></td>
                                                <td><?= htmlspecialchars($doacao['campanha_titulo']) ?></td>
                                                <td>R$ <?= number_format($doacao['valor'], 2, ',', '.') ?></td>
                                                <td>
                                                    <span class="badge badge-<?= $doacao['status'] === 'confirmada' ? 'success' : ($doacao['status'] === 'pendente' ? 'warning' : 'secondary') ?>">
                                                        <?= ucfirst($doacao['status']) ?>
                                                    </span>
                                                </td>
                                                <td><?= date('d/m H:i', strtotime($doacao['criado_em'])) ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Usuários recentes -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-user-plus"></i> Usuários Recentes
                                </h3>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Foto</th>
                                                <th>Nome</th>
                                                <th>E-mail</th>
                                                <th>Tipo</th>
                                                <th>Status</th>
                                                <th>Cadastrado em</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($usuariosRecentes as $user): ?>
                                            <tr>
                                                <td>
                                                    <?php if ($user['foto_perfil']): ?>
                                                        <img src="../<?= htmlspecialchars($user['foto_perfil']) ?>" alt="Foto" style="width:30px;height:30px;border-radius:50%;object-fit:cover;">
                                                    <?php else: ?>
                                                        <div style="width:30px;height:30px;border-radius:50%;background:#ddd;display:flex;align-items:center;justify-content:center;">
                                                            <i class="fas fa-user fa-sm"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= htmlspecialchars($user['nome']) ?></td>
                                                <td><?= htmlspecialchars($user['email']) ?></td>
                                                <td>
                                                    <span class="badge badge-<?= $user['tipo'] === 'admin' ? 'danger' : 'info' ?>">
                                                        <?= ucfirst($user['tipo']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-<?= $user['status'] === 'ativo' ? 'success' : 'secondary' ?>">
                                                        <?= ucfirst($user['status']) ?>
                                                    </span>
                                                </td>
                                                <td><?= date('d/m/Y H:i', strtotime($user['criado_em'])) ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
<?php include '_footer.php'; ?>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Gráfico de linha - Doações dos últimos 30 dias
const doacoesCtx = document.getElementById('doacoesChart').getContext('2d');
new Chart(doacoesCtx, {
    type: 'line',
    data: {
        labels: <?= json_encode(array_column($doacoes30dias, 'data')) ?>,
        datasets: [{
            label: 'Quantidade de Doações',
            data: <?= json_encode(array_column($doacoes30dias, 'total')) ?>,
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.1
        }, {
            label: 'Valor Total (R$)',
            data: <?= json_encode(array_column($doacoes30dias, 'valor_total')) ?>,
            borderColor: 'rgb(255, 99, 132)',
            backgroundColor: 'rgba(255, 99, 132, 0.2)',
            tension: 0.1,
            yAxisID: 'y1'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                type: 'linear',
                display: true,
                position: 'left',
                title: {
                    display: true,
                    text: 'Quantidade'
                }
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                title: {
                    display: true,
                    text: 'Valor (R$)'
                },
                grid: {
                    drawOnChartArea: false,
                },
            }
        }
    }
});

// Gráfico de pizza - Status das campanhas
const campanhasCtx = document.getElementById('campanhasChart').getContext('2d');
new Chart(campanhasCtx, {
    type: 'doughnut',
    data: {
        labels: ['Pendentes', 'Aprovadas', 'Finalizadas'],
        datasets: [{
            data: [<?= $campanhasPendentes ?>, <?= $campanhasAprovadas ?>, <?= $campanhasFinalizadas ?>],
            backgroundColor: [
                'rgba(255, 206, 86, 0.8)',
                'rgba(75, 192, 192, 0.8)',
                'rgba(54, 162, 235, 0.8)'
            ],
            borderColor: [
                'rgba(255, 206, 86, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(54, 162, 235, 1)'
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Gráfico de barras - Campanhas
const campanhasBarCtx = document.getElementById('campanhasBarChart').getContext('2d');
new Chart(campanhasBarCtx, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($ultimasCampanhas, 'titulo')) ?>,
        datasets: [{
            label: 'Arrecadado (R$)',
            data: <?= json_encode(array_column($ultimasCampanhas, 'arrecadado')) ?>,
            backgroundColor: 'rgba(75, 192, 192, 0.8)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1
        }, {
            label: 'Meta (R$)',
            data: <?= json_encode(array_column($ultimasCampanhas, 'meta')) ?>,
            backgroundColor: 'rgba(255, 99, 132, 0.8)',
            borderColor: 'rgba(255, 99, 132, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Valor (R$)'
                }
            }
        }
    }
});
</script>
</body>
</html> 