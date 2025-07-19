<?php
// Carregar configurações do menu
$textos = [];
foreach ($pdo->query('SELECT * FROM textos')->fetchAll(PDO::FETCH_ASSOC) as $t) {
    $textos[$t['chave']] = $t['valor'];
}

$logo = $pdo->query('SELECT * FROM logo_site WHERE id=1')->fetch(PDO::FETCH_ASSOC);
?>
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <?php if ($logo && $logo['caminho']): ?>
                <img src="<?= htmlspecialchars($logo['caminho']) ?>" alt="<?= htmlspecialchars($textos['nome_site'] ?? 'Vaquinha Online') ?>" class="navbar-logo">
            <?php else: ?>
                <i class="fas fa-heart text-primary"></i>
                <span class="fw-bold"><?= htmlspecialchars($textos['nome_site'] ?? 'Vaquinha Online') ?></span>
            <?php endif; ?>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index.php#campanhas">
                        <i class="fas fa-hand-holding-heart"></i> Vaquinhas
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php#como-funciona">
                        <i class="fas fa-question-circle"></i> Como Funciona
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php#categorias">
                        <i class="fas fa-tags"></i> Categorias
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php#sobre">
                        <i class="fas fa-info-circle"></i> Sobre
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php#contato">
                        <i class="fas fa-envelope"></i> Contato
                    </a>
                </li>
            </ul>
            
            <div class="d-flex align-items-center">
                <?php if (isset($_SESSION['usuario_id'])): ?>
                    <div class="dropdown me-2">
                        <a class="btn btn-outline-primary dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle"></i> Minha Conta
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="area_usuario.php"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
                            <li><a class="dropdown-item" href="nova_campanha_usuario.php"><i class="fas fa-plus me-2"></i>Nova Campanha</a></li>
                            <li><a class="dropdown-item" href="area_usuario.php#perfil"><i class="fas fa-user-edit me-2"></i>Editar Perfil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Sair</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline-primary me-2">
                        <i class="fas fa-sign-in-alt"></i> Entrar
                    </a>
                    <a href="cadastro.php" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i> Criar Conta
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<style>
/* Correções para menu mobile */
.navbar-logo {
    max-height: 40px;
    width: auto;
}

@media (max-width: 991.98px) {
    .navbar-collapse {
        background: white;
        border-radius: 10px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        margin-top: 1rem;
        padding: 1rem;
    }
    
    .navbar-nav {
        margin-bottom: 1rem;
    }
    
    .navbar-nav .nav-link {
        padding: 0.75rem 1rem;
        border-radius: 8px;
        margin-bottom: 0.5rem;
        transition: all 0.3s ease;
    }
    
    .navbar-nav .nav-link:hover {
        background-color: var(--cor-primaria);
        color: white !important;
    }
    
    .d-flex.align-items-center {
        flex-direction: column;
        width: 100%;
        gap: 0.5rem;
    }
    
    .d-flex.align-items-center .btn {
        width: 100%;
        margin: 0;
    }
    
    .dropdown-menu {
        position: static !important;
        transform: none !important;
        width: 100%;
        margin-top: 0.5rem;
        box-shadow: none;
        border: 1px solid #dee2e6;
    }
    
    .dropdown-item {
        padding: 0.75rem 1rem;
    }
}

/* Melhorar visibilidade do botão toggle */
.navbar-toggler {
    border: 2px solid var(--cor-primaria);
    padding: 0.5rem;
}

.navbar-toggler:focus {
    box-shadow: 0 0 0 0.2rem rgba(var(--cor-primaria-rgb), 0.25);
}

.navbar-toggler-icon {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(120, 47, 155, 1)' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
}
</style> 