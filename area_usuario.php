<?php
require 'admin/db.php';
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$page_title = 'Dashboard';

// Carregar dados do usuário
$stmt = $pdo->prepare('SELECT * FROM usuarios WHERE id = ?');
$stmt->execute([$usuario_id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Verificar se o usuário está ativo
if ($usuario['status'] !== 'ativo') {
    session_destroy();
    header('Location: login.php?erro=conta_inativa');
    exit;
}

// Atualizar último acesso
$stmt = $pdo->prepare('UPDATE usuarios SET ultimo_acesso = NOW() WHERE id = ?');
$stmt->execute([$usuario_id]);

// Estatísticas do usuário
$stmt = $pdo->prepare('SELECT COUNT(*) FROM campanhas WHERE usuario_id = ?');
$stmt->execute([$usuario_id]);
$minhasCampanhas = $stmt->fetchColumn();

$stmt = $pdo->prepare('SELECT COUNT(*) FROM campanhas WHERE usuario_id = ? AND status = "aprovada"');
$stmt->execute([$usuario_id]);
$campanhasAprovadas = $stmt->fetchColumn();

$stmt = $pdo->prepare('SELECT COUNT(*) FROM campanhas WHERE usuario_id = ? AND status = "pendente"');
$stmt->execute([$usuario_id]);
$campanhasPendentes = $stmt->fetchColumn();

$stmt = $pdo->prepare('SELECT COALESCE(SUM(arrecadado), 0) FROM campanhas WHERE usuario_id = ? AND status = "aprovada"');
$stmt->execute([$usuario_id]);
$totalArrecadado = $stmt->fetchColumn();

$stmt = $pdo->prepare('SELECT COUNT(*) FROM doacoes WHERE usuario_id = ?');
$stmt->execute([$usuario_id]);
$minhasDoacoes = $stmt->fetchColumn();

$stmt = $pdo->prepare('SELECT COALESCE(SUM(valor), 0) FROM doacoes WHERE usuario_id = ? AND status = "confirmada"');
$stmt->execute([$usuario_id]);
$totalDoado = $stmt->fetchColumn();

// Campanhas do usuário (últimas 5)
$stmt = $pdo->prepare('
    SELECT c.*, cat.nome as categoria_nome, 
           (c.arrecadado / c.meta * 100) as percentual_meta
    FROM campanhas c 
    LEFT JOIN categorias cat ON c.categoria_id = cat.id 
    WHERE c.usuario_id = ? 
    ORDER BY c.criado_em DESC 
    LIMIT 5
');
$stmt->execute([$usuario_id]);
$campanhas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Doações do usuário (últimas 5)
$stmt = $pdo->prepare('
    SELECT d.*, c.titulo as campanha_titulo, c.imagem as campanha_imagem 
    FROM doacoes d 
    LEFT JOIN campanhas c ON d.campanha_id = c.id 
    WHERE d.usuario_id = ? 
    ORDER BY d.criado_em DESC 
    LIMIT 5
');
$stmt->execute([$usuario_id]);
$doacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Gráfico de campanhas por status
$stmt = $pdo->prepare('SELECT status, COUNT(*) as total FROM campanhas WHERE usuario_id = ? GROUP BY status');
$stmt->execute([$usuario_id]);
$campanhasPorStatus = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Doações recebidas nas minhas campanhas (últimos 30 dias)
$stmt = $pdo->prepare('
    SELECT DATE_FORMAT(d.criado_em, "%Y-%m-%d") as data, 
           COUNT(*) as total_doacoes, 
           SUM(d.valor) as valor_total
    FROM doacoes d 
    JOIN campanhas c ON d.campanha_id = c.id 
    WHERE c.usuario_id = ? 
      AND d.status = "confirmada" 
      AND d.criado_em >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY DATE_FORMAT(d.criado_em, "%Y-%m-%d") 
    ORDER BY data ASC
');
$stmt->execute([$usuario_id]);
$doacoesRecebidas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Notificações não lidas
$stmt = $pdo->prepare('
    SELECT * FROM notificacoes 
    WHERE (usuario_id = ? OR destinatario IN ("todos", "usuarios"))
      AND lida = 0 
    ORDER BY criado_em DESC 
    LIMIT 5
');
$stmt->execute([$usuario_id]);
$notificacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Carregar tema
$tema = $pdo->query('SELECT * FROM temas WHERE ativo = 1')->fetch(PDO::FETCH_ASSOC);
if (!$tema) {
    $tema = ['cor_primaria' => '#782F9B', 'cor_secundaria' => '#65A300', 'cor_terciaria' => '#F7F7F7'];
}

// Carregar configurações
$textos = [];
foreach ($pdo->query('SELECT chave, valor FROM textos')->fetchAll(PDO::FETCH_ASSOC) as $t) {
    $textos[$t['chave']] = $t['valor'];
}

$nome_site = $textos['nome_site'] ?? 'Vaquinha Online';

include '_header_usuario.php';
?>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
            background-color: var(--cor-terciaria);
            margin: 0;
            padding: 0;
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--cor-primaria), var(--cor-secundaria));
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: white !important;
        }
        
        .navbar-nav .nav-link {
            color: rgba(255,255,255,0.9) !important;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .navbar-nav .nav-link:hover {
            color: white !important;
            transform: translateY(-1px);
        }
        
        .dropdown-menu {
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            border-radius: 10px;
        }
        
        .main-content {
            padding: 2rem 0;
        }
        
        .welcome-card {
            background: linear-gradient(135deg, var(--cor-primaria), var(--cor-secundaria));
            color: white;
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .welcome-card h2 {
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .welcome-card p {
            opacity: 0.9;
            margin-bottom: 0;
        }
        
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border: none;
            height: 100%;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }
        
        .stats-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            margin-bottom: 1rem;
        }
        
        .stats-number {
            font-size: 2rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 0.5rem;
        }
        
        .stats-label {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 0;
        }
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
        }
        
        .card-header {
            background: white;
            border-bottom: 1px solid #f0f0f0;
            border-radius: 15px 15px 0 0;
            padding: 1.5rem;
        }
        
        .card-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 0;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--cor-primaria), var(--cor-secundaria));
            border: none;
            border-radius: 10px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        }
        
        .btn-outline-primary {
            border: 2px solid var(--cor-primaria);
            color: var(--cor-primaria);
            border-radius: 10px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-outline-primary:hover {
            background: var(--cor-primaria);
            border-color: var(--cor-primaria);
            transform: translateY(-2px);
        }
        
        .progress {
            height: 8px;
            border-radius: 10px;
            background-color: #f0f0f0;
        }
        
        .progress-bar {
            background: linear-gradient(90deg, var(--cor-primaria), var(--cor-secundaria));
            border-radius: 10px;
        }
        
        .badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 500;
        }
        
        .badge-success {
            background: linear-gradient(135deg, #28a745, #20c997);
        }
        
        .badge-warning {
            background: linear-gradient(135deg, #ffc107, #fd7e14);
        }
        
        .badge-danger {
            background: linear-gradient(135deg, #dc3545, #e83e8c);
        }
        
        .badge-info {
            background: linear-gradient(135deg, #17a2b8, #6610f2);
        }
        
        .table {
            border-radius: 10px;
            overflow: hidden;
        }
        
        .table thead th {
            background: var(--cor-terciaria);
            border: none;
            font-weight: 600;
            color: #333;
        }
        
        .table tbody td {
            border: none;
            border-bottom: 1px solid #f0f0f0;
            vertical-align: middle;
        }
        
        .notification-item {
            padding: 1rem;
            border-radius: 10px;
            background: #f8f9fa;
            margin-bottom: 0.5rem;
            border-left: 4px solid var(--cor-primaria);
        }
        
        .notification-item.unread {
            background: #e3f2fd;
            border-left-color: var(--cor-secundaria);
        }
        
        .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #666;
        }
        
        .empty-state i {
            font-size: 3rem;
            color: #ddd;
            margin-bottom: 1rem;
        }
        
        @media (max-width: 768px) {
            .main-content {
                padding: 1rem 0;
            }
            
            .welcome-card {
                padding: 1.5rem;
                text-align: center;
            }
            
            .stats-card {
                margin-bottom: 1rem;
                text-align: center;
            }
            
            .stats-number {
                font-size: 1.5rem;
            }
        }
    </style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-tachometer-alt me-2"></i>Dashboard</h2>
    <a href="nova_campanha_usuario.php" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Nova Campanha
    </a>
</div>
<!-- Welcome Section -->
<div class="alert alert-primary border-0 shadow-sm mb-4">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h4 class="alert-heading mb-2"><i class="fas fa-hand-wave me-2"></i>Olá, <?= htmlspecialchars($usuario['nome']) ?>!</h4>
            <p class="mb-0">Bem-vindo de volta ao seu painel. Acompanhe suas campanhas e doações.</p>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <div class="text-muted small">
                <i class="fas fa-clock me-1"></i>
                Último acesso: <?= $usuario['ultimo_acesso'] ? date('d/m/Y H:i', strtotime($usuario['ultimo_acesso'])) : 'Primeiro acesso' ?>
            </div>
        </div>
    </div>
</div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="stats-card">
                        <div class="stats-icon" style="background: linear-gradient(135deg, #17a2b8, #007bff);">
                            <i class="fas fa-bullhorn"></i>
                        </div>
                        <div class="stats-number"><?= $minhasCampanhas ?></div>
                        <div class="stats-label">Minhas Campanhas</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="stats-card">
                        <div class="stats-icon" style="background: linear-gradient(135deg, #28a745, #20c997);">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stats-number"><?= $campanhasAprovadas ?></div>
                        <div class="stats-label">Campanhas Aprovadas</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="stats-card">
                        <div class="stats-icon" style="background: linear-gradient(135deg, #ffc107, #fd7e14);">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="stats-number">R$ <?= number_format($totalArrecadado, 0, ',', '.') ?></div>
                        <div class="stats-label">Total Arrecadado</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="stats-card">
                        <div class="stats-icon" style="background: linear-gradient(135deg, #e83e8c, #6610f2);">
                            <i class="fas fa-heart"></i>
                        </div>
                        <div class="stats-number">R$ <?= number_format($totalDoado, 0, ',', '.') ?></div>
                        <div class="stats-label">Total Doado</div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Minhas Campanhas -->
                <div class="col-lg-8 mb-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title">
                                <i class="fas fa-bullhorn me-2"></i>Minhas Campanhas
                            </h5>
                            <a href="nova_campanha_usuario.php" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-plus me-1"></i>Nova
                            </a>
                        </div>
                        <div class="card-body">
                            <?php if (empty($campanhas)): ?>
                                <div class="empty-state">
                                    <i class="fas fa-bullhorn"></i>
                                    <h5>Nenhuma campanha criada</h5>
                                    <p>Crie sua primeira campanha e comece a arrecadar fundos!</p>
                                    <a href="nova_campanha_usuario.php" class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i>Criar Campanha
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Campanha</th>
                                                <th>Status</th>
                                                <th>Progresso</th>
                                                <th>Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($campanhas as $campanha): ?>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <?php if ($campanha['imagem']): ?>
                                                                <img src="<?= htmlspecialchars($campanha['imagem']) ?>" 
                                                                     alt="Campanha" 
                                                                     style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;" 
                                                                     class="me-3">
                                                            <?php endif; ?>
                                                            <div>
                                                                <strong><?= htmlspecialchars($campanha['titulo']) ?></strong><br>
                                                                <small class="text-muted"><?= htmlspecialchars($campanha['categoria_nome'] ?? 'Sem categoria') ?></small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        $status_class = 'info';
                                                        $status_text = $campanha['status'];
                                                        switch ($campanha['status']) {
                                                            case 'aprovada':
                                                                $status_class = 'success';
                                                                $status_text = 'Aprovada';
                                                                break;
                                                            case 'pendente':
                                                                $status_class = 'warning';
                                                                $status_text = 'Pendente';
                                                                break;
                                                            case 'rejeitada':
                                                                $status_class = 'danger';
                                                                $status_text = 'Rejeitada';
                                                                break;
                                                        }
                                                        ?>
                                                        <span class="badge badge-<?= $status_class ?>">
                                                            <?= $status_text ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="progress mb-2" style="height: 6px;">
                                                            <div class="progress-bar" 
                                                                 style="width: <?= min(100, $campanha['percentual_meta']) ?>%"></div>
                                                        </div>
                                                        <small>R$ <?= number_format($campanha['arrecadado'], 0, ',', '.') ?> de R$ <?= number_format($campanha['meta'], 0, ',', '.') ?></small>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm">
                                                            <a href="detalhes_campanha.php?id=<?= $campanha['id'] ?>" 
                                                               class="btn btn-outline-primary btn-sm">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            <a href="editar_campanha_usuario.php?id=<?= $campanha['id'] ?>" 
                                                               class="btn btn-outline-secondary btn-sm">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <?php if ($minhasCampanhas > 5): ?>
                                    <div class="text-center mt-3">
                                        <a href="minhas_campanhas.php" class="btn btn-outline-primary">
                                            Ver todas as campanhas (<?= $minhasCampanhas ?>)
                                        </a>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Quick Actions -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title">
                                <i class="fas fa-bolt me-2"></i>Ações Rápidas
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="nova_campanha_usuario.php" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Nova Campanha
                                </a>
                                <a href="vaquinhas.php" class="btn btn-outline-primary">
                                    <i class="fas fa-search me-2"></i>Explorar Campanhas
                                </a>
                                <a href="perfil.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-user-edit me-2"></i>Editar Perfil
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">
                                <i class="fas fa-clock me-2"></i>Atividade Recente
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($doacoes)): ?>
                                <div class="text-center py-3">
                                    <i class="fas fa-heart text-muted mb-3" style="font-size: 2rem;"></i>
                                    <p class="text-muted mb-0">Nenhuma doação ainda</p>
                                </div>
                            <?php else: ?>
                                <?php foreach (array_slice($doacoes, 0, 3) as $doacao): ?>
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="flex-shrink-0 me-3">
                                            <div class="bg-success rounded-circle d-flex align-items-center justify-content-center" 
                                                 style="width: 40px; height: 40px;">
                                                <i class="fas fa-heart text-white"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <strong>R$ <?= number_format($doacao['valor'], 2, ',', '.') ?></strong><br>
                                            <small class="text-muted">
                                                para <?= htmlspecialchars($doacao['campanha_titulo']) ?><br>
                                                <?= date('d/m/Y', strtotime($doacao['criado_em'])) ?>
                                            </small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                
                                <?php if ($minhasDoacoes > 3): ?>
                                    <div class="text-center">
                                        <a href="minhas_doacoes.php" class="btn btn-outline-primary btn-sm">
                                            Ver todas (<?= $minhasDoacoes ?>)
                                        </a>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <?php if (!empty($doacoesRecebidas) || !empty($campanhasPorStatus)): ?>
            <div class="row mt-4">
                <!-- Doações Recebidas Chart -->
                <?php if (!empty($doacoesRecebidas)): ?>
                <div class="col-lg-8 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">
                                <i class="fas fa-chart-line me-2"></i>Doações Recebidas (Últimos 30 dias)
                            </h5>
                        </div>
                        <div class="card-body">
                            <canvas id="doacoesChart" height="100"></canvas>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Status Chart -->
                <?php if (!empty($campanhasPorStatus)): ?>
                <div class="col-lg-4 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">
                                <i class="fas fa-chart-pie me-2"></i>Status das Campanhas
                            </h5>
                        </div>
                        <div class="card-body">
                            <canvas id="statusChart"></canvas>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Doações Recebidas Chart
        <?php if (!empty($doacoesRecebidas)): ?>
        const doacoesData = <?= json_encode($doacoesRecebidas) ?>;
        const doacoesCtx = document.getElementById('doacoesChart').getContext('2d');
        new Chart(doacoesCtx, {
            type: 'line',
            data: {
                labels: doacoesData.map(d => {
                    const date = new Date(d.data);
                    return date.toLocaleDateString('pt-BR');
                }),
                datasets: [{
                    label: 'Valor (R$)',
                    data: doacoesData.map(d => parseFloat(d.valor_total || 0)),
                    borderColor: getComputedStyle(document.documentElement).getPropertyValue('--cor-primaria'),
                    backgroundColor: getComputedStyle(document.documentElement).getPropertyValue('--cor-primaria') + '20',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'R$ ' + value.toLocaleString('pt-BR');
                            }
                        }
                    }
                }
            }
        });
        <?php endif; ?>

        // Status Chart
        <?php if (!empty($campanhasPorStatus)): ?>
        const statusData = <?= json_encode($campanhasPorStatus) ?>;
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: statusData.map(s => {
                    switch(s.status) {
                        case 'aprovada': return 'Aprovadas';
                        case 'pendente': return 'Pendentes';
                        case 'rejeitada': return 'Rejeitadas';
                        default: return s.status;
                    }
                }),
                datasets: [{
                    data: statusData.map(s => parseInt(s.total)),
                    backgroundColor: [
                        '#28a745',
                        '#ffc107',
                        '#dc3545',
                        '#17a2b8'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
        <?php endif; ?>

    </script>

<?php include '_footer_usuario.php'; ?> 