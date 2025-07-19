<section class="categorias-section py-5 bg-light" id="categorias">
    <div class="container">
        <div class="row text-center mb-5">
            <div class="col-12">
                <h2 class="display-5 fw-bold text-primary mb-3">Categorias de Campanhas</h2>
                <p class="lead text-muted">Encontre campanhas por categoria e ajude causas que fazem sentido para você</p>
            </div>
        </div>
        
        <div class="row">
            <?php
            // Carregar categorias com contagem de campanhas
            $categoriasComContagem = $pdo->query('
                SELECT c.*, COUNT(camp.id) as total_campanhas 
                FROM categorias c 
                LEFT JOIN campanhas camp ON c.id = camp.categoria_id AND camp.status = "aprovada"
                GROUP BY c.id 
                ORDER BY total_campanhas DESC, c.nome
            ')->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($categoriasComContagem as $categoria):
            ?>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="categoria-card">
                    <div class="categoria-imagem">
                        <?php if ($categoria['imagem']): ?>
                            <img src="<?= htmlspecialchars($categoria['imagem']) ?>" 
                                 alt="<?= htmlspecialchars($categoria['nome']) ?>" 
                                 class="img-fluid">
                        <?php else: ?>
                            <div class="categoria-placeholder">
                                <i class="fas fa-heart"></i>
                            </div>
                        <?php endif; ?>
                        <div class="categoria-overlay">
                            <a href="index.php?categoria=<?= $categoria['id'] ?>" class="btn btn-light">
                                <i class="fas fa-eye"></i> Ver Campanhas
                            </a>
                        </div>
                    </div>
                    <div class="categoria-info">
                        <h5 class="fw-bold mb-2"><?= htmlspecialchars($categoria['nome']) ?></h5>
                        <p class="text-muted mb-2"><?= htmlspecialchars($categoria['descricao'] ?: 'Campanhas nesta categoria') ?></p>
                        <div class="categoria-stats">
                            <span class="badge bg-primary">
                                <i class="fas fa-hand-holding-heart"></i> 
                                <?= $categoria['total_campanhas'] ?> campanhas
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- CTA para Criar Campanha -->
        <div class="row mt-5">
            <div class="col-12 text-center">
                <div class="categoria-cta p-5 bg-gradient-primary text-white rounded-3">
                    <h3 class="fw-bold mb-3">Não encontrou sua categoria?</h3>
                    <p class="lead mb-4">Crie uma campanha personalizada e ajude sua causa</p>
                    <a href="nova_campanha_usuario.php" class="btn btn-light btn-lg">
                        <i class="fas fa-plus"></i> Criar Campanha
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.categoria-card {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    border: 1px solid rgba(0,0,0,0.05);
}

.categoria-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.15);
}

.categoria-imagem {
    position: relative;
    height: 200px;
    overflow: hidden;
}

.categoria-imagem img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.categoria-card:hover .categoria-imagem img {
    transform: scale(1.1);
}

.categoria-placeholder {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, var(--cor-primaria), var(--cor-secundaria));
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 3rem;
}

.categoria-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.categoria-card:hover .categoria-overlay {
    opacity: 1;
}

.categoria-info {
    padding: 1.5rem;
}

.categoria-stats {
    margin-top: 1rem;
}

.categoria-cta {
    background: linear-gradient(135deg, var(--cor-primaria), var(--cor-secundaria)) !important;
}

@media (max-width: 768px) {
    .categoria-card {
        margin-bottom: 2rem;
    }
    
    .categoria-imagem {
        height: 150px;
    }
}
</style> 