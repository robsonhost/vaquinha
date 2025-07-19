<?php
require 'admin/db.php';
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$page_title = 'Minhas Campanhas';

// Filtros
$status_filter = $_GET['status'] ?? '';
$search_filter = $_GET['search'] ?? '';
$categoria_filter = $_GET['categoria'] ?? '';

// Paginação
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 12;
$offset = ($page - 1) * $per_page;

// Construir query com filtros
$where_conditions = ['c.usuario_id = ?'];
$params = [$usuario_id];

if ($status_filter) {
    $where_conditions[] = 'c.status = ?';
    $params[] = $status_filter;
}

if ($search_filter) {
    $where_conditions[] = '(c.titulo LIKE ? OR c.descricao LIKE ?)';
    $params[] = "%{$search_filter}%";
    $params[] = "%{$search_filter}%";
}

if ($categoria_filter) {
    $where_conditions[] = 'c.categoria_id = ?';
    $params[] = $categoria_filter;
}

$where_clause = implode(' AND ', $where_conditions);

// Contar total de campanhas
$stmt = $pdo->prepare("SELECT COUNT(*) FROM campanhas c WHERE {$where_clause}");
$stmt->execute($params);
$total_campanhas = $stmt->fetchColumn();
$total_pages = ceil($total_campanhas / $per_page);

// Buscar campanhas
$stmt = $pdo->prepare("
    SELECT c.*, cat.nome as categoria_nome,
           (c.arrecadado / c.meta * 100) as percentual_meta,
           (SELECT COUNT(*) FROM doacoes d WHERE d.campanha_id = c.id AND d.status = 'confirmada') as total_doacoes
    FROM campanhas c 
    LEFT JOIN categorias cat ON c.categoria_id = cat.id 
    WHERE {$where_clause}
    ORDER BY c.criado_em DESC 
    LIMIT {$per_page} OFFSET {$offset}
");
$stmt->execute($params);
$campanhas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar categorias para filtro
$categorias = $pdo->query('SELECT * FROM categorias WHERE ativo = 1 ORDER BY nome')->fetchAll(PDO::FETCH_ASSOC);

include '_header_usuario.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-bullhorn me-2"></i>Minhas Campanhas</h2>
    <a href="nova_campanha_usuario.php" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Nova Campanha
    </a>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">Todos os Status</option>
                    <option value="pendente" <?= $status_filter === 'pendente' ? 'selected' : '' ?>>Pendente</option>
                    <option value="aprovada" <?= $status_filter === 'aprovada' ? 'selected' : '' ?>>Aprovada</option>
                    <option value="rejeitada" <?= $status_filter === 'rejeitada' ? 'selected' : '' ?>>Rejeitada</option>
                    <option value="finalizada" <?= $status_filter === 'finalizada' ? 'selected' : '' ?>>Finalizada</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Categoria</label>
                <select name="categoria" class="form-select">
                    <option value="">Todas as Categorias</option>
                    <?php foreach ($categorias as $categoria): ?>
                        <option value="<?= $categoria['id'] ?>" <?= $categoria_filter == $categoria['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($categoria['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Pesquisar</label>
                <input type="text" name="search" class="form-control" 
                       placeholder="Digite o título da campanha..." 
                       value="<?= htmlspecialchars($search_filter) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i>
                    </button>
                    <a href="minhas_campanhas.php" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Resumo -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <p class="text-muted mb-0">
        <strong><?= $total_campanhas ?></strong> campanhas encontradas
    </p>
</div>

<?php if (empty($campanhas)): ?>
    <div class="text-center py-5">
        <i class="fas fa-bullhorn fa-3x text-muted mb-3"></i>
        <h4>Nenhuma campanha encontrada</h4>
        <p class="text-muted">
            <?php if ($status_filter || $search_filter || $categoria_filter): ?>
                Tente alterar os filtros ou criar uma nova campanha.
            <?php else: ?>
                Você ainda não criou nenhuma campanha. Que tal começar agora?
            <?php endif; ?>
        </p>
        <div class="d-flex gap-2 justify-content-center">
            <a href="nova_campanha_usuario.php" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Criar Primeira Campanha
            </a>
            <?php if ($status_filter || $search_filter || $categoria_filter): ?>
                <a href="minhas_campanhas.php" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-2"></i>Limpar Filtros
                </a>
            <?php endif; ?>
        </div>
    </div>
<?php else: ?>
    <!-- Lista de Campanhas -->
    <div class="row">
        <?php foreach ($campanhas as $campanha): ?>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card h-100 shadow-sm">
                    <?php if ($campanha['imagem']): ?>
                        <img src="<?= htmlspecialchars($campanha['imagem']) ?>" 
                             alt="<?= htmlspecialchars($campanha['titulo']) ?>" 
                             class="card-img-top" 
                             style="height: 200px; object-fit: cover;">
                    <?php else: ?>
                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                            <i class="fas fa-image fa-3x text-muted"></i>
                        </div>
                    <?php endif; ?>
                    
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <?php
                            $status_class = match($campanha['status']) {
                                'aprovada' => 'success',
                                'pendente' => 'warning',
                                'rejeitada' => 'danger',
                                'finalizada' => 'secondary',
                                default => 'info'
                            };
                            $status_text = ucfirst($campanha['status']);
                            ?>
                            <span class="badge bg-<?= $status_class ?>">
                                <?= $status_text ?>
                            </span>
                            
                            <?php if ($campanha['categoria_nome']): ?>
                                <small class="text-muted"><?= htmlspecialchars($campanha['categoria_nome']) ?></small>
                            <?php endif; ?>
                        </div>
                        
                        <h5 class="card-title"><?= htmlspecialchars($campanha['titulo']) ?></h5>
                        <p class="card-text text-muted flex-grow-1">
                            <?= htmlspecialchars(substr($campanha['descricao'], 0, 100)) ?>...
                        </p>
                        
                        <!-- Progresso -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold text-success">
                                    R$ <?= number_format($campanha['arrecadado'], 2, ',', '.') ?>
                                </span>
                                <span class="text-muted">
                                    de R$ <?= number_format($campanha['meta'], 2, ',', '.') ?>
                                </span>
                            </div>
                            
                            <div class="progress mb-2" style="height: 8px;">
                                <div class="progress-bar bg-success" 
                                     style="width: <?= min(100, $campanha['percentual_meta']) ?>%"></div>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <small class="text-muted">
                                    <?= number_format($campanha['percentual_meta'], 1) ?>% da meta
                                </small>
                                <small class="text-muted">
                                    <?= $campanha['total_doacoes'] ?> doação(ões)
                                </small>
                            </div>
                        </div>
                        
                        <!-- Ações -->
                        <div class="d-flex justify-content-between align-items-center mt-auto">
                            <small class="text-muted">
                                <i class="fas fa-calendar me-1"></i>
                                <?= date('d/m/Y', strtotime($campanha['criado_em'])) ?>
                            </small>
                            
                            <div class="btn-group btn-group-sm">
                                <a href="campanha.php?id=<?= $campanha['id'] ?>" 
                                   class="btn btn-outline-primary" title="Ver Campanha">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <?php if ($campanha['status'] === 'pendente' || $campanha['status'] === 'aprovada'): ?>
                                    <a href="editar_campanha_usuario.php?id=<?= $campanha['id'] ?>" 
                                       class="btn btn-outline-secondary" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                <?php endif; ?>
                                <button type="button" 
                                        class="btn btn-outline-success" 
                                        onclick="compartilharCampanha(<?= $campanha['id'] ?>)"
                                        title="Compartilhar">
                                    <i class="fas fa-share"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Paginação -->
    <?php if ($total_pages > 1): ?>
        <nav aria-label="Navegação das campanhas">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page-1 ?>&status=<?= urlencode($status_filter) ?>&search=<?= urlencode($search_filter) ?>&categoria=<?= urlencode($categoria_filter) ?>">
                            <i class="fas fa-chevron-left"></i> Anterior
                        </a>
                    </li>
                <?php endif; ?>
                
                <?php for ($i = max(1, $page-2); $i <= min($total_pages, $page+2); $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&status=<?= urlencode($status_filter) ?>&search=<?= urlencode($search_filter) ?>&categoria=<?= urlencode($categoria_filter) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page+1 ?>&status=<?= urlencode($status_filter) ?>&search=<?= urlencode($search_filter) ?>&categoria=<?= urlencode($categoria_filter) ?>">
                            Próxima <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>
<?php endif; ?>

<script>
function compartilharCampanha(campanhaId) {
    const url = `${window.location.origin}/campanha.php?id=${campanhaId}`;
    
    if (navigator.share) {
        navigator.share({
            title: 'Apoie esta campanha',
            text: 'Confira esta campanha e ajude a fazer a diferença!',
            url: url
        });
    } else {
        // Fallback para copiar URL
        navigator.clipboard.writeText(url).then(() => {
            showToast('Link copiado para a área de transferência!', 'success');
        }).catch(() => {
            // Fallback manual
            const textArea = document.createElement('textarea');
            textArea.value = url;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            showToast('Link copiado para a área de transferência!', 'success');
        });
    }
}
</script>

<?php include '_footer_usuario.php'; ?>