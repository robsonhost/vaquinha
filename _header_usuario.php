<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'admin/db.php';

// Verificar se está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

// Carregar configurações
$textos = [];
foreach ($pdo->query('SELECT chave, valor FROM textos')->fetchAll(PDO::FETCH_ASSOC) as $t) {
    $textos[$t['chave']] = $t['valor'];
}

// Carregar tema
$tema = $pdo->query('SELECT * FROM temas WHERE ativo = 1')->fetch(PDO::FETCH_ASSOC);
if (!$tema) {
    $tema = ['cor_primaria' => '#782F9B', 'cor_secundaria' => '#65A300', 'cor_terciaria' => '#F7F7F7'];
}

// Dados do usuário
$stmt = $pdo->prepare('SELECT * FROM usuarios WHERE id = ?');
$stmt->execute([$_SESSION['usuario_id']]);
$usuario_logado = $stmt->fetch(PDO::FETCH_ASSOC);

$nome_site = $textos['nome_site'] ?? 'Vaquinha Online';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Painel do Usuário' ?> - <?= htmlspecialchars($nome_site) ?></title>
    
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
        
        body {
            background-color: var(--cor-terciaria);
        }
        
        .navbar-custom {
            background: linear-gradient(135deg, var(--cor-primaria), var(--cor-secundaria));
        }
        
        .navbar-custom .navbar-brand,
        .navbar-custom .nav-link {
            color: white !important;
            font-weight: 500;
        }
        
        .navbar-custom .nav-link:hover {
            color: rgba(255, 255, 255, 0.8) !important;
        }
        
        .sidebar {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 0;
            overflow: hidden;
        }
        
        .sidebar .nav-link {
            color: #333;
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            transition: all 0.3s ease;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background-color: var(--cor-primaria);
            color: white;
        }
        
        .sidebar .nav-link i {
            width: 20px;
            margin-right: 10px;
        }
        
        .content-area {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #dc3545;
            color: white;
            border-radius: 50%;
            font-size: 10px;
            padding: 2px 6px;
            min-width: 18px;
            text-align: center;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-custom mb-4">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="index.php">
            <i class="fas fa-heart me-2"></i><?= htmlspecialchars($nome_site) ?>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <?php if ($usuario_logado['foto_perfil']): ?>
                            <img src="<?= htmlspecialchars($usuario_logado['foto_perfil']) ?>" alt="Avatar" class="user-avatar me-2">
                        <?php else: ?>
                            <i class="fas fa-user-circle me-2" style="font-size: 24px;"></i>
                        <?php endif; ?>
                        <?= htmlspecialchars($usuario_logado['nome']) ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="perfil.php"><i class="fas fa-user me-2"></i>Meu Perfil</a></li>
                        <li><a class="dropdown-item" href="notificacoes.php"><i class="fas fa-bell me-2"></i>Notificações</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="index.php"><i class="fas fa-home me-2"></i>Voltar ao Site</a></li>
                        <li><a class="dropdown-item" href="logout_usuario.php"><i class="fas fa-sign-out-alt me-2"></i>Sair</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-3 col-md-4 mb-4">
            <div class="sidebar">
                <nav class="nav flex-column">
                    <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'area_usuario.php') ? 'active' : '' ?>" href="area_usuario.php">
                        <i class="fas fa-tachometer-alt"></i>Dashboard
                    </a>
                    <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'minhas_campanhas.php') ? 'active' : '' ?>" href="minhas_campanhas.php">
                        <i class="fas fa-list"></i>Minhas Campanhas
                    </a>
                    <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'nova_campanha_usuario.php') ? 'active' : '' ?>" href="nova_campanha_usuario.php">
                        <i class="fas fa-plus-circle"></i>Nova Campanha
                    </a>
                    <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'minhas_doacoes.php') ? 'active' : '' ?>" href="minhas_doacoes.php">
                        <i class="fas fa-heart"></i>Minhas Doações
                    </a>
                    <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'perfil.php') ? 'active' : '' ?>" href="perfil.php">
                        <i class="fas fa-user-edit"></i>Editar Perfil
                    </a>
                    <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'notificacoes.php') ? 'active' : '' ?>" href="notificacoes.php">
                        <i class="fas fa-bell"></i>Notificações
                        <?php
                        // Contar notificações não lidas
                        $stmt = $pdo->prepare('
                            SELECT COUNT(*) FROM notificacoes 
                            WHERE (usuario_id = ? OR destinatario IN ("todos", "usuarios"))
                              AND lida = 0
                        ');
                        $stmt->execute([$_SESSION['usuario_id']]);
                        $notificacoes_nao_lidas = $stmt->fetchColumn();
                        if ($notificacoes_nao_lidas > 0): ?>
                            <span class="notification-badge"><?= $notificacoes_nao_lidas ?></span>
                        <?php endif; ?>
                    </a>
                </nav>
            </div>
        </div>
        
        <!-- Content Area -->
        <div class="col-lg-9 col-md-8">
            <div class="content-area">