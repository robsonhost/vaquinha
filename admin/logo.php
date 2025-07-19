<?php
require 'db.php';
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}
// Upload de logo
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
    $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
    $nome_arquivo = 'logo_' . uniqid() . '.' . $ext;
    move_uploaded_file($_FILES['logo']['tmp_name'], '../images/' . $nome_arquivo);
    $caminho = 'images/' . $nome_arquivo;
    $pdo->prepare('UPDATE logo_site SET caminho=? WHERE id=1')->execute([$caminho]);
    $msg = 'Logo atualizada com sucesso!';
}
$logo = $pdo->query('SELECT caminho FROM logo_site WHERE id=1')->fetchColumn();
?>
<?php include '_head.php'; ?>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
    <?php include 'sidebar.php'; ?>
    <div class="content-wrapper">
        <section class="content-header"><div class="container-fluid"><h1>Logo do Site</h1></div></section>
        <section class="content">
            <div class="container-fluid">
                <?php if ($msg): ?><div class="alert alert-success"> <?= $msg ?> </div><?php endif; ?>
                <form method="post" enctype="multipart/form-data" class="mb-4">
                    <div class="form-group">
                        <label>Logo atual:</label><br>
                        <img src="../<?= htmlspecialchars($logo) ?>" alt="Logo atual" style="max-width:200px;max-height:100px;">
                    </div>
                    <div class="form-group">
                        <label>Nova logo:</label>
                        <input type="file" name="logo" class="form-control-file" accept="image/*" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Atualizar Logo</button>
                </form>
            </div>
        </section>
    </div>
</div>
<?php include '_footer.php'; ?> 