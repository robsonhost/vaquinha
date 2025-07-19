<?php
require 'admin/db.php';
$busca = trim($_GET['busca'] ?? '');
$sql = 'SELECT c.*, u.nome as criador FROM campanhas c JOIN usuarios u ON c.usuario_id = u.id WHERE 1';
$params = [];
if ($busca) {
    $sql .= ' AND (c.titulo LIKE ? OR c.descricao LIKE ?)';
    $params[] = "%$busca%";
    $params[] = "%$busca%";
}
$sql .= ' ORDER BY c.criado_em DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$campanhas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php include '_header.php'; ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Vaquinhas - Tamo Junto</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container py-5">
    <h1 class="mb-4">Vaquinhas</h1>
    <form class="form-inline mb-4" method="get">
        <input type="text" name="busca" class="form-control mr-2" placeholder="Buscar campanha..." value="<?= htmlspecialchars($busca) ?>">
        <button type="submit" class="btn btn-primary">Buscar</button>
    </form>
    <div class="row">
        <?php if (count($campanhas) === 0): ?>
            <div class="col-12"><p>Nenhuma campanha encontrada.</p></div>
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
                        <!-- Futuro: botão doar -->
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
</body>
</html> 