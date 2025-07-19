<?php
require 'db.php';
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $termos = $_POST['termos'] ?? '';
    $politica = $_POST['politica'] ?? '';
    $pdo->prepare('UPDATE textos_legais SET valor=? WHERE chave="termos"')->execute([$termos]);
    $pdo->prepare('UPDATE textos_legais SET valor=? WHERE chave="politica"')->execute([$politica]);
    $msg = 'Textos atualizados!';
}
$termos = $pdo->query('SELECT valor FROM textos_legais WHERE chave="termos"')->fetchColumn();
$politica = $pdo->query('SELECT valor FROM textos_legais WHERE chave="politica"')->fetchColumn();
?>
<?php include '_head.php'; ?>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
    <?php include 'sidebar.php'; ?>
    <div class="content-wrapper">
        <section class="content-header"><div class="container-fluid"><h1>Textos Legais</h1></div></section>
        <section class="content">
            <div class="container-fluid">
                <?php if ($msg): ?><div class="alert alert-success"> <?= $msg ?> </div><?php endif; ?>
                <form method="post">
                    <div class="form-group">
                        <label>Termos de Uso</label>
                        <textarea name="termos" class="form-control" rows="8" required><?= htmlspecialchars($termos) ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Política de Privacidade</label>
                        <textarea name="politica" class="form-control" rows="8" required><?= htmlspecialchars($politica) ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Salvar Textos</button>
                </form>
            </div>
        </section>
    </div>
</div>
<?php include '_footer.php'; ?> 