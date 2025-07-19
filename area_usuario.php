<?php
require 'admin/db.php';
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Carregar dados do usuário
$usuario = $pdo->prepare('SELECT * FROM usuarios WHERE id = ?')->execute([$usuario_id])->fetch(PDO::FETCH_ASSOC);

// Estatísticas do usuário
$minhasCampanhas = $pdo->prepare('SELECT COUNT(*) FROM campanhas WHERE usuario_id = ?')->execute([$usuario_id])->fetchColumn();
$campanhasAprovadas = $pdo->prepare('SELECT COUNT(*) FROM campanhas WHERE usuario_id = ? AND status = "aprovada"')->execute([$usuario_id])->fetchColumn();
$totalArrecadado = $pdo->prepare('SELECT SUM(arrecadado) FROM campanhas WHERE usuario_id = ? AND status = "aprovada"')->execute([$usuario_id])->fetchColumn() ?: 0;
$minhasDoacoes = $pdo->prepare('SELECT COUNT(*) FROM doacoes WHERE usuario_id = ?')->execute([$usuario_id])->fetchColumn();
$totalDoado = $pdo->prepare('SELECT SUM(valor) FROM doacoes WHERE usuario_id = ? AND status = "confirmada"')->execute([$usuario_id])->fetchColumn() ?: 0;

// Campanhas do usuário
$campanhas = $pdo->prepare('SELECT c.*, cat.nome as categoria_nome FROM campanhas c LEFT JOIN categorias cat ON c.categoria_id = cat.id WHERE c.usuario_id = ? ORDER BY c.criado_em DESC')->execute([$usuario_id])->fetchAll(PDO::FETCH_ASSOC);

// Doações do usuário
$doacoes = $pdo->prepare('SELECT d.*, c.titulo as campanha_titulo FROM doacoes d LEFT JOIN campanhas c ON d.campanha_id = c.id WHERE d.usuario_id = ? ORDER BY d.criado_em DESC')->execute([$usuario_id])->fetchAll(PDO::FETCH_ASSOC);

// Gráfico de campanhas por status
$campanhasPorStatus = $pdo->prepare('SELECT status, COUNT(*) as total FROM campanhas WHERE usuario_id = ? GROUP BY status')->execute([$usuario_id])->fetchAll(PDO::FETCH_ASSOC);

// Gráfico de doações por mês (últimos 6 meses)
$doacoesPorMes = $pdo->prepare('SELECT DATE_FORMAT(criado_em, "%Y-%m") as mes, COUNT(*) as total, SUM(valor) as valor_total FROM doacoes WHERE usuario_id = ? AND criado_em >= DATE_SUB(NOW(), INTERVAL 6 MONTH) GROUP BY DATE_FORMAT(criado_em, "%Y-%m") ORDER BY mes')->execute([$usuario_id])->fetchAll(PDO::FETCH_ASSOC);

// Carregar tema
$tema = $pdo->query('SELECT * FROM temas WHERE ativo = 1')->fetch(PDO::FETCH_ASSOC);
if (!$tema) {
    $tema = ['cor_primaria' => '#782F9B', 'cor_secundaria' => '#65A300', 'cor_terciaria' => '#F7F7F7'];
}

// Carregar logo
$logo = $pdo->query('SELECT * FROM logo_site WHERE id=1')->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minha Conta - Vaquinha Online</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --cor-primaria: <?= $tema['cor_primaria'] ?>;
            --cor-secundaria: <?= $tema['cor_secundaria'] ?>;
            --cor-terciaria: <?= $tema['cor_terciaria'] ?>;
        }
        
        * {
            font-family: 'Inter', sans-serif;
        }
        
        .bg-gradient-primary {
            background: linear-gradient(135deg, var(--cor-primaria), var(--cor-secundaria)) !important;
        }
        
        .btn-primary {
            background-color: var(--cor-primaria) !important;
            border-color: var(--cor-primaria) !important;
        }
        
        .btn-primary:hover {
            background-color: var(--cor-secundaria) !important;
            border-color: var(--cor-secundaria) !important;
        }
        
        .text-primary {
            color: var(--cor-primaria) !important;
        }
        
        .border-primary {
            border-color: var(--cor-primaria) !important;
        }
        
        .progress-bar {
            background-color: var(--cor-primaria) !important;
        }
        
        .navbar-brand img {
            max-height: 40px;
        }
        
        .stats-card {
            background: linear-gradient(135deg, var(--cor-primaria), var(--cor-secundaria));
            color: white;
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            transition: transform 0.3s ease;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
        }
        
        .stats-card i {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .campanha-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
            border-radius: 15px;
            overflow: hidden;
        }
        
        .campanha-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        
        .campanha-card .card-img-top {
            height: 200px;
            object-fit: cover;
        }
        
        .progress {
            height: 8px;
            border-radius: 10px;
        }
        
        .sidebar {
            background-color: var(--cor-terciaria);
            min-height: 100vh;
            padding: 2rem 0;
        }
        
        .sidebar .nav-link {
            color: var(--cor-primaria);
            padding: 1rem 2rem;
            border-radius: 10px;
            margin: 0.5rem 1rem;
            transition: all 0.3s ease;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background-color: var(--cor-primaria);
            color: white;
        }
        
        .sidebar .nav-link i {
            margin-right: 0.5rem;
        }
        
        .content-area {
            padding: 2rem;
        }
        
        .profile-header {
            background: linear-gradient(135deg, var(--cor-primaria), var(--cor-secundaria));
            color: white;
            padding: 3rem 0;
            border-radius: 15px;
            margin-bottom: 2rem;
        }
        
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid white;
        }
        
        .table-responsive {
            border-radius: 15px;
            overflow: hidden;
        }
        
        .table th {
            background-color: var(--cor-terciaria);
            border: none;
            color: var(--cor-primaria);
            font-weight: 600;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                min-height: auto;
                padding: 1rem 0;
            }
            
            .content-area {
                padding: 1rem;
            }
            
            .stats-card {
                margin-bottom: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <?php if ($logo && $logo['caminho']): ?>
                    <img src="<?= htmlspecialchars($logo['caminho']) ?>" alt="Logo">
                <?php else: ?>
                    <i class="fas fa-heart text-primary"></i>
                    <span class="fw-bold">Vaquinha Online</span>
                <?php endif; ?>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="nova_campanha_usuario.php">Nova Campanha</a>
                    </li>
                </ul>
                
                <div class="d-flex align-items-center">
                    <div class="dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle"></i> <?= htmlspecialchars($usuario['nome']) ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="area_usuario.php"><i class="fas fa-user me-2"></i>Minha Conta</a></li>
                            <li><a class="dropdown-item" href="editar_perfil.php"><i class="fas fa-edit me-2"></i>Editar Perfil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Sair</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3 col-md-4">
                <div class="sidebar">
                    <div class="text-center mb-4">
                        <?php if ($usuario['foto_perfil']): ?>
                            <img src="<?= htmlspecialchars($usuario['foto_perfil']) ?>" alt="Foto de perfil" class="profile-avatar mb-3">
                        <?php else: ?>
                            <div class="profile-avatar mb-3 mx-auto d-flex align-items-center justify-content-center bg-light">
                                <i class="fas fa-user fa-3x text-muted"></i>
                            </div>
                        <?php endif; ?>
                        <h5 class="text-primary"><?= htmlspecialchars($usuario['nome']) ?></h5>
                        <p class="text-muted"><?= htmlspecialchars($usuario['email']) ?></p>
                    </div>
                    
                    <nav class="nav flex-column">
                        <a class="nav-link active" href="#dashboard" data-bs-toggle="tab">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                        <a class="nav-link" href="#campanhas" data-bs-toggle="tab">
                            <i class="fas fa-hand-holding-heart"></i> Minhas Campanhas
                        </a>
                        <a class="nav-link" href="#doacoes" data-bs-toggle="tab">
                            <i class="fas fa-heart"></i> Minhas Doações
                        </a>
                        <a class="nav-link" href="#perfil" data-bs-toggle="tab">
                            <i class="fas fa-user-cog"></i> Configurações
                        </a>
                    </nav>
                </div>
            </div>
            
            <!-- Content Area -->
            <div class="col-lg-9 col-md-8">
                <div class="content-area">
                    <div class="tab-content">
                        <!-- Dashboard -->
                        <div class="tab-pane fade show active" id="dashboard">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h2 class="mb-0">Dashboard</h2>
                                <a href="nova_campanha_usuario.php" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Nova Campanha
                                </a>
                            </div>
                            
                            <!-- Estatísticas -->
                            <div class="row mb-4">
                                <div class="col-lg-3 col-md-6 mb-3">
                                    <div class="stats-card">
                                        <i class="fas fa-hand-holding-heart"></i>
                                        <h3 class="display-6 fw-bold"><?= number_format($minhasCampanhas, 0, ',', '.') ?></h3>
                                        <p class="mb-0">Minhas Campanhas</p>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6 mb-3">
                                    <div class="stats-card">
                                        <i class="fas fa-check-circle"></i>
                                        <h3 class="display-6 fw-bold"><?= number_format($campanhasAprovadas, 0, ',', '.') ?></h3>
                                        <p class="mb-0">Campanhas Aprovadas</p>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6 mb-3">
                                    <div class="stats-card">
                                        <i class="fas fa-dollar-sign"></i>
                                        <h3 class="display-6 fw-bold">R$ <?= number_format($totalArrecadado, 0, ',', '.') ?></h3>
                                        <p class="mb-0">Total Arrecadado</p>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6 mb-3">
                                    <div class="stats-card">
                                        <i class="fas fa-heart"></i>
                                        <h3 class="display-6 fw-bold"><?= number_format($minhasDoacoes, 0, ',', '.') ?></h3>
                                        <p class="mb-0">Minhas Doações</p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Gráficos -->
                            <div class="row mb-4">
                                <div class="col-lg-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">
                                                <i class="fas fa-chart-pie"></i> Status das Campanhas
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <canvas id="campanhasChart" style="height: 250px;"></canvas>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">
                                                <i class="fas fa-chart-line"></i> Doações por Mês
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <canvas id="doacoesChart" style="height: 250px;"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Resumo -->
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">
                                                <i class="fas fa-star"></i> Campanhas em Destaque
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <?php 
                                            $campanhasDestaque = array_filter($campanhas, function($c) { return $c['destaque'] == 1; });
                                            if (empty($campanhasDestaque)): ?>
                                                <p class="text-muted">Nenhuma campanha em destaque</p>
                                            <?php else: ?>
                                                <?php foreach (array_slice($campanhasDestaque, 0, 3) as $camp): ?>
                                                    <div class="d-flex align-items-center mb-3">
                                                        <div class="flex-shrink-0">
                                                            <i class="fas fa-star text-warning"></i>
                                                        </div>
                                                        <div class="flex-grow-1 ms-3">
                                                            <h6 class="mb-0"><?= htmlspecialchars($camp['titulo']) ?></h6>
                                                            <small class="text-muted">R$ <?= number_format($camp['arrecadado'], 2, ',', '.') ?> arrecadados</small>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">
                                                <i class="fas fa-clock"></i> Últimas Atividades
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <?php 
                                            $ultimasAtividades = array_merge(
                                                array_map(function($c) { return ['tipo' => 'campanha', 'data' => $c['criado_em'], 'titulo' => $c['titulo']]; }, array_slice($campanhas, 0, 2)),
                                                array_map(function($d) { return ['tipo' => 'doacao', 'data' => $d['criado_em'], 'titulo' => $d['campanha_titulo']]; }, array_slice($doacoes, 0, 2))
                                            );
                                            usort($ultimasAtividades, function($a, $b) { return strtotime($b['data']) - strtotime($a['data']); });
                                            ?>
                                            <?php foreach (array_slice($ultimasAtividades, 0, 4) as $atividade): ?>
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="flex-shrink-0">
                                                        <i class="fas fa-<?= $atividade['tipo'] === 'campanha' ? 'hand-holding-heart' : 'heart' ?> text-primary"></i>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <h6 class="mb-0"><?= htmlspecialchars($atividade['titulo']) ?></h6>
                                                        <small class="text-muted"><?= date('d/m/Y H:i', strtotime($atividade['data'])) ?></small>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Minhas Campanhas -->
                        <div class="tab-pane fade" id="campanhas">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h2 class="mb-0">Minhas Campanhas</h2>
                                <div>
                                    <a href="nova_campanha_usuario.php" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Nova Campanha
                                    </a>
                                    <button class="btn btn-outline-secondary ms-2" data-bs-toggle="modal" data-bs-target="#modalSugerirCategoria">
                                        <i class="fas fa-tag"></i> Sugerir Nova Categoria
                                    </button>
                                </div>
                            </div>
                            
                            <?php if (empty($campanhas)): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-hand-holding-heart fa-3x text-muted mb-3"></i>
                                    <h3>Nenhuma campanha criada</h3>
                                    <p class="text-muted">Comece criando sua primeira campanha para ajudar uma causa.</p>
                                    <a href="nova_campanha_usuario.php" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Criar Campanha
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="row">
                                    <?php foreach ($campanhas as $campanha): ?>
                                        <div class="col-lg-6 col-md-12 mb-4">
                                            <div class="card campanha-card h-100">
                                                <?php if ($campanha['imagem']): ?>
                                                    <img src="<?= htmlspecialchars($campanha['imagem']) ?>" class="card-img-top" alt="<?= htmlspecialchars($campanha['titulo']) ?>">
                                                <?php else: ?>
                                                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center">
                                                        <i class="fas fa-image fa-3x text-muted"></i>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <div class="card-body d-flex flex-column">
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <span class="badge bg-<?= $campanha['status'] === 'aprovada' ? 'success' : ($campanha['status'] === 'pendente' ? 'warning' : 'secondary') ?>">
                                                            <?= ucfirst($campanha['status']) ?>
                                                        </span>
                                                        <?php if ($campanha['destaque']): ?>
                                                            <span class="badge bg-warning">
                                                                <i class="fas fa-star"></i> Destaque
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>
                                                    
                                                    <h5 class="card-title"><?= htmlspecialchars($campanha['titulo']) ?></h5>
                                                    <p class="card-text text-muted">
                                                        <?= htmlspecialchars(substr($campanha['descricao'], 0, 100)) ?>...
                                                    </p>
                                                    
                                                    <div class="mt-auto">
                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                            <span class="fw-bold text-primary">R$ <?= number_format($campanha['arrecadado'], 2, ',', '.') ?></span>
                                                            <span class="text-muted">de R$ <?= number_format($campanha['meta'], 2, ',', '.') ?></span>
                                                        </div>
                                                        
                                                        <div class="progress mb-3">
                                                            <?php $progresso = ($campanha['meta'] > 0) ? ($campanha['arrecadado'] / $campanha['meta']) * 100 : 0; ?>
                                                            <div class="progress-bar" style="width: <?= min($progresso, 100) ?>%"></div>
                                                        </div>
                                                        
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <small class="text-muted">
                                                                <i class="fas fa-tag"></i> <?= htmlspecialchars($campanha['categoria_nome'] ?: 'Sem categoria') ?>
                                                            </small>
                                                            <a href="detalhes_campanha.php?id=<?= $campanha['id'] ?>" class="btn btn-primary btn-sm">
                                                                <i class="fas fa-eye"></i> Ver
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Minhas Doações -->
                        <div class="tab-pane fade" id="doacoes">
                            <h2 class="mb-4">Minhas Doações</h2>
                            <form method="get" class="row g-3 mb-3" id="filtroDoacoesUsuario">
                                <div class="col-md-3">
                                    <select name="status" class="form-select" onchange="this.form.submit()">
                                        <option value="">Todos os Status</option>
                                        <option value="pendente" <?= isset($_GET['status']) && $_GET['status']==='pendente' ? 'selected' : '' ?>>Pendente</option>
                                        <option value="confirmada" <?= isset($_GET['status']) && $_GET['status']==='confirmada' ? 'selected' : '' ?>>Confirmada</option>
                                        <option value="cancelada" <?= isset($_GET['status']) && $_GET['status']==='cancelada' ? 'selected' : '' ?>>Cancelada</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <input type="text" name="campanha" class="form-control" placeholder="Buscar campanha..." value="<?= htmlspecialchars($_GET['campanha'] ?? '') ?>" onchange="this.form.submit()">
                                </div>
                            </form>
                            <?php
                            $doacoesFiltradas = $doacoes;
                            if (isset($_GET['status']) && $_GET['status']) {
                                $doacoesFiltradas = array_filter($doacoesFiltradas, function($d) { return $d['status'] === $_GET['status']; });
                            }
                            if (isset($_GET['campanha']) && $_GET['campanha']) {
                                $doacoesFiltradas = array_filter($doacoesFiltradas, function($d) { return stripos($d['campanha_titulo'], $_GET['campanha']) !== false; });
                            }
                            ?>
                            <?php if (empty($doacoesFiltradas)): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-heart fa-3x text-muted mb-3"></i>
                                    <h3>Nenhuma doação encontrada</h3>
                                    <p class="text-muted">Altere os filtros ou explore campanhas para doar.</p>
                                    <a href="index.php" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Ver Campanhas
                                    </a>
                                </div>
                            <?php else: ?>
                            <div class="card">
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Campanha</th>
                                                    <th>Valor</th>
                                                    <th>Status</th>
                                                    <th>Data</th>
                                                    <th>Comprovante</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($doacoesFiltradas as $doacao): ?>
                                                <tr>
                                                    <td><strong><?= htmlspecialchars($doacao['campanha_titulo']) ?></strong></td>
                                                    <td>R$ <?= number_format($doacao['valor'], 2, ',', '.') ?></td>
                                                    <td>
                                                        <span class="badge bg-<?= $doacao['status'] === 'confirmada' ? 'success' : ($doacao['status'] === 'pendente' ? 'warning' : 'secondary') ?>">
                                                            <?= ucfirst($doacao['status']) ?>
                                                        </span>
                                                    </td>
                                                    <td><?= date('d/m/Y H:i', strtotime($doacao['criado_em'])) ?></td>
                                                    <td>
                                                        <?php if ($doacao['comprovante']): ?>
                                                            <a href="<?= htmlspecialchars($doacao['comprovante']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                                <i class="fas fa-file"></i> Ver
                                                            </a>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Configurações -->
                        <div class="tab-pane fade" id="perfil">
                            <h2 class="mb-4">Configurações da Conta</h2>
                            
                            <div class="row">
                                <div class="col-lg-8">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">
                                                <i class="fas fa-user-edit"></i> Editar Perfil
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <form action="editar_perfil.php" method="post" enctype="multipart/form-data">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group mb-3">
                                                            <label>Nome Completo</label>
                                                            <input type="text" name="nome" class="form-control" value="<?= htmlspecialchars($usuario['nome']) ?>" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group mb-3">
                                                            <label>E-mail</label>
                                                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($usuario['email']) ?>" required>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="form-group mb-3">
                                                    <label>Foto de Perfil</label>
                                                    <input type="file" name="foto_perfil" class="form-control" accept="image/*">
                                                    <small class="text-muted">Formatos: JPG, PNG, GIF. Máx: 2MB.</small>
                                                </div>
                                                
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fas fa-save"></i> Salvar Alterações
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                    
                                    <div class="card mt-4">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">
                                                <i class="fas fa-lock"></i> Alterar Senha
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <form action="alterar_senha.php" method="post">
                                                <div class="form-group mb-3">
                                                    <label>Senha Atual</label>
                                                    <input type="password" name="senha_atual" class="form-control" required>
                                                </div>
                                                
                                                <div class="form-group mb-3">
                                                    <label>Nova Senha</label>
                                                    <input type="password" name="nova_senha" class="form-control" required>
                                                </div>
                                                
                                                <div class="form-group mb-3">
                                                    <label>Confirmar Nova Senha</label>
                                                    <input type="password" name="confirmar_senha" class="form-control" required>
                                                </div>
                                                
                                                <button type="submit" class="btn btn-warning">
                                                    <i class="fas fa-key"></i> Alterar Senha
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-lg-4">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">
                                                <i class="fas fa-info-circle"></i> Informações da Conta
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <strong>Membro desde:</strong><br>
                                                <span class="text-muted"><?= date('d/m/Y', strtotime($usuario['criado_em'])) ?></span>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <strong>Status:</strong><br>
                                                <span class="badge bg-<?= $usuario['status'] === 'ativo' ? 'success' : 'secondary' ?>">
                                                    <?= ucfirst($usuario['status']) ?>
                                                </span>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <strong>Tipo de conta:</strong><br>
                                                <span class="badge bg-info"><?= ucfirst($usuario['tipo']) ?></span>
                                            </div>
                                            
                                            <hr>
                                            
                                            <div class="d-grid">
                                                <a href="logout.php" class="btn btn-outline-danger">
                                                    <i class="fas fa-sign-out-alt"></i> Sair da Conta
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        // Gráfico de pizza - Status das campanhas
        const campanhasCtx = document.getElementById('campanhasChart').getContext('2d');
        new Chart(campanhasCtx, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode(array_column($campanhasPorStatus, 'status')) ?>,
                datasets: [{
                    data: <?= json_encode(array_column($campanhasPorStatus, 'total')) ?>,
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

        // Gráfico de linha - Doações por mês
        const doacoesCtx = document.getElementById('doacoesChart').getContext('2d');
        new Chart(doacoesCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode(array_column($doacoesPorMes, 'mes')) ?>,
                datasets: [{
                    label: 'Valor Total (R$)',
                    data: <?= json_encode(array_column($doacoesPorMes, 'valor_total')) ?>,
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1
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

        // Navegação por tabs
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Remover classe active de todos os links
                document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
                
                // Adicionar classe active ao link clicado
                this.classList.add('active');
                
                // Mostrar tab correspondente
                const target = this.getAttribute('href').substring(1);
                document.querySelectorAll('.tab-pane').forEach(pane => {
                    pane.classList.remove('show', 'active');
                });
                document.getElementById(target).classList.add('show', 'active');
            });
        });
    </script>

    <!-- Modal Sugerir Categoria -->
    <div class="modal fade" id="modalSugerirCategoria" tabindex="-1" aria-labelledby="modalSugerirCategoriaLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <form id="formSugerirCategoria" enctype="multipart/form-data">
            <div class="modal-header">
              <h5 class="modal-title" id="modalSugerirCategoriaLabel"><i class="fas fa-tag"></i> Sugerir Nova Categoria</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
              <div class="mb-3">
                <label for="nome_categoria" class="form-label">Nome da Categoria *</label>
                <input type="text" class="form-control" id="nome_categoria" name="nome" required>
              </div>
              <div class="mb-3">
                <label for="descricao_categoria" class="form-label">Descrição</label>
                <textarea class="form-control" id="descricao_categoria" name="descricao" rows="3"></textarea>
              </div>
              <div class="mb-3">
                <label for="imagem_categoria" class="form-label">Imagem</label>
                <input type="file" class="form-control" id="imagem_categoria" name="imagem" accept="image/*">
                <small class="form-text text-muted">Formatos aceitos: JPG, PNG, GIF, WebP. Tamanho máximo: 2MB</small>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
              <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Enviar Sugestão</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <script>
    // Sugestão de categoria via AJAX
    const formSugerirCategoria = document.getElementById('formSugerirCategoria');
    formSugerirCategoria.addEventListener('submit', function(e) {
      e.preventDefault();
      const formData = new FormData(this);
      fetch('sugerir_categoria.php', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          alert('Sugestão enviada! Aguarde aprovação do administrador.');
          document.getElementById('modalSugerirCategoria').querySelector('.btn-close').click();
          formSugerirCategoria.reset();
        } else {
          alert(data.message || 'Erro ao enviar sugestão.');
        }
      })
      .catch(() => alert('Erro ao enviar sugestão.'));
    });
    </script>
</body>
</html> 