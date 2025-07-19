<?php
require 'admin/db.php';
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}
$id = $_SESSION['usuario_id'];
// Marcar notificações como lidas
$pdo->prepare('UPDATE notificacoes SET lida=1 WHERE usuario_id=?')->execute([$id]);
// Buscar notificações
$stmt = $pdo->prepare('SELECT * FROM notificacoes WHERE usuario_id=? ORDER BY criado_em DESC');
$stmt->execute([$id]);
$notificacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php include '_header.php'; ?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-body">
                    <h2 class="mb-4">Notificações</h2>
                    <?php if (count($notificacoes) === 0): ?>
                        <p>Nenhuma notificação.</p>
                    <?php else: ?>
                        <ul class="list-group">
                        <?php foreach ($notificacoes as $n): ?>
                            <li class="list-group-item<?= $n['lida'] ? '' : ' font-weight-bold' ?>">
                                <b><?= htmlspecialchars($n['titulo']) ?></b><br>
                                <?= nl2br(htmlspecialchars($n['mensagem'])) ?><br>
                                <small class="text-muted"><?= date('d/m/Y H:i', strtotime($n['criado_em'])) ?></small>
                            </li>
                        <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div> 