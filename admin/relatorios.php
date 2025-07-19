<?php
require 'db.php';
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

$erro = $sucesso = '';

// Filtros
$tipo = $_GET['tipo'] ?? 'geral';
$periodo = $_GET['periodo'] ?? '30';
$categoria_id = $_GET['categoria_id'] ?? '';
$status = $_GET['status'] ?? '';

// Carregar categorias para filtro
$categorias = $pdo->query('SELECT * FROM categorias ORDER BY nome')->fetchAll(PDO::FETCH_ASSOC);

// Estatísticas gerais
$totalCampanhas = $pdo->query('SELECT COUNT(*) FROM campanhas')->fetchColumn();
$totalDoacoes = $pdo->query('SELECT COUNT(*) FROM doacoes')->fetchColumn();
$totalUsuarios = $pdo->query('SELECT COUNT(*) FROM usuarios')->fetchColumn();
$totalArrecadado = $pdo->query('SELECT SUM(arrecadado) FROM campanhas')->fetchColumn() ?: 0;

// Estatísticas por período
$dataInicio = date('Y-m-d', strtotime("-{$periodo} days"));
$campanhasPeriodo = $pdo->prepare('SELECT COUNT(*) FROM campanhas WHERE criado_em >= ?')->execute([$dataInicio])->fetchColumn();
$doacoesPeriodo = $pdo->prepare('SELECT COUNT(*) FROM doacoes WHERE criado_em >= ?')->execute([$dataInicio])->fetchColumn();
$arrecadadoPeriodo = $pdo->prepare('SELECT SUM(arrecadado) FROM campanhas WHERE criado_em >= ?')->execute([$dataInicio])->fetchColumn() ?: 0;

// Dados para gráficos
$doacoesPorDia = $pdo->prepare('SELECT DATE(criado_em) as data, COUNT(*) as total, SUM(valor) as valor_total FROM doacoes WHERE criado_em >= ? GROUP BY DATE(criado_em) ORDER BY data')->execute([$dataInicio])->fetchAll(PDO::FETCH_ASSOC);

$campanhasPorCategoria = $pdo->query('SELECT c.nome as categoria, COUNT(ca.id) as total, SUM(ca.arrecadado) as arrecadado FROM categorias c LEFT JOIN campanhas ca ON c.id = ca.categoria_id GROUP BY c.id ORDER BY total DESC')->fetchAll(PDO::FETCH_ASSOC);

$statusCampanhas = $pdo->query('SELECT status, COUNT(*) as total FROM campanhas GROUP BY status')->fetchAll(PDO::FETCH_ASSOC);

$statusDoacoes = $pdo->query('SELECT status, COUNT(*) as total, SUM(valor) as valor_total FROM doacoes GROUP BY status')->fetchAll(PDO::FETCH_ASSOC);

// Aplicar filtros em todas as queries
$where = [];
$params = [];
if ($categoria_id) {
    $where[] = 'c.categoria_id = ?';
    $params[] = $categoria_id;
}
if ($status) {
    $where[] = 'c.status = ?';
    $params[] = $status;
}
$where[] = 'c.criado_em >= ?';
$params[] = $dataInicio;
$where_clause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Campanhas filtradas
$campanhasFiltradas = $pdo->prepare("SELECT c.*, u.nome as criador, cat.nome as categoria FROM campanhas c LEFT JOIN usuarios u ON c.usuario_id = u.id LEFT JOIN categorias cat ON c.categoria_id = cat.id $where_clause ORDER BY c.arrecadado DESC");
$campanhasFiltradas->execute($params);
$campanhasFiltradas = $campanhasFiltradas->fetchAll(PDO::FETCH_ASSOC);

// Doações filtradas
$doacoesFiltradas = $pdo->prepare("SELECT d.*, c.titulo as campanha, c.categoria_id FROM doacoes d JOIN campanhas c ON d.campanha_id = c.id $where_clause ORDER BY d.criado_em DESC");
$doacoesFiltradas->execute($params);
$doacoesFiltradas = $doacoesFiltradas->fetchAll(PDO::FETCH_ASSOC);

// Top campanhas e doadores filtrados
$topCampanhas = array_slice($campanhasFiltradas, 0, 10);
$topDoadores = [];
if ($doacoesFiltradas) {
    $doadores = [];
    foreach ($doacoesFiltradas as $d) {
        $doadores[$d['usuario_id']]['nome'] = $d['usuario_id'];
        $doadores[$d['usuario_id']]['valor_total'] = ($doadores[$d['usuario_id']]['valor_total'] ?? 0) + $d['valor'];
        $doadores[$d['usuario_id']]['total_doacoes'] = ($doadores[$d['usuario_id']]['total_doacoes'] ?? 0) + 1;
    }
    usort($doadores, function($a, $b) { return $b['valor_total'] <=> $a['valor_total']; });
    $topDoadores = array_slice($doadores, 0, 10);
}

// Dados para exportação
if (isset($_GET['exportar'])) {
    $dados = [
        'periodo' => $periodo,
        'total_campanhas' => $totalCampanhas,
        'total_doacoes' => $totalDoacoes,
        'total_arrecadado' => $totalArrecadado,
        'campanhas_periodo' => $campanhasPeriodo,
        'doacoes_periodo' => $doacoesPeriodo,
        'arrecadado_periodo' => $arrecadadoPeriodo,
        'top_campanhas' => $topCampanhas,
        'top_doadores' => $topDoadores,
        'doacoes_por_dia' => $doacoesPorDia,
        'campanhas_por_categoria' => $campanhasPorCategoria
    ];
    
    // Gerar PDF (simulado - em produção usar biblioteca como TCPDF ou FPDF)
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="relatorio_' . date('Y-m-d') . '.pdf"');
    // Aqui seria gerado o PDF real
    exit;
}
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
                        <h1>Relatórios Avançados</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                            <li class="breadcrumb-item active">Relatórios</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>
        
        <section class="content">
            <div class="container-fluid">
                <!-- Filtros -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-filter"></i> Filtros do Relatório
                        </h3>
                    </div>
                    <div class="card-body">
                        <form method="get" class="row">
                            <div class="col-md-3">
                                <label>Período</label>
                                <select name="periodo" class="form-control">
                                    <option value="7" <?= $periodo == '7' ? 'selected' : '' ?>>Últimos 7 dias</option>
                                    <option value="30" <?= $periodo == '30' ? 'selected' : '' ?>>Últimos 30 dias</option>
                                    <option value="90" <?= $periodo == '90' ? 'selected' : '' ?>>Últimos 90 dias</option>
                                    <option value="365" <?= $periodo == '365' ? 'selected' : '' ?>>Último ano</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Categoria</label>
                                <select name="categoria_id" class="form-control">
                                    <option value="">Todas as categorias</option>
                                    <?php foreach ($categorias as $cat): ?>
                                        <option value="<?= $cat['id'] ?>" <?= $categoria_id == $cat['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cat['nome']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Status</label>
                                <select name="status" class="form-control">
                                    <option value="">Todos os status</option>
                                    <option value="aprovada" <?= $status == 'aprovada' ? 'selected' : '' ?>>Aprovada</option>
                                    <option value="pendente" <?= $status == 'pendente' ? 'selected' : '' ?>>Pendente</option>
                                    <option value="finalizada" <?= $status == 'finalizada' ? 'selected' : '' ?>>Finalizada</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>&nbsp;</label>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Filtrar
                                    </button>
                                    <button type="button" class="btn btn-success" onclick="exportarCSV()">
                                        <i class="fas fa-file-csv"></i> Exportar CSV
                                    </button>
                                    <a href="?exportar=1&periodo=<?= $periodo ?>&categoria_id=<?= $categoria_id ?>&status=<?= $status ?>" class="btn btn-danger">
                                        <i class="fas fa-file-pdf"></i> Exportar PDF
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Estatísticas Principais -->
                <div class="row mb-4">
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-gradient-primary">
                            <div class="inner">
                                <h3><?= number_format($totalCampanhas, 0, ',', '.') ?></h3>
                                <p>Total de Campanhas</p>
                                <small>+<?= $campanhasPeriodo ?> no período</small>
                            </div>
                            <div class="icon">
                                <i class="fas fa-hand-holding-heart"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-gradient-success">
                            <div class="inner">
                                <h3>R$ <?= number_format($totalArrecadado, 0, ',', '.') ?></h3>
                                <p>Total Arrecadado</p>
                                <small>+R$ <?= number_format($arrecadadoPeriodo, 0, ',', '.') ?> no período</small>
                            </div>
                            <div class="icon">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-gradient-info">
                            <div class="inner">
                                <h3><?= number_format($totalDoacoes, 0, ',', '.') ?></h3>
                                <p>Total de Doações</p>
                                <small>+<?= $doacoesPeriodo ?> no período</small>
                            </div>
                            <div class="icon">
                                <i class="fas fa-heart"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-gradient-warning">
                            <div class="inner">
                                <h3><?= number_format($totalUsuarios, 0, ',', '.') ?></h3>
                                <p>Usuários Cadastrados</p>
                                <small>Total geral</small>
                            </div>
                            <div class="icon">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Gráficos -->
                <div class="row mb-4">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-chart-line"></i> Doações por Dia (Últimos <?= $periodo ?> dias)
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
                                    <i class="fas fa-chart-pie"></i> Campanhas por Categoria
                                </h3>
                            </div>
                            <div class="card-body">
                                <canvas id="categoriasChart" style="height: 300px;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Mais Gráficos -->
                <div class="row mb-4">
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-chart-pie"></i> Status das Campanhas
                                </h3>
                            </div>
                            <div class="card-body">
                                <canvas id="statusCampanhasChart" style="height: 250px;"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-chart-pie"></i> Status das Doações
                                </h3>
                            </div>
                            <div class="card-body">
                                <canvas id="statusDoacoesChart" style="height: 250px;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Tabelas de Dados -->
                <div class="row">
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-trophy"></i> Top 10 Campanhas
                                </h3>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Campanha</th>
                                                <th>Criador</th>
                                                <th>Categoria</th>
                                                <th>Arrecadado</th>
                                                <th>Meta</th>
                                                <th>Progresso</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($topCampanhas as $camp): ?>
                                            <tr>
                                                <td>
                                                    <strong><?= htmlspecialchars($camp['titulo']) ?></strong>
                                                    <?php if ($camp['destaque']): ?>
                                                        <span class="badge badge-warning">Destaque</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= htmlspecialchars($camp['criador'] ?: 'Anônimo') ?></td>
                                                <td><?= htmlspecialchars($camp['categoria'] ?: 'Sem categoria') ?></td>
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
                                    <i class="fas fa-medal"></i> Top 10 Doadores
                                </h3>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Doador</th>
                                                <th>Total de Doações</th>
                                                <th>Valor Total</th>
                                                <th>Média por Doação</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($topDoadores as $doador): ?>
                                            <tr>
                                                <td>
                                                    <strong><?= htmlspecialchars($doador['nome']) ?></strong>
                                                </td>
                                                <td><?= number_format($doador['total_doacoes'], 0, ',', '.') ?></td>
                                                <td>R$ <?= number_format($doador['valor_total'], 2, ',', '.') ?></td>
                                                <td>R$ <?= number_format($doador['valor_total'] / $doador['total_doacoes'], 2, ',', '.') ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Análises Avançadas -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-chart-bar"></i> Análises Avançadas
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-info"><i class="fas fa-percentage"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Taxa de Sucesso</span>
                                                <span class="info-box-number">
                                                    <?php 
                                                    $campanhasSucesso = $pdo->query('SELECT COUNT(*) FROM campanhas WHERE arrecadado >= meta')->fetchColumn();
                                                    $taxaSucesso = $totalCampanhas > 0 ? ($campanhasSucesso / $totalCampanhas) * 100 : 0;
                                                    echo number_format($taxaSucesso, 1) . '%';
                                                    ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-success"><i class="fas fa-dollar-sign"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Média por Campanha</span>
                                                <span class="info-box-number">
                                                    R$ <?= number_format($totalCampanhas > 0 ? $totalArrecadado / $totalCampanhas : 0, 2, ',', '.') ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-warning"><i class="fas fa-heart"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Média por Doação</span>
                                                <span class="info-box-number">
                                                    R$ <?= number_format($totalDoacoes > 0 ? $totalArrecadado / $totalDoacoes : 0, 2, ',', '.') ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-danger"><i class="fas fa-users"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Campanhas por Usuário</span>
                                                <span class="info-box-number">
                                                    <?= number_format($totalUsuarios > 0 ? $totalCampanhas / $totalUsuarios : 0, 1, ',', '.') ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
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
// Gráfico de linha - Doações por dia
const doacoesCtx = document.getElementById('doacoesChart').getContext('2d');
new Chart(doacoesCtx, {
    type: 'line',
    data: {
        labels: <?= json_encode(array_column($doacoesPorDia, 'data')) ?>,
        datasets: [{
            label: 'Quantidade de Doações',
            data: <?= json_encode(array_column($doacoesPorDia, 'total')) ?>,
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.1,
            yAxisID: 'y'
        }, {
            label: 'Valor Total (R$)',
            data: <?= json_encode(array_column($doacoesPorDia, 'valor_total')) ?>,
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

// Gráfico de pizza - Campanhas por categoria
const categoriasCtx = document.getElementById('categoriasChart').getContext('2d');
new Chart(categoriasCtx, {
    type: 'doughnut',
    data: {
        labels: <?= json_encode(array_column($campanhasPorCategoria, 'categoria')) ?>,
        datasets: [{
            data: <?= json_encode(array_column($campanhasPorCategoria, 'total')) ?>,
            backgroundColor: [
                'rgba(255, 99, 132, 0.8)',
                'rgba(54, 162, 235, 0.8)',
                'rgba(255, 206, 86, 0.8)',
                'rgba(75, 192, 192, 0.8)',
                'rgba(153, 102, 255, 0.8)',
                'rgba(255, 159, 64, 0.8)'
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

// Gráfico de pizza - Status das campanhas
const statusCampanhasCtx = document.getElementById('statusCampanhasChart').getContext('2d');
new Chart(statusCampanhasCtx, {
    type: 'pie',
    data: {
        labels: <?= json_encode(array_column($statusCampanhas, 'status')) ?>,
        datasets: [{
            data: <?= json_encode(array_column($statusCampanhas, 'total')) ?>,
            backgroundColor: [
                'rgba(75, 192, 192, 0.8)',
                'rgba(255, 206, 86, 0.8)',
                'rgba(54, 162, 235, 0.8)'
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

// Gráfico de pizza - Status das doações
const statusDoacoesCtx = document.getElementById('statusDoacoesChart').getContext('2d');
new Chart(statusDoacoesCtx, {
    type: 'pie',
    data: {
        labels: <?= json_encode(array_column($statusDoacoes, 'status')) ?>,
        datasets: [{
            data: <?= json_encode(array_column($statusDoacoes, 'total')) ?>,
            backgroundColor: [
                'rgba(75, 192, 192, 0.8)',
                'rgba(255, 206, 86, 0.8)',
                'rgba(255, 99, 132, 0.8)'
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

// Função para exportar CSV
function exportarCSV() {
    const params = new URLSearchParams(window.location.search);
    window.location.href = 'api/exportar_csv.php?' + params.toString();
}
</script>
</body>
</html> 