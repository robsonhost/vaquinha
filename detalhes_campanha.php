<?php
require 'admin/db.php';
$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare('SELECT c.*, u.nome as criador FROM campanhas c JOIN usuarios u ON c.usuario_id = u.id WHERE c.id = ?');
$stmt->execute([$id]);
$c = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$c) {
    echo 'Campanha não encontrada!';
    exit;
}
?>
<?php include '_header.php'; ?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <?php if ($c['imagem']): ?><img src="<?= htmlspecialchars($c['imagem']) ?>" class="card-img-top" alt="Imagem da campanha"><?php endif; ?>
                <div class="card-body">
                    <h2 class="card-title mb-3"><?= htmlspecialchars($c['titulo']) ?></h2>
                    <p class="mb-2"><b>Criador:</b> <?= htmlspecialchars($c['criador']) ?></p>
                    <p><?= nl2br(htmlspecialchars($c['descricao'])) ?></p>
                    <div class="progress mb-2">
                        <div class="progress-bar" role="progressbar" style="width: <?= min(100, $c['arrecadado']/$c['meta']*100) ?>%"></div>
                    </div>
                    <p><b>Arrecadado:</b> R$ <?= number_format($c['arrecadado'],2,',','.') ?> / R$ <?= number_format($c['meta'],2,',','.') ?></p>
                    <a href="doar.php?id=<?= $c['id'] ?>" class="btn btn-success btn-lg">Doar</a>
                    <a href="vaquinhas.php" class="btn btn-link">Voltar</a>
                </div>
            </div>
        </div>
    </div>
</div> 