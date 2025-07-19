<?php
require 'db.php';
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}
// Aprovação/reprovação
if (isset($_GET['aprovar'])) {
    $id = intval($_GET['aprovar']);
    $pdo->prepare('UPDATE campanhas SET status="aprovada" WHERE id=?')->execute([$id]);
}
if (isset($_GET['reprovar'])) {
    $id = intval($_GET['reprovar']);
    $pdo->prepare('UPDATE campanhas SET status="reprovada" WHERE id=?')->execute([$id]);
}
$stmt = $pdo->query('SELECT * FROM campanhas ORDER BY criado_em DESC');
$campanhas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php include '_head.php'; ?>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
    <?php include 'sidebar.php'; ?>
    <div class="content-wrapper">
        <section class="content-header"><div class="container-fluid"><h1>Campanhas</h1></div></section>
        <section class="content">
            <div class="container-fluid">
                <a href="index.php">Voltar ao painel</a> | <a href="nova_campanha.php">Nova Campanha</a>
                <table class="table table-bordered table-hover mt-3">
                    <tr><th>ID</th><th>Título</th><th>Meta</th><th>Arrecadado</th><th>Status</th><th>Ações</th></tr>
                    <?php foreach ($campanhas as $c): ?>
                    <tr>
                        <td><?= $c['id'] ?></td>
                        <td><?= htmlspecialchars($c['titulo']) ?></td>
                        <td>R$ <?= number_format($c['meta'],2,',','.') ?></td>
                        <td>R$ <?= number_format($c['arrecadado'],2,',','.') ?></td>
                        <td><?= $c['status'] ?? 'pendente' ?></td>
                        <td>
                            <a href="editar_campanha.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-primary">Editar</a>
                            <a href="excluir_campanha.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Excluir campanha?')">Excluir</a>
                            <?php if (($c['status'] ?? 'pendente') !== 'aprovada'): ?>
                                <a href="?aprovar=<?= $c['id'] ?>" class="btn btn-sm btn-success">Aprovar</a>
                            <?php endif; ?>
                            <?php if (($c['status'] ?? 'pendente') !== 'reprovada'): ?>
                                <a href="?reprovar=<?= $c['id'] ?>" class="btn btn-sm btn-warning">Reprovar</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </section>
    </div>
</div>
<?php include '_footer.php'; ?> 