<?php
require_once 'admin/db.php';
$menus = $pdo->query('SELECT * FROM menus ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
$textos = [];
foreach ($pdo->query('SELECT * FROM textos')->fetchAll(PDO::FETCH_ASSOC) as $t) {
    $textos[$t['chave']] = $t['valor'];
}
session_start();
?>
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-4">
  <div class="container">
    <a class="navbar-brand font-weight-bold" href="index.html">Tamo Junto</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ml-auto">
        <?php foreach ($menus as $m): ?>
          <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars($m['url']) ?>"><?= htmlspecialchars($m['nome']) ?></a></li>
        <?php endforeach; ?>
        <?php if (isset($_SESSION['usuario_id'])): ?>
          <li class="nav-item"><a class="nav-link" href="notificacoes.php">Notificações</a></li>
          <li class="nav-item"><a class="nav-link" href="perfil.php">Perfil</a></li>
          <li class="nav-item"><a class="nav-link" href="area_usuario.php">Minha Área</a></li>
          <li class="nav-item"><a class="nav-link" href="logout_usuario.php">Sair</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="entrar.php">Entrar</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
<div class="container mb-3">
  <div class="row">
    <div class="col-md-4"><b>WhatsApp:</b> <?= htmlspecialchars($textos['whatsapp'] ?? '') ?></div>
    <div class="col-md-4"><b>E-mail:</b> <?= htmlspecialchars($textos['email'] ?? '') ?></div>
    <div class="col-md-4"><b>Taxa do site:</b> <?= htmlspecialchars($textos['taxa'] ?? '') ?>%</div>
  </div>
</div>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script> 