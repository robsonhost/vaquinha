<?php
require 'admin/db.php';
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$page_title = 'Minhas Doações';

// Filtros
$status_filter = $_GET['status'] ?? '';
$search_filter = $_GET['search'] ?? '';
$data_filter = $_GET['data'] ?? '';

// Paginação
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Construir query com filtros
$where_conditions = ['d.usuario_id = ?'];
$params = [$usuario_id];

if ($status_filter) {
    $where_conditions[] = 'd.status = ?';
    $params[] = $status_filter;
}

if ($search_filter) {
    $where_conditions[] = 'c.titulo LIKE ?';
    $params[] = "%{$search_filter}%";
}

if ($data_filter) {
    $where_conditions[] = 'DATE(d.criado_em) = ?';
    $params[] = $data_filter;
}

$where_clause = implode(' AND ', $where_conditions);

// Contar total de doações
$stmt = $pdo->prepare("SELECT COUNT(*) FROM doacoes d LEFT JOIN campanhas c ON d.campanha_id = c.id WHERE {$where_clause}");
$stmt->execute($params);
$total_doacoes = $stmt->fetchColumn();
$total_pages = ceil($total_doacoes / $per_page);

// Buscar doações
$stmt = $pdo->prepare("
    SELECT d.*, c.titulo as campanha_titulo, c.imagem as campanha_imagem, c.usuario_id as campanha_usuario_id,
           u.nome as campanha_criador
    FROM doacoes d 
    LEFT JOIN campanhas c ON d.campanha_id = c.id 
    LEFT JOIN usuarios u ON c.usuario_id = u.id
    WHERE {$where_clause}
    ORDER BY d.criado_em DESC 
    LIMIT {$per_page} OFFSET {$offset}
");
$stmt->execute($params);
$doacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Estatísticas
$stmt = $pdo->prepare('SELECT COUNT(*), COALESCE(SUM(valor), 0) FROM doacoes WHERE usuario_id = ? AND status = "confirmada"');
$stmt->execute([$usuario_id]);
$stats = $stmt->fetch(PDO::FETCH_NUM);
$total_doacoes_confirmadas = $stats[0];
$valor_total_doado = $stats[1];

include '_header_usuario.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-heart text-danger me-2"></i>Minhas Doações</h2>
</div>

<!-- Estatísticas -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h3 class="card-title"><?= $total_doacoes_confirmadas ?></h3>
                        <p class="card-text">Doações Realizadas</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-heart fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h3 class="card-title">R$ <?= number_format($valor_total_doado, 2, ',', '.') ?></h3>
                        <p class="card-text">Total Doado</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-dollar-sign fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">Todos os status</option>
                    <option value="pendente" <?= $status_filter === 'pendente' ? 'selected' : '' ?>>Pendente</option>
                    <option value="confirmada" <?= $status_filter === 'confirmada' ? 'selected' : '' ?>>Confirmada</option>
                    <option value="cancelada" <?= $status_filter === 'cancelada' ? 'selected' : '' ?>>Cancelada</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Buscar Campanha</label>
                <input type="text" name="search" value="<?= htmlspecialchars($search_filter) ?>" class="form-control" placeholder="Nome da campanha">
            </div>
            <div class="col-md-3">
                <label class="form-label">Data</label>
                <input type="date" name="data" value="<?= htmlspecialchars($data_filter) ?>" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-1"></i>Filtrar
                    </button>
                    <a href="minhas_doacoes.php" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>Limpar
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<?php if (empty($doacoes)): ?>
    <div class="text-center py-5">
        <i class="fas fa-heart fa-3x text-muted mb-3"></i>
        <h4>Nenhuma doação encontrada</h4>
        <p class="text-muted">Você ainda não fez nenhuma doação<?= $status_filter || $search_filter || $data_filter ? ' com os filtros aplicados' : '' ?>.</p>
        <a href="index.php" class="btn btn-primary">
            <i class="fas fa-search me-2"></i>Explorar Campanhas
        </a>
    </div>
<?php else: ?>
    <!-- Lista de Doações -->
    <div class="row">
        <?php foreach ($doacoes as $doacao): ?>
            <div class="col-12 mb-3">
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-2">
                                <?php if ($doacao['campanha_imagem']): ?>
                                    <img src="<?= htmlspecialchars($doacao['campanha_imagem']) ?>" 
                                         alt="Imagem da Campanha" 
                                         class="img-fluid rounded" 
                                         style="height: 80px; object-fit: cover; width: 100%;">
                                <?php else: ?>
                                    <div class="bg-light d-flex align-items-center justify-content-center rounded" style="height: 80px;">
                                        <i class="fas fa-image text-muted"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-4">
                                <h6 class="mb-1"><?= htmlspecialchars($doacao['campanha_titulo']) ?></h6>
                                <small class="text-muted">Por: <?= htmlspecialchars($doacao['campanha_criador']) ?></small>
                            </div>
                            <div class="col-md-2">
                                <div class="text-center">
                                    <h5 class="mb-0 text-success">R$ <?= number_format($doacao['valor'], 2, ',', '.') ?></h5>
                                    <small class="text-muted">Valor</small>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="text-center">
                                    <?php
                                    $status_class = match($doacao['status']) {
                                        'confirmada' => 'success',
                                        'pendente' => 'warning',
                                        'cancelada' => 'danger',
                                        default => 'secondary'
                                    };
                                    ?>
                                    <span class="badge bg-<?= $status_class ?>"><?= ucfirst($doacao['status']) ?></span>
                                    <br><small class="text-muted"><?= date('d/m/Y', strtotime($doacao['criado_em'])) ?></small>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="d-flex gap-1 justify-content-end">
                                    <a href="campanha.php?id=<?= $doacao['campanha_id'] ?>" 
                                       class="btn btn-sm btn-outline-primary" 
                                       title="Ver Campanha">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if ($doacao['status'] === 'confirmada'): ?>
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-secondary" 
                                                onclick="gerarRecibo(<?= $doacao['id'] ?>)"
                                                title="Gerar Recibo">
                                            <i class="fas fa-receipt"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <?php if ($doacao['mensagem']): ?>
                            <div class="mt-3 pt-3 border-top">
                                <small class="text-muted">Mensagem:</small>
                                <p class="mb-0"><?= htmlspecialchars($doacao['mensagem']) ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Paginação -->
    <?php if ($total_pages > 1): ?>
        <nav aria-label="Navegação das doações">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page-1 ?>&status=<?= urlencode($status_filter) ?>&search=<?= urlencode($search_filter) ?>&data=<?= urlencode($data_filter) ?>">
                            <i class="fas fa-chevron-left"></i> Anterior
                        </a>
                    </li>
                <?php endif; ?>
                
                <?php for ($i = max(1, $page-2); $i <= min($total_pages, $page+2); $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&status=<?= urlencode($status_filter) ?>&search=<?= urlencode($search_filter) ?>&data=<?= urlencode($data_filter) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page+1 ?>&status=<?= urlencode($status_filter) ?>&search=<?= urlencode($search_filter) ?>&data=<?= urlencode($data_filter) ?>">
                            Próxima <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>
<?php endif; ?>

<script>
function gerarRecibo(doacaoId) {
    // Implementar geração de recibo
    showToast('Funcionalidade de recibo em desenvolvimento', 'info');
}
</script>

<?php include '_footer_usuario.php'; ?>