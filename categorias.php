<?php
require 'admin/db.php';
// Buscar categorias
$categorias = $pdo->query('SELECT * FROM categorias ORDER BY nome')->fetchAll(PDO::FETCH_ASSOC);
// Buscar campanhas por categoria
$categoria_id = $_GET['categoria_id'] ?? null;
$campanhas = [];
if ($categoria_id) {
    $stmt = $pdo->prepare('SELECT c.*, u.nome as criador FROM campanhas c JOIN usuarios u ON c.usuario_id = u.id WHERE c.categoria_id = ? ORDER BY c.criado_em DESC');
    $stmt->execute([$categoria_id]);
    $campanhas = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<?php include '_header.php'; ?>
<div class="container py-5">
    <h1 class="mb-4">Categorias</h1>
    <div class="row mb-4">
        <?php foreach ($categorias as $cat): ?>
            <div class="col-md-3 mb-2">
                <a href="?categoria_id=<?= $cat['id'] ?>" class="btn btn-outline-primary btn-block">
                    <?= htmlspecialchars($cat['nome']) ?>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
    <?php if ($categoria_id): ?>
        <h3>Campanhas da categoria: <b><?= htmlspecialchars($categorias[array_search($categoria_id, array_column($categorias, 'id'))]['nome']) ?></b></h3>
        <div class="row">
            <?php if (count($campanhas) === 0): ?>
                <div class="col-12"><p>Nenhuma campanha nesta categoria.</p></div>
            <?php else: ?>
                <?php foreach ($campanhas as $c): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <?php if ($c['imagem']): ?><img src="<?= htmlspecialchars($c['imagem']) ?>" class="card-img-top" alt="Imagem da campanha"><?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($c['titulo']) ?></h5>
                            <p class="card-text"><?= nl2br(htmlspecialchars($c['descricao'])) ?></p>
                            <p class="mb-1"><b>Criador:</b> <?= htmlspecialchars($c['criador']) ?></p>
                            <div class="progress mb-2">
                                <div class="progress-bar" role="progressbar" style="width: <?= min(100, $c['arrecadado']/$c['meta']*100) ?>%"></div>
                            </div>
                            <p><b>Arrecadado:</b> R$ <?= number_format($c['arrecadado'],2,',','.') ?> / R$ <?= number_format($c['meta'],2,',','.') ?></p>
                            <a href="detalhes_campanha.php?id=<?= $c['id'] ?>" class="btn btn-primary btn-block">Ver detalhes</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div> 