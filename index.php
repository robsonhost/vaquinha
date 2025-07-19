<?php
require 'admin/db.php';

// Carregar configurações
$textos = [];
foreach ($pdo->query('SELECT * FROM textos')->fetchAll(PDO::FETCH_ASSOC) as $t) {
    $textos[$t['chave']] = $t['valor'];
}

// Carregar tema ativo
$tema = $pdo->query('SELECT * FROM temas WHERE ativo = 1')->fetch(PDO::FETCH_ASSOC);
if (!$tema) {
    $tema = ['cor_primaria' => '#782F9B', 'cor_secundaria' => '#65A300', 'cor_terciaria' => '#F7F7F7'];
}

// Carregar logo
$logo = $pdo->query('SELECT * FROM logo_site WHERE id=1')->fetch(PDO::FETCH_ASSOC);

// Filtros
$categoria_id = $_GET['categoria'] ?? '';
$busca = trim($_GET['busca'] ?? '');
$ordenacao = $_GET['ordenacao'] ?? 'recentes';

// Query base
$sql = 'SELECT c.*, u.nome as criador, cat.nome as categoria_nome, cat.imagem as categoria_imagem 
        FROM campanhas c 
        LEFT JOIN usuarios u ON c.usuario_id = u.id 
        LEFT JOIN categorias cat ON c.categoria_id = cat.id 
        WHERE c.status = "aprovada"';

$params = [];

if ($categoria_id) {
    $sql .= ' AND c.categoria_id = ?';
    $params[] = $categoria_id;
}

if ($busca) {
    $sql .= ' AND (c.titulo LIKE ? OR c.descricao LIKE ?)';
    $params[] = "%$busca%";
    $params[] = "%$busca%";
}

// Ordenação
switch ($ordenacao) {
    case 'recentes':
        $sql .= ' ORDER BY c.criado_em DESC';
        break;
    case 'antigas':
        $sql .= ' ORDER BY c.criado_em ASC';
        break;
    case 'mais_arrecadado':
        $sql .= ' ORDER BY c.arrecadado DESC';
        break;
    case 'menos_arrecadado':
        $sql .= ' ORDER BY c.arrecadado ASC';
        break;
    case 'destaque':
        $sql .= ' ORDER BY c.destaque DESC, c.criado_em DESC';
        break;
    default:
        $sql .= ' ORDER BY c.destaque DESC, c.criado_em DESC';
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$campanhas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Carregar categorias para filtro
$categorias = $pdo->query('SELECT * FROM categorias ORDER BY nome')->fetchAll(PDO::FETCH_ASSOC);

// Estatísticas
$totalCampanhas = $pdo->query('SELECT COUNT(*) FROM campanhas WHERE status="aprovada"')->fetchColumn();
$totalArrecadado = $pdo->query('SELECT SUM(arrecadado) FROM campanhas WHERE status="aprovada"')->fetchColumn() ?: 0;
$totalDoacoes = $pdo->query('SELECT COUNT(*) FROM doacoes WHERE status="confirmada"')->fetchColumn();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <?php include 'includes/seo-head.php'; ?>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Responsividade -->
    <link rel="stylesheet" href="css/responsividade.css">
    
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
        
        .btn-secondary {
            background-color: var(--cor-secundaria) !important;
            border-color: var(--cor-secundaria) !important;
        }
        
        .text-primary {
            color: var(--cor-primaria) !important;
        }
        
        .text-secondary {
            color: var(--cor-secundaria) !important;
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
        
        .hero-section {
            background: linear-gradient(135deg, var(--cor-primaria), var(--cor-secundaria));
            color: white;
            padding: 100px 0;
            position: relative;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100" fill="rgba(255,255,255,0.1)"><polygon points="0,100 1000,0 1000,100"/></svg>');
            background-size: cover;
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
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
        
        .campanha-card .card-body {
            padding: 1.5rem;
        }
        
        .progress {
            height: 8px;
            border-radius: 10px;
        }
        
        .stats-card {
            background: linear-gradient(135deg, var(--cor-primaria), var(--cor-secundaria));
            color: white;
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
        }
        
        .stats-card i {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .categoria-badge {
            background-color: var(--cor-terciaria);
            color: var(--cor-primaria);
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .destaque-badge {
            background: linear-gradient(45deg, #ff6b6b, #ffa500);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .filtros-section {
            background-color: var(--cor-terciaria);
            padding: 2rem 0;
            border-radius: 15px;
            margin: 2rem 0;
        }
        
        .footer {
            background-color: #2c3e50;
            color: white;
            padding: 3rem 0 1rem;
        }
        
        .social-links a {
            color: white;
            font-size: 1.5rem;
            margin: 0 0.5rem;
            transition: color 0.3s ease;
        }
        
        .social-links a:hover {
            color: var(--cor-secundaria);
        }
        
        @media (max-width: 768px) {
            .hero-section {
                padding: 60px 0;
            }
            
            .stats-card {
                margin-bottom: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Menu Dinâmico -->
    <?php include 'includes/menu.php'; ?>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center hero-content">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold mb-4">
                        Faça a diferença na vida de alguém
                    </h1>
                    <p class="lead mb-4">
                        Junte-se a milhares de pessoas que já ajudaram causas importantes através das nossas vaquinhas online.
                    </p>
                    <div class="d-flex gap-3">
                        <a href="#campanhas" class="btn btn-light btn-lg">
                            <i class="fas fa-heart"></i> Ver Campanhas
                        </a>
                        <a href="nova_campanha_usuario.php" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-plus"></i> Criar Campanha
                        </a>
                    </div>
                </div>
                <div class="col-lg-6 text-center">
                    <i class="fas fa-hands-helping" style="font-size: 8rem; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </section>

    <!-- Estatísticas -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <div class="stats-card">
                        <i class="fas fa-hand-holding-heart"></i>
                        <h3 class="display-6 fw-bold"><?= number_format($totalCampanhas, 0, ',', '.') ?></h3>
                        <p class="mb-0">Campanhas Ativas</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stats-card">
                        <i class="fas fa-dollar-sign"></i>
                        <h3 class="display-6 fw-bold">R$ <?= number_format($totalArrecadado, 0, ',', '.') ?></h3>
                        <p class="mb-0">Total Arrecadado</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stats-card">
                        <i class="fas fa-heart"></i>
                        <h3 class="display-6 fw-bold"><?= number_format($totalDoacoes, 0, ',', '.') ?></h3>
                        <p class="mb-0">Doações Realizadas</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Filtros -->
    <section class="filtros-section" id="campanhas">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <h4 class="mb-0 text-primary">
                        <i class="fas fa-filter"></i> Filtrar Campanhas
                    </h4>
                </div>
                <div class="col-md-8">
                    <form method="get" class="row g-3">
                        <div class="col-md-4">
                            <input type="text" name="busca" class="form-control" placeholder="Buscar campanhas..." value="<?= htmlspecialchars($busca) ?>">
                        </div>
                        <div class="col-md-3">
                            <select name="categoria" class="form-select">
                                <option value="">Todas as categorias</option>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= $categoria_id == $cat['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="ordenacao" class="form-select">
                                <option value="destaque" <?= $ordenacao === 'destaque' ? 'selected' : '' ?>>Destaque</option>
                                <option value="recentes" <?= $ordenacao === 'recentes' ? 'selected' : '' ?>>Mais Recentes</option>
                                <option value="mais_arrecadado" <?= $ordenacao === 'mais_arrecadado' ? 'selected' : '' ?>>Mais Arrecadado</option>
                                <option value="menos_arrecadado" <?= $ordenacao === 'menos_arrecadado' ? 'selected' : '' ?>>Menos Arrecadado</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Campanhas -->
    <section class="py-5">
        <div class="container">
            <?php if (empty($campanhas)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                    <h3>Nenhuma campanha encontrada</h3>
                    <p class="text-muted">Tente ajustar os filtros ou criar uma nova campanha.</p>
                    <a href="nova_campanha_usuario.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Criar Campanha
                    </a>
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($campanhas as $campanha): ?>
                        <div class="col-lg-4 col-md-6 mb-4">
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
                                        <span class="categoria-badge">
                                            <?php if ($campanha['categoria_imagem']): ?>
                                                <img src="<?= htmlspecialchars($campanha['categoria_imagem']) ?>" alt="" style="width: 16px; height: 16px;">
                                            <?php endif; ?>
                                            <?= htmlspecialchars($campanha['categoria_nome']) ?>
                                        </span>
                                        <?php if ($campanha['destaque']): ?>
                                            <span class="destaque-badge">
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
                                                <i class="fas fa-user"></i> <?= htmlspecialchars($campanha['criador'] ?: 'Anônimo') ?>
                                            </small>
                                            <a href="detalhes_campanha.php?id=<?= $campanha['id'] ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-heart"></i> Doar
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
    </section>

    <!-- Seção de Vantagens -->
    <?php include 'includes/vantagens.php'; ?>

    <!-- Seção Como Funciona -->
    <?php include 'includes/como-funciona.php'; ?>

    <!-- Seção de Categorias -->
    <?php include 'includes/categorias.php'; ?>

    <!-- Sobre -->
    <section class="py-5 bg-light" id="sobre">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h2 class="display-5 fw-bold mb-4">Sobre Nós</h2>
                    <p class="lead mb-4">
                        <?= htmlspecialchars($textos['quem_somos'] ?? 'Somos uma plataforma dedicada a conectar pessoas que precisam de ajuda com aquelas que querem ajudar. Nossa missão é facilitar a solidariedade e tornar o mundo um lugar melhor.') ?>
                    </p>
                    <div class="row">
                        <div class="col-6">
                            <div class="text-center">
                                <i class="fas fa-shield-alt fa-2x text-primary mb-2"></i>
                                <h5>Segurança</h5>
                                <p class="text-muted">Todas as transações são seguras</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center">
                                <i class="fas fa-handshake fa-2x text-primary mb-2"></i>
                                <h5>Transparência</h5>
                                <p class="text-muted">Acompanhe cada doação</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 text-center">
                    <i class="fas fa-users fa-8x text-primary opacity-25"></i>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer Moderno -->
    <?php include 'includes/footer.php'; ?>
    
    <!-- WhatsApp Flutuante -->
    <?php include 'includes/whatsapp-float.php'; ?>
    
    <!-- Seletor de Tema -->
    <?php include 'includes/tema-selector.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Tema Dinâmico -->
    <script src="js/tema-dinamico.js"></script>
    
    <!-- Acessibilidade e Feedback Visual -->
    <script src="js/acessibilidade.js"></script>
    
    <script>
        // Smooth scroll para links internos
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
        
        // Animação de entrada para cards
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);
        
        document.querySelectorAll('.campanha-card').forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(card);
        });
        
        // Aguardar carregamento do tema dinâmico
        document.addEventListener('DOMContentLoaded', function() {
            // Aguardar um pouco para o tema ser aplicado
            setTimeout(() => {
                // Reaplicar estilos após tema ser carregado
                if (window.temaDinamico && window.temaDinamico.tema) {
                    console.log('Tema dinâmico carregado:', window.temaDinamico.tema.nome);
                }
            }, 100);
        });
    </script>
</body>
</html> 